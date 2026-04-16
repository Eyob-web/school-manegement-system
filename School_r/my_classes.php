<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

$classes_sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count 
                FROM classes c 
                WHERE c.teacher_id = $teacher_id 
                ORDER BY c.class_name, c.section";
$classes_result = $conn->query($classes_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes | Teacher Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b0f1c; color: #f1f5f9; overflow-x: hidden; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
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
        .sidebar a i { width: 24px; font-size: 1.2rem; }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            transform: translateX(5px);
        }
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
        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .class-card {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 25px;
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
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        }
        .class-header h3 { font-size: 1.4rem; color: #38bdf8; }
        .student-count {
            background: rgba(56, 189, 248, 0.15);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .class-details p {
            margin: 12px 0;
            color: #cbd5e6;
        }
        .class-details i {
            width: 30px;
            color: #38bdf8;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid #38bdf8;
            color: #38bdf8;
        }
        .btn-primary:hover {
            background: #38bdf8;
            color: #0a0f1f;
        }
        .btn-success {
            background: rgba(76, 175, 80, 0.15);
            border: 1px solid #4caf50;
            color: #4caf50;
        }
        .btn-success:hover {
            background: #4caf50;
            color: white;
        }
        @media (max-width: 768px) {
            .sidebar { width: 80px; padding: 20px 10px; }
            .sidebar h2, .sidebar a span { display: none; }
            .main-content { margin-left: 80px; padding: 20px; }
            .classes-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h2><i class="fas fa-chalkboard-user"></i> Teacher Portal</h2>
            <a href="teacher_dash.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <a href="my_classes.php" class="active"><i class="fas fa-school"></i> <span>My Classes</span></a>
            <a href="upload_assignment.php"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a href="enter_grades.php"><i class="fas fa-star"></i> <span>Gradebook</span></a>
            <a href="attendance.php"><i class="fas fa-calendar-check"></i> <span>Attendance</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>

        <main class="main-content">
            <h1><i class="fas fa-school"></i> My Classes</h1>
            <p>View and manage all your assigned classes</p>

            <div class="classes-grid">
                <?php while($class = $classes_result->fetch_assoc()): ?>
                    <div class="class-card">
                        <div class="class-header">
                            <h3><?php echo htmlspecialchars($class['class_name']); ?> <?php echo $class['section']; ?></h3>
                            <span class="student-count"><i class="fas fa-users"></i> <?php echo $class['student_count']; ?> Students</span>
                        </div>
                        <div class="class-details">
                            <p><i class="fas fa-door-open"></i> <strong>Room:</strong> <?php echo $class['room_number'] ?: 'Not assigned'; ?></p>
                            <p><i class="fas fa-users"></i> <strong>Capacity:</strong> <?php echo $class['capacity']; ?></p>
                            <p><i class="fas fa-calendar"></i> <strong>Schedule:</strong> Mon - Fri, 8:00 AM - 3:00 PM</p>
                        </div>
                        <div class="action-buttons">
                            <a href="view_students.php?class_id=<?php echo $class['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-users"></i> View Students
                            </a>
                            <a href="enter_grades.php?class_id=<?php echo $class['id']; ?>" class="btn btn-success">
                                <i class="fas fa-star"></i> Manage Grades
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>
</html>