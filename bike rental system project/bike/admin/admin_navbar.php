<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ENjdO4Dr2bkBIFxQpeoYz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous">
  <style>
    /* Basic navbar styling */
    nav {
      background-color: #333;
      padding: 0 1rem;
    }
    nav ul {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
    }
    nav li {
      margin-right: 1rem;
    }
    nav a {
      color: #fff;
      text-decoration: none;
      padding: 0.75rem 0.5rem;
      display: block;
    }
    nav a:hover {
      background-color: #444;
      border-radius: 4px;
    }
    .nav-spacer {
      flex: 1;
    }
  </style>
</head>
<body>
  <nav>
    <ul>
      <?php if (isset($_SESSION['admin_id'])): ?>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="admin_managebikes.php">Manage Bikes</a></li>
        <li><a href="admin_lists.php">Lists Bikes</a></li>
        <li><a href="admin_manage_rental.php">Manage Rentals</a></li>
        <li class="nav-spacer"></li>
        <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['admin_username']); ?>)</a></li>
      <?php else: ?>
        <li class="nav-spacer"></li>
        <li><a href="admin_login.php">Login</a></li>
        <li><a href="admin_register.php">Register</a></li>
      <?php endif; ?>
    </ul>
  </nav>
  <!-- Bootstrap JS Bundle CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>
</html>
