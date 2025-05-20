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
$user_query = mysqli_query($conn, "SELECT * FROM user_info WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
$user_speciality = $user['speciality'];
$user_department_id = $user['department_id']; // Ajout du département du chef
$date_now = date('d/m/Y');
$time_now = date('H:i');

// Statistiques pour un seul département
$product_count = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM products 
    WHERE speciality = '" . mysqli_real_escape_string($conn, $user_speciality) . "'
"))['total'];
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM cart WHERE user_id = $user_id"))['total'];

// Graphique des produits pour le département
$labels = [];
$data = [];
$graph_query = mysqli_query($conn, "
    SELECT MONTH(created_at) AS month, COUNT(*) AS count
    FROM products
    WHERE department_id = '$user_department_id'
    GROUP BY MONTH(created_at)
    ORDER BY month ASC
");
while ($row = mysqli_fetch_assoc($graph_query)) {
    $labels[] = date("F", mktime(0, 0, 0, $row['month'], 1));
    $data[] = $row['count'];
}
?>

<!-- STYLES -->
<style>
<?php include 'dashboard_styles.css'; ?>
</style>
<br>
<br>


<div class="main-content">
<div class="welcome-banner">
    <div class="welcome-text">
        <h2>Welcome, Head of Department in <?= htmlspecialchars($user_speciality) ?> 👋</h2>
        <p>We are the <strong><?= $date_now ?></strong> | Current time: <strong><?= $time_now ?></strong></p>
    </div>
</div>

    <h1> - <?= htmlspecialchars($user_speciality) ?></h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Products</h3>
            <p><?= $product_count ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?= $order_count ?></p>
        </div>
    </div>
    
        <div class="chart-container">
    <h2>Global Statistics</h2>
    <canvas id="globalStatsChart"></canvas>
</div>

    </div>
</div>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f2f5;
    margin: 0;
    padding: 0;
    color: #333;
}

.main-content {
    padding: 40px;
    margin-left: 160px;
    margin-right: 30px;
    background: #f9fbfd;
    border-radius: 10px;
    animation: fadeIn 1s ease-in-out;
}

.main-content h1 {
    font-size: 2rem;
    color: #007BFF;
    margin-bottom: 30px;
    border-bottom: 3px solid #007BFF;
    display: inline-block;
    padding-bottom: 10px;
}

.welcome-banner {
    background: linear-gradient(135deg, #007BFF, #00C6FF);
    color: white;
    padding: 25px 35px;
    border-radius: 14px;
    margin-bottom: 40px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    animation: slideInTop 1s ease forwards;
}

.welcome-text h2 {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.welcome-text p {
    font-size: 1.1rem;
    opacity: 0.95;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 25px;
    margin-top: 30px;
    animation: fadeInUp 1s ease forwards;
}

.stat-card {
    background: white;
    padding: 25px;
    border-left: 6px solid #007BFF;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.stat-card h3 {
    font-size: 1.3rem;
    margin-bottom: 12px;
    color: #555;
}

.stat-card p {
    font-size: 2rem;
    font-weight: bold;
    color: #007BFF;
}

.chart-container {
    background: white;
    padding: 35px;
    border-radius: 16px;
    margin-top: 50px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    animation: fadeIn 1s ease forwards 0.5s;
}

.chart-container h2 {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 25px;
}

.chart-container canvas {
    width: 100%;
    max-height: 320px;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInTop {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>

<!-- Chart.js & Animation JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>




<script>
const ctxGlobal = document.getElementById('globalStatsChart').getContext('2d');
new Chart(ctxGlobal, {
    type: 'bar',
    data: {
        labels: ['Total Products', 'Total Orders'],
        datasets: [{
            label: 'Quantité',
            data: [<?= $product_count ?>, <?= $order_count ?>],
            backgroundColor: ['#007BFF', '#28A745'],
            borderRadius: 10,
            barThickness: 50
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#333',
                titleFont: { size: 14 },
                bodyFont: { size: 12 }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number',
                    font: { size: 14 }
                },
                grid: {
                    color: '#eee'
                }
            },
            x: {
                title: {
                    display: false
                }
            }
        },
        animation: {
            duration: 1200,
            easing: 'easeOutBounce'
        }
    }
});
</script>