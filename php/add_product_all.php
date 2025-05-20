<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'"));

if ($user['type'] != 'admin') {
    header('Location: indexx.php');
    exit();
}

$success_msg = '';
$error_msg = '';

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $quantity = intval($_POST['quantity']);
    $image = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_path = 'images/' . basename($image);
    $selected_departments = isset($_POST['departments']) ? $_POST['departments'] : [];

    if (empty($selected_departments)) {
        $error_msg = "Veuillez sélectionner au moins un département.";
    } else if (move_uploaded_file($image_tmp, $image_path)) {
        $all_inserted = true;

        foreach ($selected_departments as $dept_id) {
            $dept_id = mysqli_real_escape_string($conn, $dept_id);
            $dept_query = mysqli_query($conn, "SELECT speciality FROM departement WHERE id = '$dept_id'");
            $dept = mysqli_fetch_assoc($dept_query);
            
            if ($dept) {
                $speciality = $dept['speciality'];
                $query = "INSERT INTO products (name, image, speciality, quantity, department_id) 
                          VALUES ('$name', '$image_path', '$speciality', $quantity, '$dept_id')";
                if (!mysqli_query($conn, $query)) {
                    $all_inserted = false;
                    $error_msg .= "Erreur pour $speciality : " . mysqli_error($conn) . "<br>";
                }
            }
        }

        if ($all_inserted) {
            $success_msg = "Produit ajouté avec succès aux départements sélectionnés.";
        }
    } else {
        $error_msg = "Erreur de téléchargement de l'image.";
    }
}
?>

<?php include 'sidebar.php'; ?>

<div class="section">
    <h2>Ajouter un produit à tous les départements</h2>

    <?php if ($success_msg): ?><div class="success"><?php echo $success_msg; ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class="error"><?php echo $error_msg; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nom du produit:</label>
        <input type="text" name="name" required>

        <label>Quantité:</label>
        <input type="number" name="quantity" required>

        <label>Image:</label>
        <input type="file" name="image" required>

        <label>Sélectionner les départements:</label>
        <div class="departments-list">
            <?php
            $departments = mysqli_query($conn, "SELECT * FROM departement ORDER BY speciality");
            while ($dept = mysqli_fetch_assoc($departments)) {
                echo "<label class='department-option'>";
                echo "<input type='checkbox' name='departments[]' value='" . $dept['id'] . "'> ";
                echo htmlspecialchars($dept['speciality']);
                echo "</label>";
            }
            ?>
        </div>

        <button type="submit" name="submit">Ajouter aux départements sélectionnés</button>
    </form>
</div>

<style>
form {
    margin-bottom: 30px;
}
form input:not([type="checkbox"]), form button {
    display: block;
    margin: 10px 0;
    padding: 8px;
    width: 100%;
}
.departments-list {
    margin: 15px 0;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 4px;
}
.department-option {
    display: block;
    margin: 8px 0;
    cursor: pointer;
}
.department-option input[type="checkbox"] {
    margin-right: 8px;
}
.success {
    color: green;
    padding: 10px;
    background: #e8f5e9;
    border-radius: 4px;
    margin-bottom: 15px;
}
.error {
    color: red;
    padding: 10px;
    background: #ffebee;
    border-radius: 4px;
    margin-bottom: 15px;
}
</style>
