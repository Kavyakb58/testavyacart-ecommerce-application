<?php
session_start();
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

// Add product
if(isset($_POST['add_product'])) {
    $name       = mysqli_real_escape_string($conn, $_POST['name']);
    $desc       = mysqli_real_escape_string($conn, $_POST['description']);
    $price      = (float)$_POST['price'];
    $stock      = (int)$_POST['stock'];
    $cat_id     = (int)$_POST['category_id'];
    $unit       = mysqli_real_escape_string($conn, $_POST['unit']);
    $unit_value = (float)$_POST['unit_value'];
    $image      = 'default.jpg';

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $image = time() . '_' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image);
        }
    }

    mysqli_query($conn,
        "INSERT INTO products (name, description, price, unit, unit_value, stock, category_id, image)
         VALUES ('$name', '$desc', $price, '$unit', $unit_value, $stock, $cat_id, '$image')");
    $msg = "✅ Product added successfully!";
}

// Delete product
if(isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM products WHERE id=$del_id"));
    if($p && $p['image'] != 'default.jpg' && file_exists('../uploads/'.$p['image'])) {
        unlink('../uploads/'.$p['image']);
    }
    mysqli_query($conn, "DELETE FROM products WHERE id=$del_id");
    header("Location: products.php");
    exit();
}

// Update stock
if(isset($_POST['update_stock'])) {
    $pid      = (int)$_POST['product_id'];
    $newstock = (int)$_POST['new_stock'];
    mysqli_query($conn, "UPDATE products SET stock=$newstock WHERE id=$pid");
    $msg = "✅ Stock updated!";
}

// Update product
if(isset($_POST['update_product'])) {
    $pid        = (int)$_POST['product_id'];
    $name       = mysqli_real_escape_string($conn, $_POST['name']);
    $desc       = mysqli_real_escape_string($conn, $_POST['description']);
    $price      = (float)$_POST['price'];
    $cat        = (int)$_POST['category_id'];
    $unit       = mysqli_real_escape_string($conn, $_POST['unit']);
    $unit_value = (float)$_POST['unit_value'];
    $image_sql  = "";

    if(isset($_FILES['edit_image_'.$pid]) && $_FILES['edit_image_'.$pid]['error'] == 0) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext     = strtolower(pathinfo($_FILES['edit_image_'.$pid]['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $newimage  = time() . '_' . basename($_FILES['edit_image_'.$pid]['name']);
            move_uploaded_file($_FILES['edit_image_'.$pid]['tmp_name'], '../uploads/' . $newimage);
            $image_sql = ", image='$newimage'";
        }
    }

    mysqli_query($conn,
        "UPDATE products
         SET name='$name', description='$desc', price=$price,
             unit='$unit', unit_value=$unit_value, category_id=$cat $image_sql
         WHERE id=$pid");
    $msg = "✅ Product updated!";
}

// Unit options
$units = ['kg','g','litre','ml','piece','dozen','pack','bundle'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-form  { display:none; background:#f9f9f9; padding:16px; border-radius:8px; margin-top:10px; }
        .btn-edit   { background:#1565c0; color:white; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; font-size:13px; }
        .btn-del    { background:#c62828; color:white; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; font-size:13px; }
        .prod-img   { width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
        .img-preview{ width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #ddd; margin-top:8px; }
        .upload-box {
            border:2px dashed #ddd; border-radius:8px; padding:12px;
            text-align:center; cursor:pointer; transition:border-color 0.2s;
            font-size:13px; color:#777;
        }
        .upload-box:hover { border-color:#2e7d32; color:#2e7d32; }
        .upload-box input[type=file] { display:none; }
        .unit-badge {
            display:inline-block; background:#e8f5e9; color:#2e7d32;
            padding:2px 8px; border-radius:12px; font-size:12px; font-weight:bold;
        }
        .unit-row { display:flex; gap:10px; }
        .unit-row input  { flex:1; }
        .unit-row select { flex:1; }
    </style>
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
                <span style="background:#c62828;color:white;border-radius:50%;
                             padding:1px 6px;font-size:11px;margin-left:4px;">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div style="max-width:1100px; margin:40px auto; padding:0 20px;">

    <?php if($msg) echo "<p class='success' style='padding:12px; margin-bottom:16px;'>$msg</p>"; ?>

    <!-- Add Product Form -->
    <div class="admin-form-card">
        <h3>➕ Add New Product</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <input type="text"   name="name"        placeholder="Product Name"   required>
                <input type="number" name="price"       placeholder="Price (₹)"      step="0.01" required>
                <input type="number" name="stock"       placeholder="Stock quantity" required>
            </div>

            <!-- Unit Row -->
            <div class="form-row" style="margin-bottom:12px;">
                <div style="flex:1;">
                    <label style="font-size:13px; font-weight:bold; color:#555; display:block; margin-bottom:4px;">
                        Unit Value <span style="color:#c62828;">*</span>
                    </label>
                    <input type="number" name="unit_value" placeholder="e.g. 500" step="0.01" min="0.01"
                           style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                           required>
                </div>
                <div style="flex:1;">
                    <label style="font-size:13px; font-weight:bold; color:#555; display:block; margin-bottom:4px;">
                        Unit <span style="color:#c62828;">*</span>
                    </label>
                    <select name="unit"
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                        <?php foreach($units as $u): ?>
                            <option value="<?php echo $u; ?>"><?php echo strtoupper($u); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:2;">
                    <label style="font-size:13px; font-weight:bold; color:#555; display:block; margin-bottom:4px;">
                        Category <span style="color:#c62828;">*</span>
                    </label>
                    <select name="category_id" required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;">
                        <option value="">Select Category</option>
                        <?php
                        $cats = mysqli_query($conn, "SELECT * FROM categories");
                        while($c = mysqli_fetch_assoc($cats)):
                        ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <input type="text" name="description" placeholder="Product description (e.g. Fresh from farm)">
            </div>

            <!-- Image Upload -->
            <div style="margin-bottom:14px;">
                <label style="font-size:13px; font-weight:bold; color:#555; display:block; margin-bottom:6px;">
                    Product Image
                </label>
                <div class="upload-box" onclick="document.getElementById('add_image').click()">
                    📷 Click to upload product image (JPG, PNG, WEBP)
                    <input type="file" id="add_image" name="image"
                           accept="image/*" onchange="previewImage(this, 'add_preview')">
                </div>
                <img id="add_preview" class="img-preview" style="display:none;" src="#">
            </div>

            <button type="submit" name="add_product" class="btn-add">Add Product</button>
        </form>
    </div>

    <!-- Products Table -->
    <h3 style="margin:32px 0 16px; color:#333;">
        All Products
        <span style="font-size:14px; color:#777; font-weight:normal;">
            (<?php echo mysqli_num_rows(mysqli_query($conn,"SELECT id FROM products")); ?> total)
        </span>
    </h3>

    <table class="admin-table">
        <tr>
            <th>#</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price / Unit</th>
            <th>Stock</th>
            <th>Update Stock</th>
            <th>Actions</th>
        </tr>

        <?php
        $products = mysqli_query($conn,
            "SELECT products.*, categories.name AS cat_name
             FROM products
             LEFT JOIN categories ON products.category_id = categories.id
             ORDER BY products.id DESC");

        while($p = mysqli_fetch_assoc($products)):
            $img_src = file_exists('../uploads/'.$p['image'])
                       ? '../uploads/'.$p['image']
                       : '../uploads/default.jpg';
        ?>
        <tr>
            <td>#<?php echo $p['id']; ?></td>
            <td>
                <img src="<?php echo $img_src; ?>" class="prod-img" alt="<?php echo htmlspecialchars($p['name']); ?>">
            </td>
            <td>
                <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
                <span style="font-size:12px; color:#777;"><?php echo htmlspecialchars($p['description']); ?></span>

                <!-- Inline Edit Form -->
                <div class="edit-form" id="edit-<?php echo $p['id']; ?>">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <div class="form-row">
                            <input type="text"   name="name"  value="<?php echo htmlspecialchars($p['name']); ?>" required>
                            <input type="number" name="price" value="<?php echo $p['price']; ?>" step="0.01" required>
                        </div>

                        <!-- Unit edit row -->
                        <div class="form-row" style="margin-bottom:10px;">
                            <div style="flex:1;">
                                <label style="font-size:12px; color:#555; font-weight:bold;">Unit Value</label>
                                <input type="number" name="unit_value" value="<?php echo $p['unit_value']; ?>"
                                       step="0.01" min="0.01"
                                       style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; margin-top:4px;">
                            </div>
                            <div style="flex:1;">
                                <label style="font-size:12px; color:#555; font-weight:bold;">Unit</label>
                                <select name="unit"
                                        style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; margin-top:4px;">
                                    <?php foreach($units as $u): ?>
                                        <option value="<?php echo $u; ?>"
                                            <?php echo $u == $p['unit'] ? 'selected' : ''; ?>>
                                            <?php echo strtoupper($u); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex:2;">
                                <label style="font-size:12px; color:#555; font-weight:bold;">Category</label>
                                <select name="category_id"
                                        style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; margin-top:4px;">
                                    <?php
                                    $cats2 = mysqli_query($conn, "SELECT * FROM categories");
                                    while($c2 = mysqli_fetch_assoc($cats2)):
                                    ?>
                                        <option value="<?php echo $c2['id']; ?>"
                                            <?php echo $c2['id']==$p['category_id'] ? 'selected':''; ?>>
                                            <?php echo $c2['name']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <input type="text" name="description" value="<?php echo htmlspecialchars($p['description']); ?>">
                        </div>

                        <!-- Edit image -->
                        <div style="margin:10px 0;">
                            <label style="font-size:12px; color:#555; font-weight:bold;">Change Image (optional)</label>
                            <div class="upload-box" style="margin-top:6px;"
                                 onclick="document.getElementById('edit_img_<?php echo $p['id']; ?>').click()">
                                📷 Click to change image
                                <input type="file" id="edit_img_<?php echo $p['id']; ?>"
                                       name="edit_image_<?php echo $p['id']; ?>"
                                       accept="image/*"
                                       onchange="previewImage(this, 'edit_preview_<?php echo $p['id']; ?>')">
                            </div>
                            <img id="edit_preview_<?php echo $p['id']; ?>"
                                 src="<?php echo $img_src; ?>"
                                 class="img-preview" style="display:block;">
                        </div>

                        <button type="submit" name="update_product" class="btn-add"
                                style="padding:6px 16px; font-size:13px;">
                            Save Changes
                        </button>
                        <button type="button" onclick="toggleEdit(<?php echo $p['id']; ?>)"
                                style="padding:6px 16px; font-size:13px; background:#777;
                                       color:white; border:none; border-radius:4px; cursor:pointer; margin-left:6px;">
                            Cancel
                        </button>
                    </form>
                </div>
            </td>

            <td><?php echo htmlspecialchars($p['cat_name']); ?></td>

            <!-- ✅ Price with unit -->
            <td>
                <strong style="color:#2e7d32;">₹<?php echo $p['price']; ?></strong><br>
                <span class="unit-badge">
                    <?php echo $p['unit_value']; ?> <?php echo $p['unit']; ?>
                </span>
            </td>

            <td>
                <span style="color:<?php echo $p['stock'] > 10 ? '#2e7d32' : ($p['stock'] > 0 ? '#ff6f00' : '#c62828'); ?>;
                             font-weight:bold;">
                    <?php echo $p['stock']; ?>
                </span>
            </td>

            <td>
                <form method="POST" style="display:flex; gap:6px; align-items:center;">
                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                    <input type="number" name="new_stock" value="<?php echo $p['stock']; ?>"
                           style="width:65px; padding:5px; border:1px solid #ddd; border-radius:4px;">
                    <button type="submit" name="update_stock" class="btn-edit">Save</button>
                </form>
            </td>

            <td style="white-space:nowrap;">
                <button class="btn-edit" onclick="toggleEdit(<?php echo $p['id']; ?>)">Edit</button>
                &nbsp;
                <a href="products.php?delete=<?php echo $p['id']; ?>"
                   onclick="return confirm('Delete <?php echo htmlspecialchars($p['name']); ?>?')">
                    <button class="btn-del">Delete</button>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Low stock warning -->
    <?php
    $low = mysqli_query($conn, "SELECT * FROM products WHERE stock <= 5 AND stock > 0");
    if(mysqli_num_rows($low) > 0):
    ?>
    <div style="margin-top:30px; background:#fff3e0; border-left:4px solid #ff6f00;
                padding:16px; border-radius:6px;">
        <strong style="color:#e65100;">⚠️ Low Stock Warning:</strong>
        <ul style="margin-top:8px; color:#555;">
            <?php while($lp = mysqli_fetch_assoc($low)): ?>
                <li>
                    <?php echo htmlspecialchars($lp['name']); ?>
                    (<?php echo $lp['unit_value'].' '.$lp['unit']; ?>)
                    — only <?php echo $lp['stock']; ?> left
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php endif; ?>

</div>

<footer class="footer"><p>© 2024 FreshCart | Admin Panel</p></footer>

<script>
function toggleEdit(id) {
    var form = document.getElementById('edit-' + id);
    form.style.display = form.style.display === 'block' ? 'none' : 'block';
}
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>