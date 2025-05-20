<?php
include 'config.php';
session_start();

if (isset($_POST['submit'])) {
   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']); 
   $pass = mysqli_real_escape_string($conn, md5($_POST['password'])); 
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
   $speciality = mysqli_real_escape_string($conn, $_POST['speciality']); 
   $select = mysqli_query($conn, "SELECT * FROM `user_info` WHERE email = '$email'") or die('query failed');
   if (mysqli_num_rows($select) > 0) {
      $message[] = 'User already exists!';
   } else {
 
      if ($pass != $cpass) {
         $message[] = 'Passwords do not match!';
      } else {
          
         $insert = mysqli_query($conn, "INSERT INTO `user_info`(name, email, password, approved,speciality) VALUES('$name', '$email', '$pass', 0 ,'$speciality')") or die('query failed');

         if ($insert) {
           
            $message[] = 'Registered successfully! Waiting for admin approval.';
           
            header("refresh:2;url=login.php");
         } else {
            $message[] = 'Registration failed!';
         }
      }
   }
}
?>
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
            background-color: #3f4f9f; /* Darker purple on hover */
            transform: scale(1.05);
        }

        .btn-back:focus {
            outline: none;
        }
    </style>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Boumerdes</title>
    <link rel="stylesheet" href="../css/register.css">
</head>

<body>

    <div class="container">
        <div class="brand-section">
        
            <div class="logo"> faculty of  <br> <span> <span></span>   science</span></div>
            <h2> Material Delivery System</h2>
            <p>Create an account to request and manage equipment deliveries for your department. Join the platform and help streamline academic resource distribution.</p>
                   <!-- Back to Homepage button with improved design -->
        <a href="../index.html" class="btn-back">Back to Homepage</a>
        <br>
        </div>

        <div class="form-section">
            <?php
      if(isset($message)){
         foreach($message as $msg){
            $class = (strpos($msg, 'successfully') !== false) ? 'success-message' : 'error-message';
            echo '<div class="message ' . $class . '" onclick="this.remove();">'.$msg.'</div>';
         }
      }
      ?>

            <h3>Create Account</h3>

            <form action="" method="post">
                <div class="input-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required class="input-field"
                        placeholder="Enter your full name">
                </div>

                <div class="input-group">
                    <label for="email">University Email</label>
                    <input type="email" id="email" name="email" required class="input-field"
                        placeholder="your.name@university.edu">
                </div>
                <div class="input-group">
    <label for="speciality">Speciality</label>
    <select id="speciality" name="speciality" required class="input-field">
        <option value="" disabled selected>Select your speciality</option>
        <option value="Computer_Science">Computer Science</option>
        <option value="Physics">Physics</option>
        <option value="Library">Library</option>
        <option value="Biology">Biology</option>
        <option value="Mathematics">Mathematics</option>
        <option value="Agronomy">Agronomy</option>
        <option value="STAPS">Sports Science (STAPS)</option>
        <option value="Medicine">Medicine</option>
        <option value="Science_Technology">Science & Technology</option>
        <option value="Administration">Public Administration</option>
    </select>
</div>

    <style>
    select.input-field {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 2.5rem;
    cursor: pointer;
}

select.input-field option {
    padding: 0.5rem;
}
</style>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required class="input-field"
                        placeholder="Create a password">
                    <div class="password-requirements">
                        Must be at least 8 characters long
                    </div>
                </div>

                <div class="input-group">
                    <label for="cpassword">Confirm Password</label>
                    <input type="password" id="cpassword" name="cpassword" required class="input-field"
                        placeholder="Confirm your password">
                </div>

                <button type="submit" name="submit" class="btn">Create Account</button>

                <p>Already have an account? <a href="login.php">Sign in</a></p>
            </form>
        </div>
    </div>

</body>

</html>