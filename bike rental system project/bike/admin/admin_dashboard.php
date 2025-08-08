<?php
// admin_dashboard.php

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// 1) Database connection
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// 2) Fetch summary counts
$counts = [];

// Split the counts query into separate simpler queries with error handling
$total_bikes_result = $conn->query("SELECT COUNT(*) AS total_bikes FROM bikes");
if ($total_bikes_result) {
    $row = $total_bikes_result->fetch_assoc();
    $counts['total_bikes'] = $row['total_bikes'];
    $total_bikes_result->free();
} else {
    $counts['total_bikes'] = 0;
}

$available_bikes_result = $conn->query("SELECT COUNT(*) AS available_bikes FROM bikes WHERE status='available'");
if ($available_bikes_result) {
    $row = $available_bikes_result->fetch_assoc();
    $counts['available_bikes'] = $row['available_bikes'];
    $available_bikes_result->free();
} else {
    $counts['available_bikes'] = 0;
}

$total_users_result = $conn->query("SELECT COUNT(*) AS total_users FROM users");
if ($total_users_result) {
    $row = $total_users_result->fetch_assoc();
    $counts['total_users'] = $row['total_users'];
    $total_users_result->free();
} else {
    $counts['total_users'] = 0;
}

$total_rentals_result = $conn->query("SELECT COUNT(*) AS total_rentals FROM rentals");
if ($total_rentals_result) {
    $row = $total_rentals_result->fetch_assoc();
    $counts['total_rentals'] = $row['total_rentals'];
    $total_rentals_result->free();
} else {
    $counts['total_rentals'] = 0;
}

// 3) Fetch 5 most recent rentals
$recent = [];
$sql = "
  SELECT 
    r.rental_id,
    u.name   AS user_name,
    b.name   AS bike_name,
    r.start_time,
    r.end_time
  FROM rentals r
  JOIN users  u ON r.user_id = u.user_id
  JOIN bikes  b ON r.bike_id = b.bike_id
  ORDER BY r.start_time DESC
  LIMIT 5
";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
    $recent[] = $r;
}
$res->free();
$conn->close();
?>

<?php include 'admin_navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .dashboard-card {
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .dashboard-card:hover {
      transform: translateY(-5px) scale(1.03);
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    .dashboard-icon {
      font-size: 2.5rem;
      margin-bottom: 0.5rem;
      color: #0d6efd;
    }
  </style>
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
  <div class="container mt-4">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></h1>
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm dashboard-card">
          <div class="card-body">
            <div class="dashboard-icon"><i class="bi bi-bicycle"></i></div>
            <h2 class="card-title display-6"><?php echo $counts['total_bikes']; ?></h2>
            <p class="card-text">Total Bikes</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm dashboard-card">
          <div class="card-body">
            <div class="dashboard-icon"><i class="bi bi-check2-circle"></i></div>
            <h2 class="card-title display-6"><?php echo $counts['available_bikes']; ?></h2>
            <p class="card-text">Available Bikes</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm dashboard-card">
          <div class="card-body">
            <div class="dashboard-icon"><i class="bi bi-people"></i></div>
            <h2 class="card-title display-6"><?php echo $counts['total_users']; ?></h2>
            <p class="card-text">Registered Users</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card text-center shadow-sm dashboard-card">
          <div class="card-body">
            <div class="dashboard-icon"><i class="bi bi-calendar2-check"></i></div>
            <h2 class="card-title display-6"><?php echo $counts['total_rentals']; ?></h2>
            <p class="card-text">Total Rentals</p>
          </div>
        </div>
      </div>
    </div>
    <h2 class="mb-3">Recent Rentals</h2>
    <?php if (count($recent) === 0): ?>
      <div class="alert alert-info">No rentals yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Rental ID</th>
              <th>User</th>
              <th>Bike</th>
              <th>Start Time</th>
              <th>End Time</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
              <tr>
                <td><?php echo $r['rental_id']; ?></td>
                <td><?php echo htmlspecialchars($r['user_name']); ?></td>
                <td><?php echo htmlspecialchars($r['bike_name']); ?></td>
                <td><?php echo $r['start_time']; ?></td>
                <td><?php echo $r['end_time'] ?? '—'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <!-- Bootstrap JS Bundle (with Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
