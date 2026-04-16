<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if user is teacher
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

// Get teacher's information
$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];

// Get teacher's assigned classes
$classes_sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count 
                FROM classes c 
                WHERE c.teacher_id = $teacher_id 
                ORDER BY c.class_name, c.section";
$classes_result = $conn->query($classes_sql);

// Get statistics
$total_classes = $classes_result->num_rows;
$total_students = 0;
$pending_assignments = 0;
$recent_activities = [];

// Calculate total students
if ($total_classes > 0) {
    $students_sql = "SELECT COUNT(DISTINCT s.id) as total 
                     FROM students s 
                     JOIN classes c ON s.class_id = c.id 
                     WHERE c.teacher_id = $teacher_id";
    $students_result = $conn->query($students_sql);
    if ($students_result) {
        $total_students = $students_result->fetch_assoc()['total'];
    }
}

// Get pending assignments count
$pending_sql = "SELECT COUNT(*) as total FROM assignments WHERE teacher_id = $teacher_id AND status = 'pending'";
$pending_result = $conn->query($pending_sql);
if ($pending_result) {
    $pending_assignments = $pending_result->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | EduSmart</title>
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

        .dashboard-wrapper {
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

        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #ffffff, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        }

        .sidebar a {
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

        .sidebar a i {
            width: 24px;
            font-size: 1.2rem;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px 40px;
        }

        .main-content h1 {
            font-size: 2rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #e2e8f0, #a0c4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .welcome-text {
            color: #8aa2d0;
            margin-bottom: 30px;
            font-size: 1rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: #38bdf8;
        }

        .stat-card i {
            font-size: 2rem;
            color: #38bdf8;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card p {
            color: #8aa2d0;
            font-size: 0.85rem;
        }

        /* Classes Grid */
        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .class-card {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            transition: all 0.3s ease;
        }

        .class-card:hover {
            transform: translateY(-5px);
            border-color: #38bdf8;
        }

        .class-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        }

        .class-header h3 {
            font-size: 1.3rem;
            color: #38bdf8;
        }

        .class-header .badge {
            background: rgba(56, 189, 248, 0.15);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        .class-info {
            margin-bottom: 15px;
        }

        .class-info p {
            margin: 8px 0;
            color: #cbd5e6;
            font-size: 0.9rem;
        }

        .class-info i {
            width: 25px;
            color: #38bdf8;
        }

        .class-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-sm {
            flex: 1;
            padding: 8px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.8rem;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-primary {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border: 1px solid #38bdf8;
        }

        .btn-primary:hover {
            background: #38bdf8;
            color: #0a0f1f;
        }

        .btn-success {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
            border: 1px solid #4caf50;
        }

        .btn-success:hover {
            background: #4caf50;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #8aa2d0;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            .sidebar h2 {
                display: none;
            }
            .sidebar a span {
                display: none;
            }
            .main-content {
                margin-left: 80px;
                padding: 20px;
            }
            .classes-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h2><i class="fas fa-chalkboard-user"></i> Teacher Portal</h2>
            <a href="teacher_dash.php" class="active">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
            <a href="my_classes.php">
                <i class="fas fa-school"></i> <span>My Classes</span>
            </a>
            <a href="upload_assignment.php">
                <i class="fas fa-tasks"></i> <span>Assignments</span>
            </a>
            <a href="enter_grades.php">
                <i class="fas fa-star"></i> <span>Gradebook</span>
            </a>
            <a href="attendance.php">
                <i class="fas fa-calendar-check"></i> <span>Attendance</span>
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </nav>

        <main class="main-content">
            <h1><i class="fas fa-chalkboard-user"></i> Teacher Dashboard</h1>
            <p class="welcome-text">Welcome back, <strong><?php echo htmlspecialchars($teacher_name); ?></strong>! Here's your teaching overview.</p>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-school"></i>
                    <h3><?php echo $total_classes; ?></h3>
                    <p>Assigned Classes</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3><?php echo $pending_assignments; ?></h3>
                    <p>Pending Assignments</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>98%</h3>
                    <p>Attendance Rate</p>
                </div>
            </div>

            <!-- My Classes -->
            <h2 style="margin: 30px 0 20px 0;"><i class="fas fa-school"></i> My Classes</h2>
            <?php if($classes_result && $classes_result->num_rows > 0): ?>
                <div class="classes-grid">
                    <?php while($class = $classes_result->fetch_assoc()): ?>
                        <div class="class-card">
                            <div class="class-header">
                                <h3><?php echo htmlspecialchars($class['class_name']); ?> <?php echo $class['section'] ? ' - ' . htmlspecialchars($class['section']) : ''; ?></h3>
                                <span class="badge"><?php echo $class['student_count']; ?> Students</span>
                            </div>
                            <div class="class-info">
                                <p><i class="fas fa-door-open"></i> Room: <?php echo $class['room_number'] ?: 'Not assigned'; ?></p>
                                <p><i class="fas fa-users"></i> Capacity: <?php echo $class['capacity']; ?></p>
                            </div>
                            <div class="class-actions">
                                <a href="view_students.php?class_id=<?php echo $class['id']; ?>" class="btn-sm btn-primary">
                                    <i class="fas fa-users"></i> View Students
                                </a>
                                <a href="enter_grades.php?class_id=<?php echo $class['id']; ?>" class="btn-sm btn-success">
                                    <i class="fas fa-star"></i> Grades
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-school"></i>
                    <p>No classes assigned yet. Please contact the administrator.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>