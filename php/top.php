<?php
include 'config.php';

// Check if user is logged in 
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$select_user = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);
?>
<!-- Top Bar -->
<div class="topbar" id="topbar">
    <div class="left-side">
        <button class="menu-toggle" id="menu-toggle">
             <i class="fas fa-bars"></i>
        </button>
        <div class="page-title">Faculty of science </div>
    </div>
    <div class="topbar-actions">
       
        <a href="archives.php" class="action-icon">
            <i class="fas fa-shopping-cart"></i>
            <span class="badge"> </span>
        </a>
        <a href="index.php?logout=<?php echo $user_id; ?>" onclick="return confirm('Are you sure you want to logout?');"
            class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>