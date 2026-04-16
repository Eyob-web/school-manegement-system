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
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$success = "";
$error = "";

// Get teacher's classes
$classes_result = $conn->query("SELECT * FROM classes WHERE teacher_id = $teacher_id");

// Get students for selected class
$students = [];
if ($class_id > 0) {
    $students_result = $conn->query("SELECT * FROM students WHERE class_id = $class_id ORDER BY full_name");
    while($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Handle attendance submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_attendance'])) {
    foreach ($_POST['attendance'] as $student_id => $status) {
        $student_id = (int)$student_id;
        $status = mysqli_real_escape_string($conn, $status);
        
        $sql = "INSERT INTO attendance (student_id, class_id, date, status, teacher_id) 
                VALUES ($student_id, $class_id, '$date', '$status', $teacher_id)
                ON DUPLICATE KEY UPDATE status = '$status'";
        $conn->query($sql);
    }
    $success = "✅ Attendance saved successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | Teacher Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b0f1c; color: #f1f5f9; }
        .dashboard-wrapper { display: flex; min-height: 100vh; }
        .sidebar {
            width: 280px;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(56, 189, 248, 0.25);
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
        }
        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #ffffff, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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
        .sidebar a i { width: 24px; }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
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
        .form-container {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #cbd5e6;
            font-weight: 600;
        }
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: white;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .attendance-table th, .attendance-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }
        .attendance-table th {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
        }
        .status-select {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: white;
            cursor: pointer;
        }
        .present { color: #4caf50; }
        .absent { color: #ff5252; }
        .late { color: #ffc107; }
        .excused { color: #2196f3; }
        .btn-submit {
            padding: 12px 24px;
            background: linear-gradient(95deg, #38bdf8, #2b9ed4);
            border: none;
            border-radius: 40px;
            color: #0a0f1f;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }
        .success {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar h2, .sidebar a span { display: none; }
            .main-content { margin-left: 80px; padding: 20px; }
            .attendance-table { font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h2><i class="fas fa-chalkboard-user"></i> Teacher Portal</h2>
            <a href="teacher_dash.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <a href="my_classes.php"><i class="fas fa-school"></i> <span>My Classes</span></a>
            <a href="upload_assignment.php"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a href="enter_grades.php"><i class="fas fa-star"></i> <span>Gradebook</span></a>
            <a href="attendance.php" class="active"><i class="fas fa-calendar-check"></i> <span>Attendance</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>

        <main class="main-content">
            <h1><i class="fas fa-calendar-check"></i> Attendance Tracking</h1>
            <p>Mark student attendance for your classes</p>

            <?php if($success): ?>
                <div class="success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="GET" action="">
                    <div class="form-group">
                        <label>Select Class</label>
                        <select name="class_id" onchange="this.form.submit()" required>
                            <option value="">-- Select Class --</option>
                            <?php while($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" value="<?php echo $date; ?>" onchange="this.form.submit()">
                    </div>
                </form>

                <?php if($class_id > 0 && !empty($students)): ?>
                    <form method="POST">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $student): 
                                    // Get current attendance status
                                    $status_sql = "SELECT status FROM attendance 
                                                   WHERE student_id = {$student['id']} 
                                                   AND date = '$date' 
                                                   AND class_id = $class_id";
                                    $status_result = $conn->query($status_sql);
                                    $current_status = $status_result && $status_result->num_rows > 0 ? $status_result->fetch_assoc()['status'] : 'present';
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td>
                                            <select name="attendance[<?php echo $student['id']; ?>]" class="status-select">
                                                <option value="present" <?php echo $current_status == 'present' ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo $current_status == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                                <option value="late" <?php echo $current_status == 'late' ? 'selected' : ''; ?>>Late</option>
                                                <option value="excused" <?php echo $current_status == 'excused' ? 'selected' : ''; ?>>Excused</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" name="save_attendance" class="btn-submit">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                    </form>
                <?php elseif($class_id > 0): ?>
                    <p>No students found in this class.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>