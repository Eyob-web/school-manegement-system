<?php
include 'config.php';
check_login('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'admin';
    $status = 'approved'; // Admin accounts are auto-approved
    
    $check = "SELECT id FROM users WHERE email='$email'";
    $result = $conn->query($check);
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role, status) VALUES ('$full_name', '$email', '$password', '$role', '$status')";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Admin account created successfully!";
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }
    }
}

header("Location: manage_users.php");
exit();
?>