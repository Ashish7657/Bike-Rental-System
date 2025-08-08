<?php
// my_rentals.php
// User page to view their own rental details

session_start();
include 'user_navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch rentals for this user, joined with bike info
$stmt = $conn->prepare(
    "SELECT r.rental_id, b.name AS bike_name, r.start_time, r.end_time, r.price, r.address, r.status
     FROM rentals r
     JOIN bikes b ON r.bike_id = b.bike_id
     WHERE r.user_id = ?
     ORDER BY r.start_time DESC"
);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$rentals = [];
while ($row = $result->fetch_assoc()) {
    $rentals[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Rentals - Bike Rental</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    h1 { text-align: center; margin-bottom: 1.5rem; }
    .rentals-table { width: 100%; border-collapse: collapse; margin: auto; }
    .rentals-table th, .rentals-table td { padding: .75rem; border: 1px solid #ddd; text-align: left; transition: background 0.2s; }
    .rentals-table th { background-color: #f4f4f4; }
    .rentals-table tr:nth-child(even) { background-color: #fafafa; }
    .rentals-table tbody tr { opacity: 0; transform: translateY(30px); transition: opacity 0.6s, transform 0.6s; }
    .rentals-table tbody tr.visible { opacity: 1; transform: translateY(0); }
    .rentals-table tbody tr:hover { background: #e7f1ff; }
    .no-data { text-align: center; color: #555; }
    .status-badge { padding: 0.4em 0.8em; border-radius: 12px; font-size: 0.95em; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-active { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>

<h1>My Rental Details</h1>

<?php if (empty($rentals)): ?>
    <p class="no-data">You have not rented any bikes yet.</p>
<?php else: ?>
    <table class="rentals-table table table-bordered table-hover align-middle">
      <thead>
        <tr>
          <th>Rental ID</th>
          <th>Bike</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Price (₹)</th>
          <th>Address</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rentals as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['rental_id']) ?></td>
            <td><?= htmlspecialchars($r['bike_name']) ?></td>
            <td><?= htmlspecialchars($r['start_time']) ?></td>
            <td><?= htmlspecialchars($r['end_time'] ?: '—') ?></td>
            <td><?= number_format($r['price'], 2) ?></td>
            <td><?= htmlspecialchars($r['address']) ?></td>
            <td>
              <?php $status = strtolower($r['status'] ?? 'pending'); ?>
              <span class="status-badge status-<?= $status ?>">
                <?= htmlspecialchars(ucfirst($status)) ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Animate table rows on load
  document.querySelectorAll('.rentals-table tbody tr').forEach((row, idx) => {
    setTimeout(() => row.classList.add('visible'), 100 + idx * 80);
  });
</script>
</body>
</html>
