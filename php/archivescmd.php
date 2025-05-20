<?php
session_start();
include 'config.php';
include 'topp.php';
include 'sidebarr.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
$speciality = $user['speciality'];

// Fetch user's archived orders (excluding 'en attente' status)
$query = "SELECT * FROM cart 
          WHERE user_id = $user_id 
          AND status != 'en attente'
          ORDER BY date_commande DESC, id DESC";
$orders = mysqli_query($conn, $query);

// Get counts for different statuses
$count_validated = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id AND status = 'validée'"))['count'];
$count_rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id AND status = 'rejetée'"))['count'];
$count_not_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id AND status = 'Not Approved'"))['count'];
$count_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cart WHERE user_id = $user_id AND status = 'en attente'"))['count'];

// Check if filter is applied
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Prepare filter query based on selection
if ($status_filter != 'all') {
    $filter_query = "SELECT * FROM cart 
                    WHERE user_id = $user_id 
                    AND status = '$status_filter' 
                    ORDER BY date_commande DESC, id DESC";
    $orders = mysqli_query($conn, $filter_query);
}

// Date range filter
$date_start = isset($_GET['date_start']) ? $_GET['date_start'] : '';
$date_end = isset($_GET['date_end']) ? $_GET['date_end'] : '';

if (!empty($date_start) && !empty($date_end)) {
    $date_filter_query = "SELECT * FROM cart 
                          WHERE user_id = $user_id 
                          AND date_commande BETWEEN '$date_start' AND '$date_end'";
    
    if ($status_filter != 'all') {
        $date_filter_query .= " AND status = '$status_filter'";
    } else {
        $date_filter_query .= " AND status != 'en attente'";
    }
    
    $date_filter_query .= " ORDER BY date_commande DESC, id DESC";
    $orders = mysqli_query($conn, $date_filter_query);
}
?>

<div class="main-content">
    <h1>My Order Archives</h1>
    
    <!-- Status summary cards -->
   
    
    <!-- Filter options -->
    <div class="filter-section">
        <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="filter-form">
            <div class="filter-group">
                <label for="status">Filter by Status:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="validée" <?php echo $status_filter == 'validée' ? 'selected' : ''; ?>>Validated</option>
                    <option value="rejetée" <?php echo $status_filter == 'rejetée' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="Not Approved" <?php echo $status_filter == 'Not Approved' ? 'selected' : ''; ?>>Pending Approval</option>
                   
                </select>
            </div>
            
            <div class="filter-group date-filter">
                <label>Date Range:</label>
                <input type="date" name="date_start" value="<?php echo $date_start; ?>">
                <span>to</span>
                <input type="date" name="date_end" value="<?php echo $date_end; ?>">
                <button type="submit" class="btn-filter">Apply Filters</button>
               
            </div>
        </form>
    </div>
    
    <?php if (mysqli_num_rows($orders) > 0): ?>
        <!-- Order listing -->
        <div class="orders-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Product</th>
                        <th>Image</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                        <tr class="order-row <?php echo strtolower($order['status']); ?>">
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td>
                                <img src="<?php echo $order['image']; ?>" alt="Product" class="product-thumbnail">
                            </td>
                            <td><?php echo $order['quantity']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($order['date_commande'])); ?></td>
                            <td>
                                <div class="status-badge <?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                    <?php 
                                    switch($order['status']) {
                                        case 'validée':
                                            echo '<span>✓ Validated</span>';
                                            break;
                                        case 'rejetée':
                                            echo '<span>✗ Rejected</span>';
                                            break;
                                        case 'Not Approved':
                                            echo '<span>⏳ Pending Approval</span>';
                                            break;
                                        case 'en attente':
                                            echo '<span>⏳ In Process</span>';
                                            break;
                                        default:
                                            echo htmlspecialchars($order['status']);
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-orders">
            <p>No orders found matching your criteria.</p>
            <a href="user_products.php" class="btn btn-shop">Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Main Content Styles */
    .main-content {
        padding: 25px;
        margin-left: 250px; /* Adjust based on your sidebar width */
    }

    h1 {
        color: #2c3e50;
        margin-bottom: 30px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }

    /* Status Summary Cards */
    .status-summary {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .status-card {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-width: 150px;
        flex: 1;
        text-align: center;
        transition: transform 0.2s ease;
    }

    .status-card:hover {
        transform: translateY(-5px);
    }

    .status-card h3 {
        margin-top: 0;
        color: #7f8c8d;
        font-size: 16px;
        font-weight: 600;
    }

    .status-count {
        display: block;
        font-size: 32px;
        font-weight: 700;
        padding: 10px 0;
    }

    .status-count.validated {
        color: #27ae60;
    }

    .status-count.rejected {
        color: #e74c3c;
    }

    .status-count.pending {
        color: #f39c12;
    }

    .status-count.in-process {
        color: #3498db;
    }

    /* Filter Section */
    .filter-section {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-filter {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    select, input[type="date"] {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }

    .btn-filter, .btn-reset {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.2s;
    }

    .btn-filter:hover {
        background-color: #2980b9;
    }

    .btn-reset {
        background-color: #95a5a6;
        text-decoration: none;
        margin-left: 5px;
    }

    .btn-reset:hover {
        background-color: #7f8c8d;
    }

    /* Orders Table */
    .orders-container {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table th, .orders-table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }

    .orders-table th {
        background-color: #f9f9f9;
        font-weight: 600;
        color: #2c3e50;
    }

    .order-row:hover {
        background-color: #f5f7fa;
    }

    .product-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 5px;
    }

    /* Status Badges */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.validée, .status-badge.validated {
        background-color: #d4f8e8;
        color: #1e8449;
    }

    .status-badge.rejetée, .status-badge.rejected {
        background-color: #fae0e4;
        color: #c0392b;
    }

    .status-badge.not-approved, .status-badge.pending-approval {
        background-color: #fef2d0;
        color: #d35400;
    }

    .status-badge.en-attente, .status-badge.in-process {
        background-color: #e1f0fa;
        color: #2980b9;
    }

    /* No Orders */
    .no-orders {
        padding: 50px 20px;
        text-align: center;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .no-orders p {
        color: #7f8c8d;
        font-size: 18px;
        margin-bottom: 20px;
    }

    .btn-shop {
        background-color: #3498db;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 6px;
        display: inline-block;
        transition: background-color 0.2s;
    }

    .btn-shop:hover {
        background-color: #2980b9;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 15px;
        }
        
        .status-summary {
            flex-direction: column;
        }
        
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group, .date-filter {
            flex-wrap: wrap;
        }
    }
</style>