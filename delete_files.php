<?php
$files = ['reviews.json', 'updated-reviews.json'];

$errors = [];

foreach ($files as $file) {
    if (file_exists($file)) {
        if (!unlink($file)) {
            $errors[] = "Failed to delete $file";
        }
    }
}

if (empty($errors)) {
    echo "JSON files deleted successfully.";
} else {
    echo implode("\n", $errors);
}
?>
