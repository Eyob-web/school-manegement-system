<?php
session_start();
include 'config.php';

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $status = 'pending';

    $check = "SELECT id FROM users WHERE email='$email'";
    $result = $conn->query($check);

    if ($result->num_rows > 0) {
        $message = "❌ This email is already registered!";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role, status) VALUES ('$full_name', '$email', '$password', '$role', '$status')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "✅ Registration successful! Your account is pending admin approval.";
            $messageType = "success";
        } else {
            $message = "❌ Database Error: " . $conn->error;
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Sign Up | EduSmart</title>
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
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Log In</a>
    </div>

    <h3><i class="fas fa-user-plus"></i> Create Account</h3>

    <?php if(!empty($message)): ?>
        <div class='<?php echo $messageType == "error" ? "error-msg" : "success-msg"; ?>'>
            <i class="fas <?php echo $messageType == "error" ? "fa-exclamation-circle" : "fa-check-circle"; ?>"></i> 
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="signup.php" method="POST" id="signupForm">
        <div class="form-group">
            <label><i class="fas fa-user"></i> Full Name</label>
            <input type="text" name="full_name" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group password-group">
            <label><i class="fas fa-lock"></i> Password</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="password" placeholder="Create a strong password" required>
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <div class="form-group">
            <label><i class="fas fa-user-tag"></i> User Role</label>
            <select name="role" required>
                <option value="">Select your role</option>
                <option value="student">📚 Student</option>
                <option value="teacher">👨‍🏫 Teacher</option>
            </select>
        </div>
        
        <p class="note-text">
            <i class="fas fa-info-circle"></i> Note: Your account will need admin approval before you can log in.
        </p>

        <button type="submit" class="btn-auth" id="registerBtn">
            <span class="btn-text"><i class="fas fa-user-plus"></i> REGISTER</span>
            <span class="loader"></span>
        </button>
    </form>

    <div class="auth-footer">
        <i class="fas fa-shield-alt"></i> Smart Student Management System
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

document.getElementById("signupForm").addEventListener("submit", function(e) {
    const btn = document.getElementById("registerBtn");
    btn.classList.add("loading");
    btn.querySelector('.btn-text').innerHTML = '<i class="fas fa-spinner fa-pulse"></i> REGISTERING...';
});

// Password strength indicator
const passwordInput = document.getElementById("password");
if(passwordInput) {
    passwordInput.addEventListener("input", function() {
        const strength = this.value.length;
        if(strength > 0 && strength < 6) {
            this.style.borderColor = "#ff5252";
        } else if(strength >= 6 && strength < 10) {
            this.style.borderColor = "#ffc107";
        } else if(strength >= 10) {
            this.style.borderColor = "#4caf50";
        } else {
            this.style.borderColor = "rgba(56,189,248,0.2)";
        }
    });
}
</script>
</body>
</html>