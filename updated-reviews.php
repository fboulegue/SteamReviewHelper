<?php
function loadLastExecutionDate($filename = 'reviews.json') {
    if (file_exists($filename)) {
        $data = json_decode(file_get_contents($filename), true);
        return $data['execution_date'] ?? 0;
    }
    return 0;
}

function fetchUpdatedReviews($appId, $lastExecutionDate) {
    $reviews = [];
    $cursor = '*';
    $params = [
        'json' => 1,
        'filter' => 'updated',
        'cursor' => $cursor,
        'num_per_page' => 50
    ];

    echo "Checking for updates since " . date('Y-m-d H:i:s', $lastExecutionDate) . "<br>";

    while (true) {
        $query = http_build_query($params);
        $url = "https://store.steampowered.com/appreviews/{$appId}?{$query}";

        $response = @file_get_contents($url);
        if ($response === FALSE) {
            echo "Request error. Retrying in 5 seconds...<br>";
            sleep(5);
            continue;
        }

        $data = json_decode($response, true);
        if (empty($data['reviews'])) {
            echo "No more updated reviews found.<br>";
            break;
        }

        foreach ($data['reviews'] as $review) {
            if ($review['timestamp_updated'] <= $lastExecutionDate) {
                echo "Reached reviews before the last execution date.<br>";
                return $reviews;
            }
            $reviews[] = $review;
        }

        $cursor = $data['cursor'] ?? '';
        if (empty($cursor) || $cursor === '*') {
            echo "No cursor found or reached the end.<br>";
            break;
        }

        $params['cursor'] = $cursor;
        sleep(1);
    }

    return $reviews;
}

function updateReviewsFiles($newReviews, $reviewsFilename = 'reviews.json', $updatedFilename = 'updated-reviews.json') {
    $existingReviews = [];
    if (file_exists($reviewsFilename)) {
        $data = json_decode(file_get_contents($reviewsFilename), true);
        $existingReviews = $data['reviews'] ?? [];
    }

    $allReviews = array_merge($existingReviews, $newReviews);
    $uniqueReviews = array_values(array_column(array_reverse($allReviews), null, 'recommendationid'));

    $executionTimestamp = time();

    $updatedData = [
        'execution_date' => $executionTimestamp,
        'reviews' => $newReviews
    ];
    file_put_contents($updatedFilename, json_encode($updatedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $fullData = [
        'execution_date' => $executionTimestamp,
        'reviews' => $uniqueReviews
    ];
    file_put_contents($reviewsFilename, json_encode($fullData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "Fetched and saved " . count($newReviews) . " new reviews to {$updatedFilename}. Total reviews: " . count($uniqueReviews) . " in {$reviewsFilename}.<br>";
}

$appId = '1926520';
$lastExecutionDate = loadLastExecutionDate();

if ($lastExecutionDate === 0) {
    echo "No previous execution date found. Please run the full fetch script first.<br>";
} else {
    $newReviews = fetchUpdatedReviews($appId, $lastExecutionDate);
    updateReviewsFiles($newReviews);
}
?>
