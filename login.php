<?php
session_start();
include 'db_connect.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);

    // Server-side validation
    if(empty($email)) {
        $error = "Email address is required!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } elseif(empty($password)) {
        $error = "Password is required!";
    } else {
        $hashed = MD5($password);
        $sql    = "SELECT * FROM users WHERE email='$email' AND password='$hashed'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - FreshCart</title>
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
        .field-group input {
            width: 100%;
            padding: 11px 12px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .field-group input:focus {
            border-color: #2e7d32;
        }
        .field-group input.invalid {
            border-color: #c62828;
        }
        .field-group input.valid {
            border-color: #2e7d32;
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

        /* Show/hide password */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 44px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            color: #777;
            user-select: none;
        }
        .toggle-password:hover {
            color: #2e7d32;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <a href="index.php" class="auth-logo">🛒 FreshCart</a>

    <div class="auth-container">
        <h2>Welcome Back</h2>

        <?php if($error) echo "<p class='error'>$error</p>"; ?>

        <form method="POST" id="loginForm" onsubmit="return validateForm()">

            <!-- Email -->
            <div class="field-group">
                <label>Email Address <span>*</span></label>
                <input type="email" name="email" id="email"
                       placeholder="Enter your email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       oninput="validateField('email')">
                <div class="field-error" id="email_error">Please enter a valid email address!</div>
            </div>

            <!-- Password -->
            <div class="field-group">
                <label>Password <span>*</span></label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password"
                           placeholder="Enter your password"
                           oninput="validateField('password')">
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
                <div class="field-error" id="password_error">Password is required!</div>
            </div>

            <button type="submit">Login</button>
        </form>

    <p>New user? <a href="register.php">Create account</a></p>

<p style="margin-top:8px;">
    <a href="admin/login.php"
       style="color:#1565c0; font-size:14px; font-weight:bold;">
        🔐 Admin Login
    </a>
</p>
    </div>
</div>

<script>
function validateField(field) {
    const input = document.getElementById(field);
    const error = document.getElementById(field + '_error');
    let valid = true;

    if(field === 'email') {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        valid = re.test(input.value.trim());
        error.textContent = input.value.trim() === ''
            ? 'Email address is required!'
            : 'Please enter a valid email address!';
    }
    if(field === 'password') {
        valid = input.value.trim().length > 0;
        error.textContent = 'Password is required!';
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

function validateForm() {
    const fields = ['email', 'password'];
    let allValid = true;
    fields.forEach(f => {
        if(!validateField(f)) allValid = false;
    });
    if(!allValid) {
        const firstError = document.querySelector('.invalid');
        if(firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return allValid;
}

function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.querySelector('.toggle-password');
    if(input.type === 'password') {
        input.type  = 'text';
        icon.textContent = '🙈';
    } else {
        input.type  = 'password';
        icon.textContent = '👁️';
    }
}
</script>

</body>
</html>