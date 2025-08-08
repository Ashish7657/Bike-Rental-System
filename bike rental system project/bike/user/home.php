<?php
session_start();
include 'user_navbar.php';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch latest bikes
$bikes = [];
$res = $conn->query("SELECT bike_id, type, brand, status, last_update, image, name, price, mileage, cc, description FROM bikes ORDER BY bike_id DESC");
while ($row = $res->fetch_assoc()) {
    $bikes[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Bike Rental</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(120deg, #f8fafc 0%, #e0e7ff 100%);
      min-height: 100vh;
    }
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: 1px;
      color: #2d3a4a;
      text-align: center;
      margin-bottom: 2rem;
      text-shadow: 0 2px 8px #e0e7ff;
    }
    .bike-card-container {
      transition: transform 0.18s, box-shadow 0.18s, background 0.18s;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(60,60,120,0.06);
      padding-top: 0.5rem;
      padding-bottom: 0.5rem;
    }
    .bike-card-container:hover {
      transform: translateY(-6px) scale(1.025);
      box-shadow: 0 8px 32px rgba(60,60,120,0.13);
      background: #f0f6ff;
      z-index: 2;
    }
    #loadMoreBtn {
      transition: background 0.2s, transform 0.2s;
      font-weight: 500;
      letter-spacing: 1px;
      border-radius: 24px;
      padding-left: 2.5rem;
      padding-right: 2.5rem;
    }
    #loadMoreBtn:hover {
      background: #4f46e5;
      transform: scale(1.06);
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Main Hero Image Section -->
<div class="background-photo" style="margin-bottom: 2rem; height: 400px; background-image: url('cbr.webp'); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: #f0f0f0; position: relative; display: flex; align-items: center; justify-content: center;">
</div>

<!-- Main Title Below Image -->
<div class="section-title mb-4">Bike Rental System</div>

<div class="container my-5">
  <div class="section-title mb-4">Latest Bikes</div>
  <?php if (empty($bikes)): ?>
    <div class="alert alert-warning text-center">No bikes available at the moment. Check back soon!</div>
  <?php else: ?>
    <div class="row g-4" id="bikesGrid">
      <?php foreach ($bikes as $i => $bike): ?>
        <div class="col-md-4 bike-card-container" data-index="<?= $i ?>">
          <?php
            $GLOBALS['bike'] = $bike;
            include 'bike_card.php';
          ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <button id="loadMoreBtn" class="btn btn-primary">Load More</button>
    </div>
  <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Show 6 bikes at first, then 6 more each click
  const bikesPerPage = 6;
  let visibleCount = bikesPerPage;
  const bikeCards = document.querySelectorAll('.bike-card-container');
  function updateVisibleBikes() {
    bikeCards.forEach((card, idx) => {
      card.style.display = idx < visibleCount ? '' : 'none';
      if (idx < visibleCount) {
        setTimeout(() => card.classList.add('visible'), 50 + idx * 60);
      }
    });
    document.getElementById('loadMoreBtn').style.display = visibleCount < bikeCards.length ? '' : 'none';
  }
  updateVisibleBikes();
  document.getElementById('loadMoreBtn').addEventListener('click', function() {
    visibleCount += bikesPerPage;
    updateVisibleBikes();
    this.blur();
  });
</script>
<?php include 'user_footer.php'; ?>
</body>
</html>
