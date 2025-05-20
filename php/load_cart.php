<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
// Only get cart items that are not yet confirmed as orders (status = 'en attente' or empty status)
$cart_items = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = $user_id AND (status = 'en attente' OR status IS NULL OR status = '')");

if (mysqli_num_rows($cart_items) === 0) {
    echo '<p class="empty-cart">Votre panier est vide.</p>';
    exit;
}
?>

<table class="cart-table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Quantité</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($item = mysqli_fetch_assoc($cart_items)) : ?>
        <tr data-id="<?= $item['id'] ?>">
            <td><img src="<?= htmlspecialchars($item['image']) ?>" style="width: 60px;"></td>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>
                <input 
                    type="number" 
                    min="1" 
                    value="<?= $item['quantity'] ?>" 
                    class="update-quantity" 
                    data-id="<?= $item['id'] ?>"
                    style="width: 60px;"
                >
            </td>
            <td>
                <button class="update-btn" data-id="<?= $item['id'] ?>">📝</button>
                <button class="delete-btn" data-id="<?= $item['id'] ?>">🗑️</button>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<button id="clear-cart" class="clear-cart-btn">🧹 Vider le panier</button>
<!-- Button to Place Order -->
<button id="place-order" class="place-order-btn">💳 Passer la commande</button>



<style>
    .cart-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-family: 'Segoe UI', sans-serif;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.cart-table thead {
    background-color: #2980b9;
    color: white;
}

.cart-table th,
.cart-table td {
    padding: 14px 16px;
    text-align: center;
}

.cart-table tr:nth-child(even) {
    background-color: #f2f2f2;
}

.cart-table img {
    border-radius: 6px;
    width: 60px;
    height: 60px;
    object-fit: cover;
}

.update-quantity {
    padding: 6px;
    width: 60px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.update-btn,
.delete-btn {
    padding: 6px 10px;
    margin: 2px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.update-btn {
    background-color: #27ae60;
    color: white;
}

.update-btn:hover {
    background-color: #219150;
}

.delete-btn {
    background-color: #e74c3c;
    color: white;
}

.delete-btn:hover {
    background-color: #c0392b;
}

.clear-cart-btn {
    margin-top: 20px;
    background-color: #c0392b;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.clear-cart-btn:hover {
    background-color: #a8322a;
}

.empty-cart {
    text-align: center;
    font-size: 18px;
    color: #888;
    margin-top: 30px;
}
.place-order-btn {
    margin-top: 20px;
    background-color: #3498db;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.place-order-btn:hover {
    background-color: #2980b9;
}


</style>
<script>document.getElementById('place-order').addEventListener('click', function () {
    // Vérifier si le panier est vide
    const cartItems = document.querySelectorAll('.cart-table tbody tr');
    if (cartItems.length === 0) {
        alert("Votre panier est vide. Vous ne pouvez pas passer la commande.");
        return;
    }

    // Envoyer la demande de commande
    fetch('place_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=place_order' // Paramètre pour passer la commande
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === 'success') {
            alert("Commande passée avec succès !");
            // Optionnel: Rediriger l'utilisateur ou vider le panier après commande
            window.location.href = "order_confirmation.php"; // Redirection vers une page de confirmation
        } else {
            alert("Une erreur est survenue lors de la commande. Réessayez.");
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert("Une erreur est survenue.");
    });
});
</script>