<?php
include 'db_connect.php';
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products - FreshCart</title>
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

<!-- Category Filter -->
<div class="filter-bar">
    <a href="products.php" class="filter-btn <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All</a>
    <?php
    $cats = mysqli_query($conn, "SELECT * FROM categories");
    while($cat = mysqli_fetch_assoc($cats)):
    ?>
        <a href="products.php?category=<?php echo $cat['id']; ?>" 
           class="filter-btn <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : ''; ?>">
            <?php echo $cat['name']; ?>
        </a>
    <?php endwhile; ?>
</div>

<!-- Search Bar -->
<div class="search-bar">
    <form method="GET">
        <?php if(isset($_GET['category'])): ?>
            <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
        <?php endif; ?>
        <input type="text" name="search" placeholder="Search products..." 
               value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
        <button type="submit">Search</button>
    </form>
</div>

<!-- Products Grid -->
<h2 class="page-heading">Our Products</h2>
<div class="products-grid">
<?php
$where = "WHERE 1=1";
if(isset($_GET['category']) && !empty($_GET['category'])) {
    $cat_id = (int)$_GET['category'];
    $where .= " AND category_id = $cat_id";
}
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

$products = mysqli_query($conn, "SELECT * FROM products $where");
$count = mysqli_num_rows($products);

if($count == 0):
?>
    <p style="text-align:center; padding:40px; grid-column:1/-1;">
        No products found. <a href="products.php">View all products</a>
    </p>
<?php else: ?>
    <?php while($p = mysqli_fetch_assoc($products)): ?>
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
            <p class="price">₹<?php echo $p['price']; ?></p>
            <p class="stock <?php echo $p['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                <?php echo $p['stock'] > 0 ? 'In Stock ('.$p['stock'].')' : 'Out of Stock'; ?>
            </p>
            <?php if($p['stock'] > 0): ?>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="add_to_cart.php?id=<?php echo $p['id']; ?>" class="btn-add">Add to Cart</a>
                <?php else: ?>
                    <a href="login.php" class="btn-add">Login to Buy</a>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn-add" disabled style="background:#ccc;">Out of Stock</button>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
</div>

<footer class="footer">
    <p>© 2024 FreshCart | Online Grocery Shopping | BCA Project</p>
</footer>

</body>
</html>