<?php 
session_start(); 
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$select_user = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);

// Redirect admin users to the admin dashboard
if ($fetch_user['type'] == 'admin') {
    header('location: index.php');
    exit();
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('location: login.php');
    exit();
}
// Include the sidebar for department head
include 'sidebarr.php';

// Show department head notification on first login
$notification = "";
if (isset($_SESSION['dept_notification']) && $_SESSION['dept_notification'] === true) {
    $notification = '<div class="dept-notification">
        <i class="fas fa-user-shield"></i> You are logged in as <strong>DEPARTMENT HEAD</strong>
    </div>';
    $_SESSION['dept_notification'] = false;
}


?>

<!-- Department Dashboard Content -->
<div class="section">
    <div class="section-header">
        <div class="section-title">
            <i class="fas fa-user"></i>
            Welcome, <?php echo $fetch_user['name'] ?? 'User'; ?>
        </div>
        
    </div>

    <?php echo $notification; ?>

    <div class="section-content">
        <p>Welcome to your department</p>
        <p>Here you can view and track orders, and access your profile and statistics.</p>
    </div>
</div>

<!-- Notification Auto-hide Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deptNotification = document.querySelector('.dept-notification');
    if (deptNotification) {
        setTimeout(() => {
            deptNotification.style.opacity = '0';
            setTimeout(() => {
                deptNotification.style.display = 'none';
            }, 500);
        }, 5000);
    }
});
</script>

<!-- Notification Style -->
<style>
.dept-notification {
    background-color:rgb(58, 193, 85);
    color: white;
    padding: 12px 20px;
    margin: 15px 0;
    border-radius: 6px;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: opacity 0.5s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    font-size: 16px;
}
</style>
