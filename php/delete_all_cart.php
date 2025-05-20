<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) exit;

mysqli_query($conn, "DELETE FROM cart WHERE user_id = {$_SESSION['user_id']}");
?>
