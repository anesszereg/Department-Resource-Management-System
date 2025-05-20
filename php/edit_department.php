<?php
// Start output buffering to avoid header errors
ob_start();

// Initialize session and include necessary files
session_start();
include 'config.php';
include 'sidebar.php';

// Check authentication and admin rights
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

// Flash messages
$success_message = '';
$error_message = '';

// Get department ID
if (!isset($_GET['id'])) {
    header('location: products.php'); // Corrected redirect link
    exit();
}

$dept_id = mysqli_real_escape_string($conn, $_GET['id']);
$dept_query = mysqli_query($conn, "SELECT * FROM departement WHERE speciality = '$dept_id'");

if (mysqli_num_rows($dept_query) == 0) {
    header('location: products.php'); // Corrected redirect link
    exit();
}

$department = mysqli_fetch_assoc($dept_query);

// Process update form
if (isset($_POST['update_department'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    // Update department
    if (mysqli_query($conn, "UPDATE departement SET speciality = '$name' WHERE speciality = '$dept_id'")) {
        // Also update references in the products table
        mysqli_query($conn, "UPDATE products SET speciality = '$name' WHERE speciality = '$dept_id'");
        
        // Redirect directly without message
        header("Location: edit_department.php?id=" . urlencode($name));
        exit();
    } else {
        $error_message = "Error updating department: " . mysqli_error($conn);
    }
}

// Process deletion
if (isset($_POST['delete_department'])) {
    // Check if there are associated products
    $products_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE speciality = '$dept_id'"))['count'];
    
    if ($products_count > 0) {
        $error_message = "Unable to delete department because it still contains $products_count products.";
    } else {
        if (mysqli_query($conn, "DELETE FROM departement WHERE speciality = '$dept_id'")) {
            // Redirect to products.php instead of admin_products.php
            header("Location: products.php");
            exit();
        } else {
            $error_message = "Error deleting department: " . mysqli_error($conn);
        }
    }
}

// Counters for statistics
$products_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE speciality = '$dept_id'"))['count'];
$out_of_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE speciality = '$dept_id' AND quantity = 0"))['count'];
$low_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE speciality = '$dept_id' AND quantity > 0 AND quantity <= 5"))['count'];

// At this point, all logic is complete and we can start displaying HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        /* Department menu */
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
            border-left: 4px solid var(--success-color, #2ecc71);
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

        /* Forms */
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

        /* Form actions */
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        /* Button styles */
        .btn-primary,
        .btn-blue,
        .btn-danger,
        .btn-outline {
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

        .navigation-links {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .edit-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .stats-section {
            margin-top: 30px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stat-box {
                flex: 1 0 100%;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="section">
    <h2><i class="fas fa-building"></i> Edit Department</h2>
    
    <!-- Navigation links -->
    <div class="navigation-links">
        <a href="products.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back to Product Management</a>
        <a href="products.php?speciality=<?= urlencode($dept_id) ?>" class="btn-blue"><i class="fas fa-filter"></i> View Department Products</a>
    </div>
    
    <!-- Notification messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i> <?= $success_message ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert error">
            <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
            <span class="close-btn" onclick="this.parentElement.style.display='none';">&times;</span>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST" class="edit-form">
            <div class="form-group">
                <label for="name"><i class="fas fa-tag"></i> Department Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($department['speciality']) ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_department" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <button type="button" class="btn-danger" onclick="confirmDelete()"><i class="fas fa-trash-alt"></i> Delete Department</button>
            </div>
        </form>
        
        <!-- Hidden form for deletion -->
        <form id="deleteForm" method="POST" style="display: none;">
            <input type="hidden" name="delete_department" value="1">
        </form>
    </div>
    
    <!-- Statistics -->
    <div class="stats-section">
        <h3>Department Statistics</h3>
        
        <div class="stats-container">
            <div class="stat-box">
                <i class="fas fa-boxes"></i>
                <span class="stat-value"><?= $products_count ?></span>
                <span class="stat-label">Total Products</span>
            </div>
            <div class="stat-box warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="stat-value"><?= $low_stock ?></span>
                <span class="stat-label">Low Stock</span>
            </div>
            <div class="stat-box danger">
                <i class="fas fa-times-circle"></i>
                <span class="stat-value"><?= $out_of_stock ?></span>
                <span class="stat-label">Out of Stock</span>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm("Are you sure you want to delete this department? This action cannot be undone.")) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
</body>
</html>
<?php
// Release output buffer at the end of script
ob_end_flush();
?>