<?php
session_start();
include 'db_connect.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Remove item
if(isset($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    mysqli_query($conn, "DELETE FROM cart WHERE id=$remove_id AND user_id=$user_id");
    header("Location: cart.php");
    exit();
}

// Update quantity
if(isset($_POST['update_qty'])) {
    $cart_id = (int)$_POST['cart_id'];
    $qty     = (int)$_POST['quantity'];
    if($qty < 1) $qty = 1;
    mysqli_query($conn, "UPDATE cart SET quantity=$qty WHERE id=$cart_id AND user_id=$user_id");
    header("Location: cart.php");
    exit();
}

// Get cart items with unit info
$sql    = "SELECT cart.id, products.name, products.price, products.description,
                  products.stock, products.unit, products.unit_value, cart.quantity,
                  (products.price * cart.quantity) AS subtotal
           FROM cart
           JOIN products ON cart.product_id = products.id
           WHERE cart.user_id = $user_id";
$result = mysqli_query($conn, $sql);
$total  = 0;
$items  = [];
while($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
    $total  += $row['subtotal'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Cart - FreshCart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .payment-section {
            background: white;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-top: 20px;
        }
        .payment-tabs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }
        .pay-tab {
            padding: 12px 8px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-size: 13px;
            background: white;
            color: #333;
            transition: all 0.2s;
            line-height: 1.4;
        }
        .pay-tab .tab-icon { font-size: 22px; display: block; margin-bottom: 4px; }
        .pay-tab.active   { border-color: #2e7d32; background: #e8f5e9; color: #2e7d32; font-weight: bold; }
        .pay-tab:hover    { border-color: #2e7d32; }
        .card-form        { display: none; }
        .card-form.active { display: block; }
        .field-group { margin-bottom: 14px; }
        .field-group label {
            display: block; font-size: 13px; font-weight: bold;
            color: #555; margin-bottom: 5px;
        }
        .field-group label span { color: #c62828; margin-left:2px; }
        .field-group input {
            width: 100%; padding: 11px 12px; border: 1.5px solid #ddd;
            border-radius: 6px; font-size: 14px; outline: none;
            box-sizing: border-box; transition: border-color 0.2s;
        }
        .field-group input:focus  { border-color: #2e7d32; }
        .field-group input.valid  { border-color: #2e7d32; }
        .field-group input.invalid{ border-color: #c62828; }
        .field-error { font-size: 12px; color: #c62828; margin-top: 3px; display: none; }
        .field-error.show { display: block; }
        .form-row { display: flex; gap: 12px; }
        .form-row .field-group { flex: 1; }
        .card-icons { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .card-icon  {
            background: #f5f5f5; border: 1px solid #ddd;
            border-radius: 4px; padding: 4px 10px; font-size: 12px; color: #555;
        }
        .card-number-input { font-family: monospace; font-size: 16px !important; letter-spacing: 2px; }
        .upi-container { display: flex; gap: 24px; align-items: flex-start; flex-wrap: wrap; }
        .qr-box {
            background: white; border: 2px solid #e0e0e0;
            border-radius: 12px; padding: 16px; text-align: center; min-width: 180px;
        }
        .qr-code {
            width: 160px; height: 160px; background: white; border: 1px solid #ddd;
            border-radius: 8px; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 8px; overflow: hidden;
        }
        .qr-label { font-size: 12px; color: #777; margin-top: 6px; }
        .upi-right { flex: 1; min-width: 200px; }
        .upi-apps  { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .upi-app-btn {
            padding: 10px 16px; border: 2px solid #ddd; border-radius: 8px;
            cursor: pointer; font-size: 13px; background: white;
            transition: all 0.2s; display: flex; align-items: center; gap: 6px;
        }
        .upi-app-btn:hover,
        .upi-app-btn.selected { border-color: #2e7d32; background: #e8f5e9; color: #2e7d32; }
        .upi-divider {
            text-align: center; color: #aaa; font-size: 13px;
            margin: 12px 0; position: relative;
        }
        .upi-divider::before, .upi-divider::after {
            content: ''; position: absolute; top: 50%;
            width: 42%; height: 1px; background: #ddd;
        }
        .upi-divider::before { left: 0; }
        .upi-divider::after  { right: 0; }
        .cod-box {
            background: #f9f9f9; border: 2px dashed #ddd;
            border-radius: 8px; padding: 30px; text-align: center;
        }
        .secure-badge {
            display: flex; align-items: center; justify-content: center;
            gap: 6px; color: #2e7d32; font-size: 13px; margin-top: 16px;
        }
        .input-with-toggle { position: relative; }
        .input-with-toggle input { padding-right: 44px; }
        .toggle-cvv {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%); cursor: pointer; font-size: 16px; color: #777;
        }
        /* Unit badge */
        .unit-tag {
            display: inline-block; background: #e8f5e9; color: #2e7d32;
            padding: 2px 8px; border-radius:12px; font-size:12px;
            font-weight: bold; margin-left: 6px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🛒 FreshCart</a>
    <div>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">My Orders</a>
        <a href="contact.php">Contact</a>
        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <a href="admin/dashboard.php">⚙️ Admin Panel</a>
        <?php endif; ?>
        <a href="profile.php">My Profile</a>
        <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
    </div>
</nav>

<div class="cart-container">
    <h2 class="page-heading">My Cart</h2>

    <?php if(empty($items)): ?>
        <div style="text-align:center; padding:60px;">
            <div style="font-size:64px; margin-bottom:16px;">🛒</div>
            <p style="font-size:18px; color:#777;">Your cart is empty!</p>
            <a href="products.php" class="btn-add"
               style="margin-top:20px; display:inline-block; padding:12px 28px;">
                Browse Products
            </a>
        </div>
    <?php else: ?>

        <!-- Cart Items -->
        <?php foreach($items as $item): ?>
        <div class="cart-item">
            <div style="flex:1;">
                <strong style="font-size:16px;"><?php echo htmlspecialchars($item['name']); ?></strong>
                <span class="unit-tag">
                    <?php echo $item['unit_value'].' '.strtoupper($item['unit']); ?>
                </span>
                <p style="color:#777; font-size:13px; margin:4px 0;">
                    <?php echo htmlspecialchars($item['description']); ?>
                </p>
                <p style="color:#2e7d32; font-weight:bold; margin:0;">
                    ₹<?php echo $item['price']; ?>
                    <span style="font-size:12px; font-weight:normal; color:#777;">
                        per <?php echo $item['unit_value'].' '.$item['unit']; ?>
                    </span>
                </p>
            </div>

            <div style="display:flex; align-items:center; gap:10px; margin:0 20px;">
                <form method="POST" style="display:flex; align-items:center; gap:6px;">
                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                    <label style="font-size:13px; color:#555;">Qty:</label>
                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                           min="1" max="<?php echo $item['stock']; ?>"
                           style="width:60px; padding:5px; border:1px solid #ddd; border-radius:4px;">
                    <button type="submit" name="update_qty"
                            style="padding:5px 10px; background:#1565c0; color:white;
                                   border:none; border-radius:4px; cursor:pointer; font-size:12px;">
                        Update
                    </button>
                </form>
            </div>

            <div style="text-align:right; min-width:100px;">
                <p class="price" style="margin-bottom:4px;">₹<?php echo $item['subtotal']; ?></p>
                <p style="font-size:12px; color:#777; margin-bottom:8px;">
                    <?php echo $item['quantity']; ?> × ₹<?php echo $item['price']; ?>
                </p>
                <a href="cart.php?remove=<?php echo $item['id']; ?>"
                   style="color:#c62828; font-size:13px; text-decoration:none;"
                   onclick="return confirm('Remove this item?')">
                    🗑️ Remove
                </a>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Order Summary -->
        <div style="background:white; border-radius:10px; padding:24px;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08); margin-top:20px;">
            <h3 style="margin-bottom:16px; color:#333;">Order Summary</h3>
            <?php foreach($items as $item): ?>
            <div style="display:flex; justify-content:space-between;
                        margin-bottom:6px; font-size:13px; color:#555;">
                <span>
                    <?php echo htmlspecialchars($item['name']); ?>
                    <span style="color:#2e7d32; font-size:12px;">
                        (<?php echo $item['unit_value'].' '.$item['unit']; ?>)
                    </span>
                    × <?php echo $item['quantity']; ?>
                </span>
                <span>₹<?php echo $item['subtotal']; ?></span>
            </div>
            <?php endforeach; ?>
            <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="color:#777;">Delivery</span>
                <span style="color:#2e7d32; font-weight:bold;">FREE</span>
            </div>
            <hr style="margin:12px 0; border:none; border-top:1px solid #eee;">
            <div style="display:flex; justify-content:space-between; font-size:20px; font-weight:bold;">
                <span>Total</span>
                <span style="color:#2e7d32;">₹<?php echo $total; ?></span>
            </div>
        </div>

        <!-- Main Order Form -->
        <form method="POST" action="place_order.php" id="orderForm" onsubmit="return validatePayment()">
            <input type="hidden" name="total_amount"   value="<?php echo $total; ?>">
            <input type="hidden" name="payment_method" id="payment_method" value="credit_card">
            <input type="hidden" name="card_name"      id="hidden_card_name">
            <input type="hidden" name="card_last4"     id="hidden_card_last4">
            <input type="hidden" name="upi_id"         id="hidden_upi_id">

            <!-- Delivery Address -->
            <div class="payment-section">
                <h3 style="margin-bottom:16px; color:#333;">📍 Delivery Address</h3>
                <div class="field-group">
                    <label>Full Delivery Address <span>*</span></label>
                    <textarea name="delivery_address" id="delivery_address"
                              placeholder="House no, Street, Area, City, Pincode..."
                              required
                              style="width:100%; padding:12px; border:1.5px solid #ddd;
                                     border-radius:6px; height:90px; font-size:14px;
                                     resize:none; box-sizing:border-box; outline:none;
                                     transition:border-color 0.2s;"
                              oninput="validateAddress()"></textarea>
                    <div class="field-error" id="address_error">Delivery address is required!</div>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="payment-section">
                <h3 style="margin-bottom:16px; color:#333;">💳 Choose Payment Method</h3>

                <div class="payment-tabs">
                    <button type="button" class="pay-tab active" onclick="switchTab('credit_card', this)">
                        <span class="tab-icon">💳</span>Credit Card
                    </button>
                    <button type="button" class="pay-tab" onclick="switchTab('debit_card', this)">
                        <span class="tab-icon">🏧</span>Debit Card
                    </button>
                    <button type="button" class="pay-tab" onclick="switchTab('upi', this)">
                        <span class="tab-icon">📱</span>UPI / Scanner
                    </button>
                    <button type="button" class="pay-tab" onclick="switchTab('cod', this)">
                        <span class="tab-icon">💵</span>Cash on Delivery
                    </button>
                </div>

                <!-- CREDIT CARD -->
                <div class="card-form active" id="form_credit_card">
                    <div class="card-icons">
                        <span class="card-icon">💳 VISA</span>
                        <span class="card-icon">💳 Mastercard</span>
                        <span class="card-icon">💳 RuPay</span>
                        <span class="card-icon">💳 Amex</span>
                    </div>
                    <div class="field-group">
                        <label>Cardholder Name <span>*</span></label>
                        <input type="text" id="cc_name" placeholder="Name as on card"
                               maxlength="50" oninput="liveValidate('cc_name')">
                        <div class="field-error" id="cc_name_error">Cardholder name is required!</div>
                    </div>
                    <div class="field-group">
                        <label>Card Number <span>*</span></label>
                        <input type="text" id="cc_number" placeholder="1234  5678  9012  3456"
                               maxlength="19" class="card-number-input"
                               oninput="formatCardNumber(this); liveValidate('cc_number')">
                        <div class="field-error" id="cc_number_error">Enter a valid 16-digit card number!</div>
                    </div>
                    <div class="form-row">
                        <div class="field-group">
                            <label>Expiry Date <span>*</span></label>
                            <input type="text" id="cc_expiry" placeholder="MM/YY"
                                   maxlength="5" oninput="formatExpiry(this); liveValidate('cc_expiry')">
                            <div class="field-error" id="cc_expiry_error">Enter valid expiry MM/YY!</div>
                        </div>
                        <div class="field-group">
                            <label>CVV <span>*</span></label>
                            <div class="input-with-toggle">
                                <input type="password" id="cc_cvv" placeholder="3 digits"
                                       maxlength="3" oninput="liveValidate('cc_cvv')">
                                <span class="toggle-cvv" onclick="toggleCVV('cc_cvv')">👁️</span>
                            </div>
                            <div class="field-error" id="cc_cvv_error">Enter valid 3-digit CVV!</div>
                        </div>
                    </div>
                    <div style="background:#e8f5e9; border-radius:6px; padding:12px;
                                font-size:13px; color:#2e7d32; margin-top:4px;">
                        🔒 Your card details are 256-bit SSL encrypted.
                    </div>
                </div>

                <!-- DEBIT CARD -->
                <div class="card-form" id="form_debit_card">
                    <div class="card-icons">
                        <span class="card-icon">🏧 VISA Debit</span>
                        <span class="card-icon">🏧 Maestro</span>
                        <span class="card-icon">🏧 RuPay</span>
                    </div>
                    <div class="field-group">
                        <label>Cardholder Name <span>*</span></label>
                        <input type="text" id="dc_name" placeholder="Name as on card"
                               maxlength="50" oninput="liveValidate('dc_name')">
                        <div class="field-error" id="dc_name_error">Cardholder name is required!</div>
                    </div>
                    <div class="field-group">
                        <label>Card Number <span>*</span></label>
                        <input type="text" id="dc_number" placeholder="1234  5678  9012  3456"
                               maxlength="19" class="card-number-input"
                               oninput="formatCardNumber(this); liveValidate('dc_number')">
                        <div class="field-error" id="dc_number_error">Enter a valid 16-digit card number!</div>
                    </div>
                    <div class="form-row">
                        <div class="field-group">
                            <label>Expiry Date <span>*</span></label>
                            <input type="text" id="dc_expiry" placeholder="MM/YY"
                                   maxlength="5" oninput="formatExpiry(this); liveValidate('dc_expiry')">
                            <div class="field-error" id="dc_expiry_error">Enter valid expiry MM/YY!</div>
                        </div>
                        <div class="field-group">
                            <label>CVV <span>*</span></label>
                            <div class="input-with-toggle">
                                <input type="password" id="dc_cvv" placeholder="3 digits"
                                       maxlength="3" oninput="liveValidate('dc_cvv')">
                                <span class="toggle-cvv" onclick="toggleCVV('dc_cvv')">👁️</span>
                            </div>
                            <div class="field-error" id="dc_cvv_error">Enter valid 3-digit CVV!</div>
                        </div>
                    </div>
                    <div style="background:#e8f5e9; border-radius:6px; padding:12px;
                                font-size:13px; color:#2e7d32; margin-top:4px;">
                        🔒 Your card details are 256-bit SSL encrypted.
                    </div>
                </div>

                <!-- UPI -->
                <div class="card-form" id="form_upi">
                    <div class="upi-container">
                        <div class="qr-box">
                            <p style="font-size:13px; font-weight:bold; color:#333; margin-bottom:10px;">
                                Scan to Pay
                            </p>
                            <div class="qr-code">
                                <svg width="150" height="150" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="150" height="150" fill="white"/>
                                    <rect x="10" y="10" width="40" height="40" fill="none" stroke="#222" stroke-width="4"/>
                                    <rect x="18" y="18" width="24" height="24" fill="#222"/>
                                    <rect x="100" y="10" width="40" height="40" fill="none" stroke="#222" stroke-width="4"/>
                                    <rect x="108" y="18" width="24" height="24" fill="#222"/>
                                    <rect x="10" y="100" width="40" height="40" fill="none" stroke="#222" stroke-width="4"/>
                                    <rect x="18" y="108" width="24" height="24" fill="#222"/>
                                    <rect x="58" y="10" width="6" height="6" fill="#222"/>
                                    <rect x="66" y="10" width="6" height="6" fill="#222"/>
                                    <rect x="58" y="18" width="6" height="6" fill="#222"/>
                                    <rect x="74" y="18" width="6" height="6" fill="#222"/>
                                    <rect x="82" y="10" width="6" height="6" fill="#222"/>
                                    <rect x="58" y="58" width="6" height="6" fill="#222"/>
                                    <rect x="74" y="58" width="6" height="6" fill="#222"/>
                                    <rect x="66" y="66" width="6" height="6" fill="#222"/>
                                    <rect x="82" y="58" width="6" height="6" fill="#222"/>
                                    <rect x="58" y="74" width="6" height="6" fill="#222"/>
                                    <rect x="74" y="74" width="6" height="6" fill="#222"/>
                                    <rect x="100" y="58" width="6" height="6" fill="#222"/>
                                    <rect x="116" y="58" width="6" height="6" fill="#222"/>
                                    <rect x="58" y="100" width="6" height="6" fill="#222"/>
                                    <rect x="74" y="108" width="6" height="6" fill="#222"/>
                                    <rect x="100" y="100" width="6" height="6" fill="#222"/>
                                    <rect x="116" y="100" width="6" height="6" fill="#222"/>
                                    <rect x="62" y="62" width="26" height="26" fill="white" rx="4"/>
                                    <text x="75" y="80" text-anchor="middle"
                                          font-size="9" font-weight="bold" fill="#6739b7">UPI</text>
                                </svg>
                            </div>
                            <p class="qr-label">UPI ID: freshcart@upi</p>
                            <p style="font-size:13px; font-weight:bold; color:#2e7d32; margin-top:6px;">
                                ₹<?php echo $total; ?>
                            </p>
                        </div>
                        <div class="upi-right">
                            <p style="font-size:14px; font-weight:bold; color:#333; margin-bottom:12px;">
                                Pay using UPI app
                            </p>
                            <div class="upi-apps">
                                <button type="button" class="upi-app-btn" onclick="selectUpiApp(this, 'gpay')">
                                    🟢 GPay
                                </button>
                                <button type="button" class="upi-app-btn" onclick="selectUpiApp(this, 'phonepe')">
                                    🟣 PhonePe
                                </button>
                                <button type="button" class="upi-app-btn" onclick="selectUpiApp(this, 'paytm')">
                                    🔵 Paytm
                                </button>
                                <button type="button" class="upi-app-btn" onclick="selectUpiApp(this, 'bhim')">
                                    🟠 BHIM
                                </button>
                            </div>
                            <div class="upi-divider">or enter UPI ID manually</div>
                            <div class="field-group" style="margin-top:12px;">
                                <label>UPI ID <span>*</span></label>
                                <input type="text" id="upi_id"
                                       placeholder="yourname@upi / mobile@paytm"
                                       oninput="liveValidate('upi_id')">
                                <div class="field-error" id="upi_id_error">
                                    Enter a valid UPI ID (e.g. name@upi)
                                </div>
                            </div>
                            <div style="background:#f3e5f5; border-radius:6px; padding:12px;
                                        font-size:13px; color:#6a1b9a; margin-top:8px;">
                                📱 Open your UPI app → Scan QR or enter UPI ID →
                                Pay ₹<?php echo $total; ?> → Enter your UPI ID above to confirm
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COD -->
                <div class="card-form" id="form_cod">
                    <div class="cod-box">
                        <div style="font-size:56px; margin-bottom:12px;">💵</div>
                        <h3 style="margin-bottom:8px; color:#333;">Cash on Delivery</h3>
                        <p style="font-size:14px; color:#777; line-height:1.7;">
                            Pay with cash when your order is delivered.<br>
                            Please keep exact change ready.
                        </p>
                        <p style="margin-top:16px; font-size:20px; font-weight:bold; color:#2e7d32;">
                            Amount to pay: ₹<?php echo $total; ?>
                        </p>
                    </div>
                </div>

                <div class="secure-badge">
                    🔐 100% Secure Checkout &nbsp;|&nbsp; SSL Encrypted &nbsp;|&nbsp; Safe & Trusted
                </div>
            </div>

            <button type="submit" class="btn-order"
                    style="width:100%; text-align:center; margin-top:16px;
                           font-size:18px; padding:16px; border-radius:8px;">
                🛒 Place Order — ₹<?php echo $total; ?>
            </button>
        </form>

    <?php endif; ?>
</div>

<footer class="footer">
    <p>© 2024 FreshCart | Online Grocery Shopping | BCA Project</p>
</footer>

<script>
function switchTab(method, btn) {
    document.getElementById('payment_method').value = method;
    document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.card-form').forEach(f => f.classList.remove('active'));
    document.getElementById('form_' + method).classList.add('active');
}
function formatCardNumber(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 16);
    let parts = val.match(/.{1,4}/g);
    input.value = parts ? parts.join('  ') : val;
}
function formatExpiry(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 4);
    if(val.length >= 2) {
        input.value = val.substring(0,2) + '/' + val.substring(2);
    } else {
        input.value = val;
    }
}
function toggleCVV(id) {
    const input = document.getElementById(id);
    input.type  = input.type === 'password' ? 'text' : 'password';
}
function selectUpiApp(btn, appName) {
    document.querySelectorAll('.upi-app-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const upiInput = document.getElementById('upi_id');
    upiInput.placeholder = 'yourname@' + appName;
    upiInput.focus();
}
function liveValidate(field) {
    const input = document.getElementById(field);
    const error = document.getElementById(field + '_error');
    if(!input || !error) return true;
    let valid = true;
    if(field === 'cc_name' || field === 'dc_name') valid = input.value.trim().length > 0;
    if(field === 'cc_number' || field === 'dc_number') valid = input.value.replace(/\s/g,'').length === 16;
    if(field === 'cc_expiry' || field === 'dc_expiry') valid = /^\d{2}\/\d{2}$/.test(input.value.trim());
    if(field === 'cc_cvv'    || field === 'dc_cvv')    valid = /^\d{3}$/.test(input.value.trim());
    if(field === 'upi_id') valid = /^[\w.\-]+@[\w]+$/.test(input.value.trim());
    input.classList.toggle('invalid', !valid);
    input.classList.toggle('valid',    valid);
    error.classList.toggle('show',    !valid);
    return valid;
}
function validateAddress() {
    const ta    = document.getElementById('delivery_address');
    const error = document.getElementById('address_error');
    const valid = ta.value.trim().length > 0;
    ta.style.borderColor = valid ? '#2e7d32' : '#c62828';
    error.classList.toggle('show', !valid);
    return valid;
}
function validatePayment() {
    const method = document.getElementById('payment_method').value;
    if(!validateAddress()) {
        document.getElementById('delivery_address').scrollIntoView({behavior:'smooth', block:'center'});
        return false;
    }
    if(method === 'credit_card') {
        const fields = ['cc_name','cc_number','cc_expiry','cc_cvv'];
        let valid = true;
        fields.forEach(f => { if(!liveValidate(f)) valid = false; });
        if(!valid) return false;
        document.getElementById('hidden_card_name').value  = document.getElementById('cc_name').value.trim();
        document.getElementById('hidden_card_last4').value = document.getElementById('cc_number').value.replace(/\s/g,'').slice(-4);
    }
    if(method === 'debit_card') {
        const fields = ['dc_name','dc_number','dc_expiry','dc_cvv'];
        let valid = true;
        fields.forEach(f => { if(!liveValidate(f)) valid = false; });
        if(!valid) return false;
        document.getElementById('hidden_card_name').value  = document.getElementById('dc_name').value.trim();
        document.getElementById('hidden_card_last4').value = document.getElementById('dc_number').value.replace(/\s/g,'').slice(-4);
    }
    if(method === 'upi') {
        if(!liveValidate('upi_id')) return false;
        document.getElementById('hidden_upi_id').value = document.getElementById('upi_id').value.trim();
    }
    return true;
}
</script>
</body>
</html>