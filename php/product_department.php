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
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));
$speciality = $user['speciality'];

// Initialize search filter
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    $products = mysqli_query($conn, "SELECT * FROM products WHERE speciality = '$speciality' AND name LIKE '%$search_term%'");
} else {
    $products = mysqli_query($conn, "SELECT * FROM products WHERE speciality = '$speciality'");
}
?>


<div class="section">
    <h2 class="specialty-heading">Products for your specialty: <?php echo htmlspecialchars($speciality); ?></h2>
    
    <!-- Product Name Filter -->
    <div class="filter-container">
        <form action="" method="GET" class="filter-form">
            <div class="input-group">
                <input type="text" name="search" placeholder="Filter by product name" value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <button type="submit" class="btn-filter">Search</button>
            <?php if(!empty($search_term)): ?>
                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="products-grid">
    <?php 
    if (mysqli_num_rows($products) > 0) {
        while ($product = mysqli_fetch_assoc($products)): 
    ?>
        <div class="product-card">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
        </div>
    <?php 
        endwhile;
    } else {
    ?>
        <div class="no-products">
            <p>No products found matching your criteria.</p>
        </div>
    <?php } ?>
    </div>
</div>

<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background-color: #f8f9fa;
}

.section {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

h2 {
    font-size: 24px;
    margin-bottom: 25px;
    color: #333;
}

/* Specialty heading with blue underline */
.specialty-heading {
    font-size: 28px;
    color: #24478f;
    position: relative;
    padding-bottom: 10px;
    margin-bottom: 30px;
    font-weight: 600;
    border-bottom: none;
}

.specialty-heading::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    background-color: #24478f;
}

/* Styles pour la grille de produits */
.products-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.product-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    width: 200px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: box-shadow 0.2s ease;
}

.product-card:hover {
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.product-card img {
    width: 100%;
    height: 150px;
    object-fit: contain;
    margin-bottom: 15px;
}

.product-card h3 {
    color: #1a56db;
    font-size: 16px;
    text-align: center;
    font-weight: 600;
    margin: 0;
    padding: 0;
}

/* Styles pour la barre de recherche */
.filter-container {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
}

.filter-form {
    display: flex;
    gap: 10px;
}

.input-group {
    position: relative;
}

.filter-form input[type="text"] {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    width: 250px;
}

.btn-filter {
    background-color: #1a56db;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
}

.btn-reset {
    background-color: #f1f1f1;
    color: #555;
    border: 1px solid #ddd;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
}

.no-products {
    padding: 20px;
    text-align: center;
    width: 100%;
    background: #f1f1f1;
    border-radius: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .products-grid {
        justify-content: center;
    }
    
    .product-card {
        width: 180px;
    }
}

@media (max-width: 480px) {
    .filter-form {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .product-card {
        width: 150px;
    }
}
.specialty-heading {
    font-size: 28px;
    color: #0d47a1; /* Bleu foncé */
    font-weight: 600;
    position: relative;
    padding-bottom: 10px;
    margin-bottom: 30px;
    font-family: 'Segoe UI', sans-serif;
}

.specialty-heading::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    height: 3px;
    width: 80px; /* Barre courte sous le début du texte */
    background-color: #4285f4; /* Bleu vif (comme Google) */
    border-radius: 2px;
    transition: width 0.3s ease;
}

.specialty-heading:hover::after {
    width: 100%;
}

</style>