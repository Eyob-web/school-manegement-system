<?php
session_start();
include 'config.php';

$notices = [];

$sql = "SELECT n.*, u.full_name as posted_by_name 
        FROM notices n 
        LEFT JOIN users u ON n.posted_by = u.id 
        ORDER BY n.created_at DESC 
        LIMIT 5";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Format target class for display
        if($row['target_class'] == 'NULL' || $row['target_class'] === null) {
            $row['target_class'] = 'Everyone';
        } elseif($row['target_class'] == 'students') {
            $row['target_class'] = 'All Students';
        } elseif($row['target_class'] == 'teachers') {
            $row['target_class'] = 'All Teachers';
        }
        
        // Format dates
        $row['created_at'] = date('M d, Y', strtotime($row['created_at']));
        if($row['expiration_date']) {
            $row['expiration_date'] = date('M d, Y', strtotime($row['expiration_date']));
        }
        
        $notices[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode(['notices' => $notices]);
?>