<?php

session_start();
include("db_connect.php");

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";

$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) > 0){

    $user = mysqli_fetch_assoc($result);

    if(password_verify($password, $user['password'])){

        $_SESSION['email'] = $user['email'];

        header("Location:index.php");
        exit();

    }else{

        $error = "Invalid Email or Password";

    }

}else{

    $error = "Invalid Email or Password";

}

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TrackMySpend - Login</title>

<link rel="stylesheet" href="login.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>
<body>

<div class="container">

<div class="left">

<h1>TrackMySpend</h1>

<h2>Welcome Back 👋</h2>

<p>
Login to manage your daily expenses,
track your spending and view reports.
</p>

</div>

<div class="right">

<div class="login-box">

<h2>Login</h2>

<form action="login.php" method="POST">

<div class="input-box">

<i class="fa-solid fa-envelope"></i>

<input type="email" id="email" name="email"
placeholder="Email Address"
required>

</div>

<div class="input-box">

<i class="fa-solid fa-lock"></i>

<input
type="password"
id="password" name="password"
placeholder="Password"
required>

<i
class="fa-solid fa-eye eye"
onclick="showPassword()">
</i>

</div>

<div class="options">

<label>

<input type="checkbox">

Remember Me

</label>

<a href="forgot-password.php">Forgot Password?</a>

</div>

<button type="submit" name="login">

Login

</button>

<p style="color:red;text-align:center;margin-top:10px;">

<?php

if(isset($error)){
    echo $error;
}

?>

</p>

<p class="register">

Don't have an account?

<a href="register.php">

Register

</a>

</p>

</form>

</div>

</div>

</div>

<script>

function showPassword(){

    var pass=document.getElementById("password");

    if(pass.type==="password"){
        pass.type="text";
    }else{
        pass.type="password";
    }

}
</script>

</body>
</html>