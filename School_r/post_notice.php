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

// Fetch classes for dropdown
$classes = [];
$class_result = $conn->query("SELECT * FROM classes ORDER BY class_name");
if ($class_result && $class_result->num_rows > 0) {
    while($row = $class_result->fetch_assoc()) {
        $classes[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $exp_date = mysqli_real_escape_string($conn, $_POST['expiration_date']);
    $target_class = isset($_POST['target_class']) ? $_POST['target_class'] : 'all';
    
    // Handle target class
    if ($target_class == 'all') {
        $target_sql = "NULL";
    } else {
        $target_sql = "'" . mysqli_real_escape_string($conn, $target_class) . "'";
    }
    
    $posted_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO notices (title, content, target_class, expiration_date, posted_by, created_at) 
            VALUES ('$title', '$content', $target_sql, '$exp_date', '$posted_by', NOW())";
    
    if ($conn->query($sql)) {
        $success = "✅ Notice published successfully!";
    } else {
        $error = "❌ Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Notice | Admin Dashboard</title>
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
            padding: 35px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            max-width: 700px;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            border-color: #38bdf8;
            box-shadow: 0 10px 30px rgba(56, 189, 248, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #cbd5e6;
            font-size: 0.9rem;
        }

        .form-group label i {
            margin-right: 8px;
            color: #38bdf8;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 10px rgba(56, 189, 248, 0.2);
            transform: scale(1.01);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: rgba(161, 179, 217, 0.5);
        }

        /* Button */
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 40px;
            background: linear-gradient(95deg, #38bdf8, #2b9ed4);
            color: #0a0f1f;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.3);
            background: linear-gradient(95deg, #4cc9ff, #38bdf8);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            font-size: 1rem;
        }

        /* Recent Notices Section */
        .recent-notices {
            margin-top: 40px;
        }

        .recent-notices h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: #38bdf8;
        }

        .notices-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .notice-card {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            transition: all 0.3s ease;
        }

        .notice-card:hover {
            transform: translateY(-3px);
            border-color: #38bdf8;
        }

        .notice-card h4 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #38bdf8;
        }

        .notice-card p {
            color: #a0b3d9;
            font-size: 0.85rem;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .notice-meta {
            font-size: 0.75rem;
            color: #6b85b0;
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid rgba(56, 189, 248, 0.1);
        }

        .badge {
            background: rgba(56, 189, 248, 0.15);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            color: #38bdf8;
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
            .form-container {
                padding: 25px;
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
            <a href="manage_classes.php">
                <i class="fas fa-chalkboard"></i> <span>Manage Classes</span>
            </a>
            <a href="post_notice.php" class="active">
                <i class="fas fa-bullhorn"></i> <span>Post Notice</span>
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </nav>

        <main class="main-content">
            <h1><i class="fas fa-bullhorn"></i> Post Notice</h1>
            <p class="page-description">Create and publish announcements for students, teachers, or everyone</p>

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

            <div class="form-container">
                <form action="post_notice.php" method="POST" id="noticeForm">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Notice Title</label>
                        <input type="text" name="title" placeholder="Enter notice title" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Content / Message</label>
                        <textarea name="content" placeholder="Write your notice details here..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Target Audience</label>
                        <select name="target_class" required>
                            <option value="all">📢 Everyone (All Users)</option>
                            <option value="students">🎓 All Students</option>
                            <option value="teachers">👨‍🏫 All Teachers</option>
                            <?php if(!empty($classes)): ?>
                                <option value="" disabled>--- Specific Classes ---</option>
                                <?php foreach($classes as $class): ?>
                                    <option value="<?php echo $class['class_name']; ?>">
                                        📚 Class <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Expiration Date</label>
                        <input type="date" name="expiration_date" required>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        <span>Publish Notice</span>
                        <span class="loader" style="display: none;"></span>
                    </button>
                </form>
            </div>

            <!-- Recent Notices Preview -->
            <div class="recent-notices">
                <h3><i class="fas fa-clock"></i> Recent Notices</h3>
                <div class="notices-grid" id="recentNotices">
                    <!-- Recent notices will load here -->
                    <div class="notice-card">
                        <p style="text-align: center; color: #6b85b0;">
                            <i class="fas fa-spinner fa-pulse"></i> Loading recent notices...
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Loading animation on submit
        document.getElementById("noticeForm").addEventListener("submit", function() {
            const btn = document.getElementById("submitBtn");
            const btnText = btn.querySelector("span:not(.loader)");
            const loader = btn.querySelector(".loader");
            
            btnText.innerHTML = "PUBLISHING...";
            loader.style.display = "inline-block";
            btn.disabled = true;
            btn.style.opacity = "0.7";
        });

        // Fetch recent notices
        function loadRecentNotices() {
            fetch('get_notices.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('recentNotices');
                    if(data.notices && data.notices.length > 0) {
                        container.innerHTML = data.notices.map(notice => `
                            <div class="notice-card">
                                <h4><i class="fas fa-bullhorn"></i> ${escapeHtml(notice.title)}</h4>
                                <p>${escapeHtml(notice.content.substring(0, 100))}${notice.content.length > 100 ? '...' : ''}</p>
                                <div class="notice-meta">
                                    <span><i class="fas fa-tag"></i> ${notice.target_class || 'Everyone'}</span>
                                    <span><i class="fas fa-calendar"></i> ${notice.created_at}</span>
                                </div>
                                ${notice.expiration_date ? `<div class="badge"><i class="fas fa-hourglass-end"></i> Expires: ${notice.expiration_date}</div>` : ''}
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = `
                            <div class="notice-card">
                                <p style="text-align: center;"><i class="fas fa-inbox"></i> No notices yet. Create your first notice above!</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.log('Error loading notices:', error);
                });
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load notices when page loads
        loadRecentNotices();

        // Set default expiration date to 30 days from now
        const dateInput = document.querySelector('input[name="expiration_date"]');
        if(dateInput) {
            const today = new Date();
            const futureDate = new Date(today);
            futureDate.setDate(today.getDate() + 30);
            dateInput.value = futureDate.toISOString().split('T')[0];
        }
    </script>
</body>
</html>