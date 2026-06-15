<?php
include 'db_connect.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);

    // Get cart items
    $sql    = "SELECT cart.product_id, cart.quantity, products.price 
               FROM cart 
               JOIN products ON cart.product_id = products.id
               WHERE cart.user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $items  = [];
    $total  = 0;

    while($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
        $total  += $row['price'] * $row['quantity'];
    }

    if(empty($items)) {
        header("Location: cart.php");
        exit();
    }

    // Insert order
    $order_sql = "INSERT INTO orders (user_id, total_amount, delivery_address) 
                  VALUES ($user_id, $total, '$delivery_address')";
    mysqli_query($conn, $order_sql);
    $order_id = mysqli_insert_id($conn);

    // Insert order items + reduce stock
    foreach($items as $item) {
        $pid = $item['product_id'];
        $qty = $item['quantity'];
        $price = $item['price'];

        mysqli_query($conn, 
            "INSERT INTO order_items (order_id, product_id, quantity, price) 
             VALUES ($order_id, $pid, $qty, $price)");

        mysqli_query($conn,
            "UPDATE products SET stock = stock - $qty WHERE id = $pid");
    }

    // Clear cart
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

    header("Location: orders.php?success=1");
    exit();
}
?>