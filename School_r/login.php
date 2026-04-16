<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] !== 'approved') {
            if ($user['status'] == 'pending') {
                $error = "⏳ Your account is pending admin approval. Please wait.";
            } elseif ($user['status'] == 'rejected') {
                $error = "❌ Your account has been rejected. Please contact admin.";
            } else {
                $error = "⚠️ Account not activated yet.";
            }
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['full_name'];
            
            if ($user['role'] == 'admin') {
                header("Location: admin_dash.php");
            } elseif ($user['role'] == 'teacher') {
                header("Location: teacher_dash.php");
            } else {
                header("Location: student_dash.php");
            }
            exit();
        } else { 
            $error = "🔑 Invalid password. Please try again.";
        }
    } else { 
        $error = "📧 Account not found. Please sign up first.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="log.css">
</head>
<body class="auth-body">

<!-- Floating animated shapes -->
<div class="floating-shape" style="width: 300px; height: 300px; top: -100px; left: -100px; animation-duration: 18s;"></div>
<div class="floating-shape" style="width: 200px; height: 200px; bottom: -50px; right: -80px; animation-duration: 22s; background: rgba(56,189,248,0.05);"></div>
<div class="floating-shape" style="width: 150px; height: 150px; top: 60%; left: 85%; animation-duration: 14s;"></div>
<div class="floating-shape" style="width: 100px; height: 100px; top: 20%; right: 90%; animation-duration: 25s;"></div>

<div class="auth-container">
    <div class="auth-header">
        <h2><i class="fas fa-graduation-cap"></i> EduSmart</h2>
        <a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a>
    </div>

    <h3><i class="fas fa-hand-wave"></i> Welcome Back!</h3>

    <?php if(!empty($error)): ?>
        <div class='error-msg'><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
        <div class='success-msg'><i class="fas fa-check-circle"></i> Registration successful! Please wait for admin approval.</div>
    <?php endif; ?>

    <form action="login.php" method="POST" id="loginForm">
        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required autocomplete="email">
        </div>

        <div class="form-group password-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Enter your password" required autocomplete="current-password">
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </span>
            </div>
        </div>
        
        <button type="submit" class="btn-auth" id="loginBtn">
            <span class="btn-text"><i class="fas fa-sign-in-alt"></i> LOG IN</span>
            <span class="loader"></span>
        </button>
    </form>

    <div class="auth-footer">
        <i class="fas fa-shield-alt"></i> Secure Student Management System
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
        icon.style.transform = "scale(1.1)";
        setTimeout(() => { icon.style.transform = "scale(1)"; }, 200);
    } else {
        password.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
        icon.style.transform = "scale(1.1)";
        setTimeout(() => { icon.style.transform = "scale(1)"; }, 200);
    }
}

document.getElementById("loginForm").addEventListener("submit", function(e) {
    const btn = document.getElementById("loginBtn");
    btn.classList.add("loading");
    btn.querySelector('.btn-text').innerHTML = '<i class="fas fa-spinner fa-pulse"></i> LOGGING IN...';
});
</script>
</body>
</html>