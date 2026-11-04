<?php

/**
 * Drives the real install and upgrade wizards over HTTP, against a live
 * web server + MySQL - the same way a real browser does, instead of
 * seeding the database directly and bypassing install/ entirely (which
 * is what the existing spTests CI job does). That gap is exactly why a
 * whole chain of real bugs in install/index.php and install/upgrade.php
 * (an unbounded telemetry DNS lookup, a blank-UI gap between "import
 * done" and the final page, a stale service worker intercepting the
 * install request, gzip compression breaking the live progress bar's
 * flush() calls, and a progress callback that flooded the page with
 * 1400+ inline <script> blocks on a large import) went undetected by
 * every existing automated check.
 *
 * This can't verify the page *visually* renders correctly in a real
 * browser (that needs actual browser automation, which this project
 * has no toolchain for) - but every one of the bugs above is also
 * detectable at the HTTP-response level, which is what this asserts:
 * the response must not be gzip-compressed, must contain the expected
 * success markers, must not contain error markers, and must not emit
 * an unreasonable number of progress-update scripts.
 *
 * Usage:
 *   php tests/install_upgrade_http_test.php
 *
 * Configuration (environment variables, all optional - defaults match
 * this project's own local Docker test setup):
 *   SEOPANEL_URL        base URL of a running install (default http://localhost:8090)
 *   SEOPANEL_WEBROOT    filesystem path to the webroot (default: repo root)
 *   SEOPANEL_DB_HOST    (default 127.0.0.1)
 *   SEOPANEL_DB_USER    (default root)
 *   SEOPANEL_DB_PASS    (default rootpass)
 *   SEOPANEL_DB_FRESH   scratch DB name for the fresh-install test (default sp_test_fresh_install)
 *   SEOPANEL_DB_UPGRADE scratch DB name for the upgrade test (default sp_test_upgrade)
 *   SEOPANEL_OLD_SEOPANEL_SQL  path to an older release's install/data/seopanel.sql,
 *                              used to seed the "before" database for the upgrade
 *                              test (e.g. `git show 6.0.0:install/data/seopanel.sql`
 *                              run separately - deliberately NOT done by this script,
 *                              so it has no dependency on git or repo history being
 *                              available wherever it runs)
 *   SEOPANEL_OLD_TEXTLANG_SQL  same, for install/data/textlang.sql
 *   SEOPANEL_SKIP_UPGRADE  set to "1" to skip the upgrade test (e.g. if the
 *                          two SEOPANEL_OLD_*_SQL seed files aren't available)
 */

$baseUrl    = rtrim(getenv('SEOPANEL_URL') ?: 'http://localhost:8090', '/');
$webRoot    = getenv('SEOPANEL_WEBROOT') ?: dirname(__DIR__);
$dbHost     = getenv('SEOPANEL_DB_HOST') ?: '127.0.0.1';
$dbUser     = getenv('SEOPANEL_DB_USER') ?: 'root';
$dbPass     = getenv('SEOPANEL_DB_PASS') ?: 'rootpass';
$dbFresh    = getenv('SEOPANEL_DB_FRESH') ?: 'sp_test_fresh_install';
$dbUpgrade  = getenv('SEOPANEL_DB_UPGRADE') ?: 'sp_test_upgrade';
$skipUpgrade = getenv('SEOPANEL_SKIP_UPGRADE') === '1';

// PHP 8.1+ defaults to throwing mysqli_sql_exception on query errors -
// this test deliberately probes for things that may not exist (e.g. an
// older schema version's columns), so query failures must be ordinary
// return values to handle, not uncaught exceptions
mysqli_report(MYSQLI_REPORT_OFF);

$failures = array();
$configFile = $webRoot . '/config/sp-config.php';

function check($cond, $label) {
	global $failures;
	if ($cond) {
		echo "  OK   $label\n";
	} else {
		echo "  FAIL $label\n";
		$failures[] = $label;
	}
}

function dbQuery($host, $user, $pass, $sql, $dbName = null) {
	$mysqli = @mysqli_connect($host, $user, $pass, $dbName);
	if (!$mysqli) {
		fwrite(STDERR, "DB connect failed: " . mysqli_connect_error() . "\n");
		exit(1);
	}
	$result = mysqli_query($mysqli, $sql);
	if ($result === false) {
		fwrite(STDERR, "Query failed: " . mysqli_error($mysqli) . "\nSQL: $sql\n");
		exit(1);
	}
	$row = null;
	if ($result instanceof mysqli_result) {
		$row = mysqli_fetch_assoc($result);
	}
	mysqli_close($mysqli);
	return $row;
}

function importSqlFile($host, $user, $pass, $dbName, $path) {
	$cmd = sprintf(
		'mysql --skip-ssl -h%s -u%s -p%s %s < %s 2>&1',
		escapeshellarg($host), escapeshellarg($user), escapeshellarg($pass),
		escapeshellarg($dbName), escapeshellarg($path)
	);
	exec($cmd, $out, $code);
	if ($code !== 0) {
		fwrite(STDERR, "Import of $path failed:\n" . implode("\n", $out) . "\n");
		exit(1);
	}
}

/**
 * @return array{code:int, headers:string, body:string}
 */
function httpPost($url, $fields, $cookieJar = null) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	// real browsers always send this - several of the bugs this test
	// guards against (mod_deflate compressing the response) are only
	// visible when it's present
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	if ($cookieJar) {
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
	}
	$response = curl_exec($ch);
	if ($response === false) {
		fwrite(STDERR, "curl error: " . curl_error($ch) . "\n");
		exit(1);
	}
	$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return array(
		'code' => $code,
		'headers' => substr($response, 0, $headerSize),
		'body' => substr($response, $headerSize),
	);
}

function writeConfig($configFile, $webPath, $dbName, $dbUser, $dbPass, $dbHost, $spInstalled) {
	$tpl = <<<PHP
<?php
define('SP_WEBPATH', '%s');
define('DB_NAME', '%s');
define('DB_USER', '%s');
define('DB_PASSWORD', '%s');
define('DB_HOST', '%s');
define('DB_ENGINE', 'mysql');
define('SP_INSTALLED', '%s');
define('SP_DEBUG', 0);
define('SP_TIMEOUT', 18000);
PHP;
	$contents = sprintf(
		$tpl,
		addslashes($webPath), addslashes($dbName), addslashes($dbUser),
		addslashes($dbPass), addslashes($dbHost), addslashes($spInstalled)
	);
	file_put_contents($configFile, $contents);
	chmod($configFile, 0666);
}

// ---------------------------------------------------------------------
// Test 1: fresh install, exactly as a real browser drives it
// ---------------------------------------------------------------------

echo "=== Fresh install smoke test ($dbFresh) ===\n";

dbQuery($dbHost, $dbUser, $dbPass, "DROP DATABASE IF EXISTS `$dbFresh`");
dbQuery($dbHost, $dbUser, $dbPass, "CREATE DATABASE `$dbFresh` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
file_put_contents($configFile, '');
chmod($configFile, 0666);

$resp = httpPost("$baseUrl/install/", array(
	'sec' => 'proceedinstall',
	'db_engine' => 'mysql',
	'db_host' => $dbHost,
	'db_name' => $dbFresh,
	'db_user' => $dbUser,
	'db_pass' => $dbPass,
	'email' => 'installtest@example.com',
));

check($resp['code'] === 200, 'HTTP 200');
check(stripos($resp['headers'], 'Content-Encoding: gzip') === false,
	'response is not gzip-compressed (mod_deflate buffers flush() output and breaks the live progress bar)');
check(strpos($resp['body'], 'Installation Complete') !== false,
	'reached the "Installation Complete" page');
check(strpos($resp['body'], 'Duplicate entry') === false,
	'no SQL duplicate-key errors during import');
check(strpos($resp['body'], 'already installed') === false,
	'did not hit the "already installed" guard');

$scriptCount = substr_count($resp['body'], 'installProgressFill');
check($scriptCount > 0 && $scriptCount < 200,
	"progress-update script count is sane ($scriptCount - regression guard against emitting 1400+ inline <script> blocks on a large import)");

$row = dbQuery($dbHost, $dbUser, $dbPass,
	"SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema='$dbFresh'");
check((int) $row['c'] > 50, "database fully populated ({$row['c']} tables)");

$row = dbQuery($dbHost, $dbUser, $dbPass, "SELECT username FROM users WHERE id=1", $dbFresh);
check(!empty($row) && $row['username'] === 'spadmin', 'default admin user created');

check(strpos(file_get_contents($configFile), "SP_INSTALLED") !== false,
	'config/sp-config.php was written with SP_INSTALLED set');

// ---------------------------------------------------------------------
// Test 2: upgrade from the previous major release (6.0.0 -> 7.0.0)
// ---------------------------------------------------------------------

if ($skipUpgrade) {
	echo "\n=== Upgrade smoke test skipped (SEOPANEL_SKIP_UPGRADE=1) ===\n";
} else {
	echo "\n=== Upgrade smoke test, 6.0.0 -> 7.0.0 ($dbUpgrade) ===\n";

	dbQuery($dbHost, $dbUser, $dbPass, "DROP DATABASE IF EXISTS `$dbUpgrade`");
	dbQuery($dbHost, $dbUser, $dbPass, "CREATE DATABASE `$dbUpgrade` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

	$oldSeopanelSql = getenv('SEOPANEL_OLD_SEOPANEL_SQL');
	$oldTextlangSql = getenv('SEOPANEL_OLD_TEXTLANG_SQL');
	$tmpDir = sys_get_temp_dir() . '/sp_upgrade_seed_' . getmypid();

	if (!$oldSeopanelSql || !$oldTextlangSql || !is_file($oldSeopanelSql) || !is_file($oldTextlangSql)) {
		fwrite(STDERR, "SEOPANEL_OLD_SEOPANEL_SQL/SEOPANEL_OLD_TEXTLANG_SQL not set to valid files - skipping the upgrade test.\n");
		fwrite(STDERR, "(extract them with e.g. `git show 6.0.0:install/data/seopanel.sql > /path/to/seopanel.sql`)\n");
	} else {
		mkdir($tmpDir);
		importSqlFile($dbHost, $dbUser, $dbPass, $dbUpgrade, $oldSeopanelSql);
		importSqlFile($dbHost, $dbUser, $dbPass, $dbUpgrade, $oldTextlangSql);

		writeConfig($configFile, "$baseUrl", $dbUpgrade, $dbUser, $dbPass, $dbHost, '6.0.0');

		// upgrade.php requires a real logged-in admin session
		// (Install::requireAdminSession()) - log in for real first,
		// exactly as a user would, and reuse that session's cookies
		$cookieJar = $tmpDir . '/cookies.txt';
		$loginResp = httpPost("$baseUrl/login.php", array(
			'sec' => 'login',
			'userName' => 'spadmin',
			'password' => 'spadmin',
		), $cookieJar);
		check($loginResp['code'] === 200 || $loginResp['code'] === 302,
			'logged in as spadmin against the seeded 6.0.0 database');

		$resp2 = httpPost("$baseUrl/install/upgrade.php", array(
			'sec' => 'proceedupgrade',
			'php_support' => 'green',
			'mysql_support' => 'green',
			'curl_support' => 'green',
			'config' => 'green',
			'db_support' => 'green',
		), $cookieJar);

		check($resp2['code'] === 200, 'HTTP 200');
		check(stripos($resp2['headers'], 'Content-Encoding: gzip') === false,
			'upgrade response is not gzip-compressed');
		check(strpos($resp2['body'], 'Upgrade Complete') !== false,
			'reached the "Upgrade Complete" page');
		check(strpos($resp2['body'], 'Admin login required') === false,
			'admin session was accepted (not bounced to the login-required guard)');

		$scriptCount2 = substr_count($resp2['body'], 'installProgressFill');
		check($scriptCount2 < 200,
			"upgrade progress-update script count is sane ($scriptCount2)");

		$row = dbQuery($dbHost, $dbUser, $dbPass,
			"SELECT set_val FROM settings WHERE set_name='SP_VERSION_NUMBER'", $dbUpgrade);
		check(!empty($row) && $row['set_val'] === '7.0.0',
			'post-upgrade database reports SP_VERSION_NUMBER=7.0.0 (got: ' . ($row['set_val'] ?? 'none') . ')');
	}

	exec('rm -rf ' . escapeshellarg($tmpDir));
}

// ---------------------------------------------------------------------

echo "\n";
if (!empty($failures)) {
	echo count($failures) . " FAILURE(S):\n";
	foreach ($failures as $f) {
		echo "  - $f\n";
	}
	exit(1);
}
echo "All checks passed.\n";
exit(0);
