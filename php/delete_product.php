<?php
session_start();
include 'config.php';

// Vérifie si l'utilisateur est connecté et admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Vérifie si l'ID du produit est passé en paramètre
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Supprimer le produit
    $query = "DELETE FROM products WHERE id = '$product_id'";
    if (mysqli_query($conn, $query)) {
        header('Location: products.php');
        exit();
    } else {
        echo 'Erreur lors de la suppression du produit.';
    }
}
?>
