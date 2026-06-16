<?php
/**
 * OAuth2 callback handler for ArticleSubmitter plugin external service connections.
 *
 * Handles return redirects from:
 *   - WordPress.com  (?service=wordpress)
 *   - Tumblr         (?service=tumblr)
 *   - Blogger        (?service=blogger)  — delegated to Google OAuth via admin-panel
 */

include_once("includes/sp-load.php");

// Must be logged in
$userId = isLoggedIn();
if (!$userId) {
    header('Location: ' . SP_WEBPATH . '/index.php');
    exit;
}

$service  = isset($_GET['service']) ? $_GET['service'] : '';
$code     = isset($_GET['code'])    ? $_GET['code']    : '';
$state    = isset($_GET['state'])   ? $_GET['state']   : '';  // plugin_id stored here
$pluginId = intval($state);

// Load ArticleSubmitter plugin settings manually
include_once(SP_CTRLPATH . "/user-token.ctrl.php");

// Load plugin settings constants from as_settings
global $dbObj;
$settingsList = $dbObj->select("SELECT set_name, set_val FROM as_settings");
if (!empty($settingsList)) {
    foreach ($settingsList as $row) {
        if (!defined($row['set_name'])) {
            define($row['set_name'], $row['set_val']);
        }
    }
}

// Default constants if not yet defined
if (!defined('AS_WORDPRESS_CLIENT_ID'))     define('AS_WORDPRESS_CLIENT_ID', '');
if (!defined('AS_WORDPRESS_CLIENT_SECRET')) define('AS_WORDPRESS_CLIENT_SECRET', '');
if (!defined('AS_TUMBLR_CONSUMER_KEY'))     define('AS_TUMBLR_CONSUMER_KEY', '');
if (!defined('AS_TUMBLR_CONSUMER_SECRET'))  define('AS_TUMBLR_CONSUMER_SECRET', '');

$redirectBase = SP_WEBPATH . '/seo-plugins.php?pid=' . $pluginId . '&action=website';

switch ($service) {

    case 'wordpress':
        if (empty($code)) {
            header('Location: ' . $redirectBase . '&connect_error=missing_code');
            exit;
        }

        include_once(SP_PLUGINPATH . "/ArticleSubmitter/libs/WordpressComAPI.php");
        $redirectUri = SP_WEBPATH . '/as-connect.php?service=wordpress';
        $tokenData   = WordpressComAPI::getAccessToken(
            $code,
            AS_WORDPRESS_CLIENT_ID,
            AS_WORDPRESS_CLIENT_SECRET,
            $redirectUri
        );

        if (!empty($tokenData['access_token'])) {
            $tokenCtrler = new UserTokenController();
            // Remove any existing WordPress.com token for this user
            $tokenCtrler->deleteAllUserTokens($userId, 'wordpress');
            // Insert new token
            $tokenInfo = [
                'user_id'        => $userId,
                'access_token'   => $tokenData['access_token'],
                'refresh_token'  => $tokenData['refresh_token'] ?? '',
                'token_type'     => $tokenData['token_type']    ?? 'bearer',
                'expires_in'     => $tokenData['expires_in']    ?? 3600,
                'token_category' => 'wordpress',
                'created'        => date('Y-m-d H:i:s'),
            ];
            $tokenCtrler->insertUserToken($tokenInfo);
            header('Location: ' . $redirectBase . '&connected=wordpress');
        } else {
            $errMsg = urlencode($tokenData['error_description'] ?? 'WordPress.com connection failed');
            header('Location: ' . $redirectBase . '&connect_error=' . $errMsg);
        }
        exit;

    case 'tumblr':
        if (empty($code)) {
            header('Location: ' . $redirectBase . '&connect_error=missing_code');
            exit;
        }

        include_once(SP_PLUGINPATH . "/ArticleSubmitter/libs/TumblrComAPI.php");
        $redirectUri = SP_WEBPATH . '/as-connect.php?service=tumblr';
        $tokenData   = TumblrComAPI::getAccessToken(
            $code,
            AS_TUMBLR_CONSUMER_KEY,
            AS_TUMBLR_CONSUMER_SECRET,
            $redirectUri
        );

        if (!empty($tokenData['access_token'])) {
            $tokenCtrler = new UserTokenController();
            $tokenCtrler->deleteAllUserTokens($userId, 'tumblr');
            $tokenInfo = [
                'user_id'        => $userId,
                'access_token'   => $tokenData['access_token'],
                'refresh_token'  => $tokenData['refresh_token'] ?? '',
                'token_type'     => $tokenData['token_type']    ?? 'bearer',
                'expires_in'     => $tokenData['expires_in']    ?? 3600,
                'token_category' => 'tumblr',
                'created'        => date('Y-m-d H:i:s'),
            ];
            $tokenCtrler->insertUserToken($tokenInfo);
            header('Location: ' . $redirectBase . '&connected=tumblr');
        } else {
            $errMsg = urlencode($tokenData['error_description'] ?? 'Tumblr connection failed');
            header('Location: ' . $redirectBase . '&connect_error=' . $errMsg);
        }
        exit;

    case 'blogger':
        // Blogger uses sp's existing Google OAuth — redirect to the Connections page
        header('Location: ' . SP_WEBPATH . '/admin-panel.php?sec=connections');
        exit;

    default:
        header('Location: ' . $redirectBase);
        exit;
}
