<?php
include 'db_config.php';
// In a real app, you would check session here: session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Dashboard | EduSmart</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    <div class="sidebar">
        <div class="sidebar-logo">
            <h2>Edu<span>Smart</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active">📊 Dashboard</a></li>
            <li><a href="students.php">👨‍🎓 Students</a></li>
            <li><a href="#">👩‍🏫 Teachers</a></li>
            <li><a href="#">📅 Attendance</a></li>
            <li><a href="#">💳 Fees</a></li>
            <li><a href="index.html" class="logout">Logout</a></li>
        </ul>
    </div>

    <main class="main-content">
        <header class="dash-header">
            <h3>Welcome, Admin</h3>
            <div class="user-profile">
                <span>Eyob Tsegaye</span>
            </div>
        </header>

        <section class="stat-cards">
            <div class="card">
                <h3>Total Students</h3>
                <p>1,240</p>
            </div>
            <div class="card">
                <h3>Teachers</h3>
                <p>45</p>
            </div>
            <div class="card">
                <h3>Pending Fees</h3>
                <p>$3,400</p>
            </div>
        </section>

        <section class="recent-activity">
            <h2>Recent Student Registrations</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id, full_name, email, role FROM users LIMIT 5";
                    $result = $conn->query($sql);
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>#{$row['id']}</td>
                                <td>{$row['full_name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['role']}</td>
                                <td><span class='badge active'>Active</span></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>