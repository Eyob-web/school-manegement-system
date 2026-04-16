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

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'];

// Get student's grades with proper joins
$sql = "SELECT g.*, c.class_name 
        FROM grades g
        JOIN students s ON g.student_id = s.id
        JOIN classes c ON g.class_id = c.id
        WHERE s.user_id = $student_id 
        ORDER BY g.created_at DESC";
$result = $conn->query($sql);

// Calculate overall GPA
$total_points = 0;
$total_subjects = 0;
$grades_data = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $grades_data[] = $row;
        $total_points += $row['grade'];
        $total_subjects++;
    }
}
$gpa = $total_subjects > 0 ? round($total_points / $total_subjects, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo">Edu<span>Smart</span></div>
        <nav>
            <a href="student_dash.php"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a class="active" href="view_grades.php"><i class="fas fa-graduation-cap"></i> <span>Grades</span></a>
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

        <header class="topbar">
            <h2 style="margin-left: 20px;"><i class="fas fa-chart-line"></i> My Grades</h2>
            <div class="user">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=38bdf8&color=fff&rounded=true">
            </div>
        </header>

        <section class="content">

            <div class="welcome">
                <h1>Academic Performance 📊</h1>
                <p>Track your subject results and academic progress</p>
            </div>

            <!-- GPA Summary Card -->
            <div class="stats">
                <div class="card">
                    <i class="fas fa-calculator"></i>
                    <h3><?php echo $gpa; ?></h3>
                    <p>Overall GPA</p>
                </div>
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h3><?php echo $total_subjects; ?></h3>
                    <p>Subjects Taken</p>
                </div>
                <div class="card">
                    <i class="fas fa-trophy"></i>
                    <h3><?php 
                        $highest = !empty($grades_data) ? max(array_column($grades_data, 'grade')) : 0;
                        echo $highest;
                    ?></h3>
                    <p>Highest Score</p>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="panel">
                <div class="panel-header">
                    <h3><i class="fas fa-list"></i> Grades Overview</h3>
                </div>
                <div class="panel-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Assessment Type</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($grades_data)): ?>
                                    <?php foreach($grades_data as $grade): 
                                        $percentage = ($grade['grade'] / 100) * 100;
                                        $letter_grade = '';
                                        if($percentage >= 90) $letter_grade = 'A+';
                                        elseif($percentage >= 80) $letter_grade = 'A';
                                        elseif($percentage >= 75) $letter_grade = 'B+';
                                        elseif($percentage >= 70) $letter_grade = 'B';
                                        elseif($percentage >= 65) $letter_grade = 'C+';
                                        elseif($percentage >= 60) $letter_grade = 'C';
                                        elseif($percentage >= 50) $letter_grade = 'D';
                                        else $letter_grade = 'F';
                                        
                                        $status = ($grade['grade'] >= 50) ? "Pass" : "Fail";
                                        $status_class = ($status == "Pass") ? "pass" : "fail";
                                    ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($grade['subject']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($grade['assessment_type']); ?></td>
                                            <td><?php echo $grade['grade']; ?>%</td>
                                            <td><?php echo $letter_grade; ?></td>
                                            <td><span class="badge <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty">No grades found yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </section>

        <footer class="footer">
            <i class="fas fa-graduation-cap"></i> © 2026 EduSmart SMS | Grades Module
        </footer>

    </main>

</div>

</body>
</html>