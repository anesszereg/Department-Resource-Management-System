<?php
include 'config.php';
include 'top.php'; 

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}
// Vérifier si le produit est passé dans l'URL
if (!isset($_GET['product_id'])) {
    header('location: user_products.php');
    exit();
}
// Récupérer les détails du produit
$product_id = $_GET['product_id'];
$product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'") or die('Query failed');
$product = mysqli_fetch_assoc($product_query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Produit</title>
</head>
<body>

<div class="container">
    <h1>Produit : <?php echo htmlspecialchars($product['name']); ?></h1>
    <p>Catégorie : <?php echo htmlspecialchars($product['category']); ?></p>
    <p>Description : <?php echo htmlspecialchars($product['description']); ?></p>
    <p><strong>Disponibilité :</strong> <?php echo $product['available'] ? 'Disponible' : 'Indisponible'; ?></p>
    <?php if ($product['available']) { ?>
        <a href="order_product.php?product_id=<?php echo $product['id']; ?>" class="btn-order">Commander</a>
    <?php } else { ?>
        <p>Ce produit est actuellement indisponible.</p>
    <?php } ?>
</div>
</body>
</html>
