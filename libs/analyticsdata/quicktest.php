<?php
require_once __DIR__ . '/vendor/autoload.php';

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Protobuf\Duration;

// Set up authentication
$refreshToken = '1//0g8iJenE7_SJOCgYIARAAGBASNwF-L9IrKtl7n2VFZkTiGY20xEgxcREyIKL8qGvECF8dNjLbjr3yP0-wNVHb-sr8MUmxAveyVWU';
$credentials = new UserRefreshCredentials(['https://www.googleapis.com/auth/analytics.readonly'], [
    'client_id' => '489522383884-26fv8l9obitp09f4r0tg7j8ccg2naobv.apps.googleusercontent.com',
    'client_secret' => 'ypnxdlnLoW2A8jXakgVr93Q8',
    'refresh_token' => $refreshToken,
]);

// Initialize the client
$client = new BetaAnalyticsDataClient(['credentials' => $credentials]);

// Define the date range for your query
$dateRange = new DateRange();
$dateRange->setStartDate('2024-01-19');
$dateRange->setEndDate('2024-01-20');

// Define the metrics and dimensions you want to retrieve
$metrics = [
    new Metric(['name' => 'activeUsers']),
    new Metric(['name' => 'newUsers']),
    new Metric(['name' => 'sessions']),
    new Metric(['name' => 'bounceRate']),
    new Metric(['name' => 'averageSessionDuration']),
    new Metric(['name' => 'conversions']),
];

$dimensions = [
    new Dimension(['name' => 'country']),
];

// Make the request
$response = $client->runReport([
    'property' => 'properties/334420094',
    'dateRanges' => [$dateRange],
    'dimensions' => $dimensions,
    'metrics' => $metrics,
]);

// Extract and print the results
$rows = $response->getRows();
foreach ($rows as $row) {
    $dimensionValues = $row->getDimensionValues();
    $metricValues = $row->getMetricValues();

    //echo "<br>Date: " . date('Y-m-d', strtotime($dimensionValues[0]->getValue())) . PHP_EOL;
    echo "<br>Country: " . $dimensionValues[0]->getValue() . PHP_EOL;
    echo "<br>Users: " . $metricValues[0]->getValue() . PHP_EOL;
    echo "<br>New Users: " . $metricValues[1]->getValue() . PHP_EOL;
    echo "<br>Sessions: " . $metricValues[2]->getValue() . PHP_EOL;
    echo "<br>bounceRate: " . $metricValues[3]->getValue() . PHP_EOL;
    echo "<br>averageSessionDuration: " . $metricValues[4]->getValue() . PHP_EOL;
    echo "<br>conversions: " . $metricValues[5]->getValue() . PHP_EOL;
    echo "<br>-----<br>" . PHP_EOL;
}
