<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('Location: login.php');
    exit();
}

$success_msg = '';
$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $speciality = mysqli_real_escape_string($conn, $_POST['speciality']);

    $check = mysqli_query($conn, "SELECT * FROM user_info WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error_msg = "Email already exists.";
    } else {
        $insert = "INSERT INTO user_info (name, email, password, approved, type, speciality) 
                   VALUES ('$name', '$email', '$password', 1, '$type', '$speciality')";
        if (mysqli_query($conn, $insert)) {
            $success_msg = "User added successfully!";
        } else {
            $error_msg = "Failed to add user.";
        }
    }
}
?>

<h2>Add User</h2>

<?php if ($success_msg): ?><p class="success"><?= $success_msg ?></p><?php endif; ?>
<?php if ($error_msg): ?><p class="error"><?= $error_msg ?></p><?php endif; ?>

<form method="post">
    <input type="text" name="name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <select name="type">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br>
    <input type="text" name="speciality" placeholder="Speciality"><br>
    <button type="submit">Add User</button>
</form>
