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

        header("Location: report.php");
        exit();

    }else{

        echo "Expense not saved: " . mysqli_error($conn);

    }

}
/* Report Data */

$user_id = $user['id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

/* Total Expense */

$totalQuery = "SELECT SUM(amount) AS total
               FROM expenses
               WHERE user_id='$user_id'";

$totalResult = mysqli_query($conn, $totalQuery);
$totalData = mysqli_fetch_assoc($totalResult);

$totalExpense = $totalData['total'] ?? 0;


/* This Month Expense */

$monthQuery = "SELECT SUM(amount) AS total
               FROM expenses
               WHERE user_id='$user_id'
               AND MONTH(expense_date)=MONTH(CURDATE())
               AND YEAR(expense_date)=YEAR(CURDATE())";

$monthResult = mysqli_query($conn, $monthQuery);
$monthData = mysqli_fetch_assoc($monthResult);

$thisMonth = $monthData['total'] ?? 0;


/* Total Categories */

$categoryCountQuery = "SELECT COUNT(DISTINCT category_id) AS total
                       FROM expenses
                       WHERE user_id='$user_id'";

$categoryCountResult = mysqli_query($conn, $categoryCountQuery);
$categoryCountData = mysqli_fetch_assoc($categoryCountResult);

$totalCategories = $categoryCountData['total'] ?? 0;


/* Category Wise Expense */

$categoryReportQuery = "SELECT categories.category_name,
                               SUM(expenses.amount) AS total
                        FROM expenses
                        LEFT JOIN categories
                        ON expenses.category_id = categories.category_id
                        WHERE expenses.user_id='$user_id'
                        GROUP BY expenses.category_id
                        ORDER BY total DESC";

$categoryReportResult = mysqli_query($conn, $categoryReportQuery);

$categoryLabels = [];
$categoryAmounts = [];

while($row = mysqli_fetch_assoc($categoryReportResult)){

    $categoryLabels[] = $row['category_name'];
    $categoryAmounts[] = $row['total'];

}


/* Monthly Expense */

$monthlyQuery = "SELECT DATE_FORMAT(expense_date,'%b') AS month,
                        MONTH(expense_date) AS month_number,
                        SUM(amount) AS total
                 FROM expenses
                 WHERE user_id='$user_id'
                 GROUP BY YEAR(expense_date), MONTH(expense_date)
                 ORDER BY YEAR(expense_date), MONTH(expense_date)";

$monthlyResult = mysqli_query($conn, $monthlyQuery);

$monthlyLabels = [];
$monthlyAmounts = [];

while($row = mysqli_fetch_assoc($monthlyResult)){

    $monthlyLabels[] = $row['month'];
    $monthlyAmounts[] = $row['total'];

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reports</title>

<link rel="stylesheet" href="report.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

<a href="report.php" class="active">
<i class="fa fa-chart-pie"></i> Reports
</a>

<a href="settings.php">
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

<form method="GET" action="report.php" class="search-form">
    <input 
        type="text" 
        name="search"
        placeholder="Search Reports..."
        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
    >

    <button type="submit">
        Search
    </button>
</form>


<div class="profile">

    <i class="fa fa-bell"></i>

    <div class="profile-menu">

        <div class="profile-icon" onclick="toggleProfileMenu()">

            <?php if(!empty($user['profile']) && file_exists($user['profile'])){ ?>

                <img src="<?php echo $user['profile']; ?>" alt="Profile">

            <?php }else{ ?>

                <i class="fa fa-user"></i>

            <?php } ?>

        </div>

    </div>

</div>

</div>

<h1>Expense Reports</h1>

<div class="cards">

<div class="card">
<h4>Total Expense</h4>
<h2>₹<?php echo number_format($totalExpense, 2); ?></h2>
</div>

<div class="card">
<h4>This Month</h4>
<h2>₹<?php echo number_format($thisMonth, 2); ?></h2>
</div>

<div class="card">
<h4>Total Categories</h4>
<h2><?php echo $totalCategories; ?></h2>
</div>

</div>

<div class="bottom">

<div class="chart">

<h3>Monthly Expense</h3>

<canvas id="barChart"></canvas>

</div>

<div class="category-chart">

<h3>Expense Categories</h3>

<canvas id="pieChart"></canvas>

</div>

</div>

<div class="table-box">

<h3>Monthly Report</h3>

<table>

<tr>

    <th>Month</th>
    <th>Expense</th>
    <th>Status</th>

</tr>

<?php

$monthlyTableQuery = "SELECT 
                      DATE_FORMAT(expenses.expense_date,'%M') AS month, 
                      SUM(expenses.amount) AS total 
                      FROM expenses
                      LEFT JOIN categories
                      ON expenses.category_id = categories.category_id
                      WHERE expenses.user_id='$user_id'";

if($search != ''){

    $search = mysqli_real_escape_string($conn, $search);

    $monthlyTableQuery .= " AND (
        expenses.title LIKE '%$search%'
        OR categories.category_name LIKE '%$search%'
        OR expenses.description LIKE '%$search%'
    )";
}

$monthlyTableQuery .= " GROUP BY YEAR(expenses.expense_date), MONTH(expenses.expense_date)
                        ORDER BY YEAR(expenses.expense_date), MONTH(expenses.expense_date)";

$monthlyTableResult = mysqli_query($conn, $monthlyTableQuery);

if(mysqli_num_rows($monthlyTableResult) > 0){

    while($row = mysqli_fetch_assoc($monthlyTableResult)){

?>

<tr>

    <td>
        <?php echo $row['month']; ?>
    </td>

    <td>
        ₹<?php echo number_format($row['total'], 2); ?>
    </td>

    <td>
        <span class="status">Completed</span>
    </td>

</tr>

<?php

    }

}else{

?>

<tr>

    <td colspan="3">
        No expense records found.
    </td>

</tr>

<?php

}

?>

</table>
</div>

</div>

<script>

new Chart(document.getElementById("barChart"),{

    type:"bar",

    data:{

        labels: <?php echo json_encode($monthlyLabels); ?>,

        datasets:[{

            label:"Expense",

            data: <?php echo json_encode($monthlyAmounts); ?>,

            backgroundColor:"#1e88e5",

            borderRadius:8

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        plugins:{

            legend:{
                display:false
            }

        }

    }

});
new Chart(document.getElementById("pieChart"),{

    type:"pie",

    data:{
        labels: <?php echo json_encode($categoryLabels); ?>,

        datasets:[{

            data: <?php echo json_encode($categoryAmounts); ?>,

            backgroundColor:[
                "#4F46E5",
                "#06B6D4",
                "#22C55E",
                "#F59E0B",
                "#EF4444",
                "#8B5CF6",
                "#EC4899",
                "#14B8A6"
            ]

        }]

    },

    options:{

        responsive:true,

        maintainAspectRatio:false,

        plugins:{
    legend:{
        display:false
    }
}

    }

});

</script>
<script>

if(localStorage.getItem("theme")=="dark"){
    document.body.classList.add("dark-mode");
}

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

function confirmLogout(){

    return confirm("Are you sure you want to logout?");

}

</script>
</body>
</html>