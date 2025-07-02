<?php
include 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$month_name = date('F', mktime(0, 0, 0, $selected_month, 1));

$income = 0;
$expense = 0;

$sql_summary = "SELECT type, SUM(amount) AS total 
                FROM transactions 
                WHERE user_id = $user_id 
                AND MONTH(date) = $selected_month 
                GROUP BY type";
$result = $conn->query($sql_summary);
if (!$result) die("Query failed: " . $conn->error);

while ($row = $result->fetch_assoc()) {
    if ($row['type'] === 'income') $income = $row['total'];
    if ($row['type'] === 'expense') $expense = $row['total'];
}

$spentPercentage = $income > 0 ? ($expense / $income) * 100 : 0;

$sql_expense = "SELECT c.name, c.type,
                       COALESCE(SUM(t.amount), 0) AS spent, 
                       COALESCE(b.amount_limit, 0) AS budget_limit
                FROM categories AS c
                LEFT JOIN transactions AS t 
                    ON t.category_id = c.category_id 
                    AND t.user_id = $user_id 
                    AND t.type = 'expense' 
                    AND MONTH(t.date) = $selected_month 
                LEFT JOIN budgets AS b 
                    ON b.category_id = c.category_id 
                    AND b.user_id = $user_id 
                    AND b.month = '$month_name'
                WHERE c.type = 'expense'
                GROUP BY c.name, c.type";

$result_expense = $conn->query($sql_expense);
if (!$result_expense) die("Query failed: " . $conn->error);

$defaultCategory = [
    'spent' => '0.00',
    'remaining' => '0.00',
    'percentage' => '0.00',
    'remain_percent' => '100.00',
    'limit' => '0.00'
];

$food = $transport = $utilities = $entertainment = $healthcare = $others = $defaultCategory;

while ($row = $result_expense->fetch_assoc()) {
    $spent = $row['spent'];
    $limit = $row['budget_limit'];
    $percentage = $limit > 0 ? ($spent / $limit) * 100 : 0;

    $categoryData = [
        'spent' => number_format($spent, 2),
        'remaining' => number_format($limit - $spent, 2),
        'percentage' => number_format($percentage, 2),
        'remain_percent' => number_format(100 - $percentage, 2),
        'limit' => number_format($limit, 2)
    ];

    switch (strtolower($row['name'])) {
        case 'food': $food = $categoryData; break;
        case 'transport': $transport = $categoryData; break;
        case 'utilities': $utilities = $categoryData; break;
        case 'entertainment': $entertainment = $categoryData; break;
        case 'healthcare': $healthcare = $categoryData; break;
        default: $others = $categoryData;
    }
}

$salary_total = 0;
$bonus_total = 0;
$investment_total = 0;
$others_total = 0;

$sql_income = "SELECT c.name, SUM(t.amount) AS total
               FROM categories c
               JOIN transactions t ON t.category_id = c.category_id
               WHERE c.type = 'income' 
               AND t.user_id = $user_id 
               AND MONTH(t.date) = $selected_month 
               GROUP BY c.name";

$result_income = $conn->query($sql_income);
if ($result_income) {
    while ($row = $result_income->fetch_assoc()) {
        switch (strtolower($row['name'])) {
            case 'salary': $salary_total = $row['total']; break;
            case 'bonus': $bonus_total = $row['total']; break;
            case 'investment': $investment_total = $row['total']; break;
            default: $others_total += $row['total'];
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master Dashboard</title>
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
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
                <li><a href="set_budget.html">Monthly Budget</a></li>
                <li><a href="overview.php">Transaction Overview</a></li>
                <li><a href="dashboard.php" class="active">Finance Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main id="dashboardmain">
        <section id="dashboardtitle">
            <div class="card-month">
                <form id="monthForm" method="GET" action="dashboard.php">
                    <select name="month" id="month-select" onchange="document.getElementById('monthForm').submit()">
                        <option value="7" <?= $selected_month == 7 ? 'selected' : '' ?>>July</option>    
                        <option value="6" <?= $selected_month == 6 ? 'selected' : '' ?>>June</option>
                        <option value="5" <?= $selected_month == 5 ? 'selected' : '' ?>>May</option>
                        <option value="4" <?= $selected_month == 4 ? 'selected' : '' ?>>April</option>
                    </select>
                </form>
            </div>


            <h2>Monthly Summary</h2>
        </section>
    
        <section id="dashboardsummary">
            <div class="dashboard-left">
                <div class="total-bar">
                    <h3>Total Budget Tracker</h3>
                    <div class="total-bar-labels">
                        <span class="progress-label-left"><?= number_format($spentPercentage, 1) ?>% spent</span>
                        <span class="progress-label-right"><?= number_format(100 - $spentPercentage, 1) ?>% remaining</span>
                    </div>
                    <div class="total-bar-fill">
                        <div class="progress-expense" style="flex: <?= $spentPercentage ?>;"></div>
                        <div class="progress-remain" style="flex: <?= 100 - $spentPercentage ?>;"></div>
                    </div>
                </div>

                <div class="incomesummary">
                    <h2>Income</h2>
                    <div class="incomesummarycards">

                        <div class="income-card">
                            <img src="img/Salary_income.png" alt="Salary total">
                            <div class="income-info">
                                <h3>Salary</h3>
                                <p>RM<?= number_format($salary_total, 2) ?></p>
                            </div>
                        </div>

                        <div class="income-card">
                            <img src="img/Bonus_income.png" alt="Bonus total">
                            <div class="income-info">
                                <h3>Bonus</h3>
                                <p>RM<?= number_format($bonus_total, 2) ?></p>
                            </div>
                        </div>

                        <div class="income-card">
                            <img src="img/Investment_income.png" alt="Investment total">
                            <div class="income-info">
                                <h3>Investment</h3>
                                <p>RM<?= number_format($investment_total, 2) ?></p>
                            </div>
                        </div>

                        <div class="income-card">
                            <img src="img/Others_income.png" alt="Other income">
                            <div class="income-info">
                                <h3>Others</h3>
                                <p>RM<?= number_format($others_total, 2) ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="dashboard-right">
                <h2>Expenses</h2>
                <div class="expensesummary">
                    <div class="bar-card">
                        <img src="img/Food.png" alt="Food summary">
                        <h3>Food</h3>
                        <div class="category-bar">
                            <div class="category-bar-labels">
                                <span class="bar-label-left">RM<?= $food['spent'] ?> spent</span>       
                                <span class="bar-label-right">RM<?= $food['remaining'] ?> remaining</span>
                            </div>
                            <div class="bar-fill">
                                <div class="bar-expense" style="flex: <?= $food['percentage'] ?>;"></div>
                                <div class="bar-remain" style="flex: <?= $food['remain_percent'] ?>;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bar-card">
                        <img src="img/Transport.png" alt="Transport summary">
                        <h3>Transport</h3>
                        <div class="category-bar">
                            <div class="category-bar-labels">
                                <span class="bar-label-left">RM<?= $transport['spent'] ?> spent</span>
                                <span class="bar-label-right">RM<?= $transport['remaining'] ?> remaining</span>
                            </div>
                            <div class="bar-fill">
                                <div class="bar-expense" style="flex: <?= $transport['percentage'] ?>;"></div>
                                <div class="bar-remain" style="flex: <?= $transport['remain_percent'] ?>;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bar-card">
                        <img src="img/Utilities.png" alt="Utilities summary">
                        <h3>Utilities</h3>
                        <div class="category-bar">
                            <div class="category-bar-labels">
                                <span class="bar-label-left">RM<?= $utilities['spent'] ?> spent</span>
                                <span class="bar-label-right">RM<?= $utilities['remaining'] ?> remaining</span>
                            </div>
                            <div class="bar-fill">
                                <div class="bar-expense" style="flex: <?= $utilities['percentage'] ?>;"></div>
                                <div class="bar-remain" style="flex: <?= $utilities['remain_percent'] ?>;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bar-card">
                        <img src="img/Entertainment.png" alt="Entertainment summary">
                        <h3>Entertainment</h3>
                        <div class="category-bar">
                            <div class="category-bar-labels">
                                <span class="bar-label-left">RM<?= $entertainment['spent'] ?> spent</span>
                                <span class="bar-label-right">RM<?= $entertainment['remaining'] ?> remaining</span>
                            </div>
                            <div class="bar-fill">
                                <div class="bar-expense" style="flex: <?= $entertainment['percentage'] ?>;"></div>
                                <div class="bar-remain" style="flex: <?= $entertainment['remain_percent'] ?>;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bar-card">
                        <img src="img/Healthcare.png" alt="Healthcare summary">
                        <h3>Healthcare</h3>
                        <div class="category-bar">
                            <div class="category-bar-labels">
                                <span class="bar-label-left">RM<?= $healthcare['spent'] ?> spent</span>
                                <span class="bar-label-right">RM<?= $healthcare['remaining'] ?> remaining</span>
                            </div>
                            <div class="bar-fill">
                                <div class="bar-expense" style="flex: <?= $healthcare['percentage'] ?>;"></div>
                                <div class="bar-remain" style="flex: <?= $healthcare['remain_percent'] ?>;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bar-card">
                        <img src="img/Others.png" alt="Others summary">
                        <h3>Others</h3>
                        <div class="category-bar">
                            <div class="category-bar-labels">
                                <span class="bar-label-left">RM<?= $others['spent'] ?> spent</span>
                                <span class="bar-label-right">RM<?= $others['remaining'] ?> remaining</span>
                            </div>
                            <div class="bar-fill">
                                <div class="bar-expense" style="flex: <?= $others['percentage'] ?>;"></div>
                                <div class="bar-remain" style="flex: <?= $others['remain_percent'] ?>;"></div>
                            </div>
                        </div>
                    </div>                                              
                </div>
            </div>
        </section>

        
    </main>
    
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
