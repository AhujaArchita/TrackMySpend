<?php

include("db_connect.php");

if(isset($_POST['register'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if($password != $confirmPassword){

        $message = "Passwords do not match!";

    }else{
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(full_name,email,phone,password)
                VALUES('$name','$email','$phone','$password')";

        if(mysqli_query($conn,$sql)){

            header("Location: login.php");
            exit();

        }else{

            $message = "Registration Failed";

        }

    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>TrackMySpend - Register</title>

<link rel="stylesheet" href="register.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>
<body>

<div class="container">

    <div class="left">

        <h1>TrackMySpend</h1>

        <h2>Create Account ✨</h2>

        <p>
            Create your account to manage your expenses,
            track spending and generate reports.
        </p>

    </div>

    <div class="right">

        <div class="register-box">

            <h2>Register</h2>

            <form action="register.php" method="POST">

                <div class="input-box">

                    <i class="fa-solid fa-user"></i>

                    <input
                    type="text" id="name" name="name"
                    placeholder="Full Name"
                    required>

                </div>

                <div class="input-box">

                    <i class="fa-solid fa-envelope"></i>

                    <input
                    type="email" id="email" name="email"
                    placeholder="Email Address"
                    required>

                </div>

                <div class="input-box">

                    <i class="fa-solid fa-phone"></i>

                    <input
                    type="text" id="phone" name="phone"
                    placeholder="Phone Number"
                    required>

                </div>

                <div class="input-box">

                    <i class="fa-solid fa-lock"></i>

                    <input
                    type="password"
                    id="password" name="password"
                    placeholder="Password"
                    required>

                    <i class="fa-solid fa-eye eye"
                    onclick="showPassword('password')"></i>

                </div>

                <div class="input-box">

                    <i class="fa-solid fa-lock"></i>

                    <input
                    type="password"
                    id="confirmPassword" name="confirmPassword"
                    placeholder="Confirm Password"
                    required>

                    <i class="fa-solid fa-eye eye"
                    onclick="showPassword('confirmPassword')"></i>

                </div>

                <button type="submit" name="register">
                    Create Account
                </button>
                <p style="text-align:center;color:red;margin-top:15px;">

                <?php

                if(isset($message)){

                    echo $message;

                }

                ?>

                </p>

                <p class="login">

                    Already have an account?

                    <a href="login.php">
                        Login
                    </a>

                </p>

            </form>

        </div>

    </div>

</div>

<script>

function showPassword(id){

    var input=document.getElementById(id);

    if(input.type==="password"){

        input.type="text";

    }else{

        input.type="password";

    }

}

</script>
</body>
</html>