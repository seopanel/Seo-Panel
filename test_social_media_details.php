<?php
/**
 * Test page to verify getSocialMediaDetails function
 * with active entries from social_media_links table
 */

// Load SEO Panel environment
include_once("includes/sp-load.php");
include_once(SP_CTRLPATH."/social_media.ctrl.php");

// Create controller instance
$smController = new SocialMediaController();

// Get active social media links from database
$sql = "SELECT id, name, type, url, status FROM social_media_links WHERE status = 1 LIMIT 5";
$linkList = $smController->db->select($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Social Media Details Fetcher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .test-item {
            border: 1px solid #ddd;
            margin: 15px 0;
            padding: 15px;
            border-radius: 5px;
            background: #fafafa;
        }
        .test-header {
            background: #007bff;
            color: white;
            padding: 10px;
            margin: -15px -15px 15px -15px;
            border-radius: 5px 5px 0 0;
        }
        .test-header h3 {
            margin: 0;
        }
        .result {
            margin-top: 10px;
            padding: 10px;
            border-left: 4px solid #28a745;
            background: white;
        }
        .result.error {
            border-left-color: #dc3545;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
            margin-left: 10px;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .info-row {
            padding: 5px 0;
        }
        .test-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .test-button:hover {
            background: #0056b3;
        }
        .loading {
            color: #ffc107;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Social Media Details Fetcher - Test Page</h1>
        <p>Testing <code>getSocialMediaDetails()</code> function with active entries from <code>social_media_links</code> table</p>

        <?php if (empty($linkList)): ?>
            <div class="test-item">
                <p class="status-error">❌ No active social media links found in the database.</p>
            </div>
        <?php else: ?>
            <p><strong>Found <?php echo count($linkList); ?> active social media link(s)</strong></p>

            <?php foreach ($linkList as $index => $link): ?>
                <div class="test-item">
                    <div class="test-header">
                        <h3>Test #<?php echo ($index + 1); ?>: <?php echo htmlspecialchars($link['name']); ?></h3>
                    </div>

                    <div class="info-row">
                        <span class="label">ID:</span>
                        <span class="value"><?php echo $link['id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Type:</span>
                        <span class="value"><?php echo htmlspecialchars($link['type']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">URL:</span>
                        <span class="value"><?php echo htmlspecialchars($link['url']); ?></span>
                    </div>

                    <?php
                    // Test the getSocialMediaDetails function
                    echo "<div class='info-row'><span class='loading'>⏳ Fetching details...</span></div>";
                    flush();

                    $startTime = microtime(true);
                    $result = $smController->getSocialMediaDetails($link['type'], $link['url']);
                    $endTime = microtime(true);
                    $executionTime = round(($endTime - $startTime), 2);
                    ?>

                    <div class="result <?php echo $result['status'] ? '' : 'error'; ?>">
                        <div class="info-row">
                            <span class="label">Status:</span>
                            <span class="<?php echo $result['status'] ? 'status-success' : 'status-error'; ?>">
                                <?php echo $result['status'] ? '✅ Success' : '❌ Failed'; ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Message:</span>
                            <span class="value"><?php echo htmlspecialchars($result['msg']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Followers:</span>
                            <span class="value"><?php echo number_format($result['followers']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Likes:</span>
                            <span class="value"><?php echo number_format($result['likes']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Execution Time:</span>
                            <span class="value"><?php echo $executionTime; ?> seconds</span>
                        </div>

                        <?php if ($result['status']): ?>
                            <div class="info-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                <span class="label">✓ Data fetched successfully!</span>
                            </div>
                        <?php else: ?>
                            <div class="info-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; color: #dc3545;">
                                <span class="label">⚠️ Check if the URL is accessible and the regex patterns are correct.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">
                <h4 style="margin-top: 0;">📊 Summary</h4>
                <?php
                $successCount = 0;
                $failCount = 0;

                foreach ($linkList as $link) {
                    $result = $smController->getSocialMediaDetails($link['type'], $link['url']);
                    if ($result['status']) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                ?>
                <p>
                    <strong>Total Tests:</strong> <?php echo count($linkList); ?><br>
                    <strong style="color: #28a745;">Successful:</strong> <?php echo $successCount; ?><br>
                    <strong style="color: #dc3545;">Failed:</strong> <?php echo $failCount; ?>
                </p>
            </div>

        <?php endif; ?>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
            <strong>ℹ️ Note:</strong> This test page calls the <code>getSocialMediaDetails()</code> method from
            <code>SocialMediaController</code> which fetches live data from social media platforms.
            Results may vary depending on:
            <ul>
                <li>Network connectivity</li>
                <li>Social media platform availability</li>
                <li>Correctness of regex patterns in the database</li>
                <li>Rate limiting by social media platforms</li>
            </ul>
        </div>
    </div>
</body>
</html>
