<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = $user_id"));
$user_department_id = $user['department_id'];

// Get notifications only for orders that have been updated in the last 30 minutes
$notifications_query = "
    SELECT c.*, p.name as product_name, p.speciality 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE p.department_id = '$user_department_id' 
    AND c.status IN ('validée', 'rejetée', 'livrée')
    AND c.status_updated_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
    ORDER BY c.status_updated_at DESC";

$notifications = mysqli_query($conn, $notifications_query);
$notification_data = [];

while ($row = mysqli_fetch_assoc($notifications)) {
    $notification_data[] = [
        'id' => $row['id'],
        'message' => sprintf(
            "Order for %s has been %s at %s",
            $row['product_name'],
            $row['status'],
            date('H:i', strtotime($row['status_updated_at']))
        ),
        'status' => $row['status'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode([
    'success' => true,
    'notifications' => $notification_data
]);
?>
