<?php
session_start();
include 'db_connect.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error   = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $phone    = trim(mysqli_real_escape_string($conn, $_POST['phone']));
    $address  = trim(mysqli_real_escape_string($conn, $_POST['address']));
    $password = trim($_POST['password']);

    // Server-side validation
    if(empty($name)) {
        $error = "Full name is required!";
    } elseif(empty($email)) {
        $error = "Email address is required!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } elseif(empty($phone)) {
        $error = "Phone number is required!";
    } elseif(!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits!";
    } elseif(empty($address)) {
        $error = "Delivery address is required!";
    } elseif(empty($password)) {
        $error = "Password is required!";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
        if(mysqli_num_rows($check) > 0) {
            $error = "Email already registered!";
        } else {
            $hashed = MD5($password);
            $sql = "INSERT INTO users (name, email, password, phone, address)
                    VALUES ('$name', '$email', '$hashed', '$phone', '$address')";
            if(mysqli_query($conn, $sql)) {
                $success = "Registration successful!";
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - FreshCart</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #f0f4f0; }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .auth-logo {
            font-size: 28px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 20px;
            text-decoration: none;
        }

        /* Field wrapper */
        .field-group {
            position: relative;
            margin-bottom: 16px;
        }
        .field-group label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .field-group label span {
            color: #c62828;
            margin-left: 2px;
        }
        .field-group input,
        .field-group textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .field-group input:focus,
        .field-group textarea:focus {
            border-color: #2e7d32;
        }
        .field-group input.invalid,
        .field-group textarea.invalid {
            border-color: #c62828;
        }
        .field-group input.valid,
        .field-group textarea.valid {
            border-color: #2e7d32;
        }
        .field-group textarea {
            height: 80px;
            resize: none;
        }
        .field-error {
            font-size: 12px;
            color: #c62828;
            margin-top: 4px;
            display: none;
        }
        .field-error.show {
            display: block;
        }

        /* Password strength */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 6px;
            background: #eee;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }
        .strength-text {
            font-size: 12px;
            margin-top: 4px;
        }

        .auth-container button {
            margin-top: 4px;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <a href="index.php" class="auth-logo">🛒 FreshCart</a>

    <div class="auth-container">
        <h2>Create Account</h2>

        <?php if($error)   echo "<p class='error'>$error</p>"; ?>
        <?php if($success) echo "<p class='success'>✅ $success <a href='login.php'>Login now →</a></p>"; ?>

        <form method="POST" id="registerForm" onsubmit="return validateForm()">

            <!-- Full Name -->
            <div class="field-group">
                <label>Full Name <span>*</span></label>
                <input type="text" name="name" id="name"
                       placeholder="Enter your full name"
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                       oninput="validateField('name')">
                <div class="field-error" id="name_error">Full name is required!</div>
            </div>

            <!-- Email -->
            <div class="field-group">
                <label>Email Address <span>*</span></label>
                <input type="email" name="email" id="email"
                       placeholder="Enter your email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       oninput="validateField('email')">
                <div class="field-error" id="email_error">Please enter a valid email!</div>
            </div>

            <!-- Phone -->
            <div class="field-group">
                <label>Phone Number <span>*</span></label>
                <input type="tel" name="phone" id="phone"
                       placeholder="Enter 10-digit mobile number"
                       maxlength="10"
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                       oninput="this.value=this.value.replace(/\D/g,''); validateField('phone')">
                <div class="field-error" id="phone_error">Enter a valid 10-digit phone number!</div>
            </div>

            <!-- Address -->
            <div class="field-group">
                <label>Delivery Address <span>*</span></label>
                <textarea name="address" id="address"
                          placeholder="Enter your full delivery address"
                          oninput="validateField('address')"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                <div class="field-error" id="address_error">Delivery address is required!</div>
            </div>

            <!-- Password -->
            <div class="field-group">
                <label>Password <span>*</span></label>
                <input type="password" name="password" id="password"
                       placeholder="Minimum 6 characters"
                       oninput="validateField('password'); checkStrength(this.value)">
                <div class="strength-bar">
                    <div class="strength-fill" id="strength_fill"></div>
                </div>
                <div class="strength-text" id="strength_text"></div>
                <div class="field-error" id="password_error">Password must be at least 6 characters!</div>
            </div>

            <button type="submit">Create Account</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<script>
function validateField(field) {
    const input = document.getElementById(field);
    const error = document.getElementById(field + '_error');
    let valid = true;

    if(field === 'name') {
        valid = input.value.trim().length > 0;
        error.textContent = 'Full name is required!';
    }
    if(field === 'email') {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        valid = re.test(input.value.trim());
        error.textContent = 'Please enter a valid email address!';
    }
    if(field === 'phone') {
        valid = /^[0-9]{10}$/.test(input.value.trim());
        error.textContent = 'Enter a valid 10-digit phone number!';
    }
    if(field === 'address') {
        valid = input.value.trim().length > 0;
        error.textContent = 'Delivery address is required!';
    }
    if(field === 'password') {
        valid = input.value.trim().length >= 6;
        error.textContent = 'Password must be at least 6 characters!';
    }

    if(!valid) {
        input.classList.add('invalid');
        input.classList.remove('valid');
        error.classList.add('show');
    } else {
        input.classList.remove('invalid');
        input.classList.add('valid');
        error.classList.remove('show');
    }
    return valid;
}

function checkStrength(password) {
    const fill = document.getElementById('strength_fill');
    const text = document.getElementById('strength_text');

    if(password.length === 0) {
        fill.style.width = '0%';
        text.textContent = '';
        return;
    }

    let score = 0;
    if(password.length >= 6)  score++;
    if(password.length >= 10) score++;
    if(/[A-Z]/.test(password)) score++;
    if(/[0-9]/.test(password)) score++;
    if(/[^A-Za-z0-9]/.test(password)) score++;

    const levels = [
        { width:'20%', color:'#c62828', label:'Very weak' },
        { width:'40%', color:'#e65100', label:'Weak' },
        { width:'60%', color:'#f9a825', label:'Fair' },
        { width:'80%', color:'#2e7d32', label:'Strong' },
        { width:'100%',color:'#1b5e20', label:'Very strong' },
    ];
    const level = levels[Math.min(score, 4)];
    fill.style.width      = level.width;
    fill.style.background = level.color;
    text.textContent      = level.label;
    text.style.color      = level.color;
}

function validateForm() {
    const fields = ['name', 'email', 'phone', 'address', 'password'];
    let allValid = true;
    fields.forEach(f => {
        if(!validateField(f)) allValid = false;
    });
    if(!allValid) {
        // Scroll to first error
        const firstError = document.querySelector('.invalid');
        if(firstError) firstError.scrollIntoView({ behavior:'smooth', block:'center' });
    }
    return allValid;
}
</script>

</body>
</html>