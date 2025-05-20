
<?php
session_start(); // 🔐 important pour la sécurité et éviter la redirection
$currentPage = 'admin_orders.php'; // pour marquer le bouton actif
include 'config.php';
include 'sidebar.php';

// Récupérer les départements
$departmentsQuery = "SELECT DISTINCT speciality FROM user_info";
$departmentsResult = mysqli_query($conn, $departmentsQuery);

// Récupérer les filtres
$filter_dept = $_GET['departement'] ?? '';
$filter_date = $_GET['date'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Construction dynamique de la requête SQL pour les commandes
$query = "
SELECT cart.*, user_info.name AS user_name, user_info.speciality AS user_speciality, products.name AS product_name
FROM cart
JOIN user_info ON cart.user_id = user_info.id
JOIN products ON cart.product_id = products.id
WHERE 1
";

if (!empty($filter_dept)) {
    $query .= " AND user_info.speciality = '" . mysqli_real_escape_string($conn, $filter_dept) . "'";
}
if (!empty($filter_date)) {
    // Correction ici: Utiliser une comparaison de date formatée correctement
    $query .= " AND DATE(status_updated_at) = '" . mysqli_real_escape_string($conn, $filter_date) . "'";
}
if (!empty($filter_status)) {
    $query .= " AND cart.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

$query .= " ORDER BY cart.created_at DESC";

// Pour le débogage - afficher la requête
// echo "<pre>" . htmlspecialchars($query) . "</pre>";

$result = mysqli_query($conn, $query);

// Vérifier s'il y a des erreurs dans la requête
if (!$result) {
    echo "Erreur SQL: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Commandes</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<?php if (isset($_SESSION['notif'])) { ?>
    <div id="notification" class="notification">
        <?= htmlspecialchars($_SESSION['notif']) ?>
    </div>
    <?php unset($_SESSION['notif']); ?>
<?php } ?>

    <h2>🧑‍💼 Orders from Department Heads</h2>
    <div id="notification" style="
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 1000;
    transition: opacity 8s;
"></div>

    <!-- Liste des départements -->
    <h3>Select a Department:</h3>
    <ul class="departments-list">
       
        <?php 
        // Reset the pointer to the beginning of the result set
        mysqli_data_seek($departmentsResult, 0);
        while ($dept = mysqli_fetch_assoc($departmentsResult)) { ?>
            <li>
                <a href="?departement=<?= urlencode($dept['speciality']) ?>" 
                   <?= $filter_dept == $dept['speciality'] ? 'class="active"' : '' ?>>
                    <?= htmlspecialchars($dept['speciality']) ?>
                </a>
            </li>
        <?php } ?>
    </ul>

    <!-- Filtres -->
    <form method="get" class="filters-form">
        <?php if (!empty($filter_dept)) { ?>
            <input type="hidden" name="departement" value="<?= htmlspecialchars($filter_dept) ?>">
        <?php } ?>
        
        <div class="filter-group">
            <label for="date-filter">Date:</label>
            <input type="date" id="date-filter" name="date" value="<?= htmlspecialchars($filter_date) ?>">
        </div>

        <div class="filter-group">
            <label for="status-filter">Status:</label>
            <select id="status-filter" name="status">
                <option value="">All</option>
                <option value="Not Approved" <?= $filter_status == 'Not Approved' ? 'selected' : '' ?>>Pending approval</option>
                <option value="pending" <?= $filter_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="validée" <?= $filter_status == 'validée' ? 'selected' : '' ?>>Approved</option>
                <option value="rejetée" <?= $filter_status == 'rejetée' ? 'selected' : '' ?>>Rejected</option>
                <option value="livrée" <?= $filter_status == 'livrée' ? 'selected' : '' ?>>Delivered</option>
            </select>
        </div>
        
        <button type="submit" class="filter-button">Apply Filters</button>
        <a href="admin_orders.php" class="reset-button">Reset</a>
    </form>

    <!-- Tableau des commandes -->
    <h3>Orders for <?= htmlspecialchars($filter_dept) ?: 'All Departments' ?>:</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Department</th>
                    <th>Product</th>
                    <th>Q.Requested</th>
                    <th>Q.Allocated</th>
               
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { 
                        // Si allotted_quantity n'est pas défini ou est 0, utiliser la quantité demandée
                        $allottedQuantity = (!empty($row['allotted_quantity'])) ? $row['allotted_quantity'] : $row['quantity'];
                ?>
                <tr class="order-row">
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['user_speciality']) ?></td>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>
                        <?php if ($row['status'] == 'validée' || $row['status'] == 'livrée') { ?>
                            <!-- Afficher la quantité allouée si approuvée -->
                            <?= $allottedQuantity ?>
                            <?php if ($allottedQuantity != $row['quantity']) { ?>
                                <span class="partial-tag">PARTIAL</span>
                            <?php } ?>
                        <?php } else { ?>
                            <!-- Afficher "N/A" pour les commandes non validées -->
                            <span class="not-allocated">N/A</span>
                        <?php } ?>
                    </td>
        
                    <td>
                        <div class="status <?= $row['status'] ?>">
                            <?php 
                            switch($row['status']) {
                                case 'Not Approved': echo 'Pending approval'; break;
                                case 'pending': echo 'Pending'; break;
                                case 'validée': echo 'Approved'; break;
                                case 'rejetée': echo 'Rejected'; break;
                                case 'livrée': echo 'Delivered'; break;
                                default: echo $row['status'];
                            }
                            ?>
                        </div>
                    </td>
                    <td><?= !empty($row['status_updated_at']) ? date("Y-m-d H:i", strtotime($row['status_updated_at'])) : 'N/A' ?></td>
                    <td class="action-buttons">
                        <div class="action-container">
                            <?php if ($row['status'] == 'pending') { ?>
                                <!-- Pour les commandes en attente, montrer seulement Approve et Reject -->
                                <button type="button" class="btn-action btn-approve" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-quantity="<?= $row['quantity'] ?>"
                                        onclick="showApproveModal(<?= $row['id'] ?>, <?= $row['quantity'] ?>)">
                                    ✅ Approve
                                </button>
                                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=rejetée" class="btn-action btn-reject">❌ Reject</a>
                            <?php } elseif ($row['status'] == 'validée') { ?>
                                <!-- Pour les commandes approuvées, montrer Modifier Quantité et Delivered -->
                                <button type="button" class="btn-action btn-edit" 
                                        onclick="showEditQuantityModal(<?= $row['id'] ?>, <?= $row['quantity'] ?>, <?= $allottedQuantity ?>)">
                                    🔄 Edit Qty
                                </button>
                                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=livrée" class="btn-action btn-deliver">📦 Delivered</a>
                            <?php } elseif ($row['status'] == 'Not Approved') { ?>
                                <!-- Pour les commandes en attente d'approbation -->
                                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=pending" class="btn-action btn-pending">⏳ Pending</a>
                                <button type="button" class="btn-action btn-approve" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-quantity="<?= $row['quantity'] ?>"
                                        onclick="showApproveModal(<?= $row['id'] ?>, <?= $row['quantity'] ?>)">
                                    ✅ Approve
                                </button>
                                <a href="update_order_status.php?id=<?= $row['id'] ?>&status=rejetée" class="btn-action btn-reject">❌ Reject</a>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                ?>
                <tr>
                    <td colspan="9" class="no-orders">No orders found matching your criteria</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour approbation avec quantité allouée -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Approve Order</h3>
            <form id="approveForm" action="update_order_status.php" method="get">
                <input type="hidden" id="orderIdApprove" name="id" value="">
                <input type="hidden" name="status" value="validée">
                
                <div class="form-group">
                    <label for="requestedQuantity">Requested Quantity:</label>
                    <input type="number" id="requestedQuantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="allottedQuantity">Allocate Quantity:</label>
                    <input type="number" id="allottedQuantity" name="allotted_quantity" min="1" required>
                    <p class="help-text">Enter the quantity you can provide (must be at least 1)</p>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="submit-btn">Approve Order</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal pour modifier la quantité allouée -->
    <div id="editQuantityModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Allocated Quantity</h3>
            <form id="editQuantityForm" action="update_order_quantity.php" method="get">
                <input type="hidden" id="orderIdEdit" name="id" value="">
                
                <div class="form-group">
                    <label for="currentRequestedQuantity">Requested Quantity:</label>
                    <input type="number" id="currentRequestedQuantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="currentAllottedQuantity">Current Allocated Quantity:</label>
                    <input type="number" id="currentAllottedQuantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="newAllottedQuantity">New Allocated Quantity:</label>
                    <input type="number" id="newAllottedQuantity" name="allotted_quantity" min="1" required>
                    <p class="help-text">Enter the new quantity you can provide (must be at least 1)</p>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="submit-btn">Update Quantity</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal pour approbation avec quantité allouée -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Approve Order</h3>
            <form id="approveForm" action="update_order_status.php" method="get">
                <input type="hidden" id="orderIdApprove" name="id" value="">
                <input type="hidden" name="status" value="validée">
                
                <div class="form-group">
                    <label for="requestedQuantity">Requested Quantity:</label>
                    <input type="number" id="requestedQuantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="allottedQuantity">Allocate Quantity:</label>
                    <input type="number" id="allottedQuantity" name="allotted_quantity" min="1" required>
                    <p class="help-text">Enter the quantity you can provide (must be at least 1)</p>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="submit-btn">Approve Order</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal pour modifier la quantité allouée -->
    <div id="editQuantityModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Allocated Quantity</h3>
            <form id="editQuantityForm" action="update_order_quantity.php" method="get">
                <input type="hidden" id="orderIdEdit" name="id" value="">
                
                <div class="form-group">
                    <label for="currentRequestedQuantity">Requested Quantity:</label>
                    <input type="number" id="currentRequestedQuantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="currentAllottedQuantity">Current Allocated Quantity:</label>
                    <input type="number" id="currentAllottedQuantity" readonly>
                </div>
                
                <div class="form-group">
                    <label for="newAllottedQuantity">New Allocated Quantity:</label>
                    <input type="number" id="newAllottedQuantity" name="allotted_quantity" min="1" required>
                    <p class="help-text">Enter the new quantity you can provide (must be at least 1)</p>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="submit-btn">Update Quantity</button>
                    <button type="button" class="cancel-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* General Styling */
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #444;
        }

        h3 {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #555;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }

        /* Department List Styling */
        .departments-list {
            list-style-type: none;
            padding: 0;
            margin: 20px 0;
            font-family: 'Arial', sans-serif;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .departments-list li {
            margin-bottom: 10px;
            transition: transform 0.2s ease-in-out;
            flex-grow: 1;
            min-width: 200px;
        }

        .departments-list li:hover {
            transform: translateY(-3px);
        }

        .departments-list a {
            display: block;
            padding: 12px 15px;
            background-color: #f8f9fa;
            color: #2c3e50;
            text-decoration: none;
            border-left: 4px solid #3498db;
            border-radius: 3px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .departments-list a:hover {
            background-color: #e9ecef;
            border-left-color: #2980b9;
            color: #1a252f;
        }

        .departments-list a.active {
            background-color: #e3f2fd;
            border-left-color: #1565c0;
            font-weight: bold;
        }

        /* Filters Form Styling */
        .filters-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
            flex-grow: 1;
        }

        .filter-group label {
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
        }

        .filter-button {
            padding: 10px 20px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .filter-button:hover {
            background-color: #3a7bc8;
        }

        .reset-button {
            padding: 10px 20px;
            background-color: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            text-align: center;
        }

        .reset-button:hover {
            background-color: #e5e5e5;
        }

        /* Table Styling */
        .table-container {
            overflow-x: auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px 16px;
            text-align: center; /* Pour centrer le contenu horizontalement */
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f4f6f8;
            font-weight: 600;
            color: #555;
        }

        tbody tr {
            animation: fadeIn 0.5s ease;
            transition: background-color 0.3s;
        }

        tbody tr:hover {
            background-color: #f9fcff;
        }

        /* Tag pour les commandes partielles */
        .partial-tag {
            display: inline-block;
            font-size: 10px;
            background-color: #ff7700;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            margin-left: 5px;
            vertical-align: middle;
        }

        .not-allocated {
            color: #999;
            font-style: italic;
        }

        /* Status Styling */
        .status {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: default;
            min-width: 120px;
            max-width: 150px;
            margin: 0 auto; /* Pour centrer le status dans sa cellule */
            text-transform: capitalize;
            transition: background-color 0.2s ease;
        }
        
        /* Statuts alignés visuellement avec les boutons d'action */
        .status.pending {
            background-color: #6c757d;
        }
        
        .status.validée {
            background-color: #28a745;
        }
        
        .status.rejetée {
            background-color: #dc3545;
        }
        
        .status.livrée {
            background-color: #17a2b8;
        }
        
        .status.Not {
            background-color: #adb5bd;
        }
        
        /* Action Buttons Styling */
        .action-container {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center; /* Pour centrer les boutons */
        }
        
        .btn-action {
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center; /* Pour centrer le texte dans le bouton */
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            min-width: 110px; /* Garantit une largeur minimum pour l'alignement */
        }
        
        /* Bouton Pending */
        .btn-pending {
            background-color: #6c757d;
        }
        
        .btn-pending:hover {
            background-color: #5a6268;
        }
        
        /* Bouton Approve */
        .btn-approve {
            background-color: #28a745;
        }
        
        .btn-approve:hover {
            background-color: #218838;
        }
        
        /* Bouton Reject */
        .btn-reject {
            background-color: #dc3545;
        }
        
        .btn-reject:hover {
            background-color: #c82333;
        }
        
        /* Bouton Delivered */
        .btn-deliver {
            background-color: #17a2b8;
        }
        
        .btn-deliver:hover {
            background-color: #138496;
        }

        /* Bouton Edit Quantity */
        .btn-edit {
            background-color: #6f42c1;
        }
        
        .btn-edit:hover {
            background-color: #5e35b1;
        }
        
        /* Affichage conditionnel des boutons d'action */
        .status.rejetée ~ .action-buttons .action-container,
        .status.rejetée ~ .action-container,
        .status.livrée ~ .action-buttons .action-container,
        .status.livrée ~ .action-container {
            visibility: hidden;
            position: relative;
        }
        
        .status.rejetée ~ .action-buttons .action-container::after,
        .status.rejetée ~ .action-container::after,
        .status.livrée ~ .action-buttons .action-container::after,
        .status.livrée ~ .action-container::after {
            content: "-";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
            font-weight: bold;
            color: #555;
            visibility: visible;
        }

        /* No Orders Message */
        .no-orders {
            text-align: center;
            padding: 30px !important;
            color: #666;
            font-style: italic;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fff;
            margin: 50px auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 500px;
            position: relative;
            animation: slideDown 0.4s;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: #555;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.1);
        }

        .form-group input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .help-text {
            margin-top: 6px;
            font-size: 13px;
            color: #666;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
        }

        .submit-btn {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            width: 48%;
        }

        .submit-btn:hover {
            background-color: #218838;
        }

        .cancel-btn {
            padding: 12px 20px;
            background-color: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            width: 48%;
        }

        .cancel-btn:hover {
            background-color: #e5e5e5;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Animation des boutons */
        .btn-action:active {
            transform: scale(0.95);
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .departments-list li {
                min-width: 150px;
            }

            .filter-group {
                min-width: 150px;
            }
        }

        @media (max-width: 768px) {
            .departments-list {
                flex-direction: column;
            }

            .departments-list a {
                padding: 10px 12px;
            }

            .filters-form {
                flex-direction: column;
                gap: 12px;
            }

            .filter-group {
                width: 100%;
            }

            .action-container {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                margin: 20px auto;
                padding: 15px;
                width: 95%;
            }

            .button-group {
                flex-direction: column;
                gap: 10px;
            }

            .submit-btn, .cancel-btn {
                width: 100%;
            }
        }
    </style>

<script>
         document.addEventListener('DOMContentLoaded', function() {
            // Définir les messages de statut dès le début
            const statusMessages = {
                'rejetée': 'Order successfully rejected',
                'pending': 'Order successfully placed on hold',
                'livrée': 'Order successfully marked as delivered',
                'validée': 'Order successfully approved',
                'edit_quantity': 'Quantity successfully modified'
            };

            // Gestion des modals
            const approveModal = document.getElementById('approveModal');
            const editQuantityModal = document.getElementById('editQuantityModal');
            const closeBtns = document.getElementsByClassName('close');
            const cancelBtns = document.getElementsByClassName('cancel-btn');
            
            // Fermer les modals avec les boutons de fermeture
            for (let i = 0; i < closeBtns.length; i++) {
                closeBtns[i].addEventListener('click', function() {
                    approveModal.style.display = "none";
                    editQuantityModal.style.display = "none";
                });
            }
            
            // Fermer les modals avec les boutons d'annulation
            for (let i = 0; i < cancelBtns.length; i++) {
                cancelBtns[i].addEventListener('click', function() {
                    approveModal.style.display = "none";
                    editQuantityModal.style.display = "none";
                });
            }
            
            // Fermer les modals en cliquant en dehors
            window.addEventListener('click', function(event) {
                if (event.target == approveModal) {
                    approveModal.style.display = "none";
                }
                if (event.target == editQuantityModal) {
                    editQuantityModal.style.display = "none";
                }
            });
            
            // Formulaires des modals avec AJAX
            document.getElementById('approveForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormWithAjax(this, function() {
                    approveModal.style.display = 'none';
                    showNotification(statusMessages['validée']);
                });
            });

            document.getElementById('editQuantityForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitFormWithAjax(this, function() {
                    editQuantityModal.style.display = 'none';
                    showNotification(statusMessages['edit_quantity']);
                });
            });

            // Fonction pour soumettre un formulaire avec AJAX
            function submitFormWithAjax(form, callback) {
                const formData = new FormData(form);
                let url = form.action + '?';
                
                const params = new URLSearchParams();
                for (const pair of formData) {
                    params.append(pair[0], pair[1]);
                }
                url += params.toString();

                const submitBtn = form.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '⏳ Processing...';
                submitBtn.disabled = true;

                const xhr = new XMLHttpRequest();
                xhr.open('GET', url, true);

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        if (callback) callback();

                        // Délai de 3 secondes avant de recharger la page
                        setTimeout(() => location.reload(), 3000);
                    } else {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        alert('Erreur lors de la mise à jour');
                    }
                };
                
                xhr.onerror = function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    alert('Erreur réseau');
                };
                
                xhr.send();
            }

            // Gérer les clics sur les boutons d'action
            const actionButtons = document.querySelectorAll('a.btn-action');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    const statusParam = new URL(url, window.location.origin).searchParams.get('status');
                    
                    const message = statusMessages[statusParam] || 'Action effectuée avec succès';

                    // Montrer l'état de chargement
                    const originalText = this.innerHTML;
                    this.innerHTML = '⏳ Processing...';
                    const originalBg = this.style.backgroundColor;
                    this.style.backgroundColor = '#999';

                    // AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', url, true);
                    xhr.onload = () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            showNotification(message);
                            // Délai de 3 secondes avant de recharger la page
                            setTimeout(() => location.reload(), 3000);
                        } else {
                            this.innerHTML = originalText;
                            this.style.backgroundColor = originalBg;
                            alert('Erreur lors de la mise à jour du statut');
                        }
                    };
                    xhr.onerror = () => {
                        this.innerHTML = originalText;
                        this.style.backgroundColor = originalBg;
                        alert('Erreur réseau');
                    };
                    xhr.send();
                });
            });
        });

        // Fonctions pour ouvrir les modals
        function showApproveModal(orderId, quantity) {
            // Remplir le modal avec les valeurs
            document.getElementById('orderIdApprove').value = orderId;
            document.getElementById('requestedQuantity').value = quantity;
            document.getElementById('allottedQuantity').value = quantity; // Par défaut, même quantité
            document.getElementById('allottedQuantity').max = quantity; // Limiter au maximum demandé
            
            // Afficher le modal
            document.getElementById('approveModal').style.display = 'block';
        }

        function showEditQuantityModal(orderId, requestedQuantity, allottedQuantity) {
            // Remplir le modal avec les valeurs
            document.getElementById('orderIdEdit').value = orderId;
            document.getElementById('currentRequestedQuantity').value = requestedQuantity;
            document.getElementById('currentAllottedQuantity').value = allottedQuantity;
            document.getElementById('newAllottedQuantity').value = allottedQuantity;
            document.getElementById('newAllottedQuantity').max = requestedQuantity; // Limiter au maximum demandé
            
            // Afficher le modal
            document.getElementById('editQuantityModal').style.display = 'block';
        }

        // Fonction pour afficher une notification
        function showNotification(message) {
            const notif = document.getElementById('notification');
            notif.innerText = message;
            notif.style.display = 'block';
            notif.style.opacity = '1';

            // Disparaît après 3 secondes avec une transition fluide
            setTimeout(() => {
                notif.style.transition = 'opacity 1s';
                notif.style.opacity = '0';
                setTimeout(() => {
                    notif.style.display = 'none';
                }, 1000);
            }, 3000);
        }
    </script>
</body>
</html>