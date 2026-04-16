<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if user is student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'];

// Get student complete profile
$sql = "SELECT u.id, u.full_name, u.email, u.created_at as joined_date,
        s.student_id, s.parent_phone, s.address, s.date_of_birth, s.gender, s.enrollment_date,
        c.class_name, c.section, c.room_number
        FROM users u 
        LEFT JOIN students s ON u.id = s.user_id
        LEFT JOIN classes c ON s.class_id = c.id
        WHERE u.id = $user_id";
$result = $conn->query($sql);
$profile = $result->fetch_assoc();

// Update profile
$update_success = "";
$update_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $parent_phone = mysqli_real_escape_string($conn, $_POST['parent_phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $date_of_birth = mysqli_real_escape_string($conn, $_POST['date_of_birth']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    
    $update_sql = "UPDATE students SET 
                   parent_phone = '$parent_phone',
                   address = '$address',
                   date_of_birth = '$date_of_birth',
                   gender = '$gender'
                   WHERE user_id = $user_id";
    
    if ($conn->query($update_sql)) {
        $update_success = "✅ Profile updated successfully!";
        // Refresh profile data
        $refresh = $conn->query($sql);
        $profile = $refresh->fetch_assoc();
    } else {
        $update_error = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
    <style>
        .profile-section {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }
        .profile-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        }
        .profile-avatar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #38bdf8;
        }
        .profile-info h2 {
            margin-bottom: 10px;
            color: #38bdf8;
        }
        .profile-info p {
            color: #8aa2d0;
            margin-bottom: 5px;
        }
        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .info-group {
            margin-bottom: 20px;
        }
        .info-group label {
            display: block;
            color: #38bdf8;
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        .info-group p {
            color: #cbd5e6;
            font-size: 1rem;
            padding: 8px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 8px;
        }
        .info-group input, .info-group select {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: white;
        }
        .edit-btn {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border: 1px solid #38bdf8;
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            margin-top: 20px;
        }
        .save-btn {
            background: linear-gradient(95deg, #38bdf8, #2b9ed4);
            color: #0a0f1f;
            border: none;
            padding: 10px 20px;
            border-radius: 40px;
            cursor: pointer;
            font-weight: bold;
        }
        .success-msg {
            background: rgba(76, 175, 80, 0.15);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #4caf50;
        }
        .error-msg {
            background: rgba(255, 82, 82, 0.15);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #ff5252;
        }
    </style>
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div class="logo">Edu<span>Smart</span></div>
        <nav>
            <a href="student_dash.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="view_grades.php"><i class="fas fa-graduation-cap"></i> <span>Grades</span></a>
            <a href="view_assignments.php"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a href="view_schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a>
            <a href="view_notices.php"><i class="fas fa-bullhorn"></i> <span>Notices</span></a>
            <a class="active" href="student_profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a>
            <div class="divider"></div>
            <a class="logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <h2 style="margin-left: 20px;"><i class="fas fa-user-circle"></i> My Profile</h2>
            <div class="user">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=38bdf8&color=fff&rounded=true">
            </div>
        </header>

        <section class="content">
            <div class="welcome">
                <h1>Student Profile 👤</h1>
                <p>View and manage your personal information</p>
            </div>

            <?php if($update_success): ?>
                <div class="success-msg"><i class="fas fa-check-circle"></i> <?php echo $update_success; ?></div>
            <?php endif; ?>
            <?php if($update_error): ?>
                <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo $update_error; ?></div>
            <?php endif; ?>

            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile['full_name']); ?>&background=38bdf8&color=fff&size=120&rounded=true">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($profile['full_name']); ?></h2>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($profile['email']); ?></p>
                        <p><i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($profile['student_id'] ?? 'Not assigned'); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> Joined: <?php echo date('F d, Y', strtotime($profile['joined_date'])); ?></p>
                    </div>
                </div>

                <form method="POST">
                    <div class="profile-details">
                        <div class="info-group">
                            <label><i class="fas fa-school"></i> Class</label>
                            <p><?php echo htmlspecialchars($profile['class_name'] . ' ' . $profile['section']); ?></p>
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-door-open"></i> Room Number</label>
                            <p><?php echo htmlspecialchars($profile['room_number'] ?? 'Not assigned'); ?></p>
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-phone"></i> Parent/Guardian Phone</label>
                            <input type="tel" name="parent_phone" value="<?php echo htmlspecialchars($profile['parent_phone'] ?? ''); ?>" placeholder="Enter parent phone number">
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-map-marker-alt"></i> Address</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>" placeholder="Enter your address">
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-calendar"></i> Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
                        </div>
                        <div class="info-group">
                            <label><i class="fas fa-venus-mars"></i> Gender</label>
                            <select name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" <?php echo ($profile['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($profile['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($profile['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="save-btn">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </section>

        <footer class="footer">
            <i class="fas fa-graduation-cap"></i> © 2026 EduSmart SMS | Student Portal
        </footer>
    </main>
</div>

</body>
</html>