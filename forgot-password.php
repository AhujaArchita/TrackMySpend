<?php

session_start();
include("db_connect.php");

$message = "";
$messageColor = "";

if(isset($_POST['resetPassword'])){

    $email = $_POST['email'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    /* Check password */

    if($newPassword !== $confirmPassword){

        $message = "Passwords do not match!";
        $messageColor = "red";

    }else{

        /* Check email */

        $sql = "SELECT id FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0){

            /* Update password */

            $newPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $update = "UPDATE users
                SET password='$newPassword'
                WHERE email='$email'";

            if(mysqli_query($conn, $update)){

                $message = "Password reset successfully!";

                $messageColor = "green";

            }else{

                $message = "Something went wrong.";
                $messageColor = "red";

            }

        }else{

            $message = "Email not registered!";
            $messageColor = "red";

        }

    }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password</title>

<link rel="stylesheet" href="forgot-password.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>

<div class="container">

<div class="left">

<h1>TrackMySpend</h1>

<h2>Forgot Password?</h2>

<p>

Enter your registered email address.
We'll help you reset your password.

</p>

</div>

<div class="right">

<div class="forgot-box">

<h2>Reset Password</h2>

<form method="POST">

    <div class="input-box">

        <i class="fa-solid fa-envelope"></i>

        <input
            type="email"
            name="email"
            placeholder="Registered Email"
            required>

    </div>


    <div class="input-box">

        <i class="fa-solid fa-lock"></i>

        <input
            type="password"
            name="newPassword"
            placeholder="New Password"
            required>

    </div>


    <div class="input-box">

        <i class="fa-solid fa-lock"></i>

        <input
            type="password"
            name="confirmPassword"
            placeholder="Confirm Password"
            required>

    </div>


    <button type="submit" name="resetPassword">

        Reset Password

    </button>


    <?php if($message != ""){ ?>

        <p style="color:<?php echo $messageColor; ?>;
                  text-align:center;
                  margin-top:15px;">

            <?php echo $message; ?>

        </p>

    <?php } ?>


    <p class="back">

        <a href="login.php">
            ← Back to Login
        </a>

    </p>

</form>

</div>

</div>

</div>

</body>

</html>