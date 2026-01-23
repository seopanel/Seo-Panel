<?php
/**
 * Test page to verify getReviewDetails function
 * with active entries from review_links table
 */

// Load SEO Panel environment
include_once("includes/sp-load.php");
include_once(SP_CTRLPATH."/review_manager.ctrl.php");

// Create controller instance
$rmController = new ReviewManagerController();

// Get active review links from database
$sql = "SELECT id, name, type, url, status FROM review_links WHERE status = 1 LIMIT 10";
$linkList = $rmController->db->select($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Review Details Fetcher</title>
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
            border-bottom: 2px solid #28a745;
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
            background: #28a745;
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
        .rating-display {
            display: inline-block;
            background: #ffc107;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .reviews-display {
            display: inline-block;
            background: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .loading {
            color: #ffc107;
            font-style: italic;
        }
        .icon {
            margin-right: 5px;
        }
        .platform-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
            color: white;
        }
        .platform-google { background: #4285f4; }
        .platform-yelp { background: #d32323; }
        .platform-trustpilot { background: #00b67a; }
        .platform-tripadvisor { background: #00aa6c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⭐ Review Details Fetcher - Test Page</h1>
        <p>Testing <code>getReviewDetails()</code> function with active entries from <code>review_links</code> table</p>

        <?php if (empty($linkList)): ?>
            <div class="test-item">
                <p class="status-error">❌ No active review links found in the database.</p>
            </div>
        <?php else: ?>
            <p><strong>Found <?php echo count($linkList); ?> active review link(s)</strong></p>

            <?php foreach ($linkList as $index => $link): ?>
                <div class="test-item">
                    <div class="test-header">
                        <h3>
                            <span class="icon">
                                <?php
                                $icons = [
                                    'google' => '🔍',
                                    'yelp' => '📍',
                                    'trustpilot' => '⭐',
                                    'tripadvisor' => '✈️'
                                ];
                                echo isset($icons[$link['type']]) ? $icons[$link['type']] : '🔗';
                                ?>
                            </span>
                            Test #<?php echo ($index + 1); ?>: <?php echo htmlspecialchars($link['name']); ?>
                        </h3>
                    </div>

                    <div class="info-row">
                        <span class="label">ID:</span>
                        <span class="value"><?php echo $link['id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Platform:</span>
                        <span class="platform-badge platform-<?php echo $link['type']; ?>">
                            <?php echo ucfirst($link['type']); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">URL:</span>
                        <span class="value"><?php echo htmlspecialchars($link['url']); ?></span>
                    </div>

                    <?php
                    // Test the getReviewDetails function
                    echo "<div class='info-row'><span class='loading'>⏳ Fetching review details...</span></div>";
                    flush();

                    $startTime = microtime(true);
                    $result = $rmController->getReviewDetails($link['type'], $link['url']);
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
                            <span class="label">Reviews Count:</span>
                            <span class="reviews-display"><?php echo number_format($result['reviews']); ?> reviews</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Rating:</span>
                            <span class="rating-display">
                                <?php
                                $rating = $result['rating'];
                                echo number_format($rating, 1);
                                if ($rating > 0) {
                                    echo ' / 5.0 ';
                                    // Display stars
                                    $fullStars = floor($rating);
                                    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;

                                    for ($i = 0; $i < $fullStars; $i++) {
                                        echo '★';
                                    }
                                    if ($halfStar) {
                                        echo '☆';
                                    }
                                    for ($i = $fullStars + $halfStar; $i < 5; $i++) {
                                        echo '☆';
                                    }
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="label">Execution Time:</span>
                            <span class="value"><?php echo $executionTime; ?> seconds</span>
                        </div>

                        <?php if ($result['status']): ?>
                            <div class="info-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                <span class="label">✓ Review data fetched successfully!</span>
                            </div>
                        <?php else: ?>
                            <div class="info-row" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; color: #dc3545;">
                                <span class="label">⚠️ Check if the URL is accessible and the regex patterns are correct.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 30px; padding: 15px; background: #e8f5e9; border-left: 4px solid #28a745; border-radius: 4px;">
                <h4 style="margin-top: 0;">📊 Summary</h4>
                <?php
                $successCount = 0;
                $failCount = 0;
                $totalReviews = 0;
                $totalRating = 0;
                $ratingCount = 0;

                foreach ($linkList as $link) {
                    $result = $rmController->getReviewDetails($link['type'], $link['url']);
                    if ($result['status']) {
                        $successCount++;
                        $totalReviews += $result['reviews'];
                        if ($result['rating'] > 0) {
                            $totalRating += $result['rating'];
                            $ratingCount++;
                        }
                    } else {
                        $failCount++;
                    }
                }

                $avgRating = $ratingCount > 0 ? round($totalRating / $ratingCount, 2) : 0;
                ?>
                <p>
                    <strong>Total Tests:</strong> <?php echo count($linkList); ?><br>
                    <strong style="color: #28a745;">Successful:</strong> <?php echo $successCount; ?><br>
                    <strong style="color: #dc3545;">Failed:</strong> <?php echo $failCount; ?><br>
                    <strong>Total Reviews Found:</strong> <?php echo number_format($totalReviews); ?><br>
                    <strong>Average Rating:</strong> <?php echo $avgRating; ?> / 5.0
                </p>
            </div>

        <?php endif; ?>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
            <strong>ℹ️ Note:</strong> This test page calls the <code>getReviewDetails()</code> method from
            <code>ReviewManagerController</code> which fetches live data from review platforms.
            Results may vary depending on:
            <ul>
                <li>Network connectivity and firewall settings</li>
                <li>Review platform availability and anti-scraping measures</li>
                <li>Correctness of regex patterns in the crawl_engine_category table</li>
                <li>Rate limiting by review platforms</li>
                <li>Changes in page structure by the platforms</li>
            </ul>
            <p><strong>Supported Platforms:</strong></p>
            <ul>
                <li>🔍 <strong>Google My Business</strong> - Business reviews and ratings</li>
                <li>📍 <strong>Yelp</strong> - Local business reviews</li>
                <li>⭐ <strong>Trustpilot</strong> - Company reviews and trust scores</li>
                <li>✈️ <strong>TripAdvisor</strong> - Travel and hospitality reviews</li>
            </ul>
        </div>
    </div>
</body>
</html>
