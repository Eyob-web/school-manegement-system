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

$student_name = $_SESSION['name'];

// Get all active notices
$sql = "SELECT * FROM notices 
        WHERE expiration_date >= CURDATE() OR expiration_date IS NULL
        ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
    <style>
        .notice-header {
            background: linear-gradient(135deg, rgba(56,189,248,0.1), rgba(56,189,248,0.05));
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .category-tag {
            background: rgba(56,189,248,0.15);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
        }
        .notice-stats {
            display: flex;
            gap: 15px;
            margin-top: 15px;
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
            <a class="active" href="view_notices.php"><i class="fas fa-bullhorn"></i> <span>Notices</span></a>
            <a href="student_profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a>
            <div class="divider"></div>
            <a class="logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <h2 style="margin-left: 20px;"><i class="fas fa-bullhorn"></i> Announcements</h2>
            <div class="user">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=38bdf8&color=fff&rounded=true">
            </div>
        </header>

        <section class="content">
            <div class="welcome">
                <h1>School Notices & Announcements 📢</h1>
                <p>Stay updated with latest news and events</p>
            </div>

            <div class="notice-container">
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($notice = $result->fetch_assoc()): 
                        $target_display = $notice['target_class'] ?? 'Everyone';
                        if($target_display == 'all') $target_display = 'Everyone';
                        elseif($target_display == 'students') $target_display = 'All Students';
                        elseif($target_display == 'teachers') $target_display = 'All Teachers';
                    ?>
                        <div class="full-notice-card">
                            <div class="notice-header">
                                <div>
                                    <span class="category-tag">
                                        <i class="fas fa-users"></i> Target: <?php echo ucfirst($target_display); ?>
                                    </span>
                                </div>
                                <div class="notice-meta">
                                    <i class="far fa-calendar-alt"></i> 
                                    Posted: <?php echo date('F d, Y', strtotime($notice['created_at'])); ?>
                                </div>
                            </div>
                            <h2><i class="fas fa-bullhorn"></i> <?php echo htmlspecialchars($notice['title']); ?></h2>
                            <p><?php echo nl2br(htmlspecialchars($notice['content'])); ?></p>
                            <?php if($notice['expiration_date']): ?>
                                <div class="notice-stats">
                                    <small><i class="fas fa-hourglass-end"></i> Valid until: <?php echo date('F d, Y', strtotime($notice['expiration_date'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px;">
                        <i class="fas fa-bell-slash" style="font-size: 4rem; color: #8aa2d0;"></i>
                        <h3>No Notices Available</h3>
                        <p>Check back later for updates and announcements.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <footer class="footer">
            <i class="fas fa-graduation-cap"></i> © 2026 EduSmart SMS | Stay Informed
        </footer>
    </main>
</div>

</body>
</html>