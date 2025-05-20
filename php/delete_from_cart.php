<?php
session_start();
include 'config.php';

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Validate and sanitize input
if (isset($_POST['cart_id'])) {
    $cart_id = (int)$_POST['cart_id'];
    $user_id = (int)$_SESSION['user_id'];
    
    // Delete item and verify it belongs to current user
    $result = mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    
    // Set a success message if deletion was successful
    if ($result) {
        $_SESSION['cart_message'] = "Item successfully removed from cart!";
    } else {
        $_SESSION['cart_message'] = "Error removing item: " . mysqli_error($conn);
    }
}

// Redirect back to the product page
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>