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

$student_name = $_SESSION['name'];

// Sample schedule data - In production, this would come from a database
$schedule = [
    'Monday' => [
        ['time' => '08:30 - 10:30', 'subject' => 'Mathematics', 'teacher' => 'Mr. John Doe', 'room' => '101'],
        ['time' => '10:45 - 12:45', 'subject' => 'Physics', 'teacher' => 'Ms. Jane Smith', 'room' => 'Lab 2'],
        ['time' => '13:30 - 15:30', 'subject' => 'English', 'teacher' => 'Mrs. Sarah Johnson', 'room' => '205']
    ],
    'Tuesday' => [
        ['time' => '08:30 - 10:30', 'subject' => 'Chemistry', 'teacher' => 'Dr. Robert Brown', 'room' => 'Lab 1'],
        ['time' => '10:45 - 12:45', 'subject' => 'History', 'teacher' => 'Ms. Emily Davis', 'room' => '304'],
        ['time' => '13:30 - 15:30', 'subject' => 'Physical Education', 'teacher' => 'Coach Mike Wilson', 'room' => 'Gym']
    ],
    'Wednesday' => [
        ['time' => '08:30 - 10:30', 'subject' => 'Computer Science', 'teacher' => 'Mr. David Lee', 'room' => 'Lab 3'],
        ['time' => '10:45 - 12:45', 'subject' => 'Mathematics', 'teacher' => 'Mr. John Doe', 'room' => '101'],
        ['time' => '13:30 - 15:30', 'subject' => 'Physics', 'teacher' => 'Ms. Jane Smith', 'room' => 'Lab 2']
    ],
    'Thursday' => [
        ['time' => '08:30 - 10:30', 'subject' => 'English', 'teacher' => 'Mrs. Sarah Johnson', 'room' => '205'],
        ['time' => '10:45 - 12:45', 'subject' => 'Chemistry', 'teacher' => 'Dr. Robert Brown', 'room' => 'Lab 1'],
        ['time' => '13:30 - 15:30', 'subject' => 'History', 'teacher' => 'Ms. Emily Davis', 'room' => '304']
    ],
    'Friday' => [
        ['time' => '08:30 - 10:30', 'subject' => 'Computer Science', 'teacher' => 'Mr. David Lee', 'room' => 'Lab 3'],
        ['time' => '10:45 - 12:45', 'subject' => 'Mathematics', 'teacher' => 'Mr. John Doe', 'room' => '101'],
        ['time' => '13:30 - 15:30', 'subject' => 'Islamic Studies', 'teacher' => 'Sheikh Ahmed Hassan', 'room' => 'Auditorium']
    ]
];

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$today = date('l');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule | EduSmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="student.css">
    <style>
        .schedule-container {
            background: rgba(20, 30, 55, 0.65);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            overflow-x: auto;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }
        .schedule-table th,
        .schedule-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(56, 189, 248, 0.1);
            vertical-align: top;
        }
        .schedule-table th {
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            font-weight: 600;
        }
        .schedule-table td {
            color: #cbd5e6;
        }
        .today-highlight {
            background: rgba(56, 189, 248, 0.15);
            border-left: 3px solid #38bdf8;
        }
        .time-slot {
            font-weight: bold;
            color: #38bdf8;
        }
        .subject-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .teacher-name, .room-info {
            font-size: 0.8rem;
            color: #8aa2d0;
        }
        .current-time-indicator {
            background: rgba(56, 189, 248, 0.2);
            padding: 10px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .day-tab {
            display: inline-block;
            padding: 10px 20px;
            margin: 0 5px;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .day-tab.active {
            background: #38bdf8;
            color: #0a0f1f;
        }
        .day-tab:hover {
            background: rgba(56, 189, 248, 0.15);
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
            <a href="view_assignments.php"><i class="fas fa-tasks"></i> <span>Assignments</span></a>
            <a class="active" href="view_schedule.php"><i class="fas fa-calendar-alt"></i> <span>Schedule</span></a>
            <a href="view_notices.php"><i class="fas fa-bullhorn"></i> <span>Notices</span></a>
            <a href="student_profile.php"><i class="fas fa-user-circle"></i> <span>Profile</span></a>
            <div class="divider"></div>
            <a class="logout" href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <main class="main">
        <header class="topbar">
            <h2 style="margin-left: 20px;"><i class="fas fa-calendar-alt"></i> Class Schedule</h2>
            <div class="user">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=38bdf8&color=fff&rounded=true">
            </div>
        </header>

        <section class="content">
            <div class="welcome">
                <h1>Weekly Class Schedule 📅</h1>
                <p>Your complete timetable for the current semester</p>
            </div>

            <div class="current-time-indicator">
                <i class="fas fa-calendar-day"></i> Today is <strong><?php echo $today; ?></strong> 
                | <i class="fas fa-clock"></i> Current Time: <span id="currentTime"></span>
            </div>

            <div class="schedule-container">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Time</th>
                            <?php foreach($days as $day): ?>
                                <th>
                                    <?php echo $day; ?>
                                    <?php if($day == $today): ?>
                                        <span style="font-size: 0.7rem; background: #38bdf8; color: #0a0f1f; padding: 2px 8px; border-radius: 20px; margin-left: 8px;">Today</span>
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get all unique time slots
                        $time_slots = [];
                        foreach($schedule as $day => $classes) {
                            foreach($classes as $class) {
                                if(!in_array($class['time'], $time_slots)) {
                                    $time_slots[] = $class['time'];
                                }
                            }
                        }
                        sort($time_slots);
                        
                        foreach($time_slots as $slot):
                        ?>
                            <tr>
                                <td class="time-slot"><?php echo $slot; ?></td>
                                <?php foreach($days as $day): 
                                    $class_info = null;
                                    foreach($schedule[$day] as $class) {
                                        if($class['time'] == $slot) {
                                            $class_info = $class;
                                            break;
                                        }
                                    }
                                ?>
                                    <td class="<?php echo ($day == $today && $class_info) ? 'today-highlight' : ''; ?>">
                                        <?php if($class_info): ?>
                                            <div class="subject-name">
                                                <i class="fas fa-book"></i> <?php echo htmlspecialchars($class_info['subject']); ?>
                                            </div>
                                            <div class="teacher-name">
                                                <i class="fas fa-chalkboard-user"></i> <?php echo htmlspecialchars($class_info['teacher']); ?>
                                            </div>
                                            <div class="room-info">
                                                <i class="fas fa-door-open"></i> Room: <?php echo htmlspecialchars($class_info['room']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #4a5a7a;">— Free Period —</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Legend -->
            <div class="panel" style="margin-top: 25px;">
                <div class="panel-header">
                    <h3><i class="fas fa-info-circle"></i> Schedule Information</h3>
                </div>
                <div class="panel-body">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <div><i class="fas fa-clock" style="color: #38bdf8;"></i> Time: 08:30 AM - 03:30 PM</div>
                        <div><i class="fas fa-coffee" style="color: #ffc107;"></i> Break: 10:30 - 10:45 AM & 12:45 - 01:30 PM</div>
                        <div><i class="fas fa-mosque" style="color: #4caf50;"></i> Friday Prayer: 12:45 - 01:30 PM</div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <i class="fas fa-graduation-cap"></i> © 2026 EduSmart SMS | Class Schedule
        </footer>
    </main>
</div>

<script>
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    document.getElementById('currentTime').innerHTML = timeString;
}
updateTime();
setInterval(updateTime, 1000);
</script>

</body>
</html>