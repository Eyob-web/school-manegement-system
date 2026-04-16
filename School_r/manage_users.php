<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config.php';

// Check if user is admin - using proper function
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$messageType = "";

// Handle approval/rejection/deletion
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];
    
    if ($action == 'approve') {
        $update = "UPDATE users SET status = 'approved' WHERE id = '$user_id'";
        $message = "User approved successfully!";
        $messageType = "success";
    } elseif ($action == 'reject') {
        $update = "UPDATE users SET status = 'rejected' WHERE id = '$user_id'";
        $message = "User rejected!";
        $messageType = "success";
    } elseif ($action == 'delete') {
        $update = "DELETE FROM users WHERE id = '$user_id'";
        $message = "User deleted!";
        $messageType = "success";
    }
    
    if (isset($update) && $conn->query($update)) {
        // Success
    } elseif (isset($update)) {
        $message = "Error: " . $conn->error;
        $messageType = "error";
    }
}

// Fetch all users
$sql = "SELECT * FROM users ORDER BY 
        CASE status 
            WHEN 'pending' THEN 1 
            WHEN 'approved' THEN 2 
            WHEN 'rejected' THEN 3 
        END, 
        created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | Admin Dashboard</title>
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
            margin-bottom: 20px;
            background: linear-gradient(135deg, #e2e8f0, #a0c4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        /* Message styles */
        .message {
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .success {
            background: rgba(76, 175, 80, 0.2);
            border-left: 4px solid #4caf50;
            color: #a5d6a7;
        }
        .error {
            background: rgba(255, 82, 82, 0.2);
            border-left: 4px solid #ff5252;
            color: #ffb3b3;
        }

        /* Form styles */
        .create-admin-form {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        .create-admin-form h3 {
            margin-bottom: 20px;
            color: #38bdf8;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row input {
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            background: rgba(15, 23, 42, 0.7);
            color: white;
            font-family: 'Inter', sans-serif;
        }

        .form-row input:focus {
            outline: none;
            border-color: #38bdf8;
        }

        .btn-create {
            padding: 12px 24px;
            background: linear-gradient(95deg, #38bdf8, #2b9ed4);
            border: none;
            border-radius: 40px;
            color: #0a0f1f;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.3);
        }

        /* Table styles */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            overflow: hidden;
        }

        .user-table th, 
        .user-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
        }

        .user-table th {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            font-weight: 600;
        }

        .user-table tr:hover {
            background: rgba(56, 189, 248, 0.05);
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-approved {
            color: #4caf50;
            font-weight: bold;
        }
        .status-rejected {
            color: #ff5252;
            font-weight: bold;
        }

        .btn-sm {
            padding: 6px 12px;
            margin: 0 3px;
            text-decoration: none;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-approve {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
            border: 1px solid #4caf50;
        }
        .btn-approve:hover {
            background: #4caf50;
            color: white;
        }
        .btn-reject {
            background: rgba(255, 82, 82, 0.2);
            color: #ff5252;
            border: 1px solid #ff5252;
        }
        .btn-reject:hover {
            background: #ff5252;
            color: white;
        }
        .btn-delete {
            background: rgba(255, 82, 82, 0.2);
            color: #ff5252;
            border: 1px solid #ff5252;
        }
        .btn-delete:hover {
            background: #ff5252;
            color: white;
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
            .user-table th, 
            .user-table td {
                padding: 10px;
                font-size: 12px;
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
            <a href="manage_users.php" class="active">
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
            <h1><i class="fas fa-users"></i> Manage Users</h1>
            
            <?php if(!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Create Admin Form -->
            <div class="create-admin-form">
                <h3><i class="fas fa-user-shield"></i> Create New Admin Account</h3>
                <form method="POST" action="create_admin.php" id="createAdminForm">
                    <div class="form-row">
                        <input type="text" name="full_name" placeholder="Full Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn-create">
                        <i class="fas fa-plus"></i> Create Admin
                    </button>
                </form>
            </div>

            <!-- Users Table -->
            <h3 style="margin-bottom: 15px;"><i class="fas fa-list"></i> All Users</h3>
            <div style="overflow-x: auto;">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php 
                                        $roleIcon = $user['role'] == 'admin' ? 'fa-user-shield' : ($user['role'] == 'teacher' ? 'fa-chalkboard-user' : 'fa-user-graduate');
                                        echo "<i class='fas $roleIcon'></i> " . ucfirst($user['role']); 
                                        ?>
                                    </td>
                                    <td class="status-<?php echo $user['status']; ?>">
                                        <?php 
                                        $statusIcon = $user['status'] == 'approved' ? 'fa-check-circle' : ($user['status'] == 'pending' ? 'fa-clock' : 'fa-times-circle');
                                        echo "<i class='fas $statusIcon'></i> " . ucfirst($user['status']);
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if($user['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $user['id']; ?>" class="btn-sm btn-approve" onclick="return confirm('Approve this user?')">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                            <a href="?action=reject&id=<?php echo $user['id']; ?>" class="btn-sm btn-reject" onclick="return confirm('Reject this user?')">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        <?php if($user['email'] != 'admin@school.com'): ?>
                                            <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Delete this user permanently?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('createAdminForm')?.addEventListener('submit', function(e) {
            const btn = this.querySelector('.btn-create');
            btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i> Creating...';
            btn.disabled = true;
        });
    </script>
</body>
</html>