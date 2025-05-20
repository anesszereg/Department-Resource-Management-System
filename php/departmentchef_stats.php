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

<div class="main-content">
<div class="welcome-banner">
    <div class="welcome-text">
        <h2>Bienvenue, Chef de Département en <?= htmlspecialchars($user_speciality) ?> 👋</h2>
        <p>Nous sommes le <strong><?= $date_now ?></strong> | Heure actuelle : <strong><?= $time_now ?></strong></p>
    </div>
</div>

    <h1>Tableau de Bord - <?= htmlspecialchars($user_speciality) ?></h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Produits</h3>
            <p><?= $product_count ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Commandes</h3>
            <p><?= $order_count ?></p>
        </div>
    </div>
    <div class="chart-container">
        <h2>Produits par Département</h2>
        <canvas id="deptChart"></canvas>
        <div class="chart-container">
    <h2>Statistiques Globales</h2>
    <canvas id="globalStatsChart"></canvas>
</div>

    </div>
</div>

<style>
.main-content {
    padding: 20px;
    margin-left: 150px;
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
}

.stat-card {
    background: white;
    padding: 20px;
    border-left: 6px solid #007BFF;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.stat-card h3 {
    font-size: 1.2rem;
    color: #333;
    margin-bottom: 10px;
}

.stat-card p {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007BFF;
}

.chart-container {
    background: white;
    padding: 30px;
    border-radius: 12px;
    margin-top: 40px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    opacity: 0;
    animation: fadeIn 1s ease-in forwards 0.6s;
}

.chart-container h2 {
    font-size: 1.4rem;
    color: #333;
    margin-bottom: 20px;
}

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
.welcome-banner {
    background: linear-gradient(to right, #007BFF, #00C6FF);
    color: white;
    padding: 20px 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    animation: fadeIn 1s ease-in forwards;
}

.welcome-text h2 {
    font-size: 1.6rem;
    margin: 0 0 10px 0;
}

.welcome-text p {
    font-size: 1rem;
    margin: 0;
    opacity: 0.9;
}
.chart-container canvas {
    max-width: 100%;
    height: 300px;
}

</style>

<!-- Chart.js & Animation JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('deptChart').getContext('2d');

const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(0, 123, 255, 0.3)');
gradient.addColorStop(1, 'rgba(0, 123, 255, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Produits',
            data: <?= json_encode($data) ?>,
            borderColor: '#007BFF',
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#007BFF',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                labels: {
                    font: {
                        size: 14
                    }
                }
            },
            tooltip: {
                backgroundColor: '#333',
                titleFont: { size: 14 },
                bodyFont: { size: 12 }
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Mois',
                    font: { size: 14 }
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Nombre de Produits',
                    font: { size: 14 }
                },
                grid: {
                    color: '#eee'
                }
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeInOutQuart'
        }
    }


});
</script>


<script>
const ctxGlobal = document.getElementById('globalStatsChart').getContext('2d');
new Chart(ctxGlobal, {
    type: 'bar',
    data: {
        labels: ['Total Produits', 'Total Commandes'],
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
                    text: 'Nombre',
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
