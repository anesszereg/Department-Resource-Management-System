<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('location: login.php');
    exit();
}

// Inclure les fichiers de configuration
include 'config.php';

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$select_user = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);

// Vérifier la spécialité et rediriger si nécessaire
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file == 'indexinfo.php' && (!isset($fetch_user['speciality']) || $fetch_user['speciality'] != 'Informatique')) {
    header('location: index.php'); // Corrigé pour éviter la boucle infinie
    exit();
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('location: login.php');
    exit();
}

// Inclure la barre latérale
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <div class="section">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-user-circle"></i>
                Welcome, <?php echo $fetch_user['name'] ?? 'User'; ?>
            </div>
        </div>
        <div class="section-content">
            <p>Welcome to Boumerdes shopping dashboard. Browse our latest products and manage your shopping cart.</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const topbar = document.getElementById('topbar');

        if (menuToggle && sidebar && mainContent && topbar) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                mainContent.classList.toggle('active');
                topbar.classList.toggle('active');
            });
        } else {
            console.log("Un ou plusieurs éléments n'ont pas été trouvés dans le DOM");
        }
    });
    </script>
</body>
</html>
