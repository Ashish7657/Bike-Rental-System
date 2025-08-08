<?php
session_start();
include 'user_navbar.php';

if (!isset($_GET['bike_id'])) {
    echo "No bike selected.";
    exit;
}

$bike_id = intval($_GET['bike_id']);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// Fetch bike details
$stmt = $conn->prepare("SELECT bike_id, type, brand, status, last_update, image, name, price, mileage, cc, description FROM bikes WHERE bike_id = ?");
$stmt->bind_param('i', $bike_id);
$stmt->execute();
$bike = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$bike) {
    echo "Bike not found.";
    exit;
}

// Prepare image for display
$imageData = '';
if (!empty($bike['image'])) {
    $imageData = 'data:image/jpeg;base64,' . base64_encode($bike['image']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bike Details - <?php echo htmlspecialchars($bike['name']); ?></title>
<style>
    .container { max-width: 800px; margin: 2rem auto; }
    .bike-image { max-width: 100%; height: auto; }
    .details { margin-top: 1rem; }
    .details dt { font-weight: bold; margin-top: 0.5rem; }
    .details dd { margin-left: 1rem; margin-bottom: 0.5rem; }
</style>
</head>
<body>

<div class="container">
    <h2><?php echo htmlspecialchars($bike['name']); ?></h2>
    <?php if ($imageData): ?>
        <img src="<?php echo $imageData; ?>" alt="<?php echo htmlspecialchars($bike['name']); ?>" class="bike-image">
    <?php else: ?>
        <p>No image available.</p>
    <?php endif; ?>

    <dl class="details">
        <dt>Type:</dt>
        <dd><?php echo htmlspecialchars($bike['type']); ?></dd>

        <dt>Brand:</dt>
        <dd><?php echo htmlspecialchars($bike['brand']); ?></dd>

        <dt>Status:</dt>
        <dd><?php echo htmlspecialchars($bike['status']); ?></dd>

        <dt>Last Update:</dt>
        <dd><?php echo htmlspecialchars($bike['last_update']); ?></dd>

        <dt>Price:</dt>
        <dd><?php echo number_format($bike['price'], 2); ?></dd>

        <dt>Mileage:</dt>
        <dd><?php echo $bike['mileage'] !== null ? htmlspecialchars($bike['mileage']) : 'N/A'; ?></dd>

        <dt>CC:</dt>
        <dd><?php echo $bike['cc'] !== null ? htmlspecialchars($bike['cc']) : 'N/A'; ?></dd>

        <dt>Description:</dt>
        <dd><?php echo nl2br(htmlspecialchars($bike['description'])); ?></dd>
    </dl>
</div>

</body>
</html>
