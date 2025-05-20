<?php
session_start();
include 'config.php';
include 'sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_info WHERE id = $user_id LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Avatar initials
function getInitials($name) {
    $parts = explode(' ', trim($name));
    $initials = '';
    foreach ($parts as $p) {
        $initials .= strtoupper($p[0]);
    }
    return substr($initials, 0, 2);
}
$initials = getInitials($user['name']);

// Traitement du formulaire
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'] ?? '';

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $update = "UPDATE user_info SET name = '$name', email = '$email', password = '$hashedPassword' WHERE id = $user_id";
    } else {
        $update = "UPDATE user_info SET name = '$name', email = '$email' WHERE id = $user_id";
    }

    if (mysqli_query($conn, $update)) {
        $success = "✅ Profile updated successfully.";
        $query = "SELECT * FROM user_info WHERE id = $user_id LIMIT 1";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
        $initials = getInitials($user['name']);
    } else {
        $error = "❌ Something went wrong. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(145deg, #eef1f7, #dfe8f5);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100vh;
            padding: 20px;
        }

        .profile-card {
            background: #fff;
            padding: 60px;  /* Augmenté de 40px à 60px */
            border-radius: 24px;  /* Augmenté de 20px à 24px */
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
            max-width: 650px;  /* Augmenté de 450px à 650px */
            width: 100%;
            animation: fadeInUp 0.8s ease-out;
            text-align: center;
        }

        /* Animation d'entrée pour le profil */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(60px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .avatar {
            width: 150px;  /* Augmenté de 110px à 150px */
            height: 150px;  /* Augmenté de 110px à 150px */
            border-radius: 50%;
            margin: 0 auto 40px;  /* Augmenté de 30px à 40px */
            background: linear-gradient(135deg, #4a90e2, #6ab7ff);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 54px;  /* Augmenté de 38px à 54px */
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
        }

        /* Animation de pulsation pour l'avatar */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(74, 144, 226, 0.6);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(74, 144, 226, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(74, 144, 226, 0);
            }
        }

        .form-group {
            margin-bottom: 30px;  /* Augmenté de 20px à 30px */
            animation: slideIn 0.6s ease-out both;
        }

        /* Animation pour les champs de formulaire - entrée séquentielle */
        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateX(-30px);
            }
            100% {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group:nth-child(1) {
            animation-delay: 0.2s;
        }

        .form-group:nth-child(2) {
            animation-delay: 0.4s;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;  /* Augmenté de 6px à 10px */
            color: #444;
            text-align: left;
            font-size: 18px;  /* Nouveau - taille de police plus grande */
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 16px;  /* Augmenté de 12px à 16px */
            border-radius: 12px;  /* Augmenté de 10px à 12px */
            border: 1px solid #ccc;
            font-size: 18px;  /* Augmenté de 15px à 18px */
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25);
            outline: none;
        }

        button {
            width: 100%;
            background-color: #4a90e2;
            border: none;
            color: white;
            padding: 18px;  /* Augmenté de 14px à 18px */
            border-radius: 12px;  /* Augmenté de 10px à 12px */
            font-weight: 600;
            font-size: 20px;  /* Augmenté de 16px à 20px */
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;  /* Augmenté de 10px à 20px */
            animation: fadeIn 1s ease-out 0.6s both;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        button:hover {
            background-color: #3a7ed1;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 126, 209, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .message {
            text-align: center;
            margin-top: 30px;  /* Augmenté de 20px à 30px */
            font-weight: 600;
            font-size: 18px;  /* Nouveau - taille de police plus grande */
            animation: bounce 0.5s ease-out;
        }

        @keyframes bounce {
            0%, 20%, 60%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            80% {
                transform: translateY(-5px);
            }
        }

        .message.success {
            color: #28a745;
        }

        .message.error {
            color: #dc3545;
        }

        /* Responsive design amélioré */
        @media (max-width: 768px) {
            .profile-card {
                padding: 40px 30px;
                max-width: 90%;
            }

            .avatar {
                width: 120px;
                height: 120px;
                font-size: 44px;
            }
            
            input[type="text"],
            input[type="email"],
            input[type="password"] {
                font-size: 16px;
                padding: 14px;
            }
            
            button {
                font-size: 18px;
            }
        }

        @media (max-width: 480px) {
            .profile-card {
                padding: 30px 20px;
            }

            .avatar {
                width: 100px;
                height: 100px;
                font-size: 36px;
                margin-bottom: 30px;
            }
            
            label {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-card">
        <div class="avatar"><?= $initials ?></div>

        <form method="POST">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope-open-text"></i> Email Address</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>



            <?php if ($success): ?>
                <div class="message success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div class="message error"><?= $error ?></div>
            <?php endif; ?>
        </form>
    </div>
</div>

</body>
</html>