<?php
session_start();
include 'config.php';
include 'top.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
if ($user['type'] !== 'admin') {
    header('location: index.php');
    exit();
}
// STATISTIQUES PRINCIPALES
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products"))['count'];
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM cart"))['count'];
$out_of_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products WHERE quantity = 0"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM cart WHERE status ='pending'"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM cart WHERE status ='Not Approved'"))['count'];
$approved_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM cart WHERE status = 'Validée'"))['count'];
$rejected_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM cart WHERE status = 'Rejetée'"))['count'];

// PRODUITS PAR DEPARTEMENT (BAR)
$dept_result = mysqli_query($conn, "SELECT speciality, COUNT(*) as total FROM products GROUP BY speciality");
$department_names = [];
$products_per_department = [];
while ($row = mysqli_fetch_assoc($dept_result)) {
    $department_names[] = $row['speciality'];
    $products_per_department[] = $row['total'];
}

// COMMANDES PAR DEPARTEMENT (BAR)
$order_dept_result = mysqli_query($conn, "SELECT p.speciality, COUNT(c.id) as orders_count 
                                          FROM cart c
                                          JOIN products p ON c.product_id = p.id
                                          GROUP BY p.speciality");
$order_departments = [];
$orders_per_department = [];
while ($row = mysqli_fetch_assoc($order_dept_result)) {
    $order_departments[] = $row['speciality'];
    $orders_per_department[] = $row['orders_count'];
}

// TOP 5 PRODUITS LES PLUS COMMANDÉS
$top_products_result = mysqli_query($conn, "
    SELECT p.name, COUNT(c.product_id) as ordered
    FROM cart c
    JOIN products p ON c.product_id = p.id
    GROUP BY c.product_id
    ORDER BY ordered DESC
    LIMIT 3");
$top_products = mysqli_fetch_all($top_products_result, MYSQLI_ASSOC);

// TOP 5 DEPARTEMENTS LES PLUS ACTIFS
$top_departments_result = mysqli_query($conn, "
    SELECT p.speciality, COUNT(c.id) as orders
    FROM cart c
    JOIN products p ON c.product_id = p.id
    GROUP BY p.speciality
    ORDER BY orders DESC
    LIMIT 3");
$top_departments = mysqli_fetch_all($top_departments_result, MYSQLI_ASSOC);

// TOP 5 DEMANDEURS LES PLUS ACTIFS
$top_users_result = mysqli_query($conn, "
    SELECT user_id, COUNT(*) as orders
    FROM cart 
    GROUP BY user_id 
    ORDER BY orders DESC 
    LIMIT 3");
$top_users = [];
while ($row = mysqli_fetch_assoc($top_users_result)) {
    $user_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM user_info WHERE id = '{$row['user_id']}'"));
    $top_users[] = [
        'name' => $user_info['name'] ?? 'Unknown',
        'orders' => $row['orders']
    ];
}
?>

<div class="main-content">
    <h1>Admin Statics</h1>

    <!-- General Statistics -->
    <div class="stats-grid">
        <div class="stat-card total-products"><h3>Total Products</h3><p><?= $total_products; ?></p></div>
        <div class="stat-card total-orders"><h3>Total Orders</h3><p><?= $total_orders; ?></p></div>
        <div class="stat-card peding-approved-orders"><h3>Pending Approved</h3><p><?= $pending_orders;?></p></div>
        <div class="stat-card pending-orders"><h3>Pending Orders</h3><p><?= $pending_orders; ?></p></div>
        <div class="stat-card approved-orders"><h3>Approved Orders</h3><p><?= $approved_orders; ?></p></div>
        <div class="stat-card rejected-orders"><h3>Rejected Orders</h3><p><?= $rejected_orders; ?></p></div>
    </div>

    <!-- Charts Grid - Les deux graphiques côte à côte -->
    <div class="charts-grid">
        <!-- Graph: Products by Department -->
        <div class="chart-container">
            <h2>Products by Department</h2>
            <canvas id="deptChart"></canvas>
        </div>

        <!-- Graph: Orders by Department -->
        <div class="chart-container">
            <h2>Orders by Department</h2>
            <canvas id="ordersChart"></canvas>
        </div>
    </div>

    <!-- Top 5 Products -->
    <div class="top-section">
        <div class="top-card">
            <h3>Top 3 Most Ordered Products</h3>
            <ul>
                <?php foreach ($top_products as $prod): ?>
                    <li><?= $prod['name'] ?> (<?= $prod['ordered'] ?> orders)</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Top 5 Departments -->
        <div class="top-card">
            <h3>Top 3 Most Active Departments</h3>
            <ul>
                <?php foreach ($top_departments as $dept): ?>
                    <li><?= $dept['speciality'] ?> (<?= $dept['orders'] ?> orders)</li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Top 5 Active Users -->
        <div class="top-card">
            <h3>Top 3 Most Active Users</h3>
            <ul>
                <?php foreach ($top_users as $user): ?>
                    <li><?= $user['name'] ?> (<?= $user['orders'] ?> orders)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
.main-content {
    padding: 20px;
    margin-left: 150px; /* Matches sidebar width */
    margin-right: 20px;
    background: #f5f7fa;
    opacity: 0;
    animation: fadeIn 1s ease-in forwards;
}

.main-content h1 {
    font-size: 1.8rem;
    color: #333;
    margin: 0 0 30px 0;
    padding: 50px 0;
    text-align: left;
    border-bottom: 2px solid #007BFF;
    display: inline-block;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin: 20px 0;
    opacity: 0;
    animation: slideIn 0.8s ease-out forwards 0.3s;
    height:150px;
}

/* Nouveau style pour les graphiques côte à côte */
.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 40px;
    opacity: 0;
    animation: fadeIn 1s ease-in forwards 0.6s;
}

/* Media query pour les petits écrans */
@media (max-width: 1100px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    display: flex;
    align-items: center;
    background: white;
    padding: 15px 20px;
    border-radius: 12px;
    text-align: left;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    gap: 15px;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: "";
    display: block;
    width: 8px;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    border-radius: 8px 0 0 8px;
    background-color: #007BFF; /* Default */
}

/* Specific left colors for each stat type */
.stat-card.total-products::before {
    background-color: #007BFF;
}
.stat-card.total-orders::before {
    background-color: #17a2b8;
}
.stat-card.pending-orders::before {
    background-color: #ffc107;
}

.stat-card.pending-approved::before {
    background-color:rgb(94, 93, 90);
}
.stat-card.approved-orders::before {
    background-color: #28a745;
}
.stat-card.rejected-orders::before {
    background-color: #dc3545;
}
.stat-card.out-of-stock::before {
    background-color: #fd7e14;
}
.stat-card.peding-approved-orders::before {
    background-color:rgb(94, 93, 90);
}

.stat-card h3 {
    font-size: 1.2rem;
    color: #333;
    margin: 0;
}

.stat-card p {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007BFF;
    margin: 5px 0 0;
}

.stat-card.low-stock p,
.stat-card.rejected-orders p,
.stat-card.out-of-stock p {
    color: #dc3545;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

/* Chart and top section */
.chart-container {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.chart-container h2 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 20px;
}

.top-section {
    display: flex;
    flex-wrap: wrap;
    margin-top: 30px;
    gap: 20px;
    opacity: 0;
    animation: slideIn 0.8s ease-out forwards 0.9s;
}

.top-card {
    flex: 1;
    min-width: 250px;
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.top-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.top-card h3 {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 15px;
}

.top-card ul {
    list-style: none;
    padding: 0;
}

.top-card li {
    padding: 8px 0;
    font-size: 1rem;
    color: #444;
    border-bottom: 1px solid #eee;
}

.top-card li:last-child {
    border-bottom: none;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Products by Department (Smooth Line Chart)
const deptCtx = document.getElementById('deptChart').getContext('2d');
const deptChart = new Chart(deptCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($department_names) ?>,
        datasets: [{
            label: 'Products',
            data: <?= json_encode($products_per_department) ?>,
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderColor: '#007BFF',
            borderWidth: 3,
            fill: true,
            tension: 0.5, // Plus la valeur est élevée (jusqu'à 1), plus le trait est courbé
            pointBackgroundColor: '#007BFF',
            pointBorderColor: '#fff',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            title: {
                display: true,
                text: 'Number of Products by Department'
            }
        },
        scales: {
            x: {
                title: { display: true, text: 'Departments' }
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Number of Products' }
            }
        }
    }
});

// Orders by Department (Smooth Line Chart)
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
const ordersChart = new Chart(ordersCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($order_departments) ?>,
        datasets: [{
            label: 'Orders',
            data: <?= json_encode($orders_per_department) ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderColor: '#28a745',
            borderWidth: 3,
            fill: true,
            tension: 0.5,
            pointBackgroundColor: '#28a745',
            pointBorderColor: '#fff',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            title: {
                display: true,
                text: 'Number of Orders by Department'
            }
        },
        scales: {
            x: {
                title: { display: true, text: 'Departments' }
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Number of Orders' }
            }
        }
    }
});
</script>