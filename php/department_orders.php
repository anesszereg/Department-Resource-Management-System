<?php
session_start();
include 'config.php';
include 'topp.php';
include 'sidebarr.php';

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les filtres
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$day = isset($_GET['day']) ? (int)$_GET['day'] : 0;
$search_name = isset($_GET['search_name']) ? mysqli_real_escape_string($conn, $_GET['search_name']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'all';

// Construction de la clause WHERE
$date_filter = '';
if ($month) $date_filter .= " AND MONTH(c.date_commande) = $month";
if ($year)  $date_filter .= " AND YEAR(c.date_commande) = $year";
if ($day)   $date_filter .= " AND DAY(c.date_commande) = $day";
if ($search_name != '') $date_filter .= " AND p.name LIKE '%$search_name%'";

// Status filter
$status_condition = " AND (c.status != 'en attente' OR c.status = 'Not Approved') AND c.status IS NOT NULL AND c.status != '' ";
if ($status_filter != 'all') {
    $status_condition = " AND c.status = '$status_filter' ";
}

// Commandes archivées - Inclure status_updated_at, date_validation et quantity_delivered
$orders = mysqli_query($conn, "SELECT c.*, p.name AS product_name, p.image, 
                               c.created_at AS datetime_commande,
                               c.status_updated_at AS date_status_update,
                               c.date_validation,
                               c.quantity_delivered
                               FROM cart c 
                               LEFT JOIN products p ON c.product_id = p.id 
                               WHERE c.user_id = $user_id 
                               $status_condition
                               $date_filter
                               ORDER BY c.created_at DESC");

// Panier
$cart_items = mysqli_query($conn, "SELECT c.*, p.name AS product_name, p.image, c.created_at AS datetime_commande 
                                   FROM cart c 
                                   LEFT JOIN products p ON c.product_id = p.id 
                                   WHERE c.user_id = $user_id 
                                   AND c.status = 'en attente' 
                                   $date_filter");

// Compter les commandes par statut
$count_validated = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart 
                                                       WHERE user_id = $user_id 
                                                       AND status = 'validée'"));

$count_rejected = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart 
                                                      WHERE user_id = $user_id 
                                                      AND status = 'rejetée'"));

$count_pending = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart 
                                                     WHERE user_id = $user_id 
                                                     AND status = 'pending'"));

$count_Not_Approved = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart 
                                                        WHERE user_id = $user_id 
                                                        AND status = 'Not Approved'"));
 $count_livrée = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM cart 
 WHERE user_id = $user_id 
 AND status = 'livrée'"));
                                                        
                                                        
?>

<div class="section">
    <h2>📁 Statistics Orders</h2>

    <!-- Status summary cards -->
    <div class="status-summary">
        <div class="status-card">
            <h3>Approved</h3>
            <span class="status-count validated"><?php echo $count_validated; ?></span>
        </div>
        <div class="status-card">
            <h3>Rejected</h3>
            <span class="status-count rejected"><?php echo $count_rejected; ?></span>
        </div>
        <div class="status-card">
            <h3>Pending</h3>
            <span class="status-count pending"><?php echo $count_pending; ?></span>
        </div>
        <div class="status-card">
            <h3>pending Approved</h3>
            <span class="status-count Not Approved"><?php echo $count_Not_Approved; ?></span>
        </div>
        <div class="status-card">
            <h3>Delivered</h3>
            <span class="status-count livrée"><?php echo $count_livrée; ?></span>
        </div>
    </div>

    <form method="get" style="margin-bottom: 20px;">
        <input type="text" name="search_name" placeholder="Nom du produit" value="<?= htmlspecialchars($search_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
        
        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All</option>
            <option value="validée" <?= $status_filter == 'validée' ? 'selected' : '' ?>>Validated</option>
            <option value="rejetée" <?= $status_filter == 'rejetée' ? 'selected' : '' ?>>Rejected</option>
            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>pending</option>
            <option value="Not Approved" <?= $status_filter == 'Not Approved' ? 'selected' : '' ?>>pending Approved</option>
            <option value="livrée" <?= $status_filter == 'livrée' ? 'selected' : '' ?>>Delivered</option>
        </select>

        <label for="day">Day:</label>
        <select name="day">
            <option value="0">All</option>
            <?php for ($d = 1; $d <= 31; $d++): ?>
                <option value="<?= $d ?>" <?= $d == $day ? 'selected' : '' ?>><?= $d ?></option>
            <?php endfor; ?>
        </select>

        <label for="month">Month:</label>
        <select name="month">
            <option value="0">All</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="year">Year:</label>
        <select name="year">
            <option value="0">All</option>
            <?php for ($y = 2023; $y <= date('Y'); $y++): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit">Filter</button>
    </form>

    <!-- Commandes archivées -->
    <?php if (!$orders || mysqli_num_rows($orders) === 0): ?>
        <div class="empty-orders">
            <p>No Orders Found.</p>
        </div>
    <?php else: ?>
        <h3>🧾 My orders</h3>
        <table class="orders-table">
            <thead>
                <tr>

                    <th>Product</th>
                    <th>Requested Quantity</th>
                    <th>Delivered Quantity</th>
                    <th>Date and Time</th>
                    <th>Status</th>
                    <th>Status Update Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                      
                        <td>
                            <div class="product-info">
                                <?php if(!empty($order['image'])): ?>
                                    <img src="<?= htmlspecialchars($order['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Produit">
                                <?php endif; ?>
                                <span><?= htmlspecialchars($order['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </td>
                        <td><?= $order['quantity'] ?></td>
                        <td>
                            <span class="delivered-quantity <?= ($order['status'] == 'validée' && $order['allotted_quantity'] > 0) ? 'allotted_quantity' : '' ?>">
                                <?= ($order['allotted_quantity'] > 0) ? $order['allotted_quantity'] : '-' ?>
                            </span>
                        </td>
                        <td><?= !empty($order['datetime_commande']) ? date('d/m/Y H:i:s', strtotime($order['datetime_commande'])) : '-' ?></td>
                        <td>
                        <span class="status-badge status-<?= str_replace(' ', '-', strtolower($order['status'])) ?>">
    <?php 
    switch($order['status']) {
        case 'Not Approved':
            echo 'Pending approval';
            break;
        case 'pending':
            echo 'Pending';
            break;
        case 'validée':
            echo 'Validated';
            break;
        case 'rejetée':
            echo 'Rejected';
            break;
        case 'livrée':
            echo 'Delivered';
            break;
           
        default:
            echo htmlspecialchars($order['status']);
    }
    ?>
</span>
                        </td>
                        <td>
                            <?php 
                            // Afficher la date de validation ou de mise à jour du statut
                            if (($order['status'] ?? '') == 'validée' || ($order['status'] ?? '') == 'rejetée' || ($order['status'] ?? '') == 'livrée') {
                                if (!empty($order['date_validation'])) {
                                    echo date('d/m/Y H:i:s', strtotime($order['date_validation']));
                                } elseif (!empty($order['date_status_update'])) {
                                    echo date('d/m/Y H:i:s', strtotime($order['date_status_update'])); 
                                } else {
                                    echo "-";
                                }
                            } else {
                                echo "-";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- PANIER -->
<!-- PANIER -->


<style>

/* Styles de base améliorés */

/* Modern Beautiful Design with Simple Animations */

/* CSS Moderne avec Thème Bleu - Inspiré par la page produits */

/* Base Styles & Typography */
/* Styles de base améliorés */

/* Modern Beautiful Design with Simple Animations */

/* Base Styles & Typography */


/* Base Styles */
/* Base Styles */
body {
  font-family: 'Segoe UI', 'Roboto', sans-serif;
  background-color: #f5f7fa;
  color: #333;
  line-height: 1.6;
  margin: 0;
  padding: 0;
}

.section {
  padding: 25px;
  margin: 25px auto;
  max-width: 1200px;
}

/* Typography */
h2 {
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 25px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e2e8f0;
}

h3 {
  font-weight: 500;
  color: #2d3748;
  margin-top: 20px;
  margin-bottom: 15px;
}

/* Status Cards with Left Border */
.status-summary {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

.status-card {
  flex: 1;
  min-width: 200px;
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* Left border colors */
.status-card:nth-child(1) {
  border-left: 5px solid #28a745;
}

.status-card:nth-child(2) {
  border-left: 5px solid  #dc3545;
}

.status-card:nth-child(3) {
  border-left: 5px solid #ffc107;
}

.status-card:nth-child(4) {
  border-left: 5px solid #6c757d;
}

.status-card:nth-child(5) {
  border-left: 5px solid  #00a0bc;
}

.status-card h3 {
  font-size: 16px;
  font-weight: 600;
  color: #4a5568;
  margin-top: 0;
  margin-bottom: 5px;
}

.status-count {
  font-size: 32px;
  font-weight: 700;
  display: block;
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
}

/* Status count colors */
.status-count.validated,
.status-card:nth-child(4) .status-count {
  color: #6c757d;
}

.status-count.rejected,
.status-card:nth-child(5) .status-count {
  color: #17a2b8;
}

.status-count.pending,
.status-card:nth-child(3) .status-count {
  color: #ffc107;
}

.status-count.in-process,
.status-card:nth-child(2) .status-count {
  color: #dc3545;
}

.status-card:nth-child(1) .status-count {
  color: #28a745;
}

/* Filter Form */
form {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 25px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  align-items: flex-end;
}

form input[type="text"], 
form select {
  padding: 10px 15px;
  font-size: 14px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  min-width: 120px;
  background-color: #fff;
}

form input[type="text"]:focus, 
form select:focus {
  border-color: #3182ce;
  outline: none;
  box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.2);
}

form button {
  background: #3182ce;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 100px;
}

form button:hover {
  background: #2c5282;
}

/* Orders Table */
.orders-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background-color: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.orders-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #4a5568;
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.orders-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.orders-table tr:last-child td {
  border-bottom: none;
}

/* Product Info */
.product-info {
  display: flex;
  align-items: center;
}

.product-info img {
  width: 50px;
  height: 50px;
  object-fit: contain;
  border-radius: 4px;
  margin-right: 15px;
  border: 1px solid #e2e8f0;
  padding: 3px;
}

.product-info span {
  font-weight: 500;
  color: #2d3748;
}

/* Status Badges - Updated Design */
.status-badge {
  display: inline-block;
  padding: 8px 15px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
  min-width: 120px;
  color: white;
  text-transform: capitalize;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Validated (Green) */
.status-validée {
  background-color: #28a745;
  color: white;
}

/* Rejected (Red) */
.status-rejetée,
.status-Not-Approved {
  background-color: #dc3545;
  color: white;
}

/* Pending (Teal/Blue) */
.status-pending,
.status-en-attente {
  background-color: #17a2b8;
  color: white;
}

/* Add this for "delivered" status if needed */
.status-livrée {
  background-color: #17a2b8; /* Same color as pending */
  color: white;
}

/* Approved - explicitly adding this status */
.status-approved {
  background-color: #28a745;
  color: white;
}

/* Make sure the table cell containing the status badge has proper alignment */
.orders-table td:nth-child(5) {
  text-align: center;
}

/* Empty Orders */
.empty-orders {
  text-align: center;
  padding: 40px 20px;
  background-color: white;
  border-radius: 8px;
  color: #718096;
  font-size: 16px;
  border: 1px dashed #cbd5e0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

/* Delivered Quantity Styles */
.delivered-quantity {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 4px;
  font-weight: 500;
}

.quantity-delivered {
  background-color: #ebf8f0;
  color: #28a745;
  font-weight: bold;
}

/* Responsive Design */
@media (max-width: 992px) {
  .status-summary {
    flex-wrap: wrap;
  }
  
  .status-card {
    min-width: calc(50% - 20px);
    flex: 0 0 calc(50% - 20px);
  }
}

@media (max-width: 768px) {
  .section {
    padding: 15px;
    margin: 15px auto;
  }
  
  .status-card {
    min-width: 100%;
    flex: 0 0 100%;
  }
  
  form {
    flex-direction: column;
    align-items: stretch;
  }
  
  form input, form select, form button {
    width: 100%;
  }
  
  .orders-table {
    display: block;
    overflow-x: auto;
  }
}

/* Custom color for the horizontal line at the top */
.top-border {
  height: 3px;
  background: linear-gradient(to right, #0066ff, #00a0bc, #ffc107, #28a745, #dc3545);
  margin-bottom: 30px;
}

/* Card title and count layout */
.status-card-content {
  position: relative;
}

.status-card-title {
  max-width: 60%;
}

/* Animation for cards */
.status-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.status-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
.status-validée {
  background-color: #28a745;
  color: white;
}

/* Rejected (Red) */
.status-rejetée{
  background-color:rgb(197, 42, 52);
  color: white;
}
.status-Not-Approved {
  background-color:rgb(120, 116, 122);
  color: white;
}

/* Pending (Teal/Blue) */
.status-pending,
.status-en-attente {
  background-color:rgb(216, 171, 24);
  color: white;
}

/* Add this for "delivered" status if needed */
.status-livrée {
  background-color: #17a2b8; /* Same color as pending */
  color: white;
}

/* Approved - explicitly adding this status */
.status-approved {
  background-color: #28a745;
  color: white;
}
.status-pending-approved {
  background-color:rgb(88, 89, 82);
  color: white;
}