<?php
// bike_card.php
// This file renders a bike card component with updated fields for display

// Expect $bike array with keys:
// bike_id, type, brand, status, last_update, image (blob), name, price, mileage, cc, description

$imageData = '';
if (!empty($bike['image'])) {
    $imageData = 'data:image/jpeg;base64,' . base64_encode($bike['image']);
}
?>

<div class="bike-card" style="border:1px solid #ccc; border-radius:4px; padding:1rem; max-width:300px; margin:1rem; box-shadow: 2px 2px 5px rgba(0,0,0,0.1);">
    <?php if ($imageData): ?>
        <img src="<?php echo $imageData; ?>" alt="<?php echo htmlspecialchars($bike['name']); ?>" style="width:100%; height:auto; border-radius:4px;">
    <?php else: ?>
        <div style="width:100%; height:180px; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:4px;">
            No Image
        </div>
    <?php endif; ?>
    <h3 style="margin-top:0.5rem;"><?php echo htmlspecialchars($bike['name']); ?></h3>
    <p><strong>Brand:</strong> <?php echo htmlspecialchars($bike['brand']); ?></p>
    <p><strong>Type:</strong> <?php echo htmlspecialchars($bike['type']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($bike['status']); ?></p>
    <p><strong>Price:</strong> ₹<?php echo number_format($bike['price'], 2); ?></p>
    <p><strong>Mileage:</strong> <?php echo $bike['mileage'] !== null ? htmlspecialchars($bike['mileage']) . ' km/l' : 'N/A'; ?></p>
    <p><strong>CC:</strong> <?php echo $bike['cc'] !== null ? htmlspecialchars($bike['cc']) . ' cc' : 'N/A'; ?></p>
    <p style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($bike['description'])); ?></p>
    <a href="rental.php?bike_id=<?php echo $bike['bike_id']; ?>" style="display:inline-block; margin-top:0.5rem; padding:0.5rem 1rem; background:#007bff; color:#fff; text-decoration:none; border-radius:4px;">Rent This Bike</a>
</div>
