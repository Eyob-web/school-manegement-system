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

// Get student's class ID
$student_sql = "SELECT s.id as student_record_id, s.class_id, s.student_id as student_number
                FROM users u 
                LEFT JOIN students s ON u.id = s.user_id
                WHERE u.id = $user_id";
$student_result = $conn->query($student_sql);
$student = $student_result->fetch_assoc();

// Get assignments for student's class
$sql = "SELECT a.*, c.class_name, c.section,
        (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id AND student_id = {$student['student_record_id']}) as has_submitted,
        (SELECT grade FROM submissions WHERE assignment_id = a.id AND student_id = {$student['student_record_id']}) as obtained_marks
        FROM assignments a
        JOIN classes c ON a.class_id = c.id
        WHERE a.class_id = " . ($student['class_id'] ?? 0) . "
        ORDER BY a.due_date ASC";
$result = $conn->query($sql);

// Handle assignment submission
$submission_success = "";
$submission_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_assignment'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $student_id = $student['student_record_id'];
    $submission_text = mysqli_real_escape_string($conn, $_POST['submission_text']);
    
    // Handle file upload
    $file_path = "";
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $target_dir = "uploads/submissions/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES['submission_file']['name']);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }
    
    $insert_sql = "INSERT INTO submissions (assignment_id, student_id, submission_text, file_path, submitted_at) 
                   VALUES ($assignment_id, $student_id, '$submission_text', '$file_path', NOW())";
    
    if ($conn->query($insert_sql)) {
        $submission_success = "✅ Assignment submitted successfully!";
        // Refresh the page to show updated status
        echo "<script>setTimeout(function(){ window.location.href = 'view_assignments.php'; }, 2000);</script>";
    } else {
        $submission_error = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: rgba(20, 30, 55, 0.95);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }
        .modal-content h3 {
            color: #38bdf8;
            margin-bottom: 20px;
        }
        .modal-content textarea {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: white;
            margin-bottom: 15px;
        }
        .modal-content input[type="file"] {
            margin-bottom: 15px;
            color: white;
        }
        .close-modal {
            float: right;
            cursor: pointer;
            font-size: 1.5rem;
            color: #ff5252;
        }
        .submitted-badge {
            background: rgba(76, 175, 80, 0.15);
            color: #4caf50;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
        }
        .grade-badge {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            display: inline-block;
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
            <a class="active" href="view_assignments.php"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a href="view_schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a>
            <a href="view_notices.php"><i class="fas fa-bullhorn"></i> <span>Notices</span></a>
            <a href="student_profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a>
            <div class="divider"></div>
            <a class="logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <h2 style="margin-left: 20px;"><i class="fas fa-tasks"></i> My Assignments</h2>
            <div class="user">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=38bdf8&color=fff&rounded=true">
            </div>
        </header>

        <section class="content">
            <div class="welcome">
                <h1>Assignments & Tasks 📝</h1>
                <p>View and submit your assignments before the deadline</p>
            </div>

            <?php if($submission_success): ?>
                <div class="success-msg" style="background:rgba(76,175,80,0.15); padding:15px; border-radius:10px; margin-bottom:20px; color:#4caf50;">
                    <i class="fas fa-check-circle"></i> <?php echo $submission_success; ?>
                </div>
            <?php endif; ?>

            <?php if($submission_error): ?>
                <div class="error-msg" style="background:rgba(255,82,82,0.15); padding:15px; border-radius:10px; margin-bottom:20px; color:#ff5252;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $submission_error; ?>
                </div>
            <?php endif; ?>

            <div class="assignment-grid">
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($assignment = $result->fetch_assoc()):
                        $is_overdue = strtotime($assignment['due_date']) < strtotime('now');
                        $is_submitted = $assignment['has_submitted'] > 0;
                        $has_grade = $assignment['obtained_marks'] !== null;
                    ?>
                        <div class="assignment-card">
                            <div class="card-tag">
                                <?php if($is_overdue && !$is_submitted): ?>
                                    <span style="color:#ff5252;"><i class="fas fa-exclamation-triangle"></i> Overdue</span>
                                <?php elseif($is_submitted): ?>
                                    <span style="color:#4caf50;"><i class="fas fa-check-circle"></i> Submitted</span>
                                <?php else: ?>
                                    <span style="color:#ffc107;"><i class="fas fa-clock"></i> Pending</span>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                            <div class="assignment-meta" style="margin: 10px 0;">
                                <p><i class="fas fa-school"></i> Class: <?php echo htmlspecialchars($assignment['class_name'] . ' ' . $assignment['section']); ?></p>
                                <p><i class="fas fa-calendar"></i> Due: <?php echo date('F d, Y', strtotime($assignment['due_date'])); ?></p>
                                <p><i class="fas fa-star"></i> Total Marks: <?php echo $assignment['total_marks']; ?></p>
                                <?php if($has_grade): ?>
                                    <p><i class="fas fa-trophy"></i> Obtained: <?php echo $assignment['obtained_marks']; ?> / <?php echo $assignment['total_marks']; ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="assignment-actions">
                                <?php if($assignment['file_path']): ?>
                                    <a href="<?php echo $assignment['file_path']; ?>" class="btn-download" download>
                                        <i class="fas fa-file-download"></i> Download Instructions
                                    </a>
                                <?php endif; ?>
                                <?php if(!$is_submitted && !$is_overdue): ?>
                                    <button onclick="openSubmitModal(<?php echo $assignment['id']; ?>, '<?php echo addslashes($assignment['title']); ?>')" class="btn-submit">
                                        <i class="fas fa-upload"></i> Submit Assignment
                                    </button>
                                <?php elseif($is_submitted): ?>
                                    <span class="submitted-badge"><i class="fas fa-check"></i> Submitted on <?php echo date('M d, Y', strtotime($assignment['submitted_at'] ?? 'now')); ?></span>
                                    <?php if($has_grade): ?>
                                        <span class="grade-badge"><i class="fas fa-star"></i> Grade: <?php echo $assignment['obtained_marks']; ?>/<?php echo $assignment['total_marks']; ?></span>
                                    <?php endif; ?>
                                <?php elseif($is_overdue && !$is_submitted): ?>
                                    <span class="submitted-badge" style="background:rgba(255,82,82,0.15); color:#ff5252;">
                                        <i class="fas fa-times-circle"></i> Submission Closed
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 40px;">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: #8aa2d0;"></i>
                        <p>No assignments available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <footer class="footer">
            <i class="fas fa-graduation-cap"></i> © 2026 EduSmart SMS | Assignment Management System
        </footer>
    </main>
</div>

<!-- Submission Modal -->
<div id="submitModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Submit Assignment</h3>
        <form method="POST" enctype="multipart/form-data" id="submissionForm">
            <input type="hidden" name="assignment_id" id="assignment_id">
            <textarea name="submission_text" rows="5" placeholder="Write your submission notes here..." required></textarea>
            <input type="file" name="submission_file" accept=".pdf,.doc,.docx,.zip">
            <small style="color: #8aa2d0;">Supported formats: PDF, DOC, DOCX, ZIP (Max 10MB)</small>
            <button type="submit" name="submit_assignment" class="btn-submit" style="width: 100%; margin-top: 15px;">
                <i class="fas fa-paper-plane"></i> Submit Assignment
            </button>
        </form>
    </div>
</div>

<script>
function openSubmitModal(assignmentId, title) {
    document.getElementById('assignment_id').value = assignmentId;
    document.getElementById('modalTitle').innerHTML = 'Submit: ' + title;
    document.getElementById('submitModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('submitModal').style.display = 'none';
}

window.onclick = function(event) {
    let modal = document.getElementById('submitModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>