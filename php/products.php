<?php
session_start();
include 'config.php';
include 'sidebar.php';

// Check authentication and admin privileges
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
if ($user['type'] != 'admin') {
    header('location: indexx.php');
    exit();
}
// Check if we have a cart message from other operations (like delete)
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']); // Clear the message after displaying it
}
// Flash messages for notifications
$success_message = '';
$error_message = '';

// Add product
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $quantity = intval($_POST['quantity']);
    
    // Check if quantity is negative
    if ($quantity < 0) {
        $error_message = 'The quantity cannot be negative.';
    } else {
        $speciality = mysqli_real_escape_string($conn, $_POST['speciality']);
        
        // Handle image upload
        $image = '';
        $image_path = '';
        
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Check if file extension is allowed
            if(in_array(strtolower($filetype), $allowed)) {
                // Generate unique filename to avoid overwrites
                $new_filename = uniqid() . '.' . $filetype;
                $image_path = 'images/' . $new_filename;
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                    if ($speciality === 'all') {
                        $departments = mysqli_query($conn, "SELECT DISTINCT speciality FROM departement");
                        $count = 0;
                        while ($d = mysqli_fetch_assoc($departments)) {
                            $dep = mysqli_real_escape_string($conn, $d['speciality']);
                            $query = "INSERT INTO products (name, quantity, speciality, image) 
                                     VALUES ('$name', $quantity, '$dep', '$image_path')";
                            if(mysqli_query($conn, $query)) {
                                $count++;
                            }
                        }
                        $success_message = "Product added successfully to $count departments!";
                    } else {
                        $query = "INSERT INTO products (name, quantity, speciality, image) 
                                VALUES ('$name', $quantity, '$speciality', '$image_path')";
                        if(mysqli_query($conn, $query)) {
                            $success_message = "Product added successfully!";
                        } else {
                            $error_message = "Error adding product: " . mysqli_error($conn);
                        }
                    }
                } else {
                    $error_message = "Error uploading image.";
                }
            } else {
                $error_message = "Image format not allowed. Use JPG, JPEG, PNG, or GIF.";
            }
        } else {
            $error_message = "Please select a valid image.";
        }
    }
}

// Delete product
if (isset($_POST['delete_product'])) {
    $id = intval($_POST['product_id']);
    // Retrieve image path before deletion to remove it from the server
    $result = mysqli_query($conn, "SELECT image FROM products WHERE id = $id");
    if ($row = mysqli_fetch_assoc($result)) {
        $image_path = $row['image'];
    }
    
    if(mysqli_query($conn, "DELETE FROM products WHERE id = $id")) {
        // Delete image from server if it's not a default image
        if (!empty($image_path) && file_exists($image_path) && strpos($image_path, 'default') === false) {
            unlink($image_path);
        }
        $success_message = "Product deleted successfully!";
    } else {
        $error_message = "Error deleting product.";
    }
}

// Handle out-of-stock products
if (isset($_POST['remove_zero_quantity'])) {
    $result = mysqli_query($conn, "SELECT id, image FROM products WHERE quantity = 0");
    $count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $image_path = $row['image'];
        if (mysqli_query($conn, "DELETE FROM products WHERE id = {$row['id']}")) {
            $count++;
            // Delete image if not used by other products
            $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE image = '$image_path'");
            $check_result = mysqli_fetch_assoc($check);
            if ($check_result['count'] == 0 && file_exists($image_path) && strpos($image_path, 'default') === false) {
                unlink($image_path);
            }
        }
    }
    if ($count > 0) {
        $success_message = "$count out-of-stock products have been deleted.";
    } else {
        $success_message = "No out-of-stock products to delete.";
    }
}

// Filters: department, search, sort, stock
$where = "WHERE 1=1";
if (!empty($_GET['speciality'])) {
    $spec = mysqli_real_escape_string($conn, $_GET['speciality']);
    $where .= " AND speciality = '$spec'";
}
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND name LIKE '%$search%'";
}
if (isset($_GET['stock_filter']) && $_GET['stock_filter'] != '') {
    if ($_GET['stock_filter'] == 'low') {
        $where .= " AND quantity > 0 AND quantity <= 5";
    } elseif ($_GET['stock_filter'] == 'zero') {
        $where .= " AND quantity = 0";
    } elseif ($_GET['stock_filter'] == 'available') {
        $where .= " AND quantity > 5";
    }
}

$order_by = "ORDER BY name ASC"; // Default
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'asc') {
        $order_by = "ORDER BY quantity ASC";
    } elseif ($_GET['sort'] === 'desc') {
        $order_by = "ORDER BY quantity DESC";
    } elseif ($_GET['sort'] === 'name_asc') {
        $order_by = "ORDER BY name ASC";
    } elseif ($_GET['sort'] === 'name_desc') {
        $order_by = "ORDER BY name DESC";
    }
}

// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_from = ($page - 1) * $items_per_page;

// Queries for products and departments
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products $where");
$total_products = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_products / $items_per_page);

$products_query = "SELECT * FROM products $where $order_by LIMIT $start_from, $items_per_page";
$products = mysqli_query($conn, $products_query);

$departments = mysqli_query($conn, "SELECT DISTINCT speciality, 
                                  (SELECT COUNT(*) FROM products WHERE speciality = d.speciality) as product_count 
                                  FROM departement d 
                                  ORDER BY speciality ASC");

// Counters for dashboard
$total_products_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$out_of_stock_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE quantity = 0"))['count'];
$low_stock_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE quantity > 0 AND quantity <= 5"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="section">
    <h2><i class="fas fa-box"></i> Product Management</h2>
    
    <!-- Quick Statistics -->
    <div class="stats-container">
        <div class="stat-box">
            <i class="fas fa-boxes"></i>
            <span class="stat-value"><?= $total_products_count ?></span>
            <span class="stat-label">Total Products</span>
        </div>
        <div class="stat-box warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span class="stat-value"><?= $low_stock_count ?></span>
            <span class="stat-label">Low Stock</span>
        </div>
        <div class="stat-box danger">
            <i class="fas fa-times-circle"></i>
            <span class="stat-value"><?= $out_of_stock_count ?></span>
            <span class="stat-label">Out of Stock</span>
        </div>
    </div>

    <!-- Department Menu -->
    <div class="department-menu">
        <a href="products.php" class="dept-btn <?= !isset($_GET['speciality']) ? 'active' : '' ?>">
            <i class="fas fa-border-all"></i> All
        </a>
        <?php mysqli_data_seek($departments, 0); while ($d = mysqli_fetch_assoc($departments)): ?>
            <a href="products.php?speciality=<?= urlencode($d['speciality']) ?>" 
               class="dept-btn <?= (isset($_GET['speciality']) && $_GET['speciality'] == $d['speciality']) ? 'active' : '' ?>">
                <?= ucfirst($d['speciality']) ?> 
                <span class="badge"><?= $d['product_count'] ?></span>
            </a>
        <?php endwhile; ?>
    </div>

    <!-- Notification Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i> <?= $success_message ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">×</span>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">×</span>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="openTab('list')"><i class="fas fa-list"></i> Product List</button>
        <button class="tab-btn" onclick="openTab('add')"><i class="fas fa-plus-circle"></i> Add Product</button>
        <button class="tab-btn" onclick="openTab('departments')"><i class="fas fa-building"></i> Manage Departments</button>
    </div>

    <!-- Tab Content: Product List -->
    <div id="list" class="tab-content">
        <!-- Filters -->
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <select name="speciality">
                    <option value="">All Departments</option>
                    <?php 
                    mysqli_data_seek($departments, 0); 
                    while ($d = mysqli_fetch_assoc($departments)) {
                        $selected = (isset($_GET['speciality']) && $_GET['speciality'] == $d['speciality']) ? 'selected' : '';
                        echo "<option value=\"{$d['speciality']}\" $selected>" . ucfirst($d['speciality']) . "</option>";
                    } 
                    ?>
                </select>
            </div>
            
            <div class="filter-group">
                <input type="text" name="search" placeholder="🔍 Search..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            
            <div class="filter-group">
                <select name="stock_filter">
                    <option value="">Stock Status</option>
                    <option value="available" <?= (isset($_GET['stock_filter']) && $_GET['stock_filter'] == 'available') ? 'selected' : '' ?>>available</option>
                    <option value="low" <?= (isset($_GET['stock_filter']) && $_GET['stock_filter'] == 'low') ? 'selected' : '' ?>>Low Stock</option>
                    <option value="zero" <?= (isset($_GET['stock_filter']) && $_GET['stock_filter'] == 'zero') ? 'selected' : '' ?>>Out of Stock</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select name="sort">
                    <option value="">Sort By</option>
                    <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : '' ?>>Name (Z-A)</option>
                    <option value="asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'asc') ? 'selected' : '' ?>>Quantity (↑)</option>
                    <option value="desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'desc') ? 'selected' : '' ?>>Quantity (↓)</option>
                </select>
            </div>
            
            <!-- Preserve other GET parameters -->
            <?php if (isset($_GET['page'])): ?>
                <input type="hidden" name="page" value="<?= htmlspecialchars($_GET['page']) ?>">
            <?php endif; ?>
            
            <button type="submit" class="btn-blue"><i class="fas fa-filter"></i> Filter</button>
        </form>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <form method="POST" onsubmit="return confirm('Delete all out-of-stock products?')">
                <button type="submit" name="remove_zero_quantity" class="btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete Out-of-Stock Products
                </button>
            </form>
        </div>

        <!-- Product List -->
        <div class="table-responsive">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($products) > 0):
                        while ($p = mysqli_fetch_assoc($products)) : 
                    ?>
                        <tr class="product-row" data-id="<?= $p['id']; ?>">
                            <td>
                                <a href="<?= htmlspecialchars($p['image']); ?>" target="_blank" class="image-preview">
                                    <img src="<?= htmlspecialchars($p['image']); ?>" alt="<?= htmlspecialchars($p['name']); ?>" height="60">
                                </a>
                            </td>
                            <td><?= htmlspecialchars($p['name']); ?></td>
                            <td><?= htmlspecialchars($p['speciality']); ?></td>
                            <td class="quantity-cell <?= $p['quantity'] <= 0 ? 'out-of-stock' : ($p['quantity'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                                <?= $p['quantity']; ?>
                                <?php if ($p['quantity'] <= 0): ?>
                                    <span class="stock-badge critical">Out of Stock</span>
                                <?php elseif ($p['quantity'] <= 5): ?>
                                    <span class="stock-badge warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="stock-badge success">available</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <a href="edit_product.php?id=<?= $p['id']; ?>" class="btn-action edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    <input type="hidden" name="product_id" value="<?= $p['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn-action delete" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                              
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <tr>
                            <td colspan="5" class="no-results">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?><?= isset($_GET['speciality']) ? '&speciality='.urlencode($_GET['speciality']) : '' ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : '' ?><?= isset($_GET['stock_filter']) ? '&stock_filter='.urlencode($_GET['stock_filter']) : '' ?>" class="page-btn">« Previous</a>
                <?php endif; ?>
                
                <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if($i == $page): ?>
                        <span class="page-btn active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= isset($_GET['speciality']) ? '&speciality='.urlencode($_GET['speciality']) : '' ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : '' ?><?= isset($_GET['stock_filter']) ? '&stock_filter='.urlencode($_GET['stock_filter']) : '' ?>" class="page-btn"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?><?= isset($_GET['speciality']) ? '&speciality='.urlencode($_GET['speciality']) : '' ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= isset($_GET['sort']) ? '&sort='.urlencode($_GET['sort']) : '' ?><?= isset($_GET['stock_filter']) ? '&stock_filter='.urlencode($_GET['stock_filter']) : '' ?>" class="page-btn">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab Content: Add Product -->
    <div id="add" class="tab-content" style="display:none;">
        <h3><i class="fas fa-plus-circle"></i> Add New Product</h3>
        
        <form method="POST" enctype="multipart/form-data" class="add-form">
            <div class="form-group">
                <label for="name"><i class="fas fa-tag"></i> Product Name</label>
                <input type="text" id="name" name="name" placeholder="Product Name" required>
            </div>
            
            <div class="form-group">
                <label for="quantity"><i class="fas fa-sort-amount-up"></i> Quantity</label>
                <input type="number" id="quantity" name="quantity" placeholder="Quantity" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="speciality"><i class="fas fa-building"></i> Department</label>
                <select id="speciality" name="speciality" required>
                    <option value="all">📌 All Departments</option>
                    <?php 
                    mysqli_data_seek($departments, 0); 
                    while ($d = mysqli_fetch_assoc($departments)): 
                    ?>
                        <option value="<?= htmlspecialchars($d['speciality']); ?>"><?= ucfirst(htmlspecialchars($d['speciality'])); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image"><i class="fas fa-image"></i> Product Image</label>
                <div class="image-upload-container">
                    <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(this)">
                    <div class="image-preview">
                        <img id="imagePreview" src="#" alt="Image Preview" style="display: none;">
                        <div class="placeholder" id="imagePlaceholder">
                            <i class="fas fa-upload"></i>
                            <p>Click to select an image</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="add_product" class="btn-primary"><i class="fas fa-plus"></i> Add Product</button>
            </div>
        </form>
    </div>

    <!-- Tab Content: Manage Departments -->
    <div id="departments" class="tab-content" style="display:none;">
        <h3><i class="fas fa-building"></i> Department Management</h3>
        
        <div class="departments-grid">
            <?php 
            mysqli_data_seek($departments, 0); 
            while ($d = mysqli_fetch_assoc($departments)): 
            ?>
                <div class="department-card">
                    <div class="department-icon">
                        <?php
                        // Icons for different departments
                        $icon = 'fas fa-building';
                        $dept = strtolower($d['speciality']);
                        
                        if (strpos($dept, 'info') !== false) {
                            $icon = 'fas fa-laptop-code';
                        } elseif (strpos($dept, 'math') !== false) {
                            $icon = 'fas fa-square-root-alt';
                        } elseif (strpos($dept, 'bio') !== false || strpos($dept, 'med') !== false) {
                            $icon = 'fas fa-dna';
                        } elseif (strpos($dept, 'chim') !== false) {
                            $icon = 'fas fa-flask';
                        } elseif (strpos($dept, 'phys') !== false) {
                            $icon = 'fas fa-atom';
                        } elseif (strpos($dept, 'lang') !== false) {
                            $icon = 'fas fa-language';
                        }
                        ?>
                        <i class="<?= $icon ?>"></i>
                    </div>
                    <div class="department-info">
                        <h4><?= ucfirst(htmlspecialchars($d['speciality'])); ?></h4>
                        <p><?= $d['product_count']; ?> products</p>
                    </div>
                    <div class="department-actions">
                        <a href="products.php?speciality=<?= urlencode($d['speciality']) ?>" class="btn-action view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="edit_department.php?id=<?= urlencode($d['speciality']) ?>" class="btn-action edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
            
            <!-- Button to add new department -->
            <div class="department-card add-new">
                <a href="add_department.php" class="add-department-btn">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Department</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS Styles -->
<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --danger-color: #e74c3c;
    --light-color: #ecf0f1;
    --dark-color: #34495e;
    --border-radius: 8px;
    --shadow: 0 2px 10px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

.section {
    max-width: 1200px;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
}

h2, h3, h4 { 
    text-align: center; 
    color: var(--primary-color);
    margin-bottom: 20px;
}

h2 { font-size: 28px; }
h3 { font-size: 22px; }

/* Statistics */
.stats-container {
    display: flex;
    justify-content: space-around;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.stat-box {
    flex: 1;
    min-width: 200px;
    background: #f8f9fa;
    padding: 20px 15px;
    border-radius: var(--border-radius);
    text-align: center;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border-top: 4px solid var(--secondary-color);
}

.stat-box:hover {
    transform: translateY(-5px);
}

.stat-box.warning {
    border-top-color: var(--warning-color);
}

.stat-box.danger {
    border-top-color: var(--danger-color);
}

.stat-box i {
    font-size: 28px;
    color: var(--secondary-color);
    margin-bottom: 10px;
}

.stat-box.warning i {
    color: var(--warning-color);
}

.stat-box.danger i {
    color: var(--danger-color);
}

.stat-value {
    display: block;
    font-size: 26px;
    font-weight: bold;
    margin: 10px 0;
}

.stat-label {
    display: block;
    color: #666;
    font-size: 14px;
}

/* Department Menu */
.department-menu {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 25px;
    justify-content: center;
}

.dept-btn {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    color: var(--dark-color);
    padding: 8px 15px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 14px;
    border: 1px solid #ddd;
    transition: var(--transition);
}

.dept-btn:hover {
    background: #f1f1f1;
    transform: translateY(-2px);
}

.dept-btn.active {
    background: var(--secondary-color);
    color: white;
    border-color: var(--secondary-color);
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.1);
    color: inherit;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 12px;
    margin-left: 5px;
}

.dept-btn.active .badge {
    background: rgba(255,255,255,0.2);
}

/* Alerts */
.alert {
    padding: 15px;
    border-radius: var(--border-radius);
    margin-bottom: 20px;
    position: relative;
    animation: fadeIn 0.5s ease;
}

.alert.success {
    background-color: rgba(46, 204, 113, 0.1);
    border-left: 4px solid var(--success-color);
    color: #27ae60;
}

.alert.error {
    background-color: rgba(231, 76, 60, 0.1);
    border-left: 4px solid var(--danger-color);
    color: #c0392b;
}

.close-btn {
    position: absolute;
    right: 15px;
    top: 15px;
    cursor: pointer;
    font-size: 18px;
}

/* Tabs */
.tabs {
    display: flex;
    justify-content: center;
    margin-bottom: 25px;
    border-bottom: 1px solid #e0e0e0;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 20px;
    cursor: pointer;
    background: transparent;
    border: none;
    font-size: 15px;
    color: #555;
    position: relative;
    transition: var(--transition);
    margin: 0 5px;
}

.tab-btn:hover {
    color: var(--secondary-color);
}

.tab-btn.active {
    color: var(--secondary-color);
    font-weight: bold;
}

.tab-btn.active:after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--secondary-color);
    border-radius: 3px 3px 0 0;
}

.tab-btn i {
    margin-right: 8px;
}

.tab-content {
    display: block;
    animation: fadeIn 0.5s ease;
}

/* Forms */
.filter-form {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
}

.filter-group {
    flex: 1;
    min-width: 180px;
}

.filter-form input,
.filter-form select,
.filter-form button {
    width: 100%;
    padding: 10px;
    border-radius: var(--border-radius);
    border: 1px solid #ddd;
    font-size: 14px;
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: var(--secondary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

/* Bulk Actions */
.bulk-actions {
    display: flex;
    justify-content: end;
    margin-bottom: 20px;
}

/* Table */
.table-responsive {
    overflow-x: auto;
    box-shadow: var(--shadow);
    border-radius: var(--border-radius);
    margin-bottom: 30px;
}

.styled-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.styled-table th {
    background: #f5f5f5;
    color: var(--dark-color);
    font-weight: bold;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}

.styled-table tr:last-child td {
    border-bottom: none;
}

.styled-table tr:hover {
    background-color: #f9f9f9;
}

.styled-table img {
    height: 60px;
    width: 60px;
    object-fit: cover;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.styled-table img:hover {
    transform: scale(1.1);
}

.image-preview {
    display: inline-block;
    position: relative;
}

.quantity-cell {
    position: relative;
    padding-right: 80px !important;
}

.stock-badge {
    position: absolute;
    right: 15px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.stock-badge.success {
   
    color: #27ae60;
}

.stock-badge.warning {

    color: #d35400;
}

.stock-badge.critical {
 
    color: #c0392b;
}

.out-of-stock {
    color: var(--danger-color);
    font-weight: bold;
}

.low-stock {
    color: var(--warning-color);
    font-weight: bold;
}

.in-stock {
    color: var(--success-color);
}

.no-results {
    text-align: center;
    padding: 30px !important;
    color: #777;
    font-style: italic;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 5px;
}

.page-btn {
    display: inline-block;
    padding: 8px 15px;
    margin: 0 2px;
    border-radius: var(--border-radius);
    background: #f8f9fa;
    color: #555;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid #ddd;
}

.page-btn:hover {
    background: #eee;
}

.page-btn.active {
    background: var(--secondary-color);
    color: white;
    border-color: var(--secondary-color);
}

/* Add Product Form */
.add-form {
    max-width: 700px;
    margin: 0 auto;
    background: #f8f9fa;
    padding: 25px;
    border-radius: var(--border-radius);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: var(--dark-color);
}

.form-group label i {
    margin-right: 6px;
    color: var(--secondary-color);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-size: 14px;
    transition: var(--transition);
}

.form-group input:focus, 
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--secondary-color);
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.image-upload-container {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: var(--border-radius);
    height: 200px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
}

.image-upload-container:hover {
    border-color: var(--secondary-color);
}

.image-upload-container input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

.image-preview {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#imagePreview {
    max-width: 100%;
    max-height: 180px;
    object-fit: contain;
}

.placeholder {
    text-align: center;
    color: #777;
}

.placeholder i {
    font-size: 40px;
    margin-bottom: 10px;
    color: #ccc;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 30px;
}

/* Button Styles */
.btn-primary,
.btn-blue,
.btn-danger {
    padding: 10px 18px;
    border-radius: var(--border-radius);
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: #243342;
    transform: translateY(-2px);
}

.btn-blue {
    background: var(--secondary-color);
    color: white;
}

.btn-blue:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-danger {
    background: var(--danger-color);
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    border: 1px solid #ddd;
    color: #555;
}

.btn-outline:hover {
    background: #f5f5f5;
    transform: translateY(-2px);
}

.btn-primary i, .btn-blue i, .btn-danger i, .btn-outline i {
    margin-right: 8px;
}

/* Table Actions */
.actions-cell {
    white-space: nowrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    text-decoration: none;
    transition: var(--transition);
    margin: 0 2px;
    border: none;
    cursor: pointer;
    color: white;
}

.btn-action.edit {
    background-color: #3498db;
}

.btn-action.delete {
    background-color: #e74c3c;
}

.btn-action.stock {
    background-color: #f39c12;
}

.btn-action.view {
    background-color: #2ecc71;
}

.btn-action:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

.inline-form {
    display: inline-block;
}

/* Department Management */
.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.department-card {
    background: white;
    border-radius: var(--border-radius);
    border: 1px solid #e0e0e0;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: var(--transition);
    position: relative;
}

.department-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.department-icon {
    width: 60px;
    height: 60px;
    background: rgba(52, 152, 219, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.department-icon i {
    font-size: 24px;
    color: var(--secondary-color);
}

.department-info {
    text-align: center;
    margin-bottom: 15px;
    flex-grow: 1;
}

.department-info h4 {
    margin: 0 0 5px 0;
    color: var(--dark-color);
}

.department-info p {
    color: #777;
    margin: 0;
}

.department-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    width: 100%;
}

.department-actions .btn-action {
    width: auto;
    height: auto;
    border-radius: var(--border-radius);
    padding: 6px 12px;
}

.department-card.add-new {
    border: 2px dashed #ddd;
    background: #f9f9f9;
    transition: var(--transition);
}

.department-card.add-new:hover {
    border-color: var(--secondary-color);
}

.add-department-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
    color: #777;
    text-decoration: none;
    transition: var(--transition);
}

.add-department-btn:hover {
    color: var(--secondary-color);
}

.add-department-btn i {
    font-size: 30px;
    margin-bottom: 10px;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes zoomInFade {
    from {
        transform: scale(0.95);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-zoom-fade {
    animation: zoomInFade 0.5s ease-out forwards;
    opacity: 0;
}

@keyframes slideInLeft {
    0% {
        transform: translateX(-20px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

.animate-slide-left {
    animation: slideInLeft 0.6s ease forwards;
    opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-group {
        flex: 1 0 100%;
    }
    
    .stat-box {
        flex: 1 0 100%;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .form-actions button {
        width: 100%;
    }
    
    .btn-action {
        width: 38px;
        height: 38px;
    }
}

</style>

<!-- JavaScript Scripts -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Animation for product rows
    document.querySelectorAll(".product-row").forEach((el, i) => {
        setTimeout(() => {
            el.classList.add("animate-slide-left");
        }, i * 80); // Slight delay between rows
    });
    
    // Image preview on upload
    document.getElementById('image').addEventListener('change', function(e) {
        previewImage(this);
    });
    
    // Animation for departments
    document.querySelectorAll(".department-card").forEach((el, i) => {
        setTimeout(() => {
            el.classList.add("animate-zoom-fade");
        }, i * 100);
    });
    
    // Auto-close notification messages
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// Function to switch tabs
function openTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.style.display = 'none';
    });
    
    // Deactivate all tab buttons
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content and activate button
    document.getElementById(tabName).style.display = 'block';
    document.querySelector(`.tab-btn[onclick="openTab('${tabName}')"]`).classList.add('active');
}

// Function to preview image before upload
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }
}
function openTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(div => div.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    document.querySelector(`.tab-btn[onclick="openTab('${tabId}')"]`).classList.add('active');
}

// Show the first tab by default
document.addEventListener("DOMContentLoaded", () => openTab('list'));
</script>
</body>
</html>