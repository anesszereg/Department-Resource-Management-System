<?php
session_start();
include 'config.php';
include 'topp.php';
include 'sidebarr.php';

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
    
}

// Function to add product to cart
function addToCart($conn, $user_id, $product_id, $name, $image, $quantity) {
    // Check if product already exists in cart
    $exists = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND name='$name' AND (status = 'en attente' OR status IS NULL OR status = '')");
    
    if (mysqli_num_rows($exists) > 0) {
        // Update existing cart item
        $update = mysqli_query($conn, "UPDATE cart 
                             SET quantity = quantity + $quantity 
                             WHERE user_id = $user_id 
                             AND name = '$name' 
                             AND (status = 'en attente' OR status IS NULL OR status = '')");
        
        return $update ? true : false;
    } else {
        // Get current date
        $current_date = date('Y-m-d');
        
        // Insert new cart item with date_commande
        $insert = mysqli_query($conn, "INSERT INTO cart (user_id, product_id, name, image, quantity, status, date_commande)
                            VALUES ($user_id, $product_id, '$name', '$image', $quantity, 'en attente', '$current_date')");
        
        return $insert ? true : false;
    }
}

// Function to update cart item quantity
function updateCartQuantity($conn, $cart_id, $quantity, $user_id) {
    if ($quantity <= 0) {
        // If quantity is 0 or negative, remove the item
        $update = mysqli_query($conn, "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id");
    } else {
        // Update the quantity
        $update = mysqli_query($conn, "UPDATE cart SET quantity = $quantity WHERE id = $cart_id AND user_id = $user_id");
    }
    
    return $update ? true : false;
}

// Function to clear the entire cart
function clearCart($conn, $user_id) {
    $delete = mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id AND (status = 'en attente' OR status IS NULL OR status = '')");
    return $delete ? true : false;
}

// Function to confirm order and clear cart
function confirmOrder($conn, $user_id) {
    $current_date = date('Y-m-d');
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update all cart items to "Not Approved" status and set the order date
        $update_result = mysqli_query($conn, "UPDATE cart 
                                            SET status = 'Not Approved', 
                                                date_commande = '$current_date' 
                                            WHERE user_id = $user_id 
                                            AND (status = 'en attente' OR status IS NULL OR status = '')");
        
        if (!$update_result) {
            throw new Exception("Erreur lors de la confirmation de la commande: " . mysqli_error($conn));
        }
        
        // Commit transaction
        mysqli_commit($conn);
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        return false;
    }
}

// Process order confirmation if requested
$order_just_confirmed = false;
if (isset($_POST['confirm_order']) && $_POST['confirm_order'] == 'yes') {
    $user_id = $_SESSION['user_id'];
    
    // Check if there are items in the cart
    $check_cart = mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
    $cart_count = mysqli_fetch_assoc($check_cart);
    
    if ($cart_count['count'] > 0) {
        $result = confirmOrder($conn, $user_id);
        
        if ($result) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => "Your order has been confirmed and is awaiting administrator approval. You can view it in your archives."
            ];
            $order_just_confirmed = true;
            
            // Ensure we refresh the page after confirmation to show empty cart
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => "Error confirming order. Please try again."
            ];
        }
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => "Cannot confirm order: Your cart is empty."
        ];
    }
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
$speciality = $user['speciality'];

// Default sort and filter values
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'name';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Build query with filter and sort
$query = "SELECT * FROM products WHERE speciality = '$speciality'";

// Add search filter if provided
if (!empty($search_term)) {
    $query .= " AND name LIKE '%$search_term%'";
}

// Add sorting
if ($sort_by == 'name') {
    $query .= " ORDER BY name $sort_order";
} elseif ($sort_by == 'id') {
    $query .= " ORDER BY id $sort_order";
} elseif ($sort_by == 'date') {
    $query .= " ORDER BY date_added $sort_order";
}

// Execute the query
$products = mysqli_query($conn, $query);

// Refresh cart data after order confirmation
if ($order_just_confirmed) {
    // Force refresh the cart after order confirmation
    $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
} else {
    // Normal cart query
    $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
}

// Process add to cart form submission
if (isset($_POST['product_id']) && isset($_POST['name']) && isset($_POST['image']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $result = addToCart($conn, $user_id, $product_id, $name, $image, $quantity);
        if ($result) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => "Product successfully added to cart!"
            ];
            $_SESSION['scroll_to_cart'] = true;
            
            // Refresh cart data after adding a product
            $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => "Error adding to cart: " . mysqli_error($conn)
            ];
        }
    }
}

// Process update quantity form submission
if (isset($_POST['update_quantity']) && isset($_POST['cart_id']) && isset($_POST['new_quantity'])) {
    $user_id = $_SESSION['user_id'];
    $cart_id = (int)$_POST['cart_id'];
    $new_quantity = (int)$_POST['new_quantity'];
    
    $result = updateCartQuantity($conn, $cart_id, $new_quantity, $user_id);
    if ($result) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Quantity updated successfully!"
        ];
        // Refresh cart data after updating quantity
        $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => "Error updating quantity: " . mysqli_error($conn)
        ];
    }
}

// Process clear cart form submission
if (isset($_POST['clear_cart'])) {
    $user_id = $_SESSION['user_id'];
    
    $result = clearCart($conn, $user_id);
    if ($result) {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => "Your cart has been successfully emptied!"
        ];
        // Refresh cart data after clearing
        $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
    } else {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => "Error deleting cart: " . mysqli_error($conn)
        ];
    }
}

// Check if we have a notification in session
$notification = null;
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}

// Check if we should scroll to cart
$scroll_to_cart = false;
if (isset($_SESSION['scroll_to_cart'])) {
    $scroll_to_cart = true;
    unset($_SESSION['scroll_to_cart']);
}
?>

<div class="section products-section">
    <h2 class="section-title">Products for your specialty: <?php echo htmlspecialchars($speciality); ?></h2>

    <!-- Filter and Sort Controls -->
    <div class="filter-controls">
        <form action="<?= $_SERVER['PHP_SELF']; ?>" method="get" class="filter-form">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search_term); ?>">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div class="sort-controls">
                
                <button type="submit" class="btn-apply-filter">
                    <i class="fas fa-filter"></i> Apply
                </button>
                
                <a href="<?= $_SERVER['PHP_SELF']; ?>" class="btn-reset-filter">
                    <i class="fas fa-sync-alt"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($products) == 0): ?>
    <div class="no-products-message animated fadeIn">
        <div class="no-products-icon">
            <i class="fas fa-box-open"></i>
        </div>
        <p>No products found matching your criteria</p>
        <p class="no-products-sub">Try different search terms or reset filters</p>
    </div>
    <?php else: ?>
    <div class="products-list">
        <?php while ($product = mysqli_fetch_assoc($products)): ?>
        <div class="product-item">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product image">
            </div>
            <div class="product-details">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <form class="add-to-cart-form" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']); ?>">
                    <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']); ?>">
                    <div class="quantity-control">
                        <label>Quantity:</label>
                        <input type="number" name="quantity" value="1" min="1" required>
                        <button type="submit" class="btn btn-add-to-cart">
                            <i class="fas fa-cart-plus"></i> Add to cart
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Toast Notification Container -->
<div id="toast-container"></div>

<!-- CART SECTION -->
<div class="section cart-section">
    <h2 class="section-title">🛒 My Cart</h2>

    <div class="cart-list" id="cart-content">
        <?php
        $has_items = false;
        mysqli_data_seek($cart, 0);  // Reset the result pointer
        while ($item = mysqli_fetch_assoc($cart)):
            $has_items = true;
            ?>
            <div class="cart-item animated fadeInUp" data-id="<?= $item['id']; ?>">
                <div class="cart-item-image">
                    <img src="<?= $item['image']; ?>" alt="Product">
                </div>
                <div class="cart-info">
                    <h4><?= htmlspecialchars($item['name']); ?></h4>
                    <p class="item-quantity">Quantity: <?= $item['quantity']; ?></p>
                </div>
                <!-- Les contrôles sont à droite -->
                <div class="cart-item-controls">
                    <form class="update-quantity-form" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                        <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                        <input type="hidden" name="update_quantity" value="1">
                        <div class="quantity-input-group">
                            <input type="number" id="quantity-<?= $item['id']; ?>" name="new_quantity" value="<?= $item['quantity']; ?>" min="1" required>
                        </div>
                        <button type="submit" class="btn-update">
                            <i class="fas fa-sync-alt"></i> Update
                        </button>
                    </form>
                    <form class="delete-form" action="delete_from_cart.php" method="post">
                        <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                        <button type="submit" class="btn-delete">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
        
        <?php if (!$has_items): ?>
            <div class="empty-cart-message animated fadeIn">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <p>Your cart is empty</p>
                <p class="empty-cart-sub">Add products to start your order</p>
            </div>
        <?php else: ?>
            <div class="cart-actions animated fadeInUp">
                <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="cart-action-form">
                    <input type="hidden" name="clear_cart" value="1">
                    <button type="submit" class="btn-delete-all">
                        <i class="fas fa-trash"></i> Empty cart
                    </button>
                </form>
                <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="cart-action-form">
                    <button id="confirm-order" type="submit" name="confirm_order" value="yes" class="btn-confirm-order">
                        <i class="fas fa-check-circle"></i> Confirm order
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toast-container"></div>



<!-- Link to Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- STYLES -->
<style>

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.section {
    padding: 40px;
    margin: 20px auto;
    max-width: 1200px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-size: 28px;
    margin-bottom: 30px;
    color: #1e3a8a;
    border-bottom: 2px solid #3b82f6;
    padding-bottom: 10px;
    position: relative;
    display: inline-block;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 60px;
    height: 4px;
    background: #3b82f6;
    border-radius: 2px;
}

/* Filter controls */
.filter-controls {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8fafc;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    border: 1px solid #e5e7eb;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
}

.search-box {
    flex: 1;
    min-width: 250px;
    position: relative;
}

.search-box input[type="text"] {
    width: 100%;
    padding: 12px 15px;
    padding-right: 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.search-box input[type="text"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-search {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #6b7280;
    font-size: 16px;
    cursor: pointer;
    transition: color 0.2s;
}

.btn-search:hover {
    color: #3b82f6;
}

.sort-controls {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.sort-controls label {
    font-weight: 500;
    color: #4b5563;
}

.sort-controls select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    font-size: 15px;
    min-width: 120px;
}

.sort-controls select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-apply-filter, .btn-reset-filter {
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-apply-filter {
    background: #3b82f6;
    color: white;
    border: none;
    cursor: pointer;
}

.btn-apply-filter:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-reset-filter {
    background: #f3f4f6;
    color: #4b5563;
    border: 1px solid #d1d5db;
}

.btn-reset-filter:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
}

/* No products message */
.no-products-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 0;
    color: #6b7280;
    text-align: center;
}

.no-products-icon {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 15px;
}

.no-products-message p {
    font-size: 20px;
    margin: 5px 0;
}

.no-products-sub {
    font-size: 16px !important;
    color: #9ca3af;
}

/* MODIFICATIONS: Products layout for smaller, better organized products */
.products-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); /* Plus petit que les 300px originaux */
    gap: 15px; /* Réduit l'espacement entre les éléments */
}

.product-item {
    display: flex;
    flex-direction: column;
    background: white;
    border-radius: 10px; /* Légèrement plus petit */
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    padding: 15px; /* Réduit le padding */
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
    border: 1px solid #eee;
    height: 300px; /* Hauteur fixe pour uniformité */
}

.product-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(59, 130, 246, 0.15);
    border-color: #dbeafe;
}

.product-image {
    width: 100%;
    height: 130px; /* Image plus petite */
    margin-bottom: 12px;
    overflow: hidden;
    border-radius: 6px;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-item:hover .product-image img {
    transform: scale(1.05);
}

.product-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-details h3 {
    margin: 0 0 10px;
    color: #1e3a8a;
    font-size: 16px; /* Police plus petite */
    font-weight: 600;
    /* Pour les titres longs */
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.3;
    height: 40px; /* Hauteur fixe pour le titre */
}

.quantity-control {
    display: flex;
    flex-direction: column; /* Changé en colonne pour un meilleur agencement */
    gap: 8px;
    margin-top: auto; /* Pousse les contrôles en bas de la carte */
}

.quantity-control label {
    font-weight: 500;
    color: #4b5563;
    font-size: 14px;
}

.quantity-control input[type="number"] {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-align: center;
    font-size: 14px;
}

.quantity-control input[type="number"]:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-add-to-cart {
    width: 100%;
    padding: 8px 0;
    background: linear-gradient(135deg, #4f46e5, #3b82f6);
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-add-to-cart:hover {
    background: linear-gradient(135deg, #4338ca, #2563eb);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Cart styles */
.cart-section {
    margin-top: 40px;
}

.cart-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-item {
    display: flex;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    padding: 20px;
    transition: all 0.3s ease;
    border: 1px solid #eee;
    position: relative;
    overflow: hidden;
    align-items: center; /* Centrer les éléments verticalement */
}

.cart-item:hover {
    transform: translateX(5px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.1);
    border-color: #dbeafe;
}

.cart-item-controls {
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 120px;
    gap: 5px;
    margin-left: auto; /* Pousse les contrôles à droite */
    border-left: 1px solid #eaeaea; /* Bordure à gauche maintenant */
    padding-left: 25px;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    flex-shrink: 0;
    margin-right: 25px;
    border-radius: 8px;
    overflow: hidden;
}

.cart-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.cart-item:hover .cart-item-image img {
    transform: scale(1.08);
}

.cart-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.cart-info h4 {
    margin: 0 0 10px;
    color: #1e3a8a;
    font-size: 18px;
    font-weight: 600;
}

.item-quantity {
    color: #6b7280;
    font-size: 16px;
    margin: 0;
}

.quantity-input-group {
    margin-bottom: 10px;
    width: 100%;
}

.quantity-input-group input[type="number"] {
    width: 100%;
    padding: 8px 12px;
    text-align: center;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 16px;
}

/* Button styles */
.btn,
.btn-update,
.btn-delete,
.btn-delete-all,
.btn-confirm-order {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    gap: 8px;
}

.btn {
    background: #3b82f6;
    color: white;
}

.btn:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-update {
    background: #3b82f6;
    color: white;
    padding: 8px 16px;
    font-size: 14px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-update:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-delete {
    background: #ef4444;
    color: white;
    padding: 8px 16px;
    border-radius: 6px;
    width: 100%;
    margin-top: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.cart-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn-delete-all {
    background: #ef4444;
    color: white;
}

.btn-delete-all:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-confirm-order {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    margin-left: 15px;
}

.btn-confirm-order:hover {
    background: linear-gradient(135deg, #059669, #047857);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Messages */
.success-message, .cart-message {
    padding: 15px;
    margin-bottom: 25px;
    border-radius: 8px;
    position: relative;
    border-left: 5px solid;
}

.success-message {
    background-color: #dcfce7;
    color: #166534;
    border-color: #10b981;
}

.cart-message {
    background-color: #dbeafe;
    color: #1e3a8a;
    border-color: #3b82f6;
}

.empty-cart-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 0;
    color: #6b7280;
    text-align: center;
}

.empty-cart-icon {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 15px;
}

.empty-cart-message p {
    font-size: 20px;
    margin: 5px 0;
}

.empty-cart-sub {
    font-size: 16px !important;
    color: #9ca3af;
}

/* Animations */
.animated {
    animation-duration: 0.6s;
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.fadeIn {
    animation-name: fadeIn;
}

.fadeInUp {
    animation-name: fadeInUp;
}

/* Toast notification */
.toast-notification {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    display: flex;
    align-items: center;
    min-width: 300px;
    max-width: 450px;
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.toast-icon {
    font-size: 24px;
    margin-right: 16px;
    background: rgba(255, 255, 255, 0.2);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.toast-message {
    font-size: 16px;
    font-weight: 500;
    flex: 1;
}

.toast-close {
    background: transparent;
    border: none;
    color: white;
    opacity: 0.7;
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
    margin-left: 10px;
    transition: opacity 0.2s;
}

.toast-close:hover {
    opacity: 1;
}

/* Responsive styles - OPTIMISÉS pour les produits plus petits */
@media (max-width: 1200px) {
    .section {
        padding: 30px;
    }
    
    .products-list {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
}

@media (max-width: 992px) {
    .products-list {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
    
    .product-item {
        height: 280px;
        padding: 12px;
    }
    
    .product-image {
        height: 120px;
    }
}

@media (max-width: 768px) {
    .cart-item {
        flex-wrap: wrap;
    }
    
    .cart-item-controls {
        width: 100%;
        flex-direction: row;
        border-left: none;
        border-top: 1px solid #eaeaea;
        padding-left: 0;
        padding-top: 15px;
        margin-left: 0;
        margin-top: 15px;
        justify-content: space-between;
        gap: 10px;
        order: 3; /* Pour s'assurer que les contrôles soient en bas en mode mobile */
    }
    
    .cart-item-image {
        order: 1;
        margin-right: 15px;
        width: 80px;
        height: 80px;
    }
    
    .cart-info {
        order: 2;
        width: calc(100% - 95px); /* Ajustement pour éviter le débordement */
    }
    
    .update-quantity-form, 
    .delete-form {
        width: 48%;
    }
    
    .cart-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .btn-confirm-order {
        margin-left: 0;
    }
    
    /* Optimisation de la grille des produits */
    .products-list {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
}

@media (max-width: 576px) {
    .section {
        padding: 20px;
        margin: 15px;
    }
    
    .section-title {
        font-size: 24px;
    }
    
    .products-list {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .product-item {
        height: 260px;
        padding: 10px;
    }
    
    .product-image {
        height: 100px;
        margin-bottom: 8px;
    }
    
    .product-details h3 {
        font-size: 14px;
        margin-bottom: 8px;
        height: 36px;
    }
    
    .quantity-control input[type="number"] {
        padding: 6px 8px;
    }
    
    .btn-add-to-cart {
        padding: 6px 0;
        font-size: 13px;
    }
    
    .toast-notification {
        bottom: 20px;
        right: 20px;
        left: 20px;
        min-width: unset;
    }
}

@media (max-width: 400px) {
    .products-list {
        grid-template-columns: repeat(2, 1fr);
        gap: 6px;
    }
    
    .product-item {
        height: 240px;
        padding: 8px;
    }
    
    .product-image {
        height: 90px;
    }
    
    .product-details h3 {
        font-size: 13px;
    }
}

.section-title {
    font-size: 28px;
    color: #0d47a1; /* Bleu foncé */
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
    margin-bottom: 30px;
    font-family: 'Segoe UI', sans-serif;
}

.section-title::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    height: 3px;
    width: 80px; /* Longueur courte au départ */
    background-color: #4285f4; /* Bleu vif */
    border-radius: 2px;
    transition: width 0.3s ease;
}

.section-title:hover::after {
    width: 100%; /* Effet animé si souhaité */
}



#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast-notification {
    display: flex;
    align-items: center;
    background-color: #fff;
    color: #333;
    border-radius: 8px;
    padding: 12px 20px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-width: 350px;
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease;
    overflow: hidden;
}

.toast-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.toast-notification.success {
    border-left: 4px solid #2ecc71;
}

.toast-notification.error {
    border-left: 4px solid #e74c3c;
}

.toast-icon {
    margin-right: 12px;
    font-size: 20px;
}

.toast-notification.success .toast-icon {
    color: #2ecc71;
}

.toast-notification.error .toast-icon {
    color: #e74c3c;
}

.toast-message {
    flex: 1;
    font-size: 14px;
}

.toast-close {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #888;
    padding: 0 0 0 12px;
}

.toast-close:hover {
    color: #333;
}

.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.1);
}

.toast-progress-bar {
    height: 100%;
    width: 100%;
    background-color: #3498db;
    transition: width linear 5s;
}

.toast-notification.success .toast-progress-bar {
    background-color: #2ecc71;
}

.toast-notification.error .toast-progress-bar {
    background-color: #e74c3c;
}

#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast-notification {
    display: flex;
    align-items: center;
    background-color: #fff;
    color: #333;
    border-radius: 8px;
    padding: 12px 20px;
    margin-bottom: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-width: 350px;
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease;
    overflow: hidden;
}

.toast-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.toast-notification.success {
    border-left: 4px solid #2ecc71;
}

.toast-notification.error {
    border-left: 4px solid #e74c3c;
}

.toast-icon {
    margin-right: 12px;
    font-size: 20px;
}

.toast-notification.success .toast-icon {
    color: #2ecc71;
}

.toast-notification.error .toast-icon {
    color: #e74c3c;
}

.toast-message {
    flex: 1;
    font-size: 14px;
}

.toast-close {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #888;
    padding: 0 0 0 12px;
}

.toast-close:hover {
    color: #333;
}

.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.1);
}

.toast-progress-bar {
    height: 100%;
    width: 100%;
    background-color: #3498db;
    transition: width linear 5s;
}

.toast-notification.success .toast-progress-bar {
    background-color: #2ecc71;
}

.toast-notification.error .toast-progress-bar {
    background-color: #e74c3c;
}

</style>

<!-- JavaScript for functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toast notification system
    function showToast(message, type = 'success', duration = 5000) {
        const toastContainer = document.getElementById('toast-container');
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        
        // Set appropriate icon based on type
        let icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas fa-${icon}"></i>
            </div>
            <div class="toast-message">${message}</div>
            <button class="toast-close">
                <i class="fas fa-times"></i>
            </button>
            <div class="toast-progress">
                <div class="toast-progress-bar"></div>
            </div>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Trigger layout and add show class for animation
        setTimeout(() => {
            toast.classList.add('show');
            
            // Start progress bar animation
            const progressBar = toast.querySelector('.toast-progress-bar');
            progressBar.style.width = '0%';
        }, 10);
        
        // Set timeout to remove toast
        const toastTimeout = setTimeout(() => {
            hideToast(toast);
        }, duration);
        
        // Close button functionality
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            clearTimeout(toastTimeout);
            hideToast(toast);
        });
    }
    
    function hideToast(toastElement) {
        toastElement.classList.remove('show');
        setTimeout(() => {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 300);
    }
    
    // Show notification from PHP if available
    <?php if ($notification): ?>
    showToast('<?= addslashes($notification['message']) ?>', '<?= $notification['type'] ?>');
    <?php endif; ?>
    
    // Add animation to product items on page load
    const productItems = document.querySelectorAll('.product-item');
    productItems.forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('animated', 'fadeInUp');
        }, index * 100);
    });
    
    // Animate cart items
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach((item, index) => {
        setTimeout(() => {
            item.classList.add('animated', 'fadeInUp');
        }, index * 150);
    });
    
    // Scroll to cart if needed
    <?php if ($scroll_to_cart): ?>
    setTimeout(() => {
        document.querySelector('.cart-section').scrollIntoView({ behavior: 'smooth' });
    }, 300);
    <?php endif; ?>
    
    // AJAX form submissions for cart interactions
    function setupCartForms() {
        // Update quantity forms
        document.querySelectorAll('.update-quantity-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormAjax(form, 'Quantity updated successfully!');
            });
        });
        
        // Delete item forms
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormAjax(form, 'Item removed from cart!');
            });
        });
        
        // Clear cart form
        const clearCartForm = document.querySelector('form input[name="clear_cart"]')?.closest('form');
        if (clearCartForm) {
            clearCartForm.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to empty your cart?')) {
                    e.preventDefault();
                    return;
                }
                e.preventDefault();
                submitFormAjax(clearCartForm, 'Your cart has been emptied!');
            });
        }
        
        // Confirm order button - regular form submission (no AJAX)
        const confirmOrderBtn = document.getElementById('confirm-order');
        if (confirmOrderBtn) {
            confirmOrderBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to confirm your order?')) {
                    // Allow the normal form submission to proceed
                    return true;
                } else {
                    event.preventDefault();
                    return false;
                }
            });
        }
    }
    
    function submitFormAjax(form, successMessage) {
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Extract cart content from response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCartContent = doc.querySelector('#cart-content');
            
            if (newCartContent) {
                document.querySelector('#cart-content').innerHTML = newCartContent.innerHTML;
                showToast(successMessage);
                setupCartForms(); // Re-bind events to new elements
            }
        })
        .catch(error => {
            showToast('An error occurred. Please try again.', 'error');
            console.error('Error:', error);
        });
    }
    
    // Initial setup
    setupCartForms();
});
</script>