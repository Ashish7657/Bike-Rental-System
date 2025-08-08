<?php
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

$error = '';
$success = false;

// Fetch available bikes and their details
$bikes = [];
$result = $conn->query("SELECT bike_id, name, price, image, type, brand, mileage, cc FROM bikes WHERE status = 'available'");
while ($row = $result->fetch_assoc()) {
    $row['base_price'] = $row['price'];
    $bikes[] = $row;
}

// If bike_id is passed via GET (from bike card), preselect it
$selected_bike_id = isset($_GET['bike_id']) ? intval($_GET['bike_id']) : ($bikes[0]['bike_id'] ?? null);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'];
    $bike_id    = intval($_POST['bike_id']);
    $start_time = trim($_POST['start_time']);
    $end_time   = trim($_POST['end_time']);
    $address    = trim($_POST['address']);

    if (empty($address) || empty($start_time) || empty($end_time)) {
        $error = "Please fill in all required fields.";
    } else {
        $start_ts = strtotime($start_time);
        $end_ts   = strtotime($end_time);
        $diff_sec = $end_ts - $start_ts;
        if ($diff_sec <= 0) {
            $error = "End time must be after start time.";
        } else {
            // Check if user already has an active/pending rental for this bike
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM rentals WHERE user_id = ? AND bike_id = ? AND status IN ('pending', 'active')");
            $stmtCheck->bind_param('ii', $user_id, $bike_id);
            $stmtCheck->execute();
            $stmtCheck->bind_result($existing_rental_count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($existing_rental_count > 0) {
                $error = "You already have an active or pending rental for this bike.";
            } else {
                $days = ceil($diff_sec / (24 * 60 * 60));
                $stmtPrice = $conn->prepare("SELECT price FROM bikes WHERE bike_id = ?");
                $stmtPrice->bind_param('i', $bike_id);
                $stmtPrice->execute();
                $stmtPrice->bind_result($base_price);
                $stmtPrice->fetch();
                $stmtPrice->close();

                $total_price = $base_price * $days;

                $stmt = $conn->prepare(
                    "INSERT INTO rentals (user_id, bike_id, start_time, end_time, price, address, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending')"
                );
                $stmt->bind_param('iissds',
                    $user_id,
                    $bike_id,
                    $start_time,
                    $end_time,
                    $total_price,
                    $address
                );
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>

<?php
function jaccard_similarity($set1, $set2) {
    $intersection = count(array_intersect($set1, $set2));
    $union = count(array_unique(array_merge($set1, $set2)));
    if ($union == 0) return 0;
    return $intersection / $union;
}

function price_range($price) {
    if ($price < 5000) return 'low_price';
    if ($price < 15000) return 'medium_price';
    return 'high_price';
}

function mileage_range($mileage) {
    if ($mileage < 20) return 'low_mileage';
    if ($mileage < 50) return 'medium_mileage';
    return 'high_mileage';
}

// Fetch selected bike details for recommendations
$selected_bike = null;
foreach ($bikes as $b) {
    if ($b['bike_id'] == $selected_bike_id) {
        $selected_bike = $b;
        break;
    }
}

$recommendations = [];
if ($selected_bike) {
    // Use existing connection to fetch all available bikes for similarity calculation
    $conn = new mysqli('localhost', 'root', '', 'bike_rental');
    if (!$conn->connect_error) {
        $all_bikes = [];
        $type = $conn->real_escape_string($selected_bike['type']);
        $result = $conn->query("SELECT bike_id, type, brand, status, last_update, image, name, price, mileage, cc, description FROM bikes WHERE status = 'available' AND bike_id != " . intval($selected_bike['bike_id']) . " AND type = '" . $type . "'");
        while ($row = $result->fetch_assoc()) {
            $all_bikes[] = $row;
        }

        // Prepare sets for selected bike
        $selected_set = [];
        if (!empty($selected_bike['brand'])) {
            $selected_set[] = strtolower($selected_bike['brand']);
        }
        if (!empty($selected_bike['type'])) {
            $selected_set[] = strtolower($selected_bike['type']);
        }
        if (!empty($selected_bike['price'])) {
            $selected_set[] = price_range($selected_bike['price']);
        }
        if (!empty($selected_bike['mileage'])) {
            $selected_set[] = mileage_range($selected_bike['mileage']);
        }
        if (!empty($selected_bike['cc'])) {
            $selected_set[] = (string)intval($selected_bike['cc']);
        }
        $selected_set = array_unique($selected_set);

        // Calculate similarity for each bike
        $similarities = [];
        foreach ($all_bikes as $bike) {
            $bike_set = [];
            if (!empty($bike['brand'])) {
                $bike_set[] = strtolower($bike['brand']);
            }
            if (!empty($bike['type'])) {
                $bike_set[] = strtolower($bike['type']);
            }
            if (!empty($bike['price'])) {
                $bike_set[] = price_range($bike['price']);
            }
            if (!empty($bike['mileage'])) {
                $bike_set[] = mileage_range($bike['mileage']);
            }
            if (!empty($bike['cc'])) {
                $bike_set[] = (string)intval($bike['cc']);
            }
            $bike_set = array_unique($bike_set);

            $sim = jaccard_similarity($selected_set, $bike_set);
            $similarities[$bike['bike_id']] = $sim;
        }

        // Sort bikes by similarity descending
        arsort($similarities);

        // Pick top 4 recommendations
        $top_ids = array_slice(array_keys($similarities), 0, 4);

        // Build recommendations array
        $recommendations = array_filter($all_bikes, function($b) use ($top_ids) {
            return in_array($b['bike_id'], $top_ids);
        });

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rent a Bike</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    form { max-width: 400px; margin: auto; }
    label { display: block; margin-bottom: .5rem; font-weight: bold; }
    select, input, textarea { width: 100%; padding: .5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; transition: box-shadow 0.2s, border-color 0.2s; }
    select:focus, input:focus, textarea:focus { box-shadow: 0 0 0 2px #0d6efd33; border-color: #0d6efd; }
    button { padding: .75rem 1.5rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer; transition: background 0.2s, transform 0.2s; }
    button:hover { background: #555; transform: scale(1.04); }
    .error { color: red; margin-bottom: 1rem; }
    .success { color: green; margin-bottom: 1rem; }
    .recommendations { max-width: 900px; margin: 2rem auto; animation: fadeInUp 0.7s; }
    .recommendations h3 { margin-bottom: 1rem; }
    .bike-grid { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }
    .rec-bike-card { transition: transform 0.2s, box-shadow 0.2s, opacity 0.6s; opacity: 0; }
    .rec-bike-card.visible { opacity: 1; transition: opacity 0.6s, transform 0.2s, box-shadow 0.2s; }
    .rec-bike-card:hover { transform: translateY(-8px) scale(1.03); box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 2; }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<h2>Rent a Bike</h2>
<?php if ($error): ?>
  <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php elseif ($success): ?>
  <p class="success">Your rental request was submitted successfully!</p>
<?php endif; ?>

<?php
$selected_bike_name = '';
foreach ($bikes as $b) {
    if ($b['bike_id'] == $selected_bike_id) {
        $selected_bike_name = $b['name'];
        break;
    }
}
?>
<?php if ($selected_bike_name): ?>
  <p><strong>Selected Bike:</strong> <?php echo htmlspecialchars($selected_bike_name); ?></p>
<?php endif; ?>

<form method="POST" action="">
  <label for="bike_id">Select Bike</label>
  <select name="bike_id" id="bike_id" required>
    <?php foreach ($bikes as $b): ?>
      <option value="<?php echo $b['bike_id']; ?>" data-base-price="<?php echo $b['base_price']; ?>" <?php if ($b['bike_id'] == $selected_bike_id) echo 'selected'; ?>>
        <?php echo htmlspecialchars($b['name']) . ' - ₹' . number_format($b['base_price'], 2) . ' per day'; ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label for="start_time">Start Date &amp; Time</label>
  <input type="datetime-local" name="start_time" id="start_time" required>

  <label for="end_time">End Date &amp; Time</label>
  <input type="datetime-local" name="end_time" id="end_time" required>

  <label for="address">Your Address</label>
  <textarea name="address" id="address" required></textarea>

  <label for="total_price">Total Price (₹)</label>
  <input type="text" name="total_price" id="total_price" readonly required>

  <button type="submit">Confirm Rental</button>
</form>

<?php if (!empty($recommendations)): ?>
<div class="recommendations">
  <h3>Recommended Bikes for You</h3>
  <div class="bike-grid">
    <?php foreach ($recommendations as $i => $recBike): ?>
      <div class="rec-bike-card" data-index="<?= $i ?>">
        <?php
          $GLOBALS['bike'] = $recBike;
          include 'bike_card.php';
        ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
  const bikeSelect = document.getElementById('bike_id');
  const startInput = document.getElementById('start_time');
  const endInput   = document.getElementById('end_time');
  const priceInput = document.getElementById('total_price');

  function updateTotalPrice() {
    const base = parseFloat(bikeSelect.selectedOptions[0].dataset.basePrice) || 0;
    const start = new Date(startInput.value);
    const end   = new Date(endInput.value);
    if (start && end && end > start) {
      const diffMs = end - start;
      const days   = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
      priceInput.value = (base * days).toFixed(2);
    } else {
      priceInput.value = '0.00';
    }
  }

  bikeSelect.addEventListener('change', updateTotalPrice);
  startInput.addEventListener('change', updateTotalPrice);
  endInput.addEventListener('change', updateTotalPrice);
  // init
  updateTotalPrice();

  // Animate recommended bike cards
  document.querySelectorAll('.rec-bike-card').forEach((card, idx) => {
    setTimeout(() => card.classList.add('visible'), 100 + idx * 120);
  });
</script>

</body>
</html>
