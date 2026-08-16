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
$user_id = $user['id'];
$startingBalance = $user['balance'];
/* Recent Transactions */

$recentQuery = "
    SELECT 
        expenses.expense_date,
        categories.category_name,
        expenses.amount
    FROM expenses
    LEFT JOIN categories
        ON expenses.category_id = categories.category_id
    WHERE expenses.user_id='$user_id'
    ORDER BY expenses.expense_date DESC
    LIMIT 5
";

$recentResult = mysqli_query($conn, $recentQuery);
/* Dashboard Expense Summary */

$totalQuery = "
    SELECT SUM(amount) AS total_expense
    FROM expenses
    WHERE user_id='$user_id'
";

$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);

$totalExpense = $totalRow['total_expense'] ?? 0;


/* Current Month Expense */

$monthlyExpenseQuery = "
    SELECT SUM(amount) AS monthly_expense
    FROM expenses
    WHERE user_id='$user_id'
    AND MONTH(expense_date) = MONTH(CURDATE())
    AND YEAR(expense_date) = YEAR(CURDATE())
";

$monthlyExpenseResult = mysqli_query($conn, $monthlyExpenseQuery);
$monthlyExpenseRow = mysqli_fetch_assoc($monthlyExpenseResult);

$monthlyExpense = $monthlyExpenseRow['monthly_expense'] ?? 0;

/* Current Balance */

/* Current Balance */

$currentBalance = $startingBalance - $totalExpense;
/* Monthly Expense Data */

$monthlyQuery = "
    SELECT 
        DATE_FORMAT(expense_date, '%b') AS month,
        MONTH(expense_date) AS month_number,
        SUM(amount) AS total
    FROM expenses
    WHERE user_id='$user_id'
    GROUP BY YEAR(expense_date), MONTH(expense_date)
    ORDER BY YEAR(expense_date), MONTH(expense_date)
";

$monthlyResult = mysqli_query($conn, $monthlyQuery);

$months = [];
$monthlyAmounts = [];

while($row = mysqli_fetch_assoc($monthlyResult)){

    $months[] = $row['month'];
    $monthlyAmounts[] = $row['total'];

}


/* Category Expense Data */

$categoryQuery = "
    SELECT 
        categories.category_name,
        SUM(expenses.amount) AS total
    FROM expenses
    LEFT JOIN categories
        ON expenses.category_id = categories.category_id
    WHERE expenses.user_id='$user_id'
    GROUP BY expenses.category_id
    ORDER BY total DESC
";

$categoryResult = mysqli_query($conn, $categoryQuery);

$categoryNames = [];
$categoryAmounts = [];

while($row = mysqli_fetch_assoc($categoryResult)){

    $categoryNames[] = $row['category_name'];
    $categoryAmounts[] = $row['total'];

}

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

        header("Location: index.php");
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
<title>Expense Management</title>

<link rel="stylesheet" href="style.css">

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

        <a href="index.php" class="active"><i class="fa fa-home"></i> Dashboard</a>
        <a href="expenses.php"><i class="fa fa-wallet"></i> Expenses</a>
        <a href="report.php"><i class="fa fa-chart-pie"></i> Reports</a>
        <a href="settings.php"><i class="fa fa-gear"></i> Settings</a>

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

<input type="text" placeholder="Search...">
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

<h1>Financial Overview</h1>
<h3>Welcome,
<?php echo $_SESSION['email']; ?>
</h3>

<div class="cards">

<div class="card">
    <h4>Current Balance</h4>
    <h2>₹<?php echo number_format($currentBalance, 2); ?></h2>
</div>

<div class="card">
    <h4>Total Expense</h4>
    <h2>₹<?php echo number_format($totalExpense, 2); ?></h2>
</div>

<div class="card">
    <h4>Monthly Expense</h4>
    <h2>₹<?php echo number_format($monthlyExpense, 2); ?></h2>
</div>
</div>

<!-- Expense Overview -->

<div class="bottom">

    <!-- Monthly Expense Chart -->

    <div class="chart">

        <h3>Expense Overview</h3>

        <div class="chart-area">
            <canvas id="barChart"></canvas>
        </div>

    </div>


    <!-- Category Pie Chart -->

    <div class="category-chart">

        <h3>Expense Categories</h3>

        <div class="pie-area">
            <canvas id="pieChart"></canvas>
        </div>

    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

/* Monthly Expense Bar Chart */

const barCtx = document.getElementById('barChart');

new Chart(barCtx, {

    type: 'bar',

    data: {

        labels: <?php echo json_encode($months); ?>,

        datasets: [{

            data: <?php echo json_encode($monthlyAmounts); ?>,

            backgroundColor: "#1e88e5",

            borderRadius: 8

        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            legend: {
                display: false
            }

        },

        scales: {

            y: {

                beginAtZero: true

            }

        }

    }

});


/* Expense Category Pie Chart */

const pieCtx = document.getElementById('pieChart');

new Chart(pieCtx, {

    type: 'pie',

    data: {

        labels: <?php echo json_encode($categoryNames); ?>,

        datasets: [{

            data: <?php echo json_encode($categoryAmounts); ?>,

            backgroundColor: [

                "#4F46E5",
                "#06B6D4",
                "#22C55E",
                "#F59E0B",
                "#EF4444",
                "#8B5CF6",
                "#EC4899",
                "#14B8A6",
                "#6366F1",
                "#F97316"

            ]

        }]

    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        plugins: {

            /* Legend completely hide */

            legend: {
                display: false
            }

        }

    }

});

</script>

<!-- Recent Transactions -->

<div class="table-box">

    <h3>Recent Transactions</h3>

    <table>

        <tr>
            <th>Date</th>
            <th>Category</th>
            <th>Amount</th>
        </tr>

        <?php if(mysqli_num_rows($recentResult) > 0){ ?>

            <?php while($row = mysqli_fetch_assoc($recentResult)){ ?>

                <tr>

                    <td>
                        <?php 
                        echo date("d M", strtotime($row['expense_date'])); 
                        ?>
                    </td>

                    <td>
                        <?php 
                        echo htmlspecialchars($row['category_name']); 
                        ?>
                    </td>

                    <td>
                        ₹<?php echo number_format($row['amount'], 2); ?>
                    </td>

                </tr>

            <?php } ?>

        <?php }else{ ?>

            <tr>

                <td colspan="3" style="text-align:center;">
                    No transactions found
                </td>

            </tr>

        <?php } ?>

    </table>

</div>
</div>

</div>

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