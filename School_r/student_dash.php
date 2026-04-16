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

// Get student's information - FIXED: using users table directly
$student_sql = "SELECT u.id, u.full_name, u.email, 
                s.id as student_id, s.class_id, s.student_id as student_number,
                c.class_name, c.section, c.room_number
                FROM users u 
                LEFT JOIN students s ON u.id = s.user_id
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE u.id = $user_id AND u.role = 'student'";
$student_result = $conn->query($student_sql);
$student = $student_result->fetch_assoc();

// Get notices
$notices_sql = "SELECT * FROM notices 
                WHERE expiration_date >= CURDATE() 
                AND (target_class = 'all' OR target_class = 'students' OR target_class = 'everyone')
                ORDER BY created_at DESC LIMIT 3";
$notices_result = $conn->query($notices_sql);

// Get assignments
$assignments_sql = "SELECT a.*, c.class_name 
                    FROM assignments a 
                    JOIN classes c ON a.class_id = c.id 
                    WHERE a.class_id = " . ($student['class_id'] ?? 0) . "
                    ORDER BY a.due_date ASC LIMIT 3";
$assignments_result = $conn->query($assignments_sql);

// Get statistics
$courses_count = 6; // Default value
$attendance_rate = 94; // Default value
$gpa = 3.8; // Default value
$pending_tasks = $assignments_result ? $assignments_result->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0b0f1c;
            color: #f1f5f9;
            overflow-x: hidden;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(56, 189, 248, 0.25);
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar .logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #ffffff, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 40px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        }

        .sidebar .logo span {
            background: linear-gradient(135deg, #38bdf8, #a5f3fc);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #cbd5e6;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .sidebar nav a i {
            width: 24px;
            font-size: 1.2rem;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            transform: translateX(5px);
        }

        .sidebar .divider {
            height: 1px;
            background: rgba(56, 189, 248, 0.2);
            margin: 15px 0;
        }

        .sidebar .logout {
            color: #ff7675;
        }

        .sidebar .logout:hover {
            background: rgba(255, 118, 117, 0.15);
            color: #ff7675;
        }

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 280px;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(16px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar .search {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(20, 30, 55, 0.65);
            padding: 8px 15px;
            border-radius: 40px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .topbar .search i {
            color: #38bdf8;
        }

        .topbar .search input {
            background: none;
            border: none;
            color: white;
            outline: none;
            width: 250px;
        }

        .topbar .search input::placeholder {
            color: #8aa2d0;
        }

        .topbar .user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar .user .text {
            text-align: right;
        }

        .topbar .user .text h4 {
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .topbar .user .text p {
            font-size: 0.75rem;
            color: #8aa2d0;
        }

        .topbar .user img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: 2px solid #38bdf8;
        }

        /* Content */
        .content {
            padding: 30px;
            flex: 1;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .welcome h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #e2e8f0, #a0c4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
        }

        .welcome p {
            color: #8aa2d0;
        }

        /* Stats Cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats .card {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            transition: all 0.3s ease;
        }

        .stats .card:hover {
            transform: translateY(-3px);
            border-color: #38bdf8;
        }

        .stats .card i {
            font-size: 2rem;
            color: #38bdf8;
            margin-bottom: 10px;
        }

        .stats .card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stats .card p {
            color: #8aa2d0;
            font-size: 0.85rem;
        }

        /* Grid Layout */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }

        /* Panels */
        .panel {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            overflow: hidden;
        }

        .panel-header {
            padding: 20px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h3 {
            color: #38bdf8;
            font-size: 1.2rem;
        }

        .panel-header a {
            color: #38bdf8;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .panel-header a:hover {
            text-decoration: underline;
        }

        .panel-body {
            padding: 20px;
        }

        .item {
            padding: 15px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
            display: flex;
            gap: 15px;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item .date {
            font-size: 0.75rem;
            color: #38bdf8;
            min-width: 60px;
        }

        .item h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .item p {
            font-size: 0.85rem;
            color: #a0b3d9;
        }

        .item.urgent {
            border-left: 3px solid #ff5252;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #8aa2d0;
        }

        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.7);
            padding: 20px 30px;
            text-align: center;
            font-size: 0.8rem;
            color: #8aa2d0;
            border-top: 1px solid rgba(56, 189, 248, 0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            
            .sidebar .logo {
                font-size: 1rem;
                text-align: center;
            }
            
            .sidebar nav a span {
                display: none;
            }
            
            .sidebar nav a i {
                margin: 0 auto;
            }
            
            .main {
                margin-left: 80px;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
            
            .topbar .search {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">
            Edu<span>Smart</span>
        </div>

        <nav>
            <a class="active" href="student_dash.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="view_grades.php"><i class="fas fa-graduation-cap"></i> <span>Grades</span></a>
            <a href="view_assignments.php"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a href="view_schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a>
            <a href="view_notices.php"><i class="fas fa-bullhorn"></i> <span>Notices</span></a>
            <a href="student_profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a>

            <div class="divider"></div>

            <a class="logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <!-- TOP BAR -->
        <header class="topbar">
            <div class="search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search assignments, notices...">
            </div>

            <div class="user">
                <div class="text">
                    <h4><?php echo htmlspecialchars($student_name); ?></h4>
                    <p>ID: <?php echo $student['student_number'] ?? $user_id; ?></p>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=38bdf8&color=fff&rounded=true">
            </div>
        </header>

        <!-- CONTENT -->
        <section class="content">

            <div class="welcome">
                <h1>Welcome back, <?php echo explode(' ', $student_name)[0]; ?>! 👋</h1>
                <p>Here's your academic overview and upcoming tasks.</p>
            </div>

            <!-- STATS -->
            <div class="stats">
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $courses_count; ?></h3>
                    <p>Enrolled Courses</p>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <h3><?php echo $attendance_rate; ?>%</h3>
                    <p>Attendance Rate</p>
                </div>
                <div class="card">
                    <i class="fas fa-star"></i>
                    <h3><?php echo $gpa; ?></h3>
                    <p>Current GPA</p>
                </div>
                <div class="card">
                    <i class="fas fa-tasks"></i>
                    <h3><?php echo $pending_tasks; ?></h3>
                    <p>Pending Tasks</p>
                </div>
            </div>

            <!-- GRID -->
            <div class="grid">

                <!-- NOTICES PANEL -->
                <div class="panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-bullhorn"></i> Latest Notices</h3>
                        <a href="view_notices.php">View all →</a>
                    </div>
                    <div class="panel-body">
                        <?php if($notices_result && $notices_result->num_rows > 0): ?>
                            <?php while($notice = $notices_result->fetch_assoc()): ?>
                                <div class="item">
                                    <span class="date"><?php echo date('M d', strtotime($notice['created_at'])); ?></span>
                                    <div>
                                        <h4><?php echo htmlspecialchars($notice['title']); ?></h4>
                                        <p><?php echo substr(htmlspecialchars($notice['content']), 0, 80); ?>...</p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="empty">No notices available</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ASSIGNMENTS PANEL -->
                <div class="panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-tasks"></i> Upcoming Assignments</h3>
                        <a href="view_assignments.php">View all →</a>
                    </div>
                    <div class="panel-body">
                        <?php if($assignments_result && $assignments_result->num_rows > 0): ?>
                            <?php while($assignment = $assignments_result->fetch_assoc()): 
                                $is_urgent = strtotime($assignment['due_date']) - strtotime('now') < 86400;
                            ?>
                                <div class="item <?php echo $is_urgent ? 'urgent' : ''; ?>">
                                    <div>
                                        <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                                        <p>Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></p>
                                        <small><?php echo htmlspecialchars($assignment['class_name']); ?></small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="empty">No assignments available</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- CLASS INFORMATION -->
            <div class="panel" style="margin-top: 25px;">
                <div class="panel-header">
                    <h3><i class="fas fa-school"></i> My Class Information</h3>
                </div>
                <div class="panel-body">
                    <?php if($student && $student['class_name']): ?>
                        <div class="item">
                            <div>
                                <h4><?php echo htmlspecialchars($student['class_name'] . ' ' . $student['section']); ?></h4>
                                <p>Room: <?php echo htmlspecialchars($student['room_number'] ?? 'Not assigned'); ?></p>
                                <p>Student ID: <?php echo htmlspecialchars($student['student_number'] ?? 'Not assigned'); ?></p>
                                <p>Email: <?php echo htmlspecialchars($student['email']); ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="empty">No class information available. Please contact administrator.</p>
                    <?php endif; ?>
                </div>
            </div>

        </section>

        <footer class="footer">
            <i class="fas fa-graduation-cap"></i> © 2026 EduSmart SMS | Empowering Education Through Innovation
        </footer>

    </main>

</div>

</body>
</html>