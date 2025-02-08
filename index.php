<?php
$reviewsFileExists = file_exists('reviews.json');
$gameName = "Ambulance Life: A Paramedic Simulator";
$appId = "1926520";

$updateInfo = '';
if (file_exists('updated-reviews.json')) {
    $updatedData = json_decode(file_get_contents('updated-reviews.json'), true);
    if (isset($updatedData['execution_date'])) {
        $lastUpdate = date('Y-m-d H:i:s', $updatedData['execution_date']);
        $updateInfo = "Last updated: $lastUpdate";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Game Reviews - <?php echo $gameName; ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f9;
      color: #333;
      margin: 20px;
      padding: 20px;
    }
    .container {
      background-color: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: auto;
      position: relative;
    }
    .delete-button {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: red;
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 4px;
    }
    .filter-buttons, .navigation-buttons, .script-buttons {
      margin-bottom: 10px;
    }
    .filter-buttons button, .navigation-buttons button, .script-buttons button {
      padding: 10px 15px;
      margin: 5px 5px 5px 0;
      display: inline-block;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .filter-buttons button.active, .navigation-buttons button.active {
      background-color: #28a745;
    }
    .filter-buttons button:hover, .navigation-buttons button:hover, .script-buttons button:hover {
      background-color: #0056b3;
    }
    .divider {
      border-bottom: 2px solid #ccc;
      margin: 10px 0;
    }
    .review-container {
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 15px;
      margin-bottom: 10px;
      background-color: #f9f9f9;
    }
    .info-container {
      margin: 10px 0;
      font-style: italic;
      color: #555;
    }
    #loading {
      display: none;
      font-weight: bold;
      color: red;
      margin-bottom: 10px;
    }
    <?php if ($reviewsFileExists) echo "#initRun { display: none; }"; ?>
  </style>
</head>
<body>
  <div class="container">
    <button class="delete-button" onclick="deleteFiles()">Delete JSON Files</button>
    <img src="https://shared.cloudflare.steamstatic.com/store_item_assets/steam/apps/<?php echo $appId; ?>/header.jpg" alt="<?php echo $gameName; ?>" style="width:100%; max-width:800px;">
    <div id="loading">Loading, please wait...</div>
    <div class="script-buttons">
      <button id="initRun" onclick="runScriptAndSwitch('fetch_reviews.php')">Init Run</button>
      <button id="updateRun" onclick="runScriptAndSwitch('updated-reviews.php')" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>>Update</button>
      <span><?php echo $updateInfo; ?></span>
    </div>
    <div class="divider"></div>
    <div class="filter-buttons">
      <button id="allBtn" class="active" onclick="switchTo('reviews.json', this)" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>>All</button>
      <button id="newBtn" onclick="switchTo('updated-reviews.json', this)" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>>New</button>
    </div>
    <div>
      <label><input type="checkbox" id="filterDevResponse" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>> Developer Responses Only</label>
      <label><input type="checkbox" id="filterNoDevResponse" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>> No Developer Response</label>
    </div>
    <div>
      <label><input type="checkbox" id="filterPositive" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>> Positive</label>
      <label><input type="checkbox" id="filterNegative" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>> Negative</label>
    </div>
    <div class="divider"></div>
    <div class="navigation-buttons">
      <button id="prevBtn" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>>Previous</button>
      <div id="review-count" style="display: inline-block; padding: 0 10px; font-weight: bold;">1 / 0</div>
      <button id="nextBtn" <?php echo !$reviewsFileExists ? 'disabled' : ''; ?>>Next</button>
    </div>
    <div id="info" class="info-container"></div>
    <div id="review" class="review-container"></div>
  </div>

  <script>
    let reviews = [];
    let filteredReviews = [];
    let currentIndex = 0;

    function deleteFiles() {
      fetch('delete_files.php')
        .then(response => response.text())
        .then(data => {
          alert(data);
          location.reload();
        })
        .catch(error => console.error('Error deleting files:', error));
    }

    function runScriptAndSwitch(scriptName) {
      document.getElementById('loading').style.display = 'block';
      fetch(scriptName)
        .then(response => response.text())
        .then(data => {
          document.getElementById('loading').style.display = 'none';
          loadReviews('reviews.json');
        })
        .catch(error => {
          document.getElementById('loading').style.display = 'none';
          console.error('Error running script:', error);
        });
    }

    function loadReviews(file) {
      fetch(file)
        .then(response => response.json())
        .then(data => {
          reviews = data.reviews;
          applyFilters();
        })
        .catch(error => console.error('Error loading reviews:', error));
    }

    function switchTo(file, button) {
      document.querySelectorAll('.filter-buttons button').forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');
      loadReviews(file);
    }

    function applyFilters() {
      filteredReviews = reviews;

      if (document.getElementById('filterDevResponse').checked) {
        filteredReviews = filteredReviews.filter(review => review.developer_response);
      } else if (document.getElementById('filterNoDevResponse').checked) {
        filteredReviews = filteredReviews.filter(review => !review.developer_response);
      }

      if (document.getElementById('filterPositive').checked && !document.getElementById('filterNegative').checked) {
        filteredReviews = filteredReviews.filter(review => review.voted_up);
      } else if (document.getElementById('filterNegative').checked && !document.getElementById('filterPositive').checked) {
        filteredReviews = filteredReviews.filter(review => !review.voted_up);
      }

      currentIndex = 0;
      displayReview(currentIndex);
      updateReviewCount();
    }

    function displayReview(index) {
      const reviewContainer = document.getElementById('review');
      if (index >= 0 && index < filteredReviews.length) {
        const review = filteredReviews[index];
        const reviewLink = `https://steamcommunity.com/profiles/${review.author.steamid}/recommended/${review.recommendationid}`;
        reviewContainer.innerHTML = `
          <p><strong>Review ID:</strong> ${review.recommendationid}</p>
          <p><strong>Review:</strong> ${review.review}</p>
          ${review.developer_response ? `<p><strong>Developer Response:</strong> ${review.developer_response}</p>` : ''}
          <button onclick="window.open('${reviewLink}', '_blank')">View Original Review on Steam</button>
        `;
      } else {
        reviewContainer.innerHTML = 'No more reviews.';
      }
      updateReviewCount();
    }

    function updateReviewCount() {
      document.getElementById('review-count').innerText = `${currentIndex + 1} / ${filteredReviews.length}`;
    }

    document.getElementById('prevBtn').addEventListener('click', () => {
      if (currentIndex > 0) {
        currentIndex--;
        displayReview(currentIndex);
        updateReviewCount();
      }
    });

    document.getElementById('nextBtn').addEventListener('click', () => {
      if (currentIndex < filteredReviews.length - 1) {
        currentIndex++;
        displayReview(currentIndex);
        updateReviewCount();
      }
    });

    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
      checkbox.addEventListener('change', applyFilters);
    });

    if (<?php echo json_encode($reviewsFileExists); ?>) {
      loadReviews('reviews.json');
    }
  </script>
</body>
</html>