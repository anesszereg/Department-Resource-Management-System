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

// Check if user is admin, if not redirect to user dashboard
if ($fetch_user['type'] != 'admin') {
    header('location: indexx.php');
    exit();
}

// Logout handling - doit être après la récupération du user pour éviter des erreurs
if (isset($_GET['logout'])) {
    session_destroy();
    header('location: login.php');
    exit();
}

include 'sidebar.php';

// Admin notification logic
$notification = "";
if (isset($_SESSION['admin_notification']) && $_SESSION['admin_notification'] === true) {
    $notification = '<div class="admin-notification">You are logged in as administrator</div>';
    $_SESSION['admin_notification'] = false;
}
?>

<div class="section">
    <div class="section-header">
        <div class="section-title">
            <i class="fas fa-user-circle"></i>
            Welcome, <?php echo $fetch_user['name'] ?? 'Admin'; ?>
        </div>
       
    </div>
    
    <?php echo $notification; ?>
    
    <div class="section-content">
        <p>   "Welcome to the Faculty of Science's Admin Panel. Oversee departments, manage users and products, and track all orders efficiently."</p>
        <p class="admin-notice">You have administrator privileges.</p>
    </div>
</div>

<script>
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('main-content');
const topbar = document.getElementById('topbar');

menuToggle?.addEventListener('click', function() {
    sidebar?.classList.toggle('active');
    mainContent?.classList.toggle('active');
    topbar?.classList.toggle('active');
});

// Auto-hide admin notification after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const adminNotification = document.querySelector('.admin-notification');
    if (adminNotification) {
        setTimeout(function() {
            adminNotification.style.opacity = '0';
            setTimeout(function() {
                adminNotification.style.display = 'none';
            }, 500);
        }, 5000);
    }
});
</script>

<style>
.admin-notification {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    margin: 10px 0;
    border-radius: 5px;
    font-weight: bold;
    transition: opacity 0.5s ease;
}
</style>
<a href="index.php" class="nav-item">
    <i class="fas fa-home"></i>
    <span>Dashboard</span>
</a>

<a href="admin_approval.php" class="nav-item">
    <i class="fas fa-users"></i>
    <span>Approve Users</span>
</a>

<a href="products.php" class="nav-item">
    <i class="fas fa-box"></i>
    <span>Products</span>
</a>

<a href="cart.php" class="nav-item">
    <i class="fas fa-shopping-cart"></i>
    <span>Shopping Cart</span>
</a>

<a href="profile.php" class="nav-item">
    <i class="fas fa-user"></i>
    <span>My Profile</span>
</a>

<a href="settings.php" class="nav-item">
    <i class="fas fa-cog"></i>
    <span>Settings</span>
</a>
