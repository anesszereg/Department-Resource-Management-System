<?php
include 'config.php';

$now = date("Y-m-d H:i:s");

// Étape 1: Sélectionner les commandes à archiver
$query = "
    SELECT * FROM cart 
    WHERE (status = 'validée' OR status = 'rejetée') 
    AND status_updated_at < NOW() - INTERVAL 24 HOUR
";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Insérer dans la table d'archive
        $insert = "
            INSERT INTO orders_archive 
            (original_order_id, user_id, product_id, quantity, allotted_quantity, status, status_updated_at, created_at) 
            VALUES (
                '{$row['id']}', '{$row['user_id']}', '{$row['product_id']}', 
                '{$row['quantity']}', '{$row['allotted_quantity']}', 
                '{$row['status']}', '{$row['status_updated_at']}', '{$row['created_at']}'
            )
        ";
        mysqli_query($conn, $insert);

        // Supprimer de la table originale
        $delete = "DELETE FROM cart WHERE id = {$row['id']}";
        mysqli_query($conn, $delete);
    }
}

echo "✅ Archive process complete.";
?>
