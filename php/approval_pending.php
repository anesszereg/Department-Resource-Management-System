<?php
session_start();
include 'config.php'; // Ensure your database connection is included

// Check if the user is logged in and not approved
if (!isset($_SESSION['user_id'])) {
   header("Location: login.php");
   exit();
}

$user_id = $_SESSION['user_id'];
$select = mysqli_query($conn, "SELECT * FROM `user_info` WHERE id = '$user_id'") or die('query failed');
$row = mysqli_fetch_assoc($select);

if ($row['approved'] == 1) {
   // If the user is already approved, redirect to the dashboard or main page
   header("Location: index.php");
   exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Pending</title>
    <style>
    :root {
        --primary: #2e4c6d;
        --primary-dark: #1e3a5f;
        --accent: #fc7753;
        --background: #f7f9fc;
        --text: #2c3e50;
        --text-light: #64748b;
        --error: #e74c3c;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: var(--background);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .container {
        display: flex;
        overflow: hidden;
        border-radius: 1rem;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        max-width: 900px;
        width: 100%;
        background-color: white;
    }

    .brand-section {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        width: 50%;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .brand-section::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .brand-section::after {
        content: "";
        position: absolute;
        bottom: -80px;
        left: -80px;
        width: 300px;
        height: 300px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }

    .logo {
        margin-bottom: 2rem;
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: 1px;
        position: relative;
        z-index: 1;
    }

    .logo span {
        color: var(--accent);
    }

    .brand-section h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }

    .brand-section p {
        opacity: 0.9;
        line-height: 1.6;
        position: relative;
        z-index: 1;
    }

    .form-section {
        width: 50%;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .message {
        padding: 1rem;
        background-color: #fef2f2;
        border-left: 4px solid var(--error);
        color: var(--error);
        margin-bottom: 1.5rem;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: opacity 0.3s ease;
    }

    h3 {
        color: var(--text);
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    p {
        color: var(--text-light);
        text-align: center;
    }

    a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    a:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }

        .brand-section,
        .form-section {
            width: 100%;
        }

        .brand-section {
            padding: 2rem;
        }

        .form-section {
            padding: 2rem;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="brand-section">
            <div class="logo">Boum<span>Erdes</span></div>
            <h1>Welcome to Boumerdes</h1>
            <p>Sign in to access your university and continue your academic journey with us.</p>
        </div>

        <div class="form-section">
            <h3>Approval Pending</h3>
            <div class="message">
                Your account is pending approval. Please contact the admin for further assistance.
            </div>
            <p>If you have any questions, please <a href="mailto:admin@Boumerdes.com">contact the admin</a>.</p>
            <a href="index.php?logout=<?php echo $user_id; ?>"
                onclick="return confirm('Are you sure you want to logout?');" class="logout-btn"
                style="width: 100%; justify-content: center;">
                <i class="fas fa-sign-out-alt"></i>
                <h4>Logout</h4>
            </a>
        </div>

    </div>
</body>

</html>