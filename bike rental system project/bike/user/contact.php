<?php
session_start();
include 'user_navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us - Bike Rental</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 2rem; }
    h1 { text-align: center; margin-bottom: 2rem; }
    form { max-width: 600px; margin: 0 auto; }
    label { display: block; margin-bottom: .5rem; font-weight: bold; }
    input, textarea { width: 100%; padding: .75rem; margin-bottom: 1.5rem; border: 1px solid #ccc; border-radius: 5px; }
    button { padding: 0.75rem 1.5rem; background-color: #333; color: white; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background-color: #555; }
  </style>
</head>
<body>

<h1>Contact Us</h1>

<form method="POST" action="contact_submit.php">
  <label for="name">Your Name</label>
  <input type="text" id="name" name="name" required>

  <label for="email">Your Email</label>
  <input type="email" id="email" name="email" required>

  <label for="message">Your Message</label>
  <textarea id="message" name="message" rows="5" required></textarea>

  <button type="submit">Send Message</button>
</form>

</body>
</html>
