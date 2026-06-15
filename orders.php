
<?php
include 'db_connect.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders  = mysqli_query($conn, 
    "SELECT * FROM orders WHERE user_id=$user_id ORDER BY ordered_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - FreshCart</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🛒 FreshCart</a>
    <div>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="contact.php">Contact</a>
        <a href="profile.php">My Profile</a>
        <a href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a>
    </div>
</nav>

<div class="cart-container">
    <h2 class="page-heading">My Orders</h2>

    <?php if(isset($_GET['success'])): ?>
        <div class="success" style="text-align:center; font-size:16px; padding:16px;">
            ✅ Order placed successfully! We will deliver soon.
        </div>
    <?php endif; ?>

    <?php if(mysqli_num_rows($orders) == 0): ?>
        <div style="text-align:center; padding:60px;">
            <p style="font-size:18px;">No orders yet!</p>
            <a href="products.php" class="btn-add" style="margin-top:16px; display:inline-block;">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <?php while($order = mysqli_fetch_assoc($orders)): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong>Order #<?php echo $order['id']; ?></strong>
                    <span style="color:#777; font-size:13px; margin-left:12px;">
                        <?php echo date('d M Y, h:i A', strtotime($order['ordered_at'])); ?>
                    </span>
                </div>
                <div>
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>

            <p style="color:#555; margin:8px 0;">
                📍 <?php echo $order['delivery_address']; ?>
            </p>

            <!-- Order items -->
            <?php
            $oi = mysqli_query($conn,
                "SELECT order_items.*, products.name 
                 FROM order_items 
                 JOIN products ON order_items.product_id = products.id
                 WHERE order_id = ".$order['id']);
            ?>
            <table class="order-table">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
                <?php while($oi_row = mysqli_fetch_assoc($oi)): ?>
                <tr>
                    <td><?php echo $oi_row['name']; ?></td>
                    <td><?php echo $oi_row['quantity']; ?></td>
                    <td>₹<?php echo $oi_row['price']; ?></td>
                    <td>₹<?php echo $oi_row['price'] * $oi_row['quantity']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

            <div style="text-align:right; font-weight:bold; color:#2e7d32; margin-top:10px;">
                Total: ₹<?php echo $order['total_amount']; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>© 2024 FreshCart | Online Grocery Shopping | BCA Project</p>
</footer>

</body>
</html>