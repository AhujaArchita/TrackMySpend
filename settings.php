<?php

session_start();

include("db_connect.php");

if(!isset($_SESSION['email'])){
    header("Location: login.php");
    exit();
}


/* =========================
   User Profile Fetch
========================= */

$email = $_SESSION['email'];

$userQuery = "SELECT * FROM users WHERE email='$email'";
$userResult = mysqli_query($conn, $userQuery);

$user = mysqli_fetch_assoc($userResult);

$user_id = $user['id'];


/* =========================
   Upload Profile Picture
========================= */

if(isset($_POST['uploadProfile'])){

    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0){

        $fileName = $_FILES['profile_image']['name'];
        $tmpName = $_FILES['profile_image']['tmp_name'];

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if(!in_array($extension, $allowed)){

            echo "<script>
                    alert('Only JPG, JPEG, PNG, GIF or WEBP images are allowed.');
                  </script>";

        }else{

            $newFileName = "profile_" . $user_id . "_" . time() . "." . $extension;

            $uploadFolder = "uploads/profile/";

            if(!is_dir($uploadFolder)){
                mkdir($uploadFolder, 0777, true);
            }

            $uploadPath = $uploadFolder . $newFileName;

            if(move_uploaded_file($tmpName, $uploadPath)){

                $profilePath = mysqli_real_escape_string($conn, $uploadPath);

                $profileQuery = "UPDATE users
                                 SET profile='$profilePath'
                                 WHERE id='$user_id'";

                if(mysqli_query($conn, $profileQuery)){

                    header("Location: settings.php");
                    exit();

                }else{

                    echo "Profile not saved: " . mysqli_error($conn);
                    exit();

                }

            }else{

                echo "<script>
                        alert('Profile image upload failed.');
                      </script>";

            }
        }

    }else{

        echo "<script>
                alert('Please select a profile picture.');
              </script>";

    }
}


/* =========================
   Save Settings
========================= */

if(isset($_POST['saveSettings'])){

    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $balance = trim($_POST['balance']);

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];


    /* Update Name, Phone and Starting Balance */

    $updateQuery = "UPDATE users 
                    SET full_name='$full_name',
                        phone='$phone',
                        balance='$balance'
                    WHERE id='$user_id'";

    if(!mysqli_query($conn, $updateQuery)){

        echo "Settings not updated: " . mysqli_error($conn);
        exit();

    }


    /* =========================
       Password Change
    ========================= */

    if(!empty($current_password) || 
       !empty($new_password) || 
       !empty($confirm_password)){

        if(empty($current_password) || 
           empty($new_password) || 
           empty($confirm_password)){

            echo "<script>
                    alert('Please fill all password fields.');
                  </script>";

        }elseif($new_password != $confirm_password){

            echo "<script>
                    alert('New password and confirm password do not match.');
                  </script>";

        }else{

            /* Check Current Password */

            if(password_verify($current_password, $user['password'])){

                $newPassword = password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );

                $passwordQuery = "UPDATE users
                                  SET password='$newPassword'
                                  WHERE id='$user_id'";

                if(mysqli_query($conn, $passwordQuery)){

                    echo "<script>
                            alert('Password changed successfully.');
                          </script>";

                }else{

                    echo "Password not updated: " . mysqli_error($conn);
                    exit();

                }

            }else{

                echo "<script>
                        alert('Current password is incorrect.');
                      </script>";

            }

        }

    }


    /* Refresh User Data */

    $userQuery = "SELECT * FROM users WHERE id='$user_id'";
    $userResult = mysqli_query($conn, $userQuery);
    $user = mysqli_fetch_assoc($userResult);


    echo "<script>
            alert('Settings saved successfully.');
          </script>";
}


/* =========================
   Save Expense
========================= */

if(isset($_POST['saveExpense'])){

    $title = $_POST['title'];
    $amount = $_POST['amount'];

    $category_name = trim($_POST['category_name']);

    $expense_date = $_POST['expense_date'];
    $description = $_POST['description'];

    $user_id = $user['id'];


    /* Check Category Already Exists */

    $categoryQuery = "SELECT category_id
                      FROM categories
                      WHERE category_name='$category_name'";

    $categoryResult = mysqli_query($conn, $categoryQuery);

    $category = mysqli_fetch_assoc($categoryResult);


    if($category){

        /* Existing Category */

        $category_id = $category['category_id'];

    }else{

        /* New Category */

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


<!-- =========================
     Sidebar
========================= -->

<div class="sidebar">

    <div>

        <a href="index.php" class="logo-link">

            <img
                src="uploads/ChatGPT Image Aug 2, 2026, 08_31_48 PM.png"
                alt="TrackMySpend Logo"
            >

        </a>


        <a href="index.php">

            <i class="fa fa-home"></i>
            Dashboard

        </a>


        <a href="expenses.php">

            <i class="fa fa-wallet"></i>
            Expenses

        </a>


        <a href="report.php">

            <i class="fa fa-chart-pie"></i>
            Reports

        </a>


        <a href="settings.php" class="active">

            <i class="fa fa-gear"></i>
            Settings

        </a>

    </div>


    <div class="bottom-menu">

        <button class="add-btn" onclick="openPopup()">

            <i class="fa fa-plus-circle"></i>
            Add Expense

        </button>


        <a
            href="logout.php"
            class="logout"
            onclick="return confirmLogout();"
        >

            <i class="fa fa-right-from-bracket"></i>
            Logout

        </a>

    </div>

</div>



<!-- =========================
     Main
========================= -->

<div class="main">


    <!-- Topbar -->

    <div class="topbar">

        <div class="profile">

            <i class="fa fa-bell"></i>


            <div class="profile-menu">


                <!-- Profile Icon -->

                <div
                    class="profile-icon"
                    onclick="toggleProfileMenu()"
                >

                    <?php if(
                        !empty($user['profile']) &&
                        file_exists($user['profile'])
                    ){ ?>

                        <img
                            src="<?php echo htmlspecialchars($user['profile']); ?>"
                            alt="Profile"
                        >

                    <?php }else{ ?>

                        <i class="fa fa-user"></i>

                    <?php } ?>

                </div>



                <!-- Profile Dropdown -->

                <div
                    class="profile-dropdown"
                    id="profileDropdown"
                >

                    <h4>
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </h4>


                    <p>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="file"
                            name="profile_image"
                            id="profileInput"
                            accept="image/*"
                            required
                        >


                        <button
                            type="submit"
                            name="uploadProfile"
                        >

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



    <!-- Page Heading -->

    <h1>Settings</h1>



    <!-- =========================
         Profile Card
    ========================= -->

    <div class="setting-card">

        <h3>

            <i class="fa fa-user"></i>
            Profile Information

        </h3>


        <div class="profile-section">


            <?php if(
                !empty($user['profile']) &&
                file_exists($user['profile'])
            ){ ?>

                <img
                    src="<?php echo htmlspecialchars($user['profile']); ?>"
                    class="profile-pic"
                    alt="Profile"
                >

            <?php }else{ ?>

                <div class="profile-pic default-profile">

                    <i class="fa fa-user"></i>

                </div>

            <?php } ?>


            <div>

                <h2>
                    <?php echo htmlspecialchars($user['full_name']); ?>
                </h2>


                <p>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>

            </div>

        </div>

    </div>



    <!-- =========================
         Personal Information
    ========================= -->

    <div class="setting-card">

        <h3>

            <i class="fa fa-id-card"></i>
            Personal Information

        </h3>


        <div class="setting-grid">


            <div>

                <label>Full Name</label>

                <input
                    type="text"
                    name="full_name"
                    value="<?php echo htmlspecialchars($user['full_name']); ?>"
                    form="settingsForm"
                >

            </div>


            <div>

                <label>Email</label>

                <input
                    type="email"
                    value="<?php echo htmlspecialchars($user['email']); ?>"
                    readonly
                >

            </div>


            <div>

                <label>Phone</label>

                <input
                    type="text"
                    name="phone"
                    value="<?php echo htmlspecialchars($user['phone']); ?>"
                    form="settingsForm"
                >

            </div>


            <div>

                <label>Country</label>

                <input
                    type="text"
                    value="India"
                    readonly
                >

            </div>

        </div>

    </div>



    <!-- =========================
         Balance Information
    ========================= -->

    <div class="setting-card">

        <h3>

            <i class="fa fa-wallet"></i>
            Balance Information

        </h3>


        <div class="setting-grid">


            <div>

                <label>Starting Balance</label>

                <input
                    type="number"
                    name="balance"
                    value="<?php echo htmlspecialchars($user['balance'] ?? ''); ?>"
                    placeholder="Enter Starting Balance"
                    min="0"
                    step="0.01"
                    form="settingsForm"
                >

            </div>

        </div>

    </div>



    <!-- =========================
         Password
    ========================= -->

    <div class="setting-card">

        <h3>

            <i class="fa fa-lock"></i>
            Change Password

        </h3>


        <div class="setting-grid">


            <div>

                <label>Current Password</label>

                <input
                    type="password"
                    name="current_password"
                    placeholder="Enter current password"
                    form="settingsForm"
                >

            </div>


            <div>

                <label>New Password</label>

                <input
                    type="password"
                    name="new_password"
                    placeholder="Enter new password"
                    form="settingsForm"
                >

            </div>


            <div>

                <label>Confirm Password</label>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    form="settingsForm"
                >

            </div>

        </div>

    </div>



    <!-- =========================
         Preferences
    ========================= -->

    <div class="setting-card">

        <h3>

            <i class="fa fa-sliders"></i>
            Preferences

        </h3>


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

                <input
                    type="checkbox"
                    checked
                >

                <span class="slider"></span>

            </label>

        </div>

    </div>



    <!-- Settings Form -->

    <form
        method="POST"
        id="settingsForm"
    >

    </form>


    <button
        type="submit"
        name="saveSettings"
        class="save-btn"
        form="settingsForm"
    >

        <i class="fa fa-floppy-disk"></i>

        Save Changes

    </button>


</div>



<!-- =========================
     Dark Mode Script
========================= -->

<script>

const toggle = document.querySelector('.switch input');


/* Page load */

if(localStorage.getItem("theme") == "dark"){

    document.body.classList.add("dark-mode");

    toggle.checked = true;

}


/* Change Theme */

toggle.addEventListener("change", function(){

    if(this.checked){

        document.body.classList.add("dark-mode");

        localStorage.setItem("theme", "dark");

    }else{

        document.body.classList.remove("dark-mode");

        localStorage.setItem("theme", "light");

    }

});

</script>



<!-- =========================
     Add Expense Popup
========================= -->

<div
    class="popup"
    id="expensePopup"
>

    <div class="popup-content">


        <span
            class="close"
            onclick="closePopup()"
        >

            &times;

        </span>


        <h2>Add Expense</h2>


        <form
            method="POST"
            action="settings.php"
        >


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


                <input
                    type="text"
                    name="category_name"
                    list="categoryList"
                    placeholder="Search or enter category"
                    required
                >


                <datalist id="categoryList">

                    <?php

                    $categoryQuery =
                        "SELECT * FROM categories
                         ORDER BY category_name ASC";

                    $categoryResult =
                        mysqli_query($conn, $categoryQuery);


                    while(
                        $cat = mysqli_fetch_assoc($categoryResult)
                    ){

                    ?>

                        <option
                            value="<?php echo htmlspecialchars($cat['category_name']); ?>"
                        >

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



<!-- =========================
     Popup Script
========================= -->

<script>

function openPopup(){

    document.getElementById("expensePopup").style.display = "flex";

}


function closePopup(){

    document.getElementById("expensePopup").style.display = "none";

}

</script>



<!-- =========================
     Profile Dropdown Script
========================= -->

<script>

function toggleProfileMenu(){

    const dropdown =
        document.getElementById("profileDropdown");


    if(dropdown.style.display === "block"){

        dropdown.style.display = "none";

    }else{

        dropdown.style.display = "block";

    }

}


/* Click outside dropdown */

document.addEventListener("click", function(event){

    const dropdown =
        document.getElementById("profileDropdown");

    const profileIcon =
        document.querySelector(".profile-icon");


    if(
        dropdown &&
        profileIcon &&
        !dropdown.contains(event.target) &&
        !profileIcon.contains(event.target)
    ){

        dropdown.style.display = "none";

    }

});

</script>



<!-- =========================
     Logout
========================= -->

<script>

function confirmLogout(){

    return confirm("Are you sure you want to logout?");

}

</script>


</body>

</html>