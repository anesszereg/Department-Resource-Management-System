<?php
session_start();
include 'config.php';
include 'sidebar.php';

// Vérification de l'authentification et des droits admin
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

// Récupérer le département demandé
$department = '';
if (isset($_GET['id'])) {
    $department = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    header('location: admin_products.php');
    exit();
}

// Récupérer les infos du département
$dept_query = mysqli_query($conn, "SELECT * FROM departement WHERE speciality = '$department'");
if (mysqli_num_rows($dept_query) == 0) {
    header('location: admin_products.php');
    exit();
}
$dept_info = mysqli_fetch_assoc($dept_query);

// Compter les produits du département
$products_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE speciality = '$department'"))['count'];

// Messages flash
$success_message = '';
$error_message = '';

// Traitement des formulaires si nécessaire
if (isset($_POST['update_department'])) {
    // Code pour mettre à jour le département
    $new_name = mysqli_real_escape_string($conn, $_POST['name']);
    
    if (mysqli_query($conn, "UPDATE departement SET name = '$new_name' WHERE speciality = '$department'")) {
        $success_message = "Département mis à jour avec succès !";
    } else {
        $error_message = "Erreur lors de la mise à jour du département : " . mysqli_error($conn);
    }
}

// Pagination pour les produits
$items_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$start_from = ($page - 1) * $items_per_page;

// Requête pour les produits du département
$products_query = "SELECT * FROM products WHERE speciality = '$department' ORDER BY name ASC LIMIT $start_from, $items_per_page";
$products = mysqli_query($conn, $products_query);

// Nombre total de produits pour la pagination
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE speciality = '$department'");
$total_products = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_products / $items_per_page);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Département <?= ucfirst(htmlspecialchars($department)) ?> - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Utilisez le même style CSS que vous avez dans admin_products.php -->
</head>
<body>

<div class="section">
    <h2><i class="fas fa-building"></i> Département: <?= ucfirst(htmlspecialchars($department)) ?></h2>
    
    <div class="stats-container">
        <div class="stat-box">
            <i class="fas fa-boxes"></i>
            <span class="stat-value"><?= $products_count ?></span>
            <span class="stat-label">Total produits</span>
        </div>
        <!-- Ajoutez d'autres statistiques si nécessaire -->
    </div>
    
    <!-- Liens de navigation -->
    <div class="navigation-links">
        <a href="admin_products.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Retour à la liste des produits</a>
        <a href="admin_products.php?speciality=<?= urlencode($department) ?>" class="btn-blue"><i class="fas fa-filter"></i> Voir les produits</a>
    </div>
    
    <!-- Messages de notification -->
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
    
    <!-- Informations du département -->
    <div class="department-details">
        <div class="card">
            <h3>Détails du département</h3>
            <form method="POST" class="edit-form">
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Nom du département</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($dept_info['speciality']) ?>" required>
                </div>
                <!-- Ajoutez d'autres champs si nécessaire -->
                
                <div class="form-actions">
                    <button type="submit" name="update_department" class="btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Liste des produits -->
    <h3><i class="fas fa-boxes"></i> Produits du département</h3>
    
    <div class="table-responsive">
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Quantité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($products) > 0):
                    while ($p = mysqli_fetch_assoc($products)) : 
                ?>
                    <tr class="product-row" data-id="<?= $p['id']; ?>">
                        <td>
                            <a href="<?= htmlspecialchars($p['image']); ?>" target="_blank" class="image-preview">
                                <img src="<?= htmlspecialchars($p['image']); ?>" alt="<?= htmlspecialchars($p['name']); ?>" height="60">
                            </a>
                        </td>
                        <td><?= htmlspecialchars($p['name']); ?></td>
                        <td class="quantity-cell <?= $p['quantity'] <= 0 ? 'out-of-stock' : ($p['quantity'] <= 5 ? 'low-stock' : 'in-stock'); ?>">
                            <?= $p['quantity']; ?>
                            <?php if ($p['quantity'] <= 0): ?>
                                <span class="stock-badge critical">Rupture</span>
                            <?php elseif ($p['quantity'] <= 5): ?>
                                <span class="stock-badge warning">Faible</span>
                            <?php else: ?>
                                <span class="stock-badge success">Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="edit_product.php?id=<?= $p['id']; ?>" class="btn-action edit" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="admin_products.php" class="inline-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit?')">
                                <input type="hidden" name="product_id" value="<?= $p['id']; ?>">
                                <button type="submit" name="delete_product" class="btn-action delete" title="Supprimer">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            <a href="quick_edit.php?id=<?= $p['id']; ?>" class="btn-action stock" title="Modifier stock rapidement">
                                <i class="fas fa-boxes"></i>
                            </a>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="4" class="no-results">Aucun produit trouvé dans ce département</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination si nécessaire -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?id=<?= urlencode($department) ?>&page=<?= $page-1 ?>" class="page-btn">&laquo; Précédent</a>
            <?php endif; ?>
            
            <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if($i == $page): ?>
                    <span class="page-btn active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?id=<?= urlencode($department) ?>&page=<?= $i ?>" class="page-btn"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?id=<?= urlencode($department) ?>&page=<?= $page+1 ?>" class="page-btn">Suivant &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Réutilisez le même style CSS et JavaScript de admin_products.php -->
<style>
/* Insérez ici votre CSS existant */
.navigation-links {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.department-details {
    margin-bottom: 30px;
}

.card {
    background: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--shadow);
}

.edit-form {
    max-width: 600px;
    margin: 0 auto;
}
</style>
<!-- Scripts JavaScript -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Animation pour les lignes de produits
    document.querySelectorAll(".product-row").forEach((el, i) => {
        setTimeout(() => {
            el.classList.add("animate-slide-left");
        }, i * 80); // petit décalage entre les lignes
    });
    
    // Prévisualisation de l'image lors de l'upload
    document.getElementById('image').addEventListener('change', function(e) {
        previewImage(this);
    });
    
    // Animation pour les départements
    document.querySelectorAll(".department-card").forEach((el, i) => {
        setTimeout(() => {
            el.classList.add("animate-zoom-fade");
        }, i * 100);
    });
    
    // Messages de notification qui se ferment automatiquement
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// Fonction pour changer d'onglet
function openTab(tabName) {
    // Masquer tous les contenus d'onglets
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.style.display = 'none';
    });
    
    // Désactiver tous les boutons d'onglets
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Afficher le contenu d'onglet sélectionné et activer le bouton
    document.getElementById(tabName).style.display = 'block';
    document.querySelector(`.tab-btn[onclick="openTab('${tabName}')"]`).classList.add('active');
}

// Fonction pour prévisualiser l'image avant upload
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePlaceholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }
}
</script>

</body>
</html>