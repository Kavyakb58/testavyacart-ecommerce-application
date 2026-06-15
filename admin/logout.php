<?php
session_start();

// Remove admin session only
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);

// Destroy session
session_destroy();

// Redirect to admin login
header("Location: login.php");
exit();
?>