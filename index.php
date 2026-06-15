<?php
include 'db_connect.php';
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>FreshCart - Online Grocery</title>
    <link rel="stylesheet" href="css/style.css">
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

<!-- Hero Banner -->
<div class="hero">
    <h1>Fresh Groceries Delivered to Your Door</h1>
    <p>Order fresh vegetables, dairy, snacks and more from the comfort of your home</p>
    <a href="products.php" class="btn-hero">Shop Now</a>
</div>

<!-- Categories Section -->
<div class="section-title">Shop by Category</div>
<div class="categories-grid">
<?php
$cats = mysqli_query($conn, "SELECT * FROM categories");
$icons = ['🥦', '🥛', '🍿', '🥤', '🌾'];
$i = 0;
while($cat = mysqli_fetch_assoc($cats)):
?>
    <a href="products.php?category=<?php echo $cat['id']; ?>" class="category-card">
        <div class="cat-icon"><?php echo $icons[$i % 5]; ?></div>
        <div class="cat-name"><?php echo $cat['name']; ?></div>
    </a>
<?php $i++; endwhile; ?>
</div>

<!-- Featured Products -->
<div class="section-title">Featured Products</div>
<div class="products-grid">
<?php
$products = mysqli_query($conn, "SELECT * FROM products LIMIT 6");
while($p = mysqli_fetch_assoc($products)):
?>
    <div class="product-card">
        <div class="product-img-wrap">
    <?php
    $img_src = (!empty($p['image']) && file_exists('uploads/'.$p['image']))
               ? 'uploads/'.$p['image']
               : 'uploads/default.jpg';
    ?>
    <img src="<?php echo $img_src; ?>"
         alt="<?php echo $p['name']; ?>"
         class="product-img">
</div>
        <h3><?php echo $p['name']; ?></h3>
        <p class="desc"><?php echo $p['description']; ?></p>
<p class="price">
    ₹<?php echo $p['price']; ?>
    <span style="font-size:13px; font-weight:normal; color:#777;">
        / <?php echo $p['unit_value']; ?> <?php echo $p['unit']; ?>
    </span>
</p>        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="add_to_cart.php?id=<?php echo $p['id']; ?>" class="btn-add">Add to Cart</a>
        <?php else: ?>
            <a href="login.php" class="btn-add">Login to Buy</a>
        <?php endif; ?>
    </div>
<?php endwhile; ?>
</div>

<!-- Footer -->
<footer class="footer">
    <p>© 2024 FreshCart | Online Grocery Shopping | BCA Project</p>
</footer>

</body>
</html>