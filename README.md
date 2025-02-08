# Steam Reviews Dashboard

This project provides a web-based dashboard to fetch, display, and filter Steam reviews for a specific game.

## Features

- Fetch initial reviews (`Init Run` button)
- Update with the latest reviews (`Update` button)
- Filter reviews by:
  - Developer Responses Only
  - No Developer Response
  - Positive/Negative
- Navigate between reviews
- Delete review JSON files

## Files Overview

- `index.php`: Main dashboard interface
- `fetch_reviews.php`: Script to fetch initial batch of reviews
- `updated-reviews.php`: Script to fetch updated reviews
- `delete_files.php`: Script to delete `reviews.json` and `updated-reviews.json`
- `reviews.json`: Stores fetched reviews
- `updated-reviews.json`: Stores updated reviews


## Adjusting for a Different Game

1. **Update the Game Name and App ID:**
   - Open `index.php`.
   - Change:
     ```php
     $gameName = "Your Game Name";
     $appId = "Your_App_ID";
     ```

2. **Ensure Review Fetching Works:**
   - Make sure your Steam App ID allows review fetching via API.

## Known Issues

- Ensure PHP and file permissions are set correctly to allow writing JSON files.
- Some features may require adjusting the server settings if using a live server.

## License

MIT License

