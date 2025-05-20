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
        if(!empty($p)) {
            $initials .= strtoupper($p[0]);
        }
    }
    return substr($initials, 0, 2);
}
$initials = getInitials($user['name']);

// Message variables
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle password update
    if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);
        
        // Vérifier si les nouveaux mots de passe correspondent
        if ($new_password !== $confirm_password) {
            $error = "❌ Les nouveaux mots de passe ne correspondent pas.";
        }
        // Vérifier la longueur minimale du mot de passe
        else if (strlen($new_password) < 6) {
            $error = "❌ Le nouveau mot de passe doit comporter au moins 6 caractères.";
        }
        // Tout est correct, mettre à jour le mot de passe
        else {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE user_info SET password = ? WHERE id = ?";
            
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "✅ Mot de passe mis à jour avec succès. Vous devrez utiliser votre nouveau mot de passe lors de votre prochaine connexion.";
            } else {
                $error = "❌ Une erreur s'est produite. Veuillez réessayer.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Handle profile update (name and email)
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "❌ Veuillez saisir une adresse email valide.";
        } else {
            // Check if email already exists for another user
            $check_email = "SELECT id FROM user_info WHERE email = ? AND id != ?";
            $stmt = mysqli_prepare($conn, $check_email);
            mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "❌ Cette adresse email est déjà utilisée par un autre compte.";
            } else {
                // Update profile information
                $update = "UPDATE user_info SET name = ?, email = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "✅ Informations du profil mises à jour avec succès.";
                    // Update user data in session
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $initials = getInitials($name);
                } else {
                    $error = "❌ Une erreur s'est produite. Veuillez réessayer.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gestion du Profil</title>
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
            padding: 60px;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.1);
            max-width: 650px;
            width: 100%;
            animation: fadeInUp 0.8s ease-out;
            text-align: center;
        }

        /* Animation for card entry */
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
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 40px;
            background: linear-gradient(135deg, #4a90e2, #6ab7ff);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 54px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
        }

        /* Pulse animation for avatar */
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

        .form-title {
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 30px;
            animation: slideIn 0.6s ease-out both;
            position: relative;
            text-align: left;
        }

        /* Sequential animation for form fields */
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
            margin-bottom: 10px;
            color: #444;
            font-size: 18px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: 1px solid #ccc;
            font-size: 18px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input[type="password"] {
            padding-right: 45px; /* Space for visibility icon */
        }

        input:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.25);
            outline: none;
        }

        .password-strength {
            height: 5px;
            width: 100%;
            background: #e0e0e0;
            margin-top: 10px;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            background: #dc3545; /* Default is weak (red) */
            transition: all 0.5s ease;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 52px;
            cursor: pointer;
            color: #666;
            z-index: 10;
        }

        .buttons-container {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        button, .back-button {
            flex: 1;
            background-color: #4a90e2;
            border: none;
            color: white;
            padding: 18px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 1s ease-out 0.8s both;
        }

        .back-button {
            background-color: #6c757d;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        button:hover, .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        button:hover {
            background-color: #3a7ed1;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        button:active, .back-button:active {
            transform: translateY(0);
        }

        .message {
            text-align: center;
            margin-top: 30px;
            font-weight: 600;
            font-size: 18px;
            animation: bounce 0.5s ease-out;
            padding: 15px;
            border-radius: 10px;
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
            background-color: rgba(40, 167, 69, 0.1);
        }

        .message.error {
            color: #dc3545;
            background-color: rgba(220, 53, 69, 0.1);
        }

        /* Tabs styling */
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }

        .tab {
            padding: 15px 25px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: #4a90e2;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #4a90e2;
        }

        .tab:hover {
            color: #4a90e2;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }

        /* Responsive design */
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
            
            .form-title {
                font-size: 24px;
            }
            
            input[type="text"],
            input[type="email"],
            input[type="password"] {
                font-size: 16px;
                padding: 14px;
            }
            
            button, .back-button {
                font-size: 18px;
                padding: 14px;
            }

            .tab {
                padding: 12px 15px;
                font-size: 16px;
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
            
            .buttons-container {
                flex-direction: column;
            }

            .tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-card">
        <div class="avatar"><?= $initials ?></div>
        
        <h1 class="form-title">Gestion du Profil</h1>

        <div class="tabs">
            <div class="tab active" onclick="openTab('profile')">Informations Personnelles</div>

        </div>

        <?php if ($success): ?>
            <div class="message success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Profile Information Tab -->
        <div id="profile-tab" class="tab-content active">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label for="name"><i class="fas fa-user"></i> Nom Complet</label>
                    <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Adresse Email</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="buttons-container">
                    <a href="profile.php" class="back-button"><i class="fas fa-arrow-left"></i> Retour</a>
                    <button type="submit"><i class="fas fa-save"></i> Mettre à jour</button>
                </div>
            </form>
        </div>

        <!-- Change Password Tab -->
        <div id="password-tab" class="tab-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                
                <div class="form-group">
                    <label for="new_password"><i class="fas fa-key"></i> Nouveau Mot de Passe</label>
                    <input type="password" name="new_password" id="new_password" required onkeyup="checkPasswordStrength()">
                    <div class="password-strength">
                        <div class="password-strength-meter" id="strength-meter"></div>
                    </div>
                    <span class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirmer le Mot de Passe</label>
                    <input type="password" name="confirm_password" id="confirm_password" required onkeyup="checkPasswordMatch()">
                    <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <div class="buttons-container">
                    <a href="profile.php" class="back-button"><i class="fas fa-arrow-left"></i> Retour</a>
                    <button type="submit"><i class="fas fa-save"></i> Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Function to switch tabs
    function openTab(tabName) {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        // Reset all tabs
        tabs.forEach(tab => tab.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        
        // Activate selected tab
        if (tabName === 'profile') {
            document.querySelector('.tab:nth-child(1)').classList.add('active');
            document.getElementById('profile-tab').classList.add('active');
        } else if (tabName === 'password') {
            document.querySelector('.tab:nth-child(2)').classList.add('active');
            document.getElementById('password-tab').classList.add('active');
        }
    }

    // Function to toggle password visibility
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const toggleElement = input.nextElementSibling;
        const icon = toggleElement.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    // Check password strength
    function checkPasswordStrength() {
        const password = document.getElementById('new_password').value;
        const meter = document.getElementById('strength-meter');
        
        // Strength criteria
        const hasLowerCase = /[a-z]/.test(password);
        const hasUpperCase = /[A-Z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        const isLongEnough = password.length >= 8;
        
        // Calculate score
        let score = 0;
        if (hasLowerCase) score++;
        if (hasUpperCase) score++;
        if (hasNumber) score++;
        if (hasSpecialChar) score++;
        if (isLongEnough) score++;
        
        // Update strength meter
        const percentage = (score / 5) * 100;
        meter.style.width = percentage + '%';
        
        // Change color based on strength
        if (percentage <= 20) {
            meter.style.background = '#dc3545'; // Red - very weak
        } else if (percentage <= 40) {
            meter.style.background = '#fd7e14'; // Orange - weak
        } else if (percentage <= 60) {
            meter.style.background = '#ffc107'; // Yellow - medium
        } else if (percentage <= 80) {
            meter.style.background = '#20c997'; // Teal - strong
        } else {
            meter.style.background = '#28a745'; // Green - very strong
        }
    }
    
    // Check if passwords match
    function checkPasswordMatch() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const confirmInput = document.getElementById('confirm_password');
        
        if (confirmPassword === '') {
            confirmInput.style.borderColor = '#ccc';
            confirmInput.style.boxShadow = 'none';
        } else if (newPassword === confirmPassword) {
            confirmInput.style.borderColor = '#28a745';
            confirmInput.style.boxShadow = '0 0 0 3px rgba(40, 167, 69, 0.25)';
        } else {
            confirmInput.style.borderColor = '#dc3545';
            confirmInput.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.25)';
        }
    }

    // Auto-check password strength when page loads (if field has value)
    window.onload = function() {
        if (document.getElementById('new_password').value) {
            checkPasswordStrength();
        }
        if (document.getElementById('confirm_password').value) {
            checkPasswordMatch();
        }
    };
</script>

</body>
</html>