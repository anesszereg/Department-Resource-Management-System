<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
    exit;
}

$cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
$new_quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($cart_id > 0 && $new_quantity > 0) {
    $query = "UPDATE cart SET quantity = $new_quantity WHERE id = $cart_id AND user_id = {$_SESSION['user_id']}";
    if (mysqli_query($conn, $query)) {
        echo "updated";
    } else {
        echo "error";
    }
} else {
    echo "invalid_input";
}
