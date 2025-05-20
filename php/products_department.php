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
    
    $result = confirmOrder($conn, $user_id);
    
    if ($result) {
        $success_message = "Your order has been confirmed and is awaiting administrator approval. You can view it in your archives..";
        $show_toast = true; // Show toast notification immediately
        $order_just_confirmed = true;
    } else {
        $error_message = "Error confirming order. Please try again..";
    }
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
$speciality = $user['speciality'];
$products = mysqli_query($conn, "SELECT * FROM products WHERE speciality = '$speciality'");

// Refresh cart data after order confirmation
if ($order_just_confirmed) {
    // Force refresh the cart after order confirmation
    $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
} else {
    // Normal cart query
    $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
}

// Process add to cart form submission
$cart_message = '';
if (isset($_POST['product_id']) && isset($_POST['name']) && isset($_POST['image']) && isset($_POST['quantity'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $result = addToCart($conn, $user_id, $product_id, $name, $image, $quantity);
        if ($result) {
            $cart_message = "Product successfully added to cart !";
            // Refresh cart data after adding a product
            $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
        } else {
            $cart_message = "Error adding to cart: " . mysqli_error($conn);
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
        $cart_message = "Quantity updated successfully!";
        // Refresh cart data after updating quantity
        $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
    } else {
        $cart_message = "Error updating quantity: " . mysqli_error($conn);
    }
}

// Process clear cart form submission
if (isset($_POST['clear_cart'])) {
    $user_id = $_SESSION['user_id'];
    
    $result = clearCart($conn, $user_id);
    if ($result) {
        $cart_message = "Your cart has been successfully emptied!";
        // Refresh cart data after clearing
        $cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND (status = 'en attente' OR status IS NULL OR status = '')");
    } else {
        $cart_message = "Error deleting cart: " . mysqli_error($conn);
    }
}

// Check if we have a success message
$success_message = '';
if (isset($_SESSION['order_success'])) {
    $success_message = $_SESSION['order_success'];
    unset($_SESSION['order_success']);
}

// Check if we have an error message
$error_message = '';
if (isset($_SESSION['order_error'])) {
    $error_message = $_SESSION['order_error'];
    unset($_SESSION['order_error']);
}

// Check if we should show a toast notification
$show_toast = false;
if (isset($_SESSION['show_toast'])) {
    $show_toast = true;
    unset($_SESSION['show_toast']);
}
?>

<div class="section">
    <h2>Products for your specialty : <?php echo htmlspecialchars($speciality); ?></h2>
    <div class="products-list">
        <?php while ($product = mysqli_fetch_assoc($products)): ?>
        <div class="product-item">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Image produit">
            </div>
            <div class="product-details">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <form class="add-to-cart-form" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']); ?>">
                    <input type="hidden" name="image" value="<?= htmlspecialchars($product['image']); ?>">
                    <div class="quantity-control">
                        <label>Quantity :</label>
                        <input type="number" name="quantity" value="1" min="1" required>
                        <button type="submit" class="btn">Add to cart</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- PANIER -->
<div class="section">
    <h2>🛒 My Cart</h2>
    
    <?php if ($success_message): ?>
    <div class="success-message">
        <?= $success_message ?>
    </div>
    <?php endif; ?>
    
    <?php if ($cart_message): ?>
    <div class="cart-message" style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <?= $cart_message ?>
    </div>
    <?php endif; ?>
    
    <div class="cart-list" id="cart-content">
        <?php
        $has_items = false;
        mysqli_data_seek($cart, 0);  // Reset the result pointer
        while ($item = mysqli_fetch_assoc($cart)):
            $has_items = true;
            ?>
            <div class="cart-item" data-id="<?= $item['id']; ?>">
                <div class="cart-item-image">
                    <img src="<?= $item['image']; ?>" alt="Produit">
                </div>
                <div class="cart-info">
                    <h4><?= htmlspecialchars($item['name']); ?></h4>
                    <div class="quantity-controls">
                        <form class="update-quantity-form" action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
                            <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                            <input type="hidden" name="update_quantity" value="1">
                            <div class="quantity-input-group">
                                <label for="quantity-<?= $item['id']; ?>">Quantity:</label>
                                <input type="number" id="quantity-<?= $item['id']; ?>" name="new_quantity" value="<?= $item['quantity']; ?>" min="1" required>
                                <button type="submit" class="btn-update">To update</button>
                            </div>
                        </form>
                        <form class="delete-form" action="delete_from_cart.php" method="post">
                            <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                            <button type="submit" class="btn-delete">delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        
        <?php if (!$has_items): ?>
            <div class="empty-cart-message">
                <p>Your cart is empty</p>
            </div>
        <?php else: ?>
            <div class="cart-actions">
                <form action="<?= $_SERVER['PHP_SELF']; ?>" method="post" class="clear-cart-form">
                    <input type="hidden" name="clear_cart" value="1">
                    <button type="submit" class="btn-delete-all">Empty cart</button>
                    <button id="confirm-order" type="submit" name="confirm_order" value="yes" class="btn-confirm-order">Confirm order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- STYLES -->
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f9f9f9;
    margin: 0;
    padding: 0;
}

.section {
    padding: 40px;
    margin: auto;
    max-width: 1200px;
}

h2 {
    font-size: 26px;
    margin-bottom: 20px;
    color: #2c3e50;
    border-bottom: 2px solid #2980b9;
    padding-bottom: 10px;
}

/* Nouveau style pour les produits en liste */
.products-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.product-item {
    display: flex;
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
}

.product-image {
    width: 120px;
    flex-shrink: 0;
    margin-right: 20px;
}

.product-image img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.product-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.product-details h3 {
    margin: 0 0 10px;
    color: #2c3e50;
    font-size: 18px;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 10px;
}

.quantity-control input[type="number"] {
    width: 70px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Style pour le panier */
.cart-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.cart-item {
    display: flex;
    background: white;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    padding: 15px;
}

.cart-item-image {
    width: 80px;
    flex-shrink: 0;
    margin-right: 20px;
}

.cart-item-image img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
}

.cart-info {
    flex: 1;
}

.cart-info h4 {
    margin: 0 0 8px;
    color: #2c3e50;
    font-size: 16px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.quantity-input-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-input-group input[type="number"] {
    width: 60px;
    padding: 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.btn,
.btn-update,
.btn-delete {
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s ease;
}

.btn {
    background: #27ae60;
    color: white;
}

.btn:hover {
    background: #219150;
}

.btn-update {
    background: #2980b9;
    color: white;
}

.btn-update:hover {
    background: #1f6391;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}

.btn-delete:hover {
    background: #c0392b;
}

.cart-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.btn-delete-all {
    background: #c0392b;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.btn-confirm-order {
    background: #27ae60;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-left: 10px;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #c3e6cb;
}

.empty-cart-message {
    width: 100%;
    text-align: center;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 5px;
    color: #6c757d;
}

/* Style pour le toast */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    z-index: 1000;
    animation: slideIn 0.5s, fadeOut 0.5s 4.5s;
    max-width: 350px;
    opacity: 0;
    animation-fill-mode: forwards;
}

.toast-content {
    display: flex;
    align-items: center;
}

.toast-icon {
    font-size: 24px;
    margin-right: 12px;
}

.toast-message {
    font-size: 14px;
    font-weight: 500;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
</style>

<!-- Toast Notification -->
<?php if ($show_toast): ?>
<div id="toast-notification" class="toast-notification">
    <div class="toast-content">
        <div class="toast-icon">✅</div>
        <div class="toast-message">Commande confirmée avec succès! Vous pouvez la consulter dans vos archives.</div>
    </div>
</div>

<script>
// Auto-hide toast after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    var toast = document.getElementById('toast-notification');
    if (toast) {
        setTimeout(function() {
            toast.style.display = 'none';
        }, 5000);
    }
});
</script>
<?php endif; ?>