<?php
include 'db_connect.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $user_id    = $_SESSION['user_id'];

    // Check if already in cart
    $check = mysqli_query($conn, 
        "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");

    if(mysqli_num_rows($check) > 0) {
        // Increase quantity
        mysqli_query($conn, 
            "UPDATE cart SET quantity = quantity + 1 
             WHERE user_id=$user_id AND product_id=$product_id");
    } else {
        // Add new item
        mysqli_query($conn, 
            "INSERT INTO cart (user_id, product_id, quantity) 
             VALUES ($user_id, $product_id, 1)");
    }
}

header("Location: cart.php");
exit();
?>