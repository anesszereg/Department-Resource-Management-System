<?php
include 'config.php';
include 'topp.php'; 
 
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
echo $currentPage; // Affiche le nom de la page actuelle pour vérifier


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U M B B- Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>


<div class="sidebar" id="sidebar">
<div class="sidebar-header">
        <div class="logo">  faculty of  <br> <span> <span></span> <br>   science</span></div>
    </div>

    <div class="sidebar-nav">
        <a href="department_dashboard.php" class="nav-item <?php if ($currentPage == 'department_dashboard.php') echo 'active'; ?>">
            <i class="fas fa-home"></i>
            <span>Statistics</span>
        </a>
        <a href="product_department.php" class="nav-item <?php if ($currentPage == 'user_products.php') echo 'active'; ?>">
            <i class="fas fa-box"></i>
            <span>View products</span>
        </a>

        <a href="user_products.php" class="nav-item <?php if ($currentPage == 'user_products.php') echo 'active'; ?>">
            <i class="fas fa-box"></i>
            <span>My Department Products</span>
        </a>
        

        <a href="department_orders.php" class="nav-item <?php if ($currentPage == 'department_orders.php') echo 'active'; ?>">
    <i class="fas fa-clipboard-list"></i>
    <span>Department Orders</span>
</a>

        <a href="profile_chef.php" class="nav-item <?php if ($currentPage == 'profile_chef.php') echo 'active'; ?>">
            <i class="fas fa-user"></i>
            <span>My Profile</span>
        </a>

        <a href="setting.php" class="nav-item <?php if ($currentPage == 'setting.php') echo 'active'; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
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