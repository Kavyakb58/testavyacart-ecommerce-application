<?php
session_start();
include 'db_connect.php';

$success = "";
$error   = "";

// Submit contact form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email   = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $subject = trim(mysqli_real_escape_string($conn, $_POST['subject']));
    $message = trim(mysqli_real_escape_string($conn, $_POST['message']));

    if(empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email!";
    } else {
        mysqli_query($conn,
            "INSERT INTO contact_messages (name, email, subject, message)
             VALUES ('$name', '$email', '$subject', '$message')");
        $success = "Thank you! We will get back to you within 24 hours.";
    }
}

// Get user's previous messages and replies
$my_messages = [];
if(isset($_SESSION['user_id'])) {
    $user_data = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT email FROM users WHERE id=".(int)$_SESSION['user_id']));
    if($user_data) {
        $user_email = mysqli_real_escape_string($conn, $user_data['email']);
        $result = mysqli_query($conn,
            "SELECT * FROM contact_messages 
             WHERE email='$user_email' 
             ORDER BY created_at DESC LIMIT 5");
        while($row = mysqli_fetch_assoc($result)) {
            $my_messages[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - FreshCart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .contact-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }
        .contact-info {
            background: linear-gradient(135deg, #2e7d32, #66bb6a);
            border-radius: 12px;
            padding: 40px 30px;
            color: white;
        }
        .contact-info h2 {
            font-size: 24px;
            margin-bottom: 12px;
        }
        .contact-info p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
        }
        .info-icon {
            font-size: 22px;
            min-width: 30px;
        }
        .info-text strong {
            display: block;
            font-size: 14px;
            margin-bottom: 2px;
        }
        .info-text span {
            font-size: 13px;
            opacity: 0.85;
        }
        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        .social-btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .social-btn:hover { background: rgba(255,255,255,0.35); }

        .contact-form-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .contact-form-card h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 6px;
        }
        .contact-form-card p {
            color: #777;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .field-group {
            margin-bottom: 16px;
        }
        .field-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .field-group label span { color: #c62828; margin-left:2px; }
        .field-group input,
        .field-group select,
        .field-group textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
            font-family: Arial, sans-serif;
        }
        .field-group input:focus,
        .field-group select:focus,
        .field-group textarea:focus { border-color: #2e7d32; }
        .field-group input.valid    { border-color: #2e7d32; }
        .field-group input.invalid  { border-color: #c62828; }
        .field-group textarea {
            height: 120px;
            resize: none;
        }
        .form-row { display: flex; gap: 14px; }
        .form-row .field-group { flex: 1; }
        .field-error {
            font-size: 12px;
            color: #c62828;
            margin-top: 3px;
            display: none;
        }
        .field-error.show { display: block; }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }
        .btn-submit:hover { background: #1b5e20; }

        /* Previous messages section */
        .prev-messages {
            max-width: 1100px;
            margin: 0 20px 30px;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .prev-messages h3 {
            color: #333;
            font-size: 18px;
            margin-bottom: 16px;
        }
        .msg-thread {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 14px;
            background: #fafafa;
            transition: box-shadow 0.2s;
        }
        .msg-thread:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .msg-thread-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .msg-subject {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        .msg-date {
            font-size: 12px;
            color: #aaa;
        }
        .msg-body {
            font-size: 13px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        .reply-box {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            padding: 12px 16px;
            border-radius: 6px;
            margin-top: 8px;
        }
        .reply-box-label {
            font-size: 12px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 4px;
        }
        .reply-box-text {
            font-size: 13px;
            color: #1b5e20;
            line-height: 1.6;
        }
        .reply-box-date {
            font-size: 11px;
            color: #777;
            margin-top: 5px;
        }
        .awaiting-box {
            background: #fff3e0;
            border-left: 4px solid #ff6f00;
            padding: 10px 14px;
            border-radius: 6px;
            margin-top: 8px;
            font-size: 13px;
            color: #e65100;
        }

        /* FAQ Section */
        .faq-section {
            max-width: 1100px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }
        .faq-section h2 {
            text-align: center;
            color: #2e7d32;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .faq-item {
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .faq-question {
            padding: 16px 20px;
            font-size: 15px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        .faq-question:hover { background: #f5f5f5; }
        .faq-arrow { transition: transform 0.3s; font-size: 12px; }
        .faq-answer {
            display: none;
            padding: 0 20px 16px;
            font-size: 14px;
            color: #555;
            line-height: 1.7;
        }
        .faq-item.open .faq-answer { display: block; }
        .faq-item.open .faq-arrow  { transform: rotate(180deg); }

        @media(max-width: 768px) {
            .contact-wrapper { grid-template-columns: 1fr; }
            .prev-messages   { margin: 0 20px 30px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="logo">🛒 FreshCart</a>
    <div>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="products.php">Products</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
            <a href="contact.php">Contact</a>
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <a href="admin/dashboard.php">⚙️ Admin Panel</a>
            <?php endif; ?>
            <a href="profile.php">My Profile</a>
            <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
        <?php else: ?>
            <a href="products.php">Products</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Page Header -->
<div style="background: linear-gradient(135deg, #2e7d32, #66bb6a);
            padding: 50px 20px; text-align: center; color: white;">
    <h1 style="font-size:32px; margin-bottom:8px;">Contact Us</h1>
    <p style="font-size:16px; opacity:0.9;">
        We're here to help! Reach out to us anytime.
    </p>
</div>

<!-- ✅ Previous Messages & Replies (logged in users only) -->
<?php if(isset($_SESSION['user_id']) && count($my_messages) > 0): ?>
<div style="max-width:1100px; margin:30px auto 0; padding:0 20px;">
    <div class="prev-messages" style="margin:0;">
        <h3>📬 Your Previous Messages</h3>
        <?php foreach($my_messages as $pm): ?>
        <div class="msg-thread">
            <div class="msg-thread-header">
                <span class="msg-subject">
                    📌 <?php echo htmlspecialchars($pm['subject']); ?>
                </span>
                <span class="msg-date">
                    <?php echo date('d M Y, h:i A', strtotime($pm['created_at'])); ?>
                </span>
            </div>
            <div class="msg-body">
                <?php echo nl2br(htmlspecialchars($pm['message'])); ?>
            </div>

            <?php if (!empty($pm['admin_reply'])): ?>
                <div class="reply-box">
                    <div class="reply-box-label">✅ FreshCart Support replied:</div>
                    <div class="reply-box-text">
                        <?php echo nl2br(htmlspecialchars($pm['admin_reply'])); ?>
                    </div>
                    <div class="reply-box-date">
                        🕐 <?php echo date('d M Y, h:i A', strtotime($pm['replied_at'])); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="awaiting-box">
                    ⏳ Awaiting reply from our support team. We'll respond within 24 hours.
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Contact Section -->
<div class="contact-wrapper">

    <!-- Left Info Panel -->
    <div class="contact-info">
        <h2>Get in Touch</h2>
        <p>Have a question about your order or our products?
           We'd love to hear from you. Send us a message!</p>

        <div class="info-item">
            <div class="info-icon">📍</div>
            <div class="info-text">
                <strong>Our Address</strong>
                <span>Soundarya Nagar, Sidedahalli,<br>
                Nagasandra Post, Bangalore – 73</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📞</div>
            <div class="info-text">
                <strong>Phone Number</strong>
                <span>+91 98765 43210</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">📧</div>
            <div class="info-text">
                <strong>Email Address</strong>
                <span>support@freshcart.com</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon">🕐</div>
            <div class="info-text">
                <strong>Working Hours</strong>
                <span>Mon – Sat: 9:00 AM – 7:00 PM<br>
                Sunday: 10:00 AM – 4:00 PM</span>
            </div>
        </div>

        <div class="social-links">
            <a href="#" class="social-btn">📘 Facebook</a>
            <a href="#" class="social-btn">📸 Instagram</a>
            <a href="#" class="social-btn">🐦 Twitter</a>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="contact-form-card">
        <h2>Send us a Message</h2>
        <p>Fill out the form below and we'll respond within 24 hours.</p>

        <?php if($success): ?>
            <div class="success" style="padding:14px; margin-bottom:20px; font-size:15px;">
                ✅ <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="error" style="padding:14px; margin-bottom:20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="contactForm" onsubmit="return validateContact()">
            <div class="form-row">
                <div class="field-group">
                    <label>Full Name <span>*</span></label>
                    <input type="text" name="name" id="c_name"
                           placeholder="Your full name"
                           value="<?php echo isset($_POST['name'])
                               ? htmlspecialchars($_POST['name'])
                               : (isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''); ?>"
                           oninput="cv('c_name')">
                    <div class="field-error" id="c_name_error">Name is required!</div>
                </div>
                <div class="field-group">
                    <label>Email Address <span>*</span></label>
                    <input type="email" name="email" id="c_email"
                           placeholder="your@email.com"
                           value="<?php echo isset($_POST['email'])
                               ? htmlspecialchars($_POST['email'])
                               : (isset($user_data['email']) ? htmlspecialchars($user_data['email']) : ''); ?>"
                           oninput="cv('c_email')">
                    <div class="field-error" id="c_email_error">Enter a valid email!</div>
                </div>
            </div>

            <div class="field-group">
                <label>Subject <span>*</span></label>
                <select name="subject" id="c_subject" onchange="cv('c_subject')">
                    <option value="">Select a subject</option>
                    <option value="Order Issue">📦 Order Issue</option>
                    <option value="Payment Problem">💳 Payment Problem</option>
                    <option value="Product Query">🛍️ Product Query</option>
                    <option value="Delivery Problem">🚚 Delivery Problem</option>
                    <option value="Return & Refund">↩️ Return & Refund</option>
                    <option value="Other">💬 Other</option>
                </select>
                <div class="field-error" id="c_subject_error">Please select a subject!</div>
            </div>

            <div class="field-group">
                <label>Message <span>*</span></label>
                <textarea name="message" id="c_message"
                          placeholder="Describe your issue or question in detail..."
                          oninput="cv('c_message')"></textarea>
                <div style="text-align:right; font-size:12px; color:#aaa; margin-top:3px;">
                    <span id="msg_count">0</span>/500 characters
                </div>
                <div class="field-error" id="c_message_error">Message is required!</div>
            </div>

            <button type="submit" class="btn-submit">
                📨 Send Message
            </button>
        </form>
    </div>
</div>

<!-- FAQ Section -->
<div class="faq-section">
    <h2>Frequently Asked Questions</h2>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            How long does delivery take?
            <span class="faq-arrow">▼</span>
        </div>
        <div class="faq-answer">
            We deliver within 2–4 hours for orders placed before 5 PM.
            Orders placed after 5 PM will be delivered the next morning by 10 AM.
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            Can I cancel or modify my order?
            <span class="faq-arrow">▼</span>
        </div>
        <div class="faq-answer">
            Yes! You can cancel or modify your order within 30 minutes of placing it.
            Contact us immediately via this form or call us directly.
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            What payment methods do you accept?
            <span class="faq-arrow">▼</span>
        </div>
        <div class="faq-answer">
            We accept Credit Cards, Debit Cards, UPI (GPay, PhonePe, Paytm, BHIM)
            and Cash on Delivery for all orders.
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            Are the products fresh and quality checked?
            <span class="faq-arrow">▼</span>
        </div>
        <div class="faq-answer">
            Absolutely! All our products are sourced directly from local farms and
            quality checked every morning before dispatch. Freshness is our top priority.
        </div>
    </div>

    <div class="faq-item">
        <div class="faq-question" onclick="toggleFaq(this)">
            Is there a minimum order amount?
            <span class="faq-arrow">▼</span>
        </div>
        <div class="faq-answer">
            There is no minimum order amount. We deliver all orders for free
            regardless of the order value!
        </div>
    </div>
</div>

<footer class="footer">
    <p>© 2024 FreshCart | Online Grocery Shopping | BCA Project</p>
    <p style="margin-top:6px; font-size:12px; opacity:0.8;">
        Soundarya Institute of Management and Science
    </p>
</footer>

<script>
function cv(field) {
    const input = document.getElementById(field);
    const error = document.getElementById(field + '_error');
    let valid = true;

    if(field === 'c_name')    valid = input.value.trim().length > 0;
    if(field === 'c_email')   valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim());
    if(field === 'c_subject') valid = input.value !== '';
    if(field === 'c_message') {
        valid = input.value.trim().length > 0;
        document.getElementById('msg_count').textContent = input.value.length;
        if(input.value.length > 500) {
            input.value = input.value.substring(0, 500);
        }
    }

    input.classList.toggle('invalid', !valid);
    input.classList.toggle('valid',    valid);
    error.classList.toggle('show',    !valid);
    return valid;
}

function validateContact() {
    const fields = ['c_name','c_email','c_subject','c_message'];
    let allValid = true;
    fields.forEach(f => { if(!cv(f)) allValid = false; });
    if(!allValid) {
        document.querySelector('.invalid')
                .scrollIntoView({behavior:'smooth', block:'center'});
    }
    return allValid;
}

function toggleFaq(el) {
    const item = el.parentElement;
    item.classList.toggle('open');
}
</script>

</body>
</html>