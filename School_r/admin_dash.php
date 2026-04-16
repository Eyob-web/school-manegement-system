<?php
// Start session and enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EduSmart</title>
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
            margin-bottom: 30px;
            background: linear-gradient(135deg, #e2e8f0, #a0c4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Card Grid */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .card {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: #38bdf8;
            background: rgba(30, 45, 75, 0.75);
        }

        .card i {
            font-size: 2.5rem;
            color: #38bdf8;
            margin-bottom: 15px;
        }

        .card h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
        }

        .card p {
            color: #a0b3d9;
            font-size: 1.5rem;
            font-weight: bold;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }
            .sidebar h2 {
                font-size: 0;
                display: none;
            }
            .sidebar a span {
                display: none;
            }
            .sidebar a i {
                font-size: 1.5rem;
                margin: 0 auto;
            }
            .main-content {
                margin-left: 80px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <nav class="sidebar">
            <h2><i class="fas fa-graduation-cap"></i> EduSmart</h2>
            <a href="admin_dash.php" class="active">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
            <a href="manage_users.php">
                <i class="fas fa-users"></i> <span>Manage Users</span>
            </a>
            <a href="manage_classes.php">
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
            <h1><i class="fas fa-tachometer-alt"></i> Welcome, Admin <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            
            <div class="card-grid">
                <div class="card">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Students</h3>
                    <p>150</p>
                </div>
                <div class="card">
                    <i class="fas fa-chalkboard-user"></i>
                    <h3>Teachers</h3>
                    <p>20</p>
                </div>
                <div class="card">
                    <i class="fas fa-bell"></i>
                    <h3>Active Notices</h3>
                    <p>5</p>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Approvals</h3>
                    <p id="pendingCount">0</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card" style="margin-top: 20px;">
                <i class="fas fa-history"></i>
                <h3>Recent Activity</h3>
                <p style="font-size: 0.9rem; color: #a0b3d9;">Welcome to EduSmart Admin Dashboard</p>
            </div>
        </main>
    </div>

    <script>
        // Fetch pending users count
        fetch('get_pending_count.php')
            .then(response => response.json())
            .then(data => {
                if(data.count) {
                    document.getElementById('pendingCount').innerText = data.count;
                }
            })
            .catch(error => console.log('Error:', error));
    </script>
</body>
</html>