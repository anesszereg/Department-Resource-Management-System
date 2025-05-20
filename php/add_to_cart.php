<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get user ID and product details
$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : '';
$image = isset($_POST['image']) ? mysqli_real_escape_string($conn, $_POST['image']) : '';
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate inputs
if (empty($name) || empty($image) || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product data']);
    exit;
}

try {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    // Check product availability in general stock
    $stock_query = mysqli_query($conn, "SELECT quantity FROM products WHERE id = $product_id");
    $stock = mysqli_fetch_assoc($stock_query);
    
    if (!$stock || $stock['quantity'] < $quantity) {
        throw new Exception("Insufficient stock available");
    }
    
    // Check if product already exists in cart
    $exists = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND name='$name' AND (status = 'en attente' OR status IS NULL OR status = '')");
    
    if (mysqli_num_rows($exists) > 0) {
        // Update existing cart item
        $update_result = mysqli_query($conn, "UPDATE cart 
                                           SET quantity = quantity + $quantity 
                                           WHERE user_id = $user_id 
                                           AND name = '$name' 
                                           AND (status = 'en attente' OR status IS NULL OR status = '')");
        
        if (!$update_result) {
            throw new Exception("Error updating cart: " . mysqli_error($conn));
        }
    } else {
        // Insert new cart item
        $insert_result = mysqli_query($conn, "INSERT INTO cart (user_id, product_id, name, image, quantity, status)
                                          VALUES ($user_id, $product_id, '$name', '$image', $quantity, 'en attente')");
        
        if (!$insert_result) {
            throw new Exception("Error adding to cart: " . mysqli_error($conn));
        }
    }
    
    // Deduct quantity from general stock
    $update_stock = mysqli_query($conn, "UPDATE products 
                                      SET quantity = quantity - $quantity 
                                      WHERE id = $product_id");
                                      
    if (!$update_stock) {
        throw new Exception("Error updating stock: " . mysqli_error($conn));
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    // Return success response
    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
