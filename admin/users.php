<?php
session_start();
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$users = mysqli_query($conn,"
    SELECT *
    FROM users
    WHERE role='customer'
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Users - Admin</title>
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
        <a href="messages.php">Messages</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div style="max-width:1200px;margin:40px auto;padding:0 20px;">

    <h2 style="color:#2e7d32;">
        👥 Registered Users
    </h2>

    <table class="admin-table">

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Address</th>
        </tr>

        <?php while($u = mysqli_fetch_assoc($users)): ?>

        <tr>
            <td>#<?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['name']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo htmlspecialchars($u['phone']); ?></td>
            <td><?php echo htmlspecialchars($u['address']); ?></td>
        </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>
</html>