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
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';
$where = " WHERE expenses.user_id='$user_id' ";
/* Fetch Expenses */

$expenseQuery = "SELECT expenses.*, categories.category_name
    FROM expenses
    LEFT JOIN categories
    ON expenses.category_id = categories.category_id
    WHERE expenses.user_id='$user_id'
    ORDER BY expenses.expense_date DESC";

$expenseResult = mysqli_query($conn, $expenseQuery);

/* Update Expense */

if(isset($_POST['updateExpense'])){

    $edit_id = $_POST['edit_expense_id'];
    $edit_title = trim($_POST['edit_title']);
    $edit_amount = $_POST['edit_amount'];
    $edit_category_name = trim($_POST['edit_category_name']);
    $edit_expense_date = $_POST['edit_expense_date'];
    $edit_description = trim($_POST['edit_description']);

    $user_id = $user['id'];


    /* Check category */

    $categoryQuery = "SELECT category_id
                      FROM categories
                      WHERE category_name='$edit_category_name'";

    $categoryResult = mysqli_query($conn, $categoryQuery);

    $category = mysqli_fetch_assoc($categoryResult);


    if($category){

        $category_id = $category['category_id'];

    }else{

        /* New category */

        $insertCategory = "INSERT INTO categories (category_name)
                           VALUES ('$edit_category_name')";

        if(mysqli_query($conn, $insertCategory)){

            $category_id = mysqli_insert_id($conn);

        }else{

            echo "Category not added: " . mysqli_error($conn);
            exit();

        }

    }

    /* Update expense */

    $updateQuery = "UPDATE expenses
                    SET title='$edit_title',
                        category_id='$category_id',
                        amount='$edit_amount',
                        expense_date='$edit_expense_date',
                        description='$edit_description'
                    WHERE expense_id='$edit_id'
                    AND user_id='$user_id'";


    if(mysqli_query($conn, $updateQuery)){

        header("Location: expenses.php");
        exit();

    }else{

        echo "Expense update failed: " . mysqli_error($conn);

    }

}
/* Delete Expense */

if(isset($_POST['deleteExpense'])){

    $delete_id = intval($_POST['delete_expense_id']);

    $deleteQuery = "DELETE FROM expenses
                    WHERE expense_id='$delete_id'
                    AND user_id='$user_id'";

    if(mysqli_query($conn, $deleteQuery)){

        header("Location: expenses.php");
        exit();

    }else{

        echo "Expense delete failed: " . mysqli_error($conn);

    }

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

        header("Location: expenses.php");
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
<title>Expenses</title>

<link rel="stylesheet" href="expenses.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>
<body>

<!-- Sidebar -->

<div class="sidebar">

    <div>

        <a href="index.php" class="logo-link">
        <img src="uploads\ChatGPT Image Aug 2, 2026, 08_31_48 PM.png" alt="TrackMySpend Logo">
        </a>

        <a href="index.php">
            <i class="fa fa-home"></i> Dashboard
        </a>

        <a href="expenses.php" class="active">
            <i class="fa fa-wallet"></i> Expenses
        </a>

        <a href="report.php">
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

<!-- Main -->

<div class="main">

<div class="topbar">

<div class="profile">

    <i class="fa fa-bell"></i>

    <div class="profile-menu">

        <div class="profile-icon" onclick="window.location.href='settings.php';">
    
    <?php if(!empty($user['profile']) && file_exists($user['profile'])){ ?>

        <img src="<?php echo $user['profile']; ?>" alt="Profile">

    <?php }else{ ?>

        <i class="fa fa-user"></i>

    <?php } ?>

</div>

    </div>

</div>

</div>

<h1>Expenses</h1>

<!-- Filter Section -->

<form method="GET" action="expenses.php">

    <input 
    type="text"
    name="search"
    id="liveSearch"
    placeholder="Search expenses..."
    value="<?php echo htmlspecialchars($search); ?>"
    autocomplete="off"
>

    <select name="category" onchange="this.form.submit()">

        <option value="">All Categories</option>

        <?php
        $categoryQuery = "SELECT * FROM categories ORDER BY category_name ASC";
        $categoryResult = mysqli_query($conn, $categoryQuery);

        while($cat = mysqli_fetch_assoc($categoryResult)){
        ?>

            <option 
                value="<?php echo htmlspecialchars($cat['category_name']); ?>"
                <?php 
                if($category == $cat['category_name']) echo 'selected'; 
                ?>
            >
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>

        <?php } ?>

    </select>

    <input 
    type="date"
    name="date"
    value="<?php echo htmlspecialchars($date); ?>"
    onchange="this.form.submit()"
>

    <button type="submit">
        Search
    </button>

</form>

<?php

/* Search Filters */

$where = " WHERE expenses.user_id='$user_id' ";

if($search != ''){

    $search = mysqli_real_escape_string($conn, $search);

    $where .= " AND (
        expenses.title LIKE '%$search%'
        OR categories.category_name LIKE '%$search%'
        OR expenses.description LIKE '%$search%'
    )";
}

if($category != ''){

    $category = mysqli_real_escape_string($conn, $category);

    $where .= " AND categories.category_name='$category'";
}

if($date != ''){

    $date = mysqli_real_escape_string($conn, $date);

    $where .= " AND expenses.expense_date='$date'";
}


/* Count filtered expenses */

$countQuery = "SELECT COUNT(*) AS total
               FROM expenses
               LEFT JOIN categories
               ON expenses.category_id = categories.category_id
               $where";

$countResult = mysqli_query($conn, $countQuery);

$countData = mysqli_fetch_assoc($countResult);

$totalExpenses = $countData['total'];


/* Pagination */

$rowsPerPage = 8;

$totalPages = ceil($totalExpenses / $rowsPerPage);

if($totalPages < 1){

    $totalPages = 1;

}

?>

<!-- Expense Table -->

<div class="expense-table">
<?php

$expenseCountQuery = "SELECT COUNT(*) AS total
    FROM expenses
    WHERE user_id='$user_id';";

$expenseCountResult = mysqli_query($conn, $expenseCountQuery);

$expenseCount = mysqli_fetch_assoc($expenseCountResult);

if($expenseCount['total'] == 0){

?>
<div class="empty-expense">

    <i class="fa fa-wallet"></i>

    <h2>Add Your Expense</h2>

    <p>
        Start tracking your spending
        by adding your first expense.
    </p>

    <button class="add-first-expense"
            onclick="openPopup()">

        <i class="fa fa-plus-circle"></i>
        Add Expense

    </button>

</div>

<?php

}else{

?>

<table>

    <thead>

        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

    </thead>

    <tbody>
        <script>

const searchBox = document.getElementById("liveSearch");

searchBox.addEventListener("input", function(){

    let searchValue = this.value.toLowerCase().trim();

    let rows = document.querySelectorAll("tbody tr[class^='page-']");

    rows.forEach(function(row){

        let title = row.cells[0].innerText.toLowerCase();
        let category = row.cells[1].innerText.toLowerCase();
        let amount = row.cells[2].innerText.toLowerCase();
        let date = row.cells[3].innerText.toLowerCase();

        if(
            title.includes(searchValue) ||
            category.includes(searchValue) ||
            amount.includes(searchValue) ||
            date.includes(searchValue)
        ){

            row.style.display = "";

        }else{

            row.style.display = "none";

        }

    });

});

</script>

<?php

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$date = isset($_GET['date']) ? trim($_GET['date']) : '';

$expenseQuery = "SELECT expenses.*,
                        categories.category_name
                 FROM expenses
                 LEFT JOIN categories
                 ON expenses.category_id = categories.category_id
                 WHERE expenses.user_id='$user_id'";

if($search != ''){

    $search = mysqli_real_escape_string($conn, $search);

    $expenseQuery .= " AND (
        expenses.title LIKE '%$search%'
        OR categories.category_name LIKE '%$search%'
        OR expenses.description LIKE '%$search%'
    )";
}

if($category != ''){

    $category = mysqli_real_escape_string($conn, $category);

    $expenseQuery .= " AND categories.category_name='$category'";
}

if($date != ''){

    $date = mysqli_real_escape_string($conn, $date);

    $expenseQuery .= " AND expenses.expense_date='$date'";
}

$expenseQuery .= " ORDER BY expenses.expense_date DESC";

$expenseResult = mysqli_query($conn, $expenseQuery);


if(mysqli_num_rows($expenseResult) == 0){

?>

    <tr class="no-expense-row">
        <td colspan="5" style="text-align:center; padding:40px;">

            <i class="fa fa-wallet" style="font-size:30px;"></i>

            <h3>No Expense Found</h3>

            <p>No expense matches your search.</p>

        </td>
    </tr>

<?php

}else{

    $rowNumber = 0;

    while($expense = mysqli_fetch_assoc($expenseResult)){

        $rowNumber++;

        $pageNumber = ceil($rowNumber / 8);

?>

    <tr class="page-<?php echo $pageNumber; ?>">

        <td>
            <?php echo htmlspecialchars($expense['title']); ?>
        </td>

        <td>
            <?php echo htmlspecialchars($expense['category_name']); ?>
        </td>

        <td>
            ₹<?php echo htmlspecialchars($expense['amount']); ?>
        </td>

        <td>
            <?php echo date("d-m-Y", strtotime($expense['expense_date'])); ?>
        </td>

        <td>

            <button type="button"
                    class="edit"
                    onclick="editExpense(<?php echo $expense['expense_id']; ?>)">
                <i class="fa fa-pen"></i>
            </button>

            <button type="button"
                    class="delete"
                    onclick="deleteExpense(<?php echo $expense['expense_id']; ?>)">
                <i class="fa fa-trash"></i>
            </button>

        </td>

    </tr>

<?php

    }

}

?>

</tbody>
</table>
<script>

let currentPage = 1;
const totalPages = <?php echo $totalPages; ?>;

function showPage(page){

    document.querySelectorAll("tbody tr[class^='page-']").forEach(row=>{
    row.style.display="none";
    });
    document.querySelectorAll(".page-"+page).forEach(row=>{
        row.style.display="";
    });

    document.getElementById("pageNumber").innerHTML=page;

}

function nextPage(){

    if(currentPage<totalPages){

        currentPage++;

        showPage(currentPage);

    }

}

function prevPage(){

    if(currentPage>1){

        currentPage--;

        showPage(currentPage);

    }

}

showPage(1);

</script>
<div class="pagination">

    <button id="prevBtn" onclick="prevPage()">
        <i class="fa-solid fa-angle-left"></i> Previous
    </button>

    <span id="pageNumber">1</span>

    <button id="nextBtn" onclick="nextPage()">
        Next <i class="fa-solid fa-angle-right"></i>
    </button>

</div>

</div>

</div>
<?php

}

?>
<!-- Edit Expense Popup -->

<div class="popup" id="editExpensePopup">

    <div class="popup-content">

        <span class="close" onclick="closeEditPopup()">&times;</span>

        <h2>Edit Expense</h2>

        <form method="POST" action="expenses.php">

            <input type="hidden"
                   name="edit_expense_id"
                   id="edit_expense_id">

            <input type="text"
                   name="edit_title"
                   id="edit_title"
                   placeholder="Expense Title"
                   required>

            <input type="number"
                   name="edit_amount"
                   id="edit_amount"
                   placeholder="Amount"
                   required>

            <input type="text"
                   name="edit_category_name"
                   id="edit_category_name"
                   list="categoryList"
                   placeholder="Search or enter category"
                   required>

            <input type="date"
                   name="edit_expense_date"
                   id="edit_expense_date"
                   required>

            <textarea
                   name="edit_description"
                   id="edit_description"
                   placeholder="Description"></textarea>

            <button type="submit"
                    name="updateExpense"
                    class="save-expense">

                Save Changes

            </button>

        </form>

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

<form action="expenses.php" method="POST">

    <input 
        type="text"
        name="title"
        placeholder="Expense Title"
        required>

    <input 
        type="number"
        name="amount"
        placeholder="Amount"
        required>

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
        required>

    <textarea 
        name="description"
        placeholder="Description"></textarea>

    <button 
        type="submit"
        name="saveExpense"
        class="save-expense">

        Save Expense

    </button>

</form>

    </div>

</div>
<script>

function openPopup(){

    document.getElementById("expensePopup").style.display="flex";

}

function closePopup(){

    document.getElementById("expensePopup").style.display="none";

}

</script>
<script>

function editExpense(id){

    fetch("get_expense.php?id=" + id)
    .then(response => response.json())
    .then(data => {

        if(data.success){

            document.getElementById("edit_expense_id").value = data.expense.expense_id;

            document.getElementById("edit_title").value = data.expense.title;

            document.getElementById("edit_amount").value = data.expense.amount;

            document.getElementById("edit_category_name").value = data.expense.category_name;

            document.getElementById("edit_expense_date").value = data.expense.expense_date;

            document.getElementById("edit_description").value = data.expense.description;

            document.getElementById("editExpensePopup").style.display = "flex";

        }else{

            alert("Expense not found.");

        }

    })
    .catch(error => {

        console.error(error);
        alert("Unable to load expense details.");

    });

}

function closeEditPopup(){

    document.getElementById("editExpensePopup").style.display = "none";

}

</script>
<script>
function deleteExpense(id){

    if(confirm("Are you sure you want to delete this expense?")){

        const form = document.createElement("form");

        form.method = "POST";
        form.action = "expenses.php";

        const input = document.createElement("input");

        input.type = "hidden";
        input.name = "delete_expense_id";
        input.value = id;

        form.appendChild(input);

        const button = document.createElement("input");

        button.type = "hidden";
        button.name = "deleteExpense";
        button.value = "1";

        form.appendChild(button);

        document.body.appendChild(form);

        form.submit();

    }

}
</script>
<script>

function confirmLogout(){

    return confirm("Are you sure you want to logout?");

}

</script>
</body>
</html>