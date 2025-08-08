<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
include 'admin_navbar.php';

// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete rental
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM rentals WHERE rental_id = ?");
    $stmt->bind_param('i', $_GET['delete_id']);
    $stmt->execute();
    header('Location: admin_manage_rental.php');
    exit;
}

$error = '';
$success = false;
$editRental = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rental_id = $_POST['edit_id'] ?? null;
    $user_id = intval($_POST['user_id']);
    $bike_id = intval($_POST['bike_id']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $price = floatval($_POST['price']);
    $address = trim($_POST['address']);
    $status = in_array($_POST['status'], ['pending', 'active', 'cancelled']) ? $_POST['status'] : 'pending';

    $admin_id = $_SESSION['admin_id'];

    if ($rental_id) {
        $stmt = $conn->prepare("UPDATE rentals SET user_id=?, bike_id=?, start_time=?, end_time=?, price=?, address=?, updated_by_admin=?, status=? WHERE rental_id=?");
        $stmt->bind_param('iissdissi', $user_id, $bike_id, $start_time, $end_time, $price, $address, $admin_id, $status, $rental_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO rentals (user_id, bike_id, start_time, end_time, price, address, created_by_admin, updated_by_admin, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissdsiis', $user_id, $bike_id, $start_time, $end_time, $price, $address, $admin_id, $admin_id, $status);
    }

    if ($stmt->execute()) {
        header('Location: admin_manage_rental.php');
        exit;
    } else {
        $error = "Error saving rental: " . $stmt->error;
    }
}

// Edit mode
if (isset($_GET['edit_id'])) {
    $stmt = $conn->prepare("SELECT * FROM rentals WHERE rental_id = ?");
    $stmt->bind_param('i', $_GET['edit_id']);
    $stmt->execute();
    $editRental = $stmt->get_result()->fetch_assoc();
}

// Get all rentals
$rentals = $conn->query("SELECT * FROM rentals ORDER BY rental_id DESC")->fetch_all(MYSQLI_ASSOC);

// Fetch users
$users = $conn->query("SELECT user_id, name FROM users ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch bikes
$bikes = $conn->query("SELECT bike_id, name FROM bikes ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rentals</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4"><?= $editRental ? 'Edit Rental' : 'Add New Rental' ?></h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="" class="mb-5">
        <?php if ($editRental): ?>
            <input type="hidden" name="edit_id" value="<?= $editRental['rental_id'] ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Select User</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['user_id'] ?>" <?= ($editRental['user_id'] ?? '') == $user['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="bike_id" class="form-label">Bike</label>
                <select name="bike_id" class="form-select" required>
                    <option value="">Select Bike</option>
                    <?php foreach ($bikes as $bike): ?>
                        <option value="<?= $bike['bike_id'] ?>" <?= ($editRental['bike_id'] ?? '') == $bike['bike_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bike['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <?php
                    $statuses = ['pending', 'active', 'cancelled'];
                    foreach ($statuses as $st) {
                        $selected = ($editRental['status'] ?? 'pending') === $st ? 'selected' : '';
                        echo "<option value=\"$st\" $selected>" . ucfirst($st) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="datetime-local" name="start_time" class="form-control" required
                       value="<?= isset($editRental['start_time']) ? date('Y-m-d\TH:i', strtotime($editRental['start_time'])) : '' ?>">
            </div>
            <div class="col-md-6">
                <label for="end_time" class="form-label">End Time</label>
                <input type="datetime-local" name="end_time" class="form-control"
                       value="<?= isset($editRental['end_time']) ? date('Y-m-d\TH:i', strtotime($editRental['end_time'])) : '' ?>">
            </div>
            <div class="col-md-6">
                <label for="price" class="form-label">Price</label>
                <input type="number" name="price" step="0.01" class="form-control" required value="<?= htmlspecialchars($editRental['price'] ?? '0.00') ?>">
            </div>
            <div class="col-md-6">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($editRental['address'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-primary"><?= $editRental ? 'Update Rental' : 'Add Rental' ?></button>
            <?php if ($editRental): ?>
                <a href="admin_manage_rental.php" class="btn btn-secondary ms-2">Cancel Edit</a>
            <?php endif; ?>
        </div>
    </form>
    <h2 class="mb-3">All Rentals</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Bike</th>
                <th>Start</th>
                <th>End</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rentals as $r): ?>
                <tr>
                    <td><?= $r['rental_id'] ?></td>
                    <td><?= $r['user_id'] ?></td>
                    <td><?= $r['bike_id'] ?></td>
                    <td><?= $r['start_time'] ?></td>
                    <td><?= $r['end_time'] ?></td>
                    <td>₹<?= $r['price'] ?></td>
                    <td>
                        <span class="badge bg-<?= $r['status'] === 'active' ? 'success' : ($r['status'] === 'pending' ? 'warning text-dark' : 'secondary') ?>">
                            <?= ucfirst($r['status']) ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a href="?edit_id=<?= $r['rental_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $r['rental_id'] ?>">Delete</button>
                        <!-- Status Quick Update -->
                        <?php foreach (['pending', 'active', 'cancelled'] as $st):
                            if ($r['status'] !== $st): ?>
                                <a href="admin_manage_rental.php?edit_id=<?= $r['rental_id'] ?>&set_status=<?= $st ?>" class="btn btn-sm btn-outline-<?= $st === 'active' ? 'success' : ($st === 'pending' ? 'warning' : 'secondary') ?> ms-1">Set <?= ucfirst($st) ?></a>
                            <?php endif;
                        endforeach; ?>
                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?= $r['rental_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $r['rental_id'] ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel<?= $r['rental_id'] ?>">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                Are you sure you want to delete rental #<?= $r['rental_id'] ?>?
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <a href="?delete_id=<?= $r['rental_id'] ?>" class="btn btn-danger">Delete</a>
                              </div>
                            </div>
                          </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Bootstrap JS Bundle (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
