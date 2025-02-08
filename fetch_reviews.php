<?php

function fetchReviews($appId) {
    $reviews = [];
    $cursor = '*';
    $hasMore = true;
    $maxReviews = 1000; // Maximum number of reviews to fetch

    while ($hasMore && count($reviews) < $maxReviews) {
        $url = "https://store.steampowered.com/appreviews/{$appId}?json=1&filter=updated&cursor=" . urlencode($cursor) . "&num_per_page=50";
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['reviews']) && is_array($data['reviews'])) {
            $reviews = array_merge($reviews, $data['reviews']);
            $cursor = $data['cursor'];
            $hasMore = count($data['reviews']) > 0;
        } else {
            $hasMore = false;
        }

        // Respect API rate limits
        usleep(1000000); // 1-second delay
    }

    return $reviews;
}

function removeDuplicateReviews($reviews) {
    $uniqueReviews = [];
    $seenIds = [];

    foreach ($reviews as $review) {
        if (!in_array($review['recommendationid'], $seenIds)) {
            $uniqueReviews[] = $review;
            $seenIds[] = $review['recommendationid'];
        }
    }

    return $uniqueReviews;
}

function saveReviewsToFile($reviews, $filename = 'reviews.json') {
    $executionTimestamp = time(); // Unix timestamp format
    $data = [
        'execution_date' => $executionTimestamp,
        'reviews' => $reviews
    ];

    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Game App ID for "Ambulance Life: A Paramedic Simulator"
$appId = '1926520';

// Fetch, deduplicate, and save reviews
$reviews = fetchReviews($appId);
$uniqueReviews = removeDuplicateReviews($reviews);
saveReviewsToFile($uniqueReviews);

echo "Fetched and saved " . count($uniqueReviews) . " unique reviews to reviews.json.";

?>
