<?php
// managebikes.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
include 'admin_navbar.php';

// 1) Connect
$conn = new mysqli('localhost', 'root', '', 'bike_rental');
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// 2) Handle Delete
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM bikes WHERE bike_id = ?");
    $stmt->bind_param('i', $_GET['delete_id']);
    $stmt->execute();
    header('Location: admin_managebikes.php');
    exit;
}

// 3) Handle Add / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize inputs
    $type        = trim($_POST['type']);
    $brand       = trim($_POST['brand']);
    $name        = trim($_POST['name']);
    $price       = floatval($_POST['price']);
    $mileage     = isset($_POST['mileage']) ? floatval($_POST['mileage']) : null;
    $cc          = isset($_POST['cc']) ? intval($_POST['cc']) : null;
    $description = trim($_POST['description']);
    $status      = in_array($_POST['status'], ['available','rented','maintenance'])
                   ? $_POST['status'] : 'available';

    // handle image upload (optional)
    $imageData = null;
    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (!empty($_POST['edit_id'])) {
        // UPDATE
        if ($imageData !== null) {
            $sql = "UPDATE bikes
                    SET type=?, brand=?, name=?, price=?, mileage=?, cc=?, description=?, status=?, image=?
                    WHERE bike_id=?";
            $stmt = $conn->prepare($sql);
            $null = NULL;
            $stmt->bind_param('ssssdissbi',
                $type, $brand, $name, $price, $mileage, $cc, $description, $status,
                $null, // for blob
                $_POST['edit_id']
            );
            // send blob
            $stmt->send_long_data(8, $imageData);
        } else {
            $sql = "UPDATE bikes
                    SET type=?, brand=?, name=?, price=?, mileage=?, cc=?, description=?, status=?
                    WHERE bike_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssdissi',
                $type, $brand, $name, $price, $mileage, $cc, $description, $status,
                $_POST['edit_id']
            );
        }
        $stmt->execute();
    } else {
        // INSERT
        $sql = "INSERT INTO bikes
                (type, brand, name, price, mileage, cc, description, status, image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $null = NULL;
            $stmt->bind_param('ssssdissb',
                $type, $brand, $name, $price, $mileage, $cc, $description, $status,
                $null // blob placeholder
            );
        $stmt->send_long_data(8, $imageData);
        $stmt->execute();
    }

    header('Location: admin_managebikes.php');
    exit;
}

// 4) If editing, fetch existing bike
$editBike = null;
if (isset($_GET['edit_id'])) {
  $stmt = $conn->prepare("SELECT bike_id, name, type, brand, status, image FROM bikes WHERE bike_id = ?");

    $stmt->bind_param('i', $_GET['edit_id']);
    $stmt->execute();
    $editBike = $stmt->get_result()->fetch_assoc();
}

// 5) Fetch all bikes
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
  <title>Manage Bikes</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-4">
    <h2 class="mb-4"><?php echo $editBike ? 'Edit Bike' : 'Add New Bike'; ?></h2>
    <form method="POST" action="" enctype="multipart/form-data" class="mb-5">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Type
            <select name="type" class="form-select" required>
              <?php
                $types = ['Adventure', 'Cruiser', 'Mountain', 'Scooter', 'Electric'];
                $currentType = $editBike['type'] ?? '';
                foreach ($types as $type) {
                  $selected = ($type === $currentType) ? 'selected' : '';
                  echo "<option value=\"" . htmlspecialchars($type) . "\" $selected>" . htmlspecialchars($type) . "</option>";
                }
              ?>
            </select>
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Brand
            <input type="text" name="brand" class="form-control" required value="<?php echo htmlspecialchars($editBike['brand'] ?? ''); ?>">
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Name
            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($editBike['name'] ?? ''); ?>">
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Mileage
            <input type="number" step="0.01" name="mileage" class="form-control" value="<?php echo htmlspecialchars($editBike['mileage'] ?? ''); ?>">
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">CC
            <input type="number" name="cc" class="form-control" value="<?php echo htmlspecialchars($editBike['cc'] ?? ''); ?>">
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Price
            <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo htmlspecialchars($editBike['price'] ?? '0.00'); ?>">
          </label>
        </div>
        <div class="col-12">
          <label class="form-label">Description
            <textarea name="description" class="form-control"><?php echo htmlspecialchars($editBike['description'] ?? ''); ?></textarea>
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status
            <select name="status" class="form-select">
              <?php foreach (['available','rented','maintenance'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php if (($editBike['status'] ?? '') === $st) echo 'selected'; ?>>
                  <?php echo ucfirst($st); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Image
            <input type="file" name="image" accept="image/*" class="form-control">
            <?php if ($editBike): ?>
              <small class="form-text text-muted">Leave blank to keep existing image.</small>
            <?php endif; ?>
          </label>
        </div>
        <?php if ($editBike): ?>
          <input type="hidden" name="edit_id" value="<?php echo $editBike['bike_id']; ?>">
        <?php endif; ?>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary"><?php echo $editBike ? 'Update Bike' : 'Add Bike'; ?></button>
        <?php if ($editBike): ?>
          <a href="admin_managebikes.php" class="btn btn-secondary ms-2">Cancel Edit</a>
        <?php endif; ?>
      </div>
    </form>
    <h2 class="mb-3">All Bikes</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Status</th>
            <th>Last Update</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bikes as $bike): ?>
            <tr>
              <td><?php echo htmlspecialchars($bike['bike_id']); ?></td>
              <td><?php echo htmlspecialchars($bike['name']); ?></td>
              <td><?php echo number_format($bike['price'], 2); ?></td>
              <td><?php echo htmlspecialchars($bike['status']); ?></td>
              <td><?php echo htmlspecialchars($bike['last_update']); ?></td>
              <td class="actions">
                <a href="?edit_id=<?php echo $bike['bike_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="?delete_id=<?php echo $bike['bike_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this bike?');">Delete</a>
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
