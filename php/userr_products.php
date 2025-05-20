<?php
include 'config.php';
include 'top.php'; 

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

// Récupération des informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$select_user = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'") or die('Query failed');
$fetch_user = mysqli_fetch_assoc($select_user);

// Récupération des produits attribués au département
$products_query = "SELECT * FROM products WHERE department_id = '$user_id'";
$products_result = mysqli_query($conn, $products_query);

// Récupération des catégories de produits pour le filtrage
$categories_query = "SELECT DISTINCT category FROM products WHERE department_id = '$user_id'";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Produits - Département</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

<div class="main-content" id="main-content">
    <div class="container">

        <h1>📦 Mes Produits</h1>

        <!-- Filtrage par catégorie -->
        <form method="GET" class="filter-form">
            <label for="category">Filtrer par catégorie :</label>
            <select name="category" id="category">
                <option value="">Toutes les catégories</option>
                <?php while ($category = mysqli_fetch_assoc($categories_result)) { ?>
                    <option value="<?php echo $category['category']; ?>" <?php if (isset($_GET['category']) && $_GET['category'] == $category['category']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($category['category']); ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit">Filtrer</button>
        </form>

        <div class="product-list">
            <?php
            // Application du filtrage par catégorie si une catégorie est sélectionnée
            if (isset($_GET['category']) && $_GET['category'] != '') {
                $category_filter = mysqli_real_escape_string($conn, $_GET['category']);
                $products_query .= " AND category = '$category_filter'";
                $products_result = mysqli_query($conn, $products_query);
            }

            // Affichage des produits
            if (mysqli_num_rows($products_result) > 0) {
                while ($product = mysqli_fetch_assoc($products_result)) {
                    ?>
                    <div class="product-card">
                        <img src="<?php echo $product['image'] ? $product['image'] : 'default.jpg'; ?>" alt="Image du produit">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p>Catégorie : <?php echo htmlspecialchars($product['category']); ?></p>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <p><strong>Disponibilité :</strong> <?php echo $product['available'] ? 'Disponible' : 'Indisponible'; ?></p>
                        </div>
                        <div class="product-actions">
                            <a href="product_details.php?product_id=<?php echo $product['id']; ?>" class="btn-details">Voir Détails</a>
                            <?php if ($product['available']) { ?>
                                <a href="order_product.php?product_id=<?php echo $product['id']; ?>" class="btn-order">Commander</a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>Aucun produit trouvé dans votre département.</p>';
            }
            ?>
        </div>

    </div>
</div>

</body>

</html>
