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
$success = "";
$error = "";

// Get teacher's classes
$classes_result = $conn->query("SELECT * FROM classes WHERE teacher_id = $teacher_id");

// Handle assignment upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $class_id = (int)$_POST['class_id'];
    $due_date = $_POST['due_date'];
    $total_marks = (int)$_POST['total_marks'];
    
    // Handle file upload
    $file_path = "";
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $target_dir = "uploads/assignments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES['assignment_file']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }
    
    $sql = "INSERT INTO assignments (title, description, class_id, teacher_id, due_date, total_marks, file_path) 
            VALUES ('$title', '$description', $class_id, $teacher_id, '$due_date', $total_marks, '$file_path')";
    
    if ($conn->query($sql)) {
        $success = "✅ Assignment uploaded successfully!";
    } else {
        $error = "❌ Error: " . $conn->error;
    }
}

// Get existing assignments
$assignments_sql = "SELECT a.*, c.class_name, c.section 
                    FROM assignments a 
                    JOIN classes c ON a.class_id = c.id 
                    WHERE a.teacher_id = $teacher_id 
                    ORDER BY a.created_at DESC";
$assignments_result = $conn->query($assignments_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assignments | Teacher Dashboard</title>
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
        .form-container, .assignments-list {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
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
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: white;
            font-family: 'Inter', sans-serif;
        }
        .btn-submit {
            padding: 12px 24px;
            background: linear-gradient(95deg, #38bdf8, #2b9ed4);
            border: none;
            border-radius: 40px;
            color: #0a0f1f;
            font-weight: bold;
            cursor: pointer;
        }
        .assignment-card {
            background: rgba(15, 23, 42, 0.5);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 3px solid #38bdf8;
        }
        .success { background: rgba(76, 175, 80, 0.15); color: #4caf50; padding: 10px; border-radius: 10px; margin-bottom: 20px; }
        .error { background: rgba(255, 82, 82, 0.15); color: #ff5252; padding: 10px; border-radius: 10px; margin-bottom: 20px; }
        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar h2, .sidebar a span { display: none; }
            .main-content { margin-left: 80px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h2><i class="fas fa-chalkboard-user"></i> Teacher Portal</h2>
            <a href="teacher_dash.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            <a href="my_classes.php"><i class="fas fa-school"></i> <span>My Classes</span></a>
            <a href="upload_assignment.php" class="active"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a href="enter_grades.php"><i class="fas fa-star"></i> <span>Gradebook</span></a>
            <a href="attendance.php"><i class="fas fa-calendar-check"></i> <span>Attendance</span></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>

        <main class="main-content">
            <h1><i class="fas fa-tasks"></i> Upload Assignments</h1>
            <p>Create and manage assignments for your classes</p>

            <?php if($success): ?>
                <div class="success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-container">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> New Assignment</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Assignment Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Select Class</label>
                        <select name="class_id" required>
                            <option value="">Select Class</option>
                            <?php while($class = $classes_result->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>">
                                    <?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" required>
                    </div>
                    <div class="form-group">
                        <label>Total Marks</label>
                        <input type="number" name="total_marks" value="100" required>
                    </div>
                    <div class="form-group">
                        <label>Attachment (Optional)</label>
                        <input type="file" name="assignment_file">
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-upload"></i> Upload Assignment</button>
                </form>
            </div>

            <div class="assignments-list">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-list"></i> Recent Assignments</h3>
                <?php if($assignments_result && $assignments_result->num_rows > 0): ?>
                    <?php while($assignment = $assignments_result->fetch_assoc()): ?>
                        <div class="assignment-card">
                            <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                            <p><i class="fas fa-school"></i> Class: <?php echo $assignment['class_name'] . ' ' . $assignment['section']; ?></p>
                            <p><i class="fas fa-calendar"></i> Due: <?php echo date('M d, Y', strtotime($assignment['due_date'])); ?></p>
                            <p><i class="fas fa-star"></i> Total Marks: <?php echo $assignment['total_marks']; ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No assignments uploaded yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>