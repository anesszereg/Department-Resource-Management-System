<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_GET['id'];
$user_query = mysqli_query($conn, "SELECT * FROM user_info WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($user_query);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $speciality = mysqli_real_escape_string($conn, $_POST['speciality']);

    $update = "UPDATE user_info SET name='$name', email='$email', type='$type', speciality='$speciality' WHERE id='$user_id'";
    if (mysqli_query($conn, $update)) {
        echo "User updated successfully!";
    } else {
        echo "Failed to update user.";
    }
}
?>

<h2>Edit User</h2>

<form method="post">
    <input type="text" name="name" value="<?= $user['name'] ?>" required><br>
    <input type="email" name="email" value="<?= $user['email'] ?>" required><br>
    <select name="type">
        <option value="user" <?= $user['type'] == 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $user['type'] == 'admin' ? 'selected' : '' ?>>Admin</option>
    </select><br>
    <input type="text" name="speciality" value="<?= $user['speciality'] ?>"><br>
    <button type="submit">Update</button>
</form>
