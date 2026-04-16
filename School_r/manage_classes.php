<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";
$edit_class = null;

// Handle Add Class
if (isset($_POST['add_class'])) {
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : 'NULL';
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    $capacity = (int)$_POST['capacity'];
    
    $sql = "INSERT INTO classes (class_name, section, teacher_id, room_number, capacity) 
            VALUES ('$class_name', '$section', $teacher_id, '$room_number', $capacity)";
    
    if ($conn->query($sql)) {
        $success = "✅ Class added successfully!";
    } else {
        $error = "❌ Error: " . $conn->error;
    }
}

// Handle Edit Class
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $result = $conn->query("SELECT * FROM classes WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $edit_class = $result->fetch_assoc();
    }
}

// Handle Update Class
if (isset($_POST['update_class'])) {
    $id = (int)$_POST['class_id'];
    $class_name = mysqli_real_escape_string($conn, $_POST['class_name']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : 'NULL';
    $room_number = mysqli_real_escape_string($conn, $_POST['room_number']);
    $capacity = (int)$_POST['capacity'];
    
    $sql = "UPDATE classes SET 
            class_name = '$class_name', 
            section = '$section', 
            teacher_id = $teacher_id, 
            room_number = '$room_number', 
            capacity = $capacity 
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        $success = "✅ Class updated successfully!";
        $edit_class = null;
    } else {
        $error = "❌ Error: " . $conn->error;
    }
}

// Handle Delete Class
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if class has students
    $check = $conn->query("SELECT COUNT(*) as count FROM students WHERE class_id = $id");
    $has_students = $check && $check->fetch_assoc()['count'] > 0;
    
    if ($has_students) {
        $error = "❌ Cannot delete class with enrolled students!";
    } else {
        $sql = "DELETE FROM classes WHERE id = $id";
        if ($conn->query($sql)) {
            $success = "✅ Class deleted successfully!";
        } else {
            $error = "❌ Error: " . $conn->error;
        }
    }
}

// Fetch all classes with teacher names
$sql = "SELECT c.*, u.full_name as teacher_name 
        FROM classes c 
        LEFT JOIN users u ON c.teacher_id = u.id AND u.role = 'teacher'
        ORDER BY c.class_name, c.section";
$classes_result = $conn->query($sql);

// Fetch teachers for dropdown
$teachers_result = $conn->query("SELECT id, full_name FROM users WHERE role = 'teacher' AND status = 'approved' ORDER BY full_name");

// Get statistics
$total_classes = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_teachers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher' AND status = 'approved'")->fetch_assoc()['count'];
$avg_capacity = $conn->query("SELECT AVG(capacity) as avg FROM classes")->fetch_assoc()['avg'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes | Admin Dashboard</title>
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

        .page-description {
            color: #8aa2d0;
            margin-bottom: 30px;
            font-size: 0.95rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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

        /* Message styles */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success {
            background: rgba(76, 175, 80, 0.15);
            border-left: 4px solid #4caf50;
            color: #a5d6a7;
        }

        .error {
            background: rgba(255, 82, 82, 0.15);
            border-left: 4px solid #ff5252;
            color: #ffb3b3;
        }

        /* Form Container */
        .form-container {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            padding: 30px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            margin-bottom: 30px;
        }

        .form-container h3 {
            margin-bottom: 20px;
            color: #38bdf8;
            font-size: 1.3rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #cbd5e6;
            font-size: 0.85rem;
        }

        .form-group label i {
            margin-right: 6px;
            color: #38bdf8;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 8px rgba(56, 189, 248, 0.2);
        }

        .btn-submit {
            padding: 10px 24px;
            border: none;
            border-radius: 40px;
            background: linear-gradient(95deg, #38bdf8, #2b9ed4);
            color: #0a0f1f;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.3);
        }

        .btn-cancel {
            background: rgba(255, 82, 82, 0.2);
            color: #ff5252;
            margin-left: 10px;
        }

        .btn-cancel:hover {
            background: #ff5252;
            color: white;
            box-shadow: 0 5px 15px rgba(255, 82, 82, 0.3);
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }

        .data-table th {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .data-table tr:hover {
            background: rgba(56, 189, 248, 0.05);
        }

        .data-table td {
            color: #cbd5e6;
            font-size: 0.9rem;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-edit {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border: 1px solid #38bdf8;
        }

        .btn-edit:hover {
            background: #38bdf8;
            color: #0a0f1f;
        }

        .btn-delete {
            background: rgba(255, 82, 82, 0.15);
            color: #ff5252;
            border: 1px solid #ff5252;
        }

        .btn-delete:hover {
            background: #ff5252;
            color: white;
        }

        .badge {
            background: rgba(56, 189, 248, 0.15);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
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

        /* Responsive */
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
            .form-grid {
                grid-template-columns: 1fr;
            }
            .data-table th,
            .data-table td {
                padding: 10px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h2><i class="fas fa-graduation-cap"></i> Admin Portal</h2>
            <a href="admin_dash.php">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
            <a href="manage_users.php">
                <i class="fas fa-users"></i> <span>Manage Users</span>
            </a>
            <a href="manage_classes.php" class="active">
                <i class="fas fa-chalkboard"></i> <span>Manage Classes</span>
            </a>
            <a href="post_notice.php">
                <i class="fas fa-bullhorn"></i> <span>Post Notice</span>
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </nav>

        <main class="main-content">
            <h1><i class="fas fa-chalkboard"></i> Manage Classes</h1>
            <p class="page-description">Create, edit, and manage class sections with assigned teachers</p>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-school"></i>
                    <h3><?php echo $total_classes; ?></h3>
                    <p>Total Classes</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3><?php echo $total_students; ?></h3>
                    <p>Total Students</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chalkboard-user"></i>
                    <h3><?php echo $total_teachers; ?></h3>
                    <p>Available Teachers</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo round($avg_capacity); ?></h3>
                    <p>Avg Class Capacity</p>
                </div>
            </div>

            <?php if(!empty($success)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Add/Edit Class Form -->
            <div class="form-container">
                <h3><i class="fas <?php echo $edit_class ? 'fa-edit' : 'fa-plus-circle'; ?>"></i> <?php echo $edit_class ? 'Edit Class' : 'Add New Class'; ?></h3>
                <form method="POST" action="" id="classForm">
                    <?php if($edit_class): ?>
                        <input type="hidden" name="class_id" value="<?php echo $edit_class['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label><i class="fas fa-layer-group"></i> Class Name</label>
                            <input type="text" name="class_name" placeholder="e.g., Grade 10, Form 4, Year 1" 
                                   value="<?php echo $edit_class ? htmlspecialchars($edit_class['class_name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-code-branch"></i> Section</label>
                            <input type="text" name="section" placeholder="e.g., A, B, Science, Arts" 
                                   value="<?php echo $edit_class ? htmlspecialchars($edit_class['section']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-chalkboard-user"></i> Class Teacher</label>
                            <select name="teacher_id">
                                <option value="">-- Select Teacher --</option>
                                <?php while($teacher = $teachers_result->fetch_assoc()): ?>
                                    <option value="<?php echo $teacher['id']; ?>" 
                                        <?php echo ($edit_class && $edit_class['teacher_id'] == $teacher['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($teacher['full_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-door-open"></i> Room Number</label>
                            <input type="text" name="room_number" placeholder="e.g., 101, B-12, Lab-3" 
                                   value="<?php echo $edit_class ? htmlspecialchars($edit_class['room_number']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> Capacity</label>
                            <input type="number" name="capacity" placeholder="Maximum students" 
                                   value="<?php echo $edit_class ? $edit_class['capacity'] : '40'; ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="<?php echo $edit_class ? 'update_class' : 'add_class'; ?>" class="btn-submit">
                        <i class="fas <?php echo $edit_class ? 'fa-save' : 'fa-plus'; ?>"></i>
                        <?php echo $edit_class ? 'UPDATE CLASS' : 'ADD CLASS'; ?>
                    </button>
                    
                    <?php if($edit_class): ?>
                        <a href="manage_classes.php" class="btn-submit btn-cancel">
                            <i class="fas fa-times"></i> CANCEL
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Classes List -->
            <h3 style="margin-bottom: 15px;"><i class="fas fa-list"></i> All Classes</h3>
            <div class="table-container">
                <?php if($classes_result && $classes_result->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Class Name</th>
                                <th>Section</th>
                                <th>Class Teacher</th>
                                <th>Room</th>
                                <th>Capacity</th>
                                <th>Enrolled</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($class = $classes_result->fetch_assoc()): 
                                // Get enrolled students count
                                $enrolled_result = $conn->query("SELECT COUNT(*) as count FROM students WHERE class_id = " . $class['id']);
                                $enrolled = $enrolled_result ? $enrolled_result->fetch_assoc()['count'] : 0;
                                $capacity_percentage = $class['capacity'] > 0 ? ($enrolled / $class['capacity']) * 100 : 0;
                            ?>
                                <tr>
                                    <td><?php echo $class['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                                    <td><?php echo $class['section'] ? htmlspecialchars($class['section']) : '<span class="badge">Not set</span>'; ?></td>
                                    <td>
                                        <?php if($class['teacher_name']): ?>
                                            <i class="fas fa-user-check"></i> <?php echo htmlspecialchars($class['teacher_name']); ?>
                                        <?php else: ?>
                                            <span class="badge"><i class="fas fa-user-plus"></i> Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $class['room_number'] ? htmlspecialchars($class['room_number']) : '—'; ?></td>
                                    <td>
                                        <?php echo $enrolled . ' / ' . $class['capacity']; ?>
                                        <div style="width: 100%; background: rgba(56,189,248,0.1); border-radius: 10px; margin-top: 5px; height: 4px;">
                                            <div style="width: <?php echo min($capacity_percentage, 100); ?>%; height: 4px; background: #38bdf8; border-radius: 10px;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge">
                                            <i class="fas fa-user-graduate"></i> <?php echo $enrolled; ?> students
                                        </span>
                                    </td>
                                    <td class="action-btns">
                                        <a href="?edit=<?php echo $class['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $class['id']; ?>" class="btn-delete" onclick="return confirm('Delete this class? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chalkboard"></i>
                        <p>No classes found. Click "Add New Class" to create one.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Form submission loading state
        document.getElementById("classForm")?.addEventListener("submit", function() {
            const btn = this.querySelector(".btn-submit");
            if(btn && !btn.classList.contains('btn-cancel')) {
                btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> PROCESSING...';
                btn.disabled = true;
            }
        });
    </script>
</body>
</html>