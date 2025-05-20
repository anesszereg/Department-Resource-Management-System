<?php
include 'config.php';
session_start();

// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $select = mysqli_query($conn, "SELECT approved, speciality, type FROM `user_info` WHERE id = '$user_id'") or die('query failed');
    $row = mysqli_fetch_assoc($select);

    if ($row['approved'] != 1) {
        header('location: approval_pending.php');
        exit();
    }

    if ($row['speciality'] == "Informatique" || $row['speciality'] == "informatique") {
        header('Location: indexinfo.php');
        exit();
    }

    if ($row['type'] == 'admin') {
        header('location: index.php');
    } else {
        header('location: indexx.php');
    }
    exit();
}

$message = [];

if (isset($_POST['submit'])) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $message[] = 'Please fill in all fields.';
    } else {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
        $stmt = $conn->prepare("SELECT * FROM `user_info` WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

          // Check approval and active status
if ($row['approved'] != 1) {
    $message[] = "Your account is not approved yet. Please wait for admin validation.";
} elseif ($row['is_active'] != 1) {
    $message[] = "Your account has been disabled. Please contact the administrator.";
} else {

                // Store session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'] ?? '';
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_type'] = $row['type'];
                $_SESSION['user_speciality'] = $row['speciality'];

                // Redirect by speciality
                if ($row['speciality'] == 'Informatique' || $row['speciality'] == 'informatique') {
                    header('location: indexinfo.php');
                    exit();
                }

                if ($row['type'] == 'admin') {
                    $_SESSION['admin_notification'] = true;
                    header('location: index.php');
                } else {
                    $_SESSION['dept_notification'] = true; // ➕ NOTIFICATION POUR CHEF DE DÉPARTEMENT
                    header('location: indexx.php');
                }
                
                exit();
            }
        } else {
            $message[] = 'Incorrect email or password. Please try again.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Boumerdes</title>
    <link rel="stylesheet" href="../css/login.css">
    <style>
        /* Custom Styles for the Back Button */
        .btn-back {
            background-color: #5C6BC0; /* A smooth purple */
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            display: inline-block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: blueviolet; /* Darker purple on hover */
            transform: scale(1.05);
        }

        .btn-back:focus {
            outline: none;
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="brand-section">
       
    <div class="logo"> faculty of  <br> <span> <span></span>   science</span></div>
    <h2> Material Delivery System</h2>
        <p>Login to Your Account
        Access the Faculty of Science material delivery system..</p>
                   <!-- Back to Homepage button with improved design -->
                   <a href="../index.html" class="btn-back">Back to Homepage</a>
        <br>
    </div>

    <div class="form-section">
        <?php if (!empty($message)): ?>
            <?php foreach ($message as $msg): ?>
                <div class="message" onclick="this.remove();"><?= $msg ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h3>Sign In</h3>
        <form action="" method="post">
            <div class="input-group">
                <label for="email">University Email</label>
                <input type="email" id="email" name="email" required class="input-field"
                       placeholder="your.name@university.edu">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="input-field"
                       placeholder="Enter your password">
            </div>

            <button type="submit" name="submit" class="btn">Access Project</button>

            

            <p>New team member? <a href="register.php">Register for access</a></p>
        </form>
    </div>
</div>

</body>
</html>
