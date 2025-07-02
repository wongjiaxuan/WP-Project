<?php
require_once 'admin_header.php';
require_once 'includes/db.php';

$selected_month = isset($_GET['month']) && is_numeric($_GET['month']) && $_GET['month'] >= 1 && $_GET['month'] <= 12 ? (int)$_GET['month'] : date('n');

// Total income and expenses (for all users)
$income = 0;
$expense = 0;

$sql_summary = "
    SELECT type, SUM(amount) AS total 
    FROM transactions 
    WHERE MONTH(date) = $selected_month 
    GROUP BY type
";
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

// Get category-wise expense breakdown
$sql_categories = "
    SELECT c.category_id, c.name, COALESCE(SUM(t.amount), 0) AS total
    FROM categories c
    LEFT JOIN transactions t 
    ON t.category_id = c.category_id 
    AND t.type = 'expense' 
    AND MONTH(t.date) = $selected_month
    GROUP BY c.category_id, c.name
";
$category_result = $conn->query($sql_categories);
$category_data = [];
while ($row = $category_result->fetch_assoc()) {
    $category_data[] = $row;
}

// Get recent transactions for all users
$sql_history = "
    SELECT t.*, c.name AS category_name, u.username 
    FROM transactions t
    JOIN categories c ON t.category_id = c.category_id
    JOIN users u ON t.user_id = u.user_id
    WHERE MONTH(t.date) = $selected_month
    ORDER BY t.date DESC
    LIMIT 10
";
$history_result = $conn->query($sql_history);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Jimat Master</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin panel overview for income, expenses, and user transactions.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>

<body>
    <main class="dashboardmain">
        <section id="dashboard">
            <div class="summary">
                <div class="monthlysummary">
                    <h2>Monthly Summary (All Users)</h2>
                    <div class="progress-card">
                        <div class="card-month">
                            <form method="GET" id="monthForm">
                                <select name="month" id="month-select" onchange="document.getElementById('monthForm').submit()">
                                    <option value="7" <?= $selected_month == 7 ? 'selected' : '' ?>>July</option>
                                    <option value="6" <?= $selected_month == 6 ? 'selected' : '' ?>>June</option>
                                    <option value="5" <?= $selected_month == 5 ? 'selected' : '' ?>>May</option>
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
                                <h3>Total Income</h3>
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
                    <h2>Recent Transactions (All Users)</h2>
                    <table class="transactiontable">
                        <thead>
                            <tr>
                                <th style="width: 17%;">User</th>
                                <th style="width: 17%;">Category</th>
                                <th style="width: 46%;">Note</th>
                                <th style="width: 20%; text-align: center;">Amount (RM)</th>
                                <th style="width: 25%; text-align: center;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($txn = $history_result->fetch_assoc()): ?>
                                <tr class="<?= $txn['type'] === 'expense' ? 'table_expense' : 'table_income' ?>">
                                    <td><?= htmlspecialchars($txn['username']) ?></td>
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
