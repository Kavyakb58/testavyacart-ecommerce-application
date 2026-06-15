<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

if(isset($_POST['update_profile'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    mysqli_query($conn,"
        UPDATE users
        SET
            name='$name',
            email='$email',
            phone='$phone',
            address='$address'
        WHERE id=$user_id
    ");

    $_SESSION['user_name'] = $name;

    $msg = "✅ Profile updated successfully!";
}

$user = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT * FROM users WHERE id=$user_id")
);
?>

<!DOCTYPE html>

<html>
<head>
    <title>My Profile - FreshCart</title>
    <link rel="stylesheet" href="css/style.css">


<style>
    body{
        background:#f5f5f5;
        margin:0;
        font-family:Arial,sans-serif;
    }

    .profile-container{
        max-width:700px;
        margin:40px auto;
        background:#fff;
        padding:30px;
        border-radius:10px;
        box-shadow:0 2px 10px rgba(0,0,0,0.1);
    }

    .profile-title{
        text-align:center;
        color:#2e7d32;
        margin-bottom:25px;
    }

    label{
        display:block;
        font-weight:bold;
        margin-bottom:5px;
        color:#555;
    }

    input,
    textarea{
        width:100%;
        padding:12px;
        border:1px solid #ddd;
        border-radius:6px;
        margin-bottom:15px;
        box-sizing:border-box;
    }

    textarea{
        height:100px;
        resize:none;
    }

    .btn-save{
        background:#2e7d32;
        color:white;
        border:none;
        padding:12px 25px;
        border-radius:6px;
        cursor:pointer;
        font-size:15px;
    }

    .btn-save:hover{
        background:#1b5e20;
    }

    .success{
        background:#e8f5e9;
        color:#2e7d32;
        padding:12px;
        border-radius:6px;
        margin-bottom:15px;
    }
</style>


</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <a href="index.php" class="logo">🛒 FreshCart</a>
    <div>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="products.php">Products</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
            <a href="contact.php">Contact</a>
            <a href="profile.php">My Profile</a>
           <a href="logout.php">Logout (<?php echo $_SESSION['user_name']; ?>)</a>
        <?php else: ?>
            <a href="products.php">Products</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="profile-container">


<h2 class="profile-title">👤 My Profile</h2>

<?php if($msg): ?>
    <div class="success">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<form method="POST">

    <label>Full Name</label>
    <input type="text"
           name="name"
           value="<?php echo htmlspecialchars($user['name']); ?>"
           required>

    <label>Email Address</label>
    <input type="email"
           name="email"
           value="<?php echo htmlspecialchars($user['email']); ?>"
           required>

    <label>Mobile Number</label>
    <input type="text"
           name="phone"
           value="<?php echo htmlspecialchars($user['phone']); ?>"
           required>

    <label>Delivery Address</label>
    <textarea name="address" required><?php echo htmlspecialchars($user['address']); ?></textarea>

    <button type="submit"
            name="update_profile"
            class="btn-save">
        Update Profile
    </button>

</form>


</div>

</body>
</html>
