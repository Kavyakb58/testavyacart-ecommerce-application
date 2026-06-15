<?php
session_start();
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";


// Update order status
if(isset($_POST['update_status'])) {
    $order_id  = (int)$_POST['order_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE id=$order_id");
}

$orders = mysqli_query($conn,
    "SELECT orders.*, users.name, users.phone 
     FROM orders 
     JOIN users ON orders.user_id = users.id
     ORDER BY ordered_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">🛒 FreshCart Admin</a>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="users.php">Users</a>
        <a href="messages.php">
            Messages
            <?php
            $unread_count = mysqli_fetch_row(mysqli_query($conn,
                "SELECT COUNT(*) FROM contact_messages WHERE is_read=0"))[0];
            if($unread_count > 0): ?>
                <span style="background:#c62828; color:white; border-radius:50%;
                             padding:1px 6px; font-size:11px; margin-left:4px;">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
       <a href="logout.php">Logout</a>
    </div>
</nav>

<div style="max-width:1100px; margin:40px auto; padding:0 20px;">
    <h2 style="color:#2e7d32; margin-bottom:24px;">All Orders</h2>

    <table class="admin-table">
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Phone</th>
            <th>Items</th>
            <th>Total</th>
            <th>Address</th>
            <th>Date</th>
            <th>Status</th>
        </tr>
        <?php while($o = mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td>#<?php echo $o['id']; ?></td>
            <td><?php echo $o['name']; ?></td>
            <td><?php echo $o['phone']; ?></td>
            <td>
                <?php
                $oi = mysqli_query($conn,
                    "SELECT order_items.quantity, products.name 
                     FROM order_items 
                     JOIN products ON order_items.product_id = products.id
                     WHERE order_id=".$o['id']);
                while($item = mysqli_fetch_assoc($oi)) {
                    echo $item['name'].' x'.$item['quantity'].'<br>';
                }
                ?>
            </td>
            <td>₹<?php echo $o['total_amount']; ?></td>
            <td style="font-size:12px;"><?php echo $o['delivery_address']; ?></td>
            <td><?php echo date('d M Y', strtotime($o['ordered_at'])); ?></td>
            <td>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                    <select name="status" onchange="this.form.submit()" 
                            style="padding:4px; border-radius:4px; border:1px solid #ddd;">
                        <option <?php echo $o['status']=='pending'     ? 'selected':'' ?> value="pending">Pending</option>
                        <option <?php echo $o['status']=='processing'  ? 'selected':'' ?> value="processing">Processing</option>
                        <option <?php echo $o['status']=='delivered'   ? 'selected':'' ?> value="delivered">Delivered</option>
                        <option <?php echo $o['status']=='cancelled'   ? 'selected':'' ?> value="cancelled">Cancelled</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<footer class="footer">
    <p>© 2024 FreshCart | Admin Panel</p>
</footer>

</body>
</html>