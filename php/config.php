<?php
/**
 * Database Configuration File for Boumerdes Project
 * 
 * This file handles the database connection and provides error handling
 */

// Database credentials
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'root';  // MAMP default password is 'root'
$db_name = 'shop_db';

// Create connection with error handling
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);  // MAMP default port is 8889

// Check connection and display detailed error for debugging
if (!$conn) {
    die('Database Connection Failed: ' . mysqli_connect_error());
}

// Set character set to ensure proper data handling
mysqli_set_charset($conn, "utf8mb4");

// Optional: Enable this for development environment only
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>`