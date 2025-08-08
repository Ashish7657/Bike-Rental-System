<?php
// all_bikes.php

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
include 'admin_navbar.php';

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch all bikes
$bikes = [];
$res = $conn->query("SELECT bike_id, name, price, status, last_update FROM bikes ORDER BY bike_id DESC");
while ($row = $res->fetch_assoc()) {
    $bikes[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Bikes</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-4">
    <h1 class="mb-4">All Bikes</h1>
    <a class="btn btn-primary mb-3" href="admin_managebikes.php">Add / Edit Bikes</a>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Bike ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Status</th>
            <th>Last Update</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bikes)): ?>
            <tr><td colspan="6">No bikes found.</td></tr>
          <?php else: ?>
            <?php foreach ($bikes as $b): ?>
              <tr>
                <td><?php echo $b['bike_id']; ?></td>
                <td><?php echo htmlspecialchars($b['name']); ?></td>
                <td><?php echo number_format($b['price'], 2); ?> ₹</td>
                <td><?php echo ucfirst($b['status']); ?></td>
                <td><?php echo $b['last_update']; ?></td>
                <td>
                  <a href="admin_managebikes.php?edit_id=<?php echo $b['bike_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                  <a href="admin_managebikes.php?delete_id=<?php echo $b['bike_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this bike?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <!-- Bootstrap JS Bundle (with Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
