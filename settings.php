<?php

session_start();

include("db_connect.php");

if(!isset($_SESSION['email'])){

    header("Location: login.php");
    exit();

}


/* User profile fetch */

$email = $_SESSION['email'];

$userQuery = "SELECT * FROM users WHERE email='$email'";

$userResult = mysqli_query($conn, $userQuery);

$user = mysqli_fetch_assoc($userResult);


/* Save Expense */

if(isset($_POST['saveExpense'])){

    $title = $_POST['title'];
    $amount = $_POST['amount'];

    // Category name user se lena
    $category_name = trim($_POST['category_name']);

    $expense_date = $_POST['expense_date'];
    $description = $_POST['description'];

    $user_id = $user['id'];


    /* Check category already exists */

    $categoryQuery = "SELECT category_id
                      FROM categories
                      WHERE category_name='$category_name'";

    $categoryResult = mysqli_query($conn, $categoryQuery);

    $category = mysqli_fetch_assoc($categoryResult);


    if($category){

        // Existing category
        $category_id = $category['category_id'];

    }else{

        // New category
        $insertCategory = "INSERT INTO categories (category_name)
                           VALUES ('$category_name')";

        if(mysqli_query($conn, $insertCategory)){

            $category_id = mysqli_insert_id($conn);

        }else{

            echo "Category not added: " . mysqli_error($conn);
            exit();

        }

    }


    /* Save Expense */

    $sql = "INSERT INTO expenses
            (user_id, title, category_id, amount, expense_date, description)
            VALUES
            ('$user_id', '$title', '$category_id', '$amount', '$expense_date', '$description')";


    if(mysqli_query($conn, $sql)){

        header("Location: settings.php");
        exit();

    }else{

        echo "Expense not saved: " . mysqli_error($conn);

    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>

<link rel="stylesheet" href="settings.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>
<body>

<div class="sidebar">

<div>

<a href="index.html" class="logo-link">
<img src="uploads\ChatGPT Image Aug 2, 2026, 08_31_48 PM.png" alt="TrackMySpend Logo">
</a>

<a href="index.php">
<i class="fa fa-home"></i> Dashboard
</a>

<a href="expenses.php">
<i class="fa fa-wallet"></i> Expenses
</a>

<a href="report.php">
<i class="fa fa-chart-pie"></i> Reports
</a>

<a href="settings.php" class="active">
<i class="fa fa-gear"></i> Settings
</a>

</div>

<div class="bottom-menu">

<button class="add-btn" onclick="openPopup()">
<i class="fa fa-plus-circle"></i> Add Expense
</button>

<a href="logout.php"
   class="logout"
   onclick="return confirmLogout();">

    <i class="fa fa-right-from-bracket"></i> Logout

</a>

</div>

</div>

<div class="main">

<div class="topbar">

<input type="text" placeholder="Search Settings...">

<div class="profile">

    <i class="fa fa-bell"></i>

    <div class="profile-menu">

        <div class="profile-icon" onclick="toggleProfileMenu()">

            <?php if(!empty($user['profile'])){ ?>

                <img src="<?php echo $user['profile']; ?>" alt="Profile">

            <?php }else{ ?>

                <i class="fa fa-user"></i>

            <?php } ?>

        </div>

        <div class="profile-dropdown" id="profileDropdown">

            <h4><?php echo $user['full_name']; ?></h4>

            <p><?php echo $user['email']; ?></p>

            <form method="POST" enctype="multipart/form-data">

                <input type="file"
                       name="profile_image"
                       id="profileInput"
                       accept="image/*"
                       required>

                <button type="submit" name="uploadProfile">
                    <?php
                    echo !empty($user['profile'])
                    ? "Change Profile Picture"
                    : "Upload Profile Picture";
                    ?>
                </button>

            </form>

        </div>

    </div>

</div>
</div>

<h1>Settings</h1>

<!-- Profile Card -->

<div class="setting-card">

<h3><i class="fa fa-user"></i> Profile Information</h3>

<div class="profile-section">

<img src="<?php echo !empty($user['profile']) ? $user['profile'] : 'default-profile.png'; ?>"
     class="profile-pic"
     alt="Profile">

<div>

<h2><?php echo htmlspecialchars($user['full_name']); ?></h2>

<p><?php echo htmlspecialchars($user['email']); ?></p>

</div>

</div>

</div>

<!-- Personal Information -->

<div class="setting-card">

<h3><i class="fa fa-id-card"></i> Personal Information</h3>

<div class="setting-grid">

<div>

<label>Full Name</label>

<input type="text"
       name="full_name"
       value="<?php echo $user['full_name']; ?>">
</div>

<div>

<label>Email</label>

<input type="email"
       name="email"
       value="<?php echo $user['email']; ?>">
</div>

<div>

<label>Phone</label>

<input type="text"
       name="phone"
       value="<?php echo $user['phone']; ?>">
</div>

<div>

<label>Country</label>

<input type="text" value="India">

</div>

</div>

</div>

<!-- Password -->

<div class="setting-card">

<h3><i class="fa fa-lock"></i> Change Password</h3>

<div class="setting-grid">

<div>

<label>Current Password</label>

<input type="password">

</div>

<div>

<label>New Password</label>

<input type="password">

</div>

<div>

<label>Confirm Password</label>

<input type="password">

</div>

</div>

</div>

<!-- Preferences -->

<div class="setting-card">

<h3><i class="fa fa-sliders"></i> Preferences</h3>

<div class="toggle-row">

<span>Dark Mode</span>

<label class="switch">
<input type="checkbox">
<span class="slider"></span>
</label>

</div>

<div class="toggle-row">

<span>Email Notifications</span>

<label class="switch">
<input type="checkbox" checked>
<span class="slider"></span>
</label>

</div>

</div>

<button class="save-btn">

<i class="fa fa-floppy-disk"></i>

Save Changes

</button>

</div>

<script>

const toggle = document.querySelector('.switch input');

// Page load hone par
if(localStorage.getItem("theme")=="dark"){
    document.body.classList.add("dark-mode");
    toggle.checked=true;
}

toggle.addEventListener("change",function(){

    if(this.checked){

        document.body.classList.add("dark-mode");

        localStorage.setItem("theme","dark");

    }else{

        document.body.classList.remove("dark-mode");

        localStorage.setItem("theme","light");

    }

});

</script>
<div class="popup" id="expensePopup">

    <div class="popup-content">

        <span class="close" onclick="closePopup()">&times;</span>

        <h2>Add Expense</h2>

        <form method="POST">

            <input
                type="text"
                name="title"
                placeholder="Expense Title"
                required
            >

            <input
                type="number"
                name="amount"
                placeholder="Amount"
                required
            >

            <div class="category-box">

    <input type="text"
       name="category_name"
       list="categoryList"
       placeholder="Search or enter category"
       required>

<datalist id="categoryList">

<?php

$categoryQuery = "SELECT * FROM categories ORDER BY category_name ASC";
$categoryResult = mysqli_query($conn, $categoryQuery);

while($cat = mysqli_fetch_assoc($categoryResult)){

?>

<option value="<?php echo htmlspecialchars($cat['category_name']); ?>">

<?php

}

?>

</datalist>

</div>

            <input
                type="date"
                name="expense_date"
                required
            >

            <textarea
                name="description"
                placeholder="Description"
            ></textarea>

            <button
                type="submit"
                name="saveExpense"
                class="save-expense"
            >
                Save Expense
            </button>

        </form>

    </div>

</div>
<script>

function openPopup(){

    document.getElementById("expensePopup").style.display = "flex";

}

function closePopup(){

    document.getElementById("expensePopup").style.display = "none";

}

</script>
<script>
function toggleProfileMenu(){

    var menu = document.getElementById("profileDropdown");

    menu.classList.toggle("show");

}
</script>
<script>

function confirmLogout(){

    return confirm("Are you sure you want to logout?");

}

</script>
</body>
</html>