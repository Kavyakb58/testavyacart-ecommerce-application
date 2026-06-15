<?php
session_start();
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";


// Stats
$total_users    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='customer'"))[0];
$total_products = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM products"))[0];
$total_orders   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0];
$total_revenue  = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'"))[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - FreshCart</title>
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
        <a href="../logout.php">Logout</a>
    </div>
</nav>

<div style="max-width:1100px; margin:40px auto; padding:0 20px;">
    <h2 style="color:#2e7d32; margin-bottom:24px;">Admin Dashboard</h2>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left:4px solid #2e7d32;">
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #ff6f00;">
            <div class="stat-number"><?php echo $total_products; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #1565c0;">
            <div class="stat-number"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card" style="border-left:4px solid #6a1b9a;">
            <div class="stat-number">₹<?php echo number_format($total_revenue, 2); ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <!-- Recent Orders -->
    <h3 style="margin:32px 0 16px; color:#333;">Recent Orders</h3>
    <table class="admin-table">
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php
        $orders = mysqli_query($conn,
            "SELECT orders.*, users.name 
             FROM orders 
             JOIN users ON orders.user_id = users.id
             ORDER BY ordered_at DESC LIMIT 10");
        while($o = mysqli_fetch_assoc($orders)):
        ?>
        <tr>
            <td>#<?php echo $o['id']; ?></td>
            <td><?php echo $o['name']; ?></td>
            <td>₹<?php echo $o['total_amount']; ?></td>
            <td>
                <span class="status-badge status-<?php echo $o['status']; ?>">
                    <?php echo ucfirst($o['status']); ?>
                </span>
            </td>
            <td><?php echo date('d M Y', strtotime($o['ordered_at'])); ?></td>
            <td><a href="orders.php?update=<?php echo $o['id']; ?>">Manage</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<footer class="footer">
    <p>© 2024 FreshCart | Admin Panel</p>
</footer>

</body>
</html>