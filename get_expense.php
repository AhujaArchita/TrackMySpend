<?php

session_start();

include("db_connect.php");

header('Content-Type: application/json');

if(!isset($_SESSION['email'])){

    echo json_encode([
        "success" => false
    ]);

    exit();

}

$email = $_SESSION['email'];

/* Logged-in user */

$userQuery = "SELECT id
              FROM users
              WHERE email='$email'";

$userResult = mysqli_query($conn, $userQuery);

$user = mysqli_fetch_assoc($userResult);

if(!$user){

    echo json_encode([
        "success" => false
    ]);

    exit();

}

$user_id = $user['id'];

$expense_id = $_GET['id'];


/* Fetch expense */

$sql = "SELECT expenses.*,
               categories.category_name

        FROM expenses

        LEFT JOIN categories
        ON expenses.category_id = categories.category_id

        WHERE expenses.expense_id='$expense_id'
        AND expenses.user_id='$user_id'";

$result = mysqli_query($conn, $sql);

$expense = mysqli_fetch_assoc($result);


if($expense){

    echo json_encode([
        "success" => true,
        "expense" => $expense
    ]);

}else{

    echo json_encode([
        "success" => false
    ]);

}

?>