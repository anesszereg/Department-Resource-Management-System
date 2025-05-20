<?php
include 'config.php';
include 'top.php'; 
 
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$select_user = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);
$currentPage = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boumerdes - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">  faculty of  <br> <span> <span></span> <br>   science</span></div>
    </div>

    <div class="sidebar-nav">
    <?php if ($fetch_user['type'] == 'admin') : ?>
    <a href="admin_dashboard.php" class="nav-item <?php if ($currentPage == 'admin_dashboard.php') echo 'active'; ?>">
        <i class="fas fa-chart-line"></i>
        <span>Statics</span>
    </a>
<?php endif; ?>

        
        <a href="admin_approval.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Manage account</span>
        </a>
     

        <a href="products.php" class="nav-item <?php if ($currentPage == 'products.php') echo 'active'; ?>">
    <i class="fas fa-box"></i>
    <span> Manage Products</span>
</a>
<a href="admin_orders.php" class="nav-item <?php if ($currentPage == 'admin_orders.php') echo 'active'; ?>">
    <i class="fas fa-plus"></i>
    <span>Manage Orders</span>
</a>

        <a href="archives.php" class="nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>archives</span>
        </a>
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>My Profile</span>
        </a>
        <a href="update.profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Setting</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo substr($fetch_user['name'] ?? 'User', 0, 1); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo $fetch_user['name'] ?? 'User'; ?></div>
                <div class="user-email"><?php echo $fetch_user['email'] ?? 'email@example.com'; ?></div>
            </div>
        </div>
        <a href="index.php?logout=<?php echo $user_id; ?>" onclick="return confirm('Are you sure you want to logout?');"
            class="logout-btn" style="width: 100%; justify-content: center;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
<div class="main-content" id="main-content">