<?php
include 'config.php';

// Vérifier que les paramètres nécessaires sont présents
if (!isset($_GET['id'], $_GET['status'])) {
    exit("❌ Informations manquantes pour mettre à jour le statut.");
}

$order_id = (int)$_GET['id'];
$status = mysqli_real_escape_string($conn, $_GET['status']);

if ($status === 'validée' && isset($_GET['allotted_quantity'])) {
    $allotted_quantity = (int)$_GET['allotted_quantity'];

    if ($allotted_quantity <= 0) {
        exit("❌ La quantité allouée doit être positive.");
    }

    // Récupérer la commande (quantity demandée + product_id)
    $stmt = $conn->prepare("SELECT quantity, product_id FROM cart WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $requested_quantity = (int)$row['quantity'];
        $product_id = (int)$row['product_id'];

        if ($allotted_quantity > $requested_quantity) {
            exit("❌ La quantité allouée ne peut pas dépasser la quantité demandée ($requested_quantity).");
        }

        // Vérifier la quantité en stock
        $stock_check = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $stock_check->bind_param("i", $product_id);
        $stock_check->execute();
        $stock_result = $stock_check->get_result();

        if ($product = $stock_result->fetch_assoc()) {
            $stock_quantity = (int)$product['quantity'];

            if ($allotted_quantity > $stock_quantity) {
                exit("❌ Stock insuffisant. Stock actuel : $stock_quantity.");
            }

            // Mettre à jour la commande
            $update_cart = $conn->prepare("
                UPDATE cart 
                SET status = ?, allotted_quantity = ?, status_updated_at = NOW() 
                WHERE id = ?
            ");
            $update_cart->bind_param("sii", $status, $allotted_quantity, $order_id);

            // Mettre à jour le stock du produit
            $update_product = $conn->prepare("
                UPDATE products 
                SET quantity = quantity - ? 
                WHERE id = ?
            ");
            $update_product->bind_param("ii", $allotted_quantity, $product_id);

            if ($update_cart->execute() && $update_product->execute()) {
                header("Location: admin_orders.php?msg=updated");
                exit;
            } else {
                exit("❌ Erreur lors de la mise à jour.");
            }

        } else {
            exit("❌ Produit non trouvé.");
        }

    } else {
        exit("❌ Commande non trouvée.");
    }

} else {
    // Mise à jour simple du statut
    $update = $conn->prepare("
        UPDATE cart 
        SET status = ?, status_updated_at = NOW() 
        WHERE id = ?
    ");
    $update->bind_param("si", $status, $order_id);

    if ($update->execute()) {
        header("Location: admin_orders.php?msg=updated");
        exit;
    } else {
        exit("❌ Erreur lors de la mise à jour : " . $update->error);
    }
}
?>
