<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
?>

<style>
  body { margin: 0; font-family: Arial, sans-serif; }
  nav {
    background-color: #333;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1rem;
  }
  .nav-left, .nav-right {
    display: flex;
    align-items: center;
  }
  .nav-left a, .nav-right a {
    color: white;
    text-decoration: none;
    margin: 0 0.75rem;
    font-weight: bold;
    transition: color 0.3s;
  }
  .nav-left a:hover, .nav-right a:hover {
    color: #ffcc00;
  }
  .logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #ffcc00;
    margin-right: 1rem;
  }
</style>

<nav>
  <div class="nav-left">
    <div class="logo">BikeRental</div>
    <a href="home.php">Home</a>
    <a href="rental.php">Rental</a>
    <a href="about.php">About Us</a>
    <a href="contact.php">Contact Us</a>
  </div>
  <div class="nav-right">
    <?php if (isset($_SESSION['user_name'])): ?>
      <span style="color: white; margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a href="my_rentals.php">My Rentals</a>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="user_login.php">Login</a>
      <a href="user_register.php">Register</a>  
    <?php endif; ?>
  </div>
</nav>
<!-- Bootstrap JS Bundle (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
