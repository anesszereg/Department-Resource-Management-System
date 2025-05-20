<?php
include 'config.php';

// Vérifier les paramètres nécessaires
if (!isset($_GET['id'], $_GET['allotted_quantity'])) {
    exit("❌ Paramètres manquants.");
}

$order_id = (int)$_GET['id'];
$allotted_quantity = (int)$_GET['allotted_quantity'];

// Vérification de la quantité
if ($allotted_quantity <= 0) {
    exit("❌ La quantité allouée doit être supérieure à zéro.");
}

// Récupérer la quantité demandée depuis la base
$stmt = $conn->prepare("SELECT quantity, status FROM cart WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $requested_quantity = (int)$row['quantity'];
    $status = $row['status'];

    if ($status !== 'validée') {
        exit("❌ Impossible de modifier la quantité : la commande n'est pas validée.");
    }

    if ($allotted_quantity > $requested_quantity) {
        exit("❌ La quantité allouée ne peut pas dépasser la quantité demandée ($requested_quantity).");
    }

    // Mise à jour de la quantité allouée
    $update = $conn->prepare("
        UPDATE cart 
        SET allotted_quantity = ?, status_updated_at = NOW() 
        WHERE id = ?
    ");
    $update->bind_param("ii", $allotted_quantity, $order_id);

    if ($update->execute()) {
        header("Location: admin_orders.php?msg=quantite_modifiee");
        exit;
    } else {
        exit("❌ Erreur lors de la mise à jour : " . $update->error);
    }

} else {
    exit("❌ Commande non trouvée.");
}
?>
