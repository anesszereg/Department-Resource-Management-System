<?php
ob_start();
session_start();
include 'config.php';

// Initialize notification messages
$success_message = '';
$error_message = '';

/**
 * Send email using SMTP
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body
 * @param string $headers Additional headers
 * @return bool Success or failure
 */
function send_smtp_email($to, $subject, $message, $headers) {
    // SMTP configuration
    $smtp_server = 'smtp.gmail.com';
    $smtp_port = 587;
    $smtp_username = 'anesszereg.fs@univ-boumerdes.dz'; // UMBB Equipment System email
    $smtp_password = 'rclh rlip llql oddd'; // Your provided password
    
    // Create socket connection
    $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
    if (!$socket) {
        return false;
    }
    
    // Read server greeting
    if (!server_parse($socket, '220')) {
        return false;
    }
    
    // Say EHLO to server
    fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    if (!server_parse($socket, '250')) {
        return false;
    }
    
    // Request TLS encryption
    fwrite($socket, "STARTTLS\r\n");
    if (!server_parse($socket, '220')) {
        return false;
    }
    
    // Enable TLS encryption
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    
    // Say EHLO again after TLS
    fwrite($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    if (!server_parse($socket, '250')) {
        return false;
    }
    
    // Authenticate
    fwrite($socket, "AUTH LOGIN\r\n");
    if (!server_parse($socket, '334')) {
        return false;
    }
    
    fwrite($socket, base64_encode($smtp_username) . "\r\n");
    if (!server_parse($socket, '334')) {
        return false;
    }
    
    fwrite($socket, base64_encode($smtp_password) . "\r\n");
    if (!server_parse($socket, '235')) {
        return false;
    }
    
    // Set sender
    fwrite($socket, "MAIL FROM: <" . $smtp_username . ">\r\n");
    if (!server_parse($socket, '250')) {
        return false;
    }
    
    // Set recipient
    fwrite($socket, "RCPT TO: <" . $to . ">\r\n");
    if (!server_parse($socket, '250')) {
        return false;
    }
    
    // Send data
    fwrite($socket, "DATA\r\n");
    if (!server_parse($socket, '354')) {
        return false;
    }
    
    // Send email headers and body
    fwrite($socket, "To: " . $to . "\r\n");
    fwrite($socket, "Subject: " . $subject . "\r\n");
    fwrite($socket, $headers . "\r\n");
    fwrite($socket, "\r\n" . $message . "\r\n.\r\n");
    if (!server_parse($socket, '250')) {
        return false;
    }
    
    // Close connection
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
    
    return true;
}

/**
 * Helper function to parse server responses
 */
function server_parse($socket, $expected_response) {
    $response = '';
    while (substr($response, 3, 1) != ' ') {
        if (!($response = fgets($socket, 256))) {
            return false;
        }
    }
    if (substr($response, 0, 3) != $expected_response) {
        return false;
    }
    return true;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_type'] != "admin") {
    header('Location: index.php');
    exit();
}

$edit_mode = false;
$edit_user = ['id' => '', 'name' => '', 'email' => '', 'type' => 'user', 'speciality' => ''];

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM user_info WHERE id = $edit_id");
    if ($result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
    } else {
        echo "User not found!";
        exit();
    }
}

if (isset($_POST['save_user'])) {
    $id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $type = $_POST['type'];
    $speciality = $_POST['speciality'];
    $password = isset($_POST['password']) && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;


    if ($id) {
        // Mise à jour de l'utilisateur
        if ($password) {
            $stmt = $conn->prepare("UPDATE user_info SET name=?, email=?, password=?, type=?, speciality=?, is_active=? WHERE id=?");
            $stmt->bind_param("ssssssi", $name, $email, $password, $type, $speciality, $is_active, $id);
        } else {
            $stmt = $conn->prepare("UPDATE user_info SET name=?, email=?, type=?, speciality=?, is_active=? WHERE id=?");
            $stmt->bind_param("ssssii", $name, $email, $type, $speciality, $is_active, $id);
        }
        $stmt->execute();
    } else {
        // Ajout d'un nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO user_info (name, email, password, type, speciality, approved, is_active) VALUES (?, ?, ?, ?, ?, 0, ?)");
        $stmt->bind_param("sssssi", $name, $email, password_hash($_POST['password'], PASSWORD_DEFAULT), $type, $speciality, $is_active);
        $stmt->execute();
    }

    header('Location: admin_approval.php');
    exit();
}

if (isset($_POST['approve'])) {
    $id = $_POST['user_id'];
    $conn->query("UPDATE user_info SET approved = 1, approval_date = NOW() WHERE id = $id");

    // Enhanced email notification with HTML formatting
    $result = $conn->query("SELECT email, name FROM user_info WHERE id = $id");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $to = $user['email'];
        $subject = "Account Approved - UMBB Equipment System";
        
        // HTML version of the email - Modern design
        $htmlMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
                
                body {
                    font-family: 'Poppins', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f7f9fc;
                    margin: 0;
                    padding: 0;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }
                
                .email-header {
                    background: linear-gradient(135deg, #00c853 0%, #009624 100%);
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .email-header h2 {
                    color: white;
                    margin: 0;
                    font-weight: 600;
                    font-size: 28px;
                    letter-spacing: 0.5px;
                }
                
                .email-header img {
                    width: 80px;
                    height: 80px;
                    margin-bottom: 15px;
                }
                
                .email-body {
                    padding: 30px;
                    color: #4a4a4a;
                }
                
                .greeting {
                    font-size: 20px;
                    font-weight: 500;
                    margin-bottom: 20px;
                    color: #333;
                }
                
                .message-text {
                    font-size: 16px;
                    line-height: 1.7;
                    margin-bottom: 25px;
                }
                
                .cta-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #00c853 0%, #009624 100%);
                    color: white;
                    padding: 14px 30px;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 500;
                    font-size: 16px;
                    margin: 15px 0;
                    text-align: center;
                    box-shadow: 0 4px 10px rgba(0, 200, 83, 0.3);
                    transition: all 0.3s ease;
                }
                
                .cta-button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 15px rgba(0, 200, 83, 0.4);
                }
                
                .email-footer {
                    background-color: #f7f9fc;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #8a94a6;
                    border-top: 1px solid #eaedf3;
                }
                
                .social-links {
                    margin: 15px 0;
                }
                
                .social-links a {
                    display: inline-block;
                    margin: 0 8px;
                    color: #8a94a6;
                    text-decoration: none;
                }
                
                .divider {
                    height: 5px;
                    background: linear-gradient(90deg, #00c853 0%, #009624 100%);
                    margin: 0;
                }
                
                .highlight {
                    color: #00c853;
                    font-weight: 600;
                }
                
                .contact-info {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #eaedf3;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <img src='https://cdn-icons-png.flaticon.com/512/1055/1055683.png' alt='UMBB Logo'>
                    <h2>Account Approved! 🎉</h2>
                </div>
                
                <div class='divider'></div>
                
                <div class='email-body'>
                    <p class='greeting'>Hello, <span class='highlight'>{$user['name']}</span>!</p>
                    
                    <p class='message-text'>Great news! Your account for the <strong>UMBB Equipment System</strong> has been approved by an administrator. You now have full access to all features and services.</p>
                    
                    <p class='message-text'>You can start using the system immediately to manage equipment requests, track your orders, and more.</p>
                    
                    <center>
                        <a href='http://localhost:8888/PFE_BOTT/php/login.php' class='cta-button'>Log In Now</a>
                    </center>
                    
                    <p class='message-text'>If you have any questions or need assistance, our support team is always here to help.</p>
                    
                    <div class='contact-info'>
                        <p>Need help? Contact us at <a href='mailto:support@umbb.dz'>support@umbb.dz</a></p>
                    </div>
                </div>
                
                <div class='email-footer'>
                    <div class='social-links'>
                        <a href='#'>Facebook</a> • 
                        <a href='#'>Twitter</a> • 
                        <a href='#'>Instagram</a>
                    </div>
                    <p>&copy; " . date('Y') . " UMBB Equipment System. All rights reserved.</p>
                    <p>University M'hamed Bougara of Boumerdes, Algeria</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version as fallback
        $plainMessage = "Hello {$user['name']},\n\n" .
                       "We are pleased to inform you that your account has been approved by an administrator. " .
                       "You can now log in to the UMBB Equipment System and access all features.\n\n" .
                       "If you have any questions or need assistance, please don't hesitate to contact our support team.\n\n" .
                       "Login at: http://localhost:8888/PFE_BOTT/php/login.php\n\n" .
                       "Thank you,\nUMBB Equipment System Team";
        
        // Generate a boundary for the multipart message
        $boundary = md5(time());
        
        // Email headers for HTML email
        $headers = "From: UMBB Equipment System <no-reply@umbb.dz>\r\n";
        $headers .= "Reply-To: support@umbb.dz\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"\r\n";
        
        // Email body with both plain text and HTML versions
        $message = "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $plainMessage . "\r\n\r\n";
        
        $message .= "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlMessage . "\r\n\r\n";
        
        $message .= "--" . $boundary . "--";
        
        // Send the email using our SMTP function instead of mail()
        $email_sent = send_smtp_email($to, $subject, $message, $headers);
        
        if ($email_sent) {
            // Set success message in session
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => "User approved and notification email sent to {$user['name']} ({$user['email']})."
            ];
        } else {
            // Set error message in session
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => "User approved but there was a problem sending the notification email to {$user['email']}. Check SMTP settings."
            ];
        }
    } else {
        // User not found error
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => "Error: User information not found."
        ];
    }

    header('Location: admin_approval.php');
    exit();
}

if (isset($_POST['reject'])) {
    $id = $_POST['user_id'];
    $conn->query("UPDATE user_info SET approved = -1 WHERE id = $id");

    // Send email notification for rejection
    $result = $conn->query("SELECT email, name FROM user_info WHERE id = $id");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $to = $user['email'];
        $subject = "Account Application Status - UMBB Equipment System";
        
        // HTML version of the email - Modern design for rejection
        $htmlMessage = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
                
                body {
                    font-family: 'Poppins', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f7f9fc;
                    margin: 0;
                    padding: 0;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
                }
                
                .email-header {
                    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
                    padding: 30px 20px;
                    text-align: center;
                }
                
                .email-header h2 {
                    color: white;
                    margin: 0;
                    font-weight: 600;
                    font-size: 28px;
                    letter-spacing: 0.5px;
                }
                
                .email-header img {
                    width: 80px;
                    height: 80px;
                    margin-bottom: 15px;
                }
                
                .email-body {
                    padding: 30px;
                    color: #4a4a4a;
                }
                
                .greeting {
                    font-size: 20px;
                    font-weight: 500;
                    margin-bottom: 20px;
                    color: #333;
                }
                
                .message-text {
                    font-size: 16px;
                    line-height: 1.7;
                    margin-bottom: 25px;
                }
                
                .reasons-list {
                    background-color: #f9f9f9;
                    padding: 20px 25px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                
                .reasons-list ul {
                    margin: 10px 0;
                    padding-left: 20px;
                }
                
                .reasons-list li {
                    margin-bottom: 10px;
                    position: relative;
                    list-style-type: none;
                    padding-left: 5px;
                }
                
                .reasons-list li:before {
                    content: '•';
                    color: #f44336;
                    font-weight: bold;
                    display: inline-block;
                    width: 1em;
                    margin-left: -1em;
                }
                
                .contact-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #607d8b 0%, #455a64 100%);
                    color: white;
                    padding: 14px 30px;
                    text-decoration: none;
                    border-radius: 50px;
                    font-weight: 500;
                    font-size: 16px;
                    margin: 15px 0;
                    text-align: center;
                    box-shadow: 0 4px 10px rgba(96, 125, 139, 0.3);
                    transition: all 0.3s ease;
                }
                
                .contact-button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 15px rgba(96, 125, 139, 0.4);
                }
                
                .email-footer {
                    background-color: #f7f9fc;
                    padding: 20px;
                    text-align: center;
                    font-size: 14px;
                    color: #8a94a6;
                    border-top: 1px solid #eaedf3;
                }
                
                .social-links {
                    margin: 15px 0;
                }
                
                .social-links a {
                    display: inline-block;
                    margin: 0 8px;
                    color: #8a94a6;
                    text-decoration: none;
                }
                
                .divider {
                    height: 5px;
                    background: linear-gradient(90deg, #f44336 0%, #d32f2f 100%);
                    margin: 0;
                }
                
                .highlight {
                    color: #f44336;
                    font-weight: 600;
                }
                
                .contact-info {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #eaedf3;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <img src='https://cdn-icons-png.flaticon.com/512/1055/1055683.png' alt='UMBB Logo'>
                    <h2>Application Status Update</h2>
                </div>
                
                <div class='divider'></div>
                
                <div class='email-body'>
                    <p class='greeting'>Hello, <span class='highlight'>{$user['name']}</span></p>
                    
                    <p class='message-text'>Thank you for your interest in the <strong>UMBB Equipment System</strong>. After careful review, we regret to inform you that your application has not been approved at this time.</p>
                    
                    <div class='reasons-list'>
                        <p><strong>This could be due to one of the following reasons:</strong></p>
                        <ul>
                            <li>Incomplete or incorrect information provided in your application</li>
                            <li>Unable to verify your affiliation with the institution</li>
                            <li>Your department may not currently be participating in the system</li>
                            <li>The information provided does not meet our current requirements</li>
                        </ul>
                    </div>
                    
                    <p class='message-text'>If you believe this decision was made in error or if you would like to provide additional information, please don't hesitate to contact our administrative team.</p>
                    
                    <center>
                        <a href='mailto:support@umbb.dz' class='contact-button'>Contact Support</a>
                    </center>
                    
                    <div class='contact-info'>
                        <p>For further assistance, contact us at <a href='mailto:support@umbb.dz'>support@umbb.dz</a></p>
                    </div>
                </div>
                
                <div class='email-footer'>
                    <div class='social-links'>
                        <a href='#'>Facebook</a> • 
                        <a href='#'>Twitter</a> • 
                        <a href='#'>Instagram</a>
                    </div>
                    <p>&copy; " . date('Y') . " UMBB Equipment System. All rights reserved.</p>
                    <p>University M'hamed Bougara of Boumerdes, Algeria</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version as fallback
        $plainMessage = "Hello {$user['name']},\n\n" .
                       "We regret to inform you that your application for an account in the UMBB Equipment System has not been approved at this time.\n\n" .
                       "This could be due to one of the following reasons:\n" .
                       "- Incomplete or incorrect information provided\n" .
                       "- Unable to verify your affiliation with the institution\n" .
                       "- Your department may not currently be participating in the system\n\n" .
                       "If you believe this is an error or would like more information, please contact the system administrator for assistance.\n\n" .
                       "UMBB Equipment System Team";
        
        // Generate a boundary for the multipart message
        $boundary = md5(time());
        
        // Email headers for HTML email
        $headers = "From: UMBB Equipment System <" . $smtp_username . ">\r\n";
        $headers .= "Reply-To: support@umbb.dz\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"" . $boundary . "\"\r\n";
        
        // Email body with both plain text and HTML versions
        $message = "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $plainMessage . "\r\n\r\n";
        
        $message .= "--" . $boundary . "\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $message .= $htmlMessage . "\r\n\r\n";
        
        $message .= "--" . $boundary . "--";
        
        // Send the email using our SMTP function
        $email_sent = send_smtp_email($to, $subject, $message, $headers);
        
        if ($email_sent) {
            // Set success message in session
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => "User rejected and notification email sent to {$user['name']} ({$user['email']})."
            ];
        } else {
            // Set error message in session
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => "User rejected but there was a problem sending the notification email to {$user['email']}. Check SMTP settings."
            ];
        }
    } else {
        // User not found error
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => "Error: User information not found."
        ];
    }

    header('Location: admin_approval.php');
    exit();
}

if (isset($_POST['delete'])) {
    $id = intval($_POST['user_id']);
    $conn->query("DELETE FROM user_info WHERE id = $id");
    header('Location: admin_approval.php');
    exit();
}

$pending_users = $conn->query("SELECT * FROM user_info WHERE approved = 0");
$all_users = $conn->query("SELECT * FROM user_info");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f8fa;
            display: flex;
        }

        .section {
            flex: 1;
            padding: 20px;
            margin-left: 0;
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin: 20px;
        }

        h2, h3 {
            color: #333;
            margin-bottom: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        form input[type="text"],
        form input[type="email"],
        form input[type="password"],
        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }

        form button {
            background-color: #007bff;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        li form {
            display: inline;
            margin-bottom: 0;
        }

        a {
            color: #007bff;
            text-decoration: none;
            margin-left: 10px;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            background-color: #fff;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        table td {
            background-color: #f9f9f9;
        }

        button[name="delete"] {
            background-color: red;
            color: white;
            border-radius: 4px;
            padding: 4px 8px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .status-approved {
            color: green;
        }

        .status-rejected {
            color: red;
        }

        .status-pending {
            color: orange;
        }

        .status-active {
            color: green;
        }

        .status-inactive {
            color: red;
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            width: 320px;
        }

        .toast {
            margin-bottom: 10px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transform: translateY(-20px);
            animation: slideIn 0.3s forwards, fadeOut 0.5s 5s forwards;
        }

        .toast-success {
            background-color: #4CAF50;
        }

        .toast-error {
            background-color: #F44336;
        }

        .toast-info {
            background-color: #2196F3;
        }

        .toast-warning {
            background-color: #FF9800;
        }

        .toast-icon {
            margin-right: 10px;
            font-size: 20px;
        }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-20px);
                height: 0;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer">
        <?php
        // Display notification if set
        if (isset($_SESSION['notification'])) {
            $notification = $_SESSION['notification'];
            $type = $notification['type'];
            $message = $notification['message'];
            $icon = ($type === 'success') ? '✅' : '❌';
            
            echo "<div class='toast toast-{$type}'>
                    <span class='toast-icon'>{$icon}</span>
                    {$message}
                  </div>";
            
            // Clear the notification after displaying
            unset($_SESSION['notification']);
        }
        ?>    
    </div>

    <?php include 'sidebar.php'; ?>
   
    <div class="section">
        <h2>➕ Add / Edit User</h2>
        <form method="POST" id="userForm">
            <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
            <input type="text" name="name" id="username" placeholder="Full Name" value="<?php echo $edit_user['name']; ?>" required>
            <input type="email" name="email" id="email" placeholder="Email" value="<?php echo $edit_user['email']; ?>" required>
            <?php if (!$edit_mode): ?>
                <input type="password" name="password" id="password" placeholder="Password" required>
            <?php else: ?>
                <input type="password" name="password" id="password" placeholder="Change Password (leave empty to keep current)">
            <?php endif; ?>
            <select name="speciality" required>
                <option value="" disabled <?php echo $edit_user['speciality'] === '' ? 'selected' : ''; ?>>Select your speciality</option>
                <option value="Computer_Science" <?php echo $edit_user['speciality'] === 'Computer_Science' ? 'selected' : ''; ?>>Computer Science</option>
                <option value="Physics" <?php echo $edit_user['speciality'] === 'Physics' ? 'selected' : ''; ?>>Physics</option>
                <option value="Library" <?php echo $edit_user['speciality'] === 'Library' ? 'selected' : ''; ?>>Library</option>
                <option value="biologie" <?php echo $edit_user['speciality'] === 'biologie' ? 'selected' : ''; ?>>biologie</option>
                <option value="mathematics" <?php echo $edit_user['speciality'] === 'mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                <option value="Agronomy" <?php echo $edit_user['speciality'] === 'Agronomy' ? 'selected' : ''; ?>>Agronomy</option>
                <option value="Sports Science (STAPS)" <?php echo $edit_user['speciality'] === 'Sports Science (STAPS)' ? 'selected' : ''; ?>>sports science (STAPS)</option>
                <option value="Medicine" <?php echo $edit_user['speciality'] === 'Medicine' ? 'selected' : ''; ?>>Medicine</option>
                <option value="Science & Technology" <?php echo $edit_user['speciality'] === 'Science & Technology' ? 'selected' : ''; ?>>Science & Technology</option>
                <option value="Public administration" <?php echo $edit_user['speciality'] === 'Public administration ' ? 'selected' : ''; ?>>Public administration</option>
            </select>
            <select name="type">
                <option value="user" <?php echo $edit_user['type'] === 'user' ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo $edit_user['type'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
            <label>
                <input type="checkbox" name="is_active" value="1" <?php echo isset($edit_user['is_active']) && $edit_user['is_active'] == 1 ? 'checked' : ''; ?>>
                Active
            </label>
            <button type="submit" name="save_user" id="saveUserBtn">
                <?php echo $edit_mode ? 'Update User' : 'Add User'; ?>
            </button>
        </form>

        <br><br><br><br>

        <!-- Users Awaiting Approval -->
        <h3 class="users-awaiting">⏳ Users Awaiting Approval</h3>
        <?php if ($pending_users->num_rows > 0): ?>
            <ul>
                <?php while ($row = $pending_users->fetch_assoc()): ?>
                    <li>
                        <strong><?php echo $row['name']; ?></strong> - <?php echo $row['email']; ?>
                        <div class="action-buttons">
                            <form method="POST" class="action-form">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="approve" class="approve-btn">✅ Approve</button>
                                <button type="submit" name="reject" class="reject-btn">❌ Reject</button>
                            </form>
                            <a href="?edit=<?php echo $row['id']; ?>" class="edit-link">Edit</a>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No users awaiting approval</p>
        <?php endif; ?>

        <br><br><br><br>

        <!-- All Users -->
        <h3 class="users-all">🔄 All Users</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Speciality</th>
                    <th>Registration Date</th>
                    <th>Approval Date</th>
                    <th>Approved</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $all_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['type']; ?></td>
                        <td><?php echo $user['speciality']; ?></td>
                        <td><?php echo $user['registration_date']; ?></td>
                        <td><?php echo $user['approval_date'] ?? '—'; ?></td>
                        <td>
                            <?php
                                if ($user['approved'] == 1) echo '<span class="status-approved">✅</span>';
                                elseif ($user['approved'] == -1) echo '<span class="status-rejected">❌ Refused</span>';
                                else echo '<span class="status-pending">⏳ Pending</span>';
                            ?>
                        </td>
                        <td><?php echo $user['is_active'] == 1 ? 'Yes' : 'No'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?edit=<?php echo $user['id']; ?>" class="edit-link">Edit</a>
                                <form method="POST" class="delete-form">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript for toast notifications -->
    <script>
        // Function to show a toast notification
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            // Set the appropriate icon based on the type
            let icon = '📢';
            if (type === 'success') icon = '✅';
            if (type === 'error') icon = '❌';
            if (type === 'warning') icon = '⚠️';
            
            toast.innerHTML = `<span class="toast-icon">${icon}</span>${message}`;
            
            container.appendChild(toast);
            
            // Auto-remove after animation completes (approx. 5.5 seconds)
            setTimeout(() => {
                toast.remove();
            }, 5500);
        }

        // User form submission
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
            const action = isEditMode ? 'update' : 'add';
            
            if (!isEditMode) {
                const password = document.getElementById('password').value;
                if (!password) {
                    e.preventDefault();
                    showToast('Password is required for new users', 'error');
                    return;
                }
            }
            
            // Let the form submit normally, but store what action we're doing
            localStorage.setItem('userFormAction', action);
        });

        // Check if we need to show a confirmation message after page load
        document.addEventListener('DOMContentLoaded', function() {
            const action = localStorage.getItem('userFormAction');
            if (action === 'add') {
                showToast('User added successfully!', 'success');
            } else if (action === 'update') {
                showToast('User updated successfully!', 'success');
            }
            localStorage.removeItem('userFormAction');
            
            // Add event listeners to all approve buttons
            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // We'll use a timeout to allow form submission to happen
                    setTimeout(() => {
                        showToast('User approved! Sending notification email...', 'success');
                    }, 0);
                });
            });
            
            // Add event listeners to all reject buttons
            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    setTimeout(() => {
                        showToast('User rejected! Sending notification email...', 'warning');
                    }, 0);
                });
            });
            
            // Add event listeners to all delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to delete this user?')) {
                        e.preventDefault();
                        return;
                    }
                    
                    setTimeout(() => {
                        showToast('User deleted successfully!', 'info');
                    }, 0);
                });
            });
            
            // Add event listener to edit links
            document.querySelectorAll('.edit-link').forEach(link => {
                link.addEventListener('click', function() {
                    localStorage.setItem('showEditToast', 'true');
                });
            });
            
            // Show toast if we're in edit mode and came from clicking an edit link
            if (<?php echo $edit_mode ? 'true' : 'false'; ?> && localStorage.getItem('showEditToast')) {
                showToast('Editing user...', 'info');
                localStorage.removeItem('showEditToast');
            }
        });
    </script>
</body>
</html>