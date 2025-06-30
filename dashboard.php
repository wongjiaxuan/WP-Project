<?php
include 'includes/db.php';
session_start();  

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$selected_month = isset($_GET['month']) && is_numeric($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12? (int)$_GET['month']: date('n');

$income = 0;
$expense = 0;

$sql_summary = "SELECT type, SUM(amount) AS total 
                FROM transactions 
                WHERE user_id = $user_id AND MONTH(date) = $selected_month 
                GROUP BY type";

$result = $conn->query($sql_summary);

while ($row = $result->fetch_assoc()) {
    if ($row['type'] === 'income') {
        $income = $row['total'];
    } elseif ($row['type'] === 'expense') {
        $expense = $row['total'];
    }
}

$totalBudget = $income;
$spentPercentage = $income > 0 ? ($expense / $income) * 100 : 0;
$remaining = $income - $expense;

$sql_categories = "SELECT c.category_id, c.name,
                   COALESCE(SUM(t.amount), 0) AS total
                   FROM categories c
                   LEFT JOIN transactions t 
                   ON t.category_id = c.category_id 
                   AND t.type = 'expense' 
                   AND t.user_id = $user_id 
                   AND MONTH(t.date) = $selected_month
                   GROUP BY c.category_id, c.name";

$category_result = $conn->query($sql_categories);
$category_data = [];

while ($row = $category_result->fetch_assoc()) {
    $category_data[] = $row;
}

$sql_history = "SELECT t.*, c.name AS category_name
                FROM transactions t
                JOIN categories c ON t.category_id = c.category_id
                WHERE t.user_id = $user_id AND MONTH(t.date) = $selected_month
                ORDER BY t.date DESC";

$history_result = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="en">
    

<head>
    <title>Jimat Master Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="An online budget tracker simplifies user's work on managing income, expenses and savings.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>

<body>
    <header>
        <nav aria-label="Main Navigation">
            <div class="headername">Jimat Master</div>
            <i class="fa-solid fa-bars" id="menuicon"></i>
            <ul class="menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="input.php">Transaction Input</a></li>
                <li><a href="overview.php">Transaction Overview</a></li>
                <li><a href="dashboard.php" class="active">Finance Dashboard</a></li>
            </ul>
        </nav>
    </header>

        <section id="dashboard">

            <div class="summary">

                <div class="monthlysummary" >
                  <h2>Monthly Summary</h2>

                  <div class="progress-card">

                    <div class="card-month">
                        <form method="GET" id="monthForm">
                            <select name="month" id="month-select" onchange="document.getElementById('monthForm').submit()">
                                <option value="6" <?= $selected_month == 6 ? 'selected' : '' ?>>June</option>
                                <option value="5" <?= $selected_month == 5 ? 'selected' : '' ?>>May</option>
                                <option value="4" <?= $selected_month == 4 ? 'selected' : '' ?>>April</option>
                            </select>
                        </form>
                    </div>

                    <div class="progress-container">
                      
                        <div class="monthly-progress-bar">

                            <div class="progress-labels">
                                <span class="progress-label-left">RM<?= number_format($expense, 2) ?> spent</span>
                                <span class="progress-label-right">RM<?= number_format($remaining, 2) ?> remaining</span>
                            </div>

                            <div class="progress-bar-fill">
                                <div class="progress-expense" style="flex: <?= $spentPercentage ?>;"></div>
                                <div class="progress-remain" style="flex: <?= 100 - $spentPercentage ?>;"></div>
                            </div>
          
                        </div>
                    </div>
                </div>

                <div class="income-budget-summary">

                    <div class="income-budget-card">
                        <img src="img/income.png" alt="Income">
                        <div class="category-info">
                            <h3>Income</h3>
                            <p class="income">RM<?= number_format($income, 2) ?></p>
                        </div>
                    </div>

                    <div class="income-budget-card">
                        <img src="img/budget.png" alt="Budget Limit">
                        <div class="category-info">
                            <h3>Budget Limit</h3>
                            <p class="budget">RM<?= number_format($totalBudget, 2) ?></p>
                        </div>
                    </div>

                </div>

                <div class="category-summary">
                    <?php foreach ($category_data as $cat): ?>
                        <div class="category-card">
                            <img src="img/<?= $cat['name'] ?>.png" alt="<?= $cat['name'] ?>">
                            <div class="category-info">
                                <h3><?= $cat['name'] ?></h3>
                                <p>RM<?= number_format($cat['total'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
            </div>

                <div class="transactionhistory">
                  <h2>Recent Transactions</h2>
                  
                  <table class="transactiontable"> 
                    <thead>
                      <tr>
                        <th style="width: 17%;">Category</th>
                        <th style="width: 47%;">Remark</th>
                        <th style="width: 20%; text-align: center;">Amount(RM)</th>
                        <th style="width: 25%; text-align: center;">Date</th>
                      </tr>
                    </th>
                    <tbody>
                        <?php while ($txn = $history_result->fetch_assoc()): ?>
                            <tr class="<?= $txn['type'] === 'expense' ? 'table_expense' : 'table_income' ?>">
                                <td><img src="img/<?= $txn['category_name'] ?>.png" alt="<?= $txn['category_name'] ?>" class="transactiontable_icon"></td>
                                <td><?= htmlspecialchars($txn['note']) ?></td>
                                <td style="text-align: center;">
                                    <?= $txn['type'] === 'expense' ? '-' : '+' ?><?= number_format($txn['amount'], 2) ?>
                                </td>
                                <td style="text-align: center;"><?= $txn['date'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                  </table>

                </div>

            </div>
        </section>
    
    <footer>
        <div class="footercontainer">
            <p>
                <span class="footeryear">&copy; <span id="current-year"></span> SECV2223-05</span>
                <span class="footergroup">Web Programming Group 4</span>
            </p>
        </div>
    </footer>
</body>
</html>
