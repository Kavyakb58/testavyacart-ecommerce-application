<?php
session_start();
include("../db_connect.php");

if(isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = trim($_POST['password']);

    if(empty($email)) {
        $error = "Email address is required!";
    }
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    }
    elseif(empty($password)) {
        $error = "Password is required!";
    }
    else {

$sql = "SELECT * FROM users
        WHERE email='$email'
        AND role='admin'";        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 1) {

            $admin = mysqli_fetch_assoc($result);

            if(md5($password) == $admin['password']) {

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];

                header("Location: dashboard.php");
                exit();

            } else {
                $error = "Invalid password!";
            }

        } else {
            $error = "Admin not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - FreshCart</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        body {
            background: #f0f4f0;
        }

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
        }

        .field-group input {
            width: 100%;
            padding: 11px 12px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .field-group input:focus {
            border-color: #2e7d32;
            outline: none;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
        }

        .admin-badge {
            text-align: center;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <a href="../index.php" class="auth-logo">🛒 FreshCart</a>

    <div class="auth-container">

        <div class="admin-badge">
            🔐 Admin Panel Login
        </div>

        <h2>Admin Login</h2>

        <?php if($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST">

            <div class="field-group">
                <label>Email Address <span>*</span></label>
                <input type="email"
                       name="email"
                       placeholder="Enter admin email"
                       required>
            </div>

            <div class="field-group">
                <label>Password <span>*</span></label>

                <div class="password-wrapper">
                    <input type="password"
                           name="password"
                           id="password"
                           placeholder="Enter password"
                           required>

                    <span class="toggle-password"
                          onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <button type="submit">Login to Dashboard</button>

        </form>

        <p>
            <a href="../login.php">← Back to User Login</a>
        </p>

    </div>
</div>

<script>
function togglePassword() {

    const password = document.getElementById('password');

    if(password.type === 'password') {
        password.type = 'text';
    } else {
        password.type = 'password';
    }
}
</script>

</body>
</html>