<?php
require_once 'admin_header.php';
require_once '../includes/db.php';

$selected_month_str = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$timestamp = strtotime($selected_month_str);
$selected_month = (int)date('n', $timestamp);
$selected_year = (int)date('Y', $timestamp);

$income = 0;
$expense = 0;


$sql_summary = "SELECT type, SUM(amount) AS total 
                FROM transactions 
                WHERE MONTH(date) = $selected_month 
                AND YEAR(date) = $selected_year
                GROUP BY type";
$result = $conn->query($sql_summary);

while ($row = $result->fetch_assoc()) {
    if ($row['type'] === 'income') $income = $row['total'];
    if ($row['type'] === 'expense') $expense = $row['total'];
}

$spentPercentage = $income > 0 ? ($expense / $income) * 100 : 0;


$sql_expense = "SELECT c.name,
                       COALESCE(SUM(t.amount), 0) AS spent, 
                       COALESCE(SUM(b.amount_limit), 0) AS budget_limit
                FROM categories AS c
                LEFT JOIN transactions AS t 
                    ON t.category_id = c.category_id 
                    AND t.type = 'expense' 
                    AND MONTH(t.date) = $selected_month
                    AND YEAR(t.date) = $selected_year
                LEFT JOIN budgets AS b 
                    ON b.category_id = c.category_id 
                    AND b.month = '$selected_month_str'
                WHERE c.type = 'expense'
                GROUP BY c.name";

$result_expense = $conn->query($sql_expense);

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
               AND MONTH(t.date) = $selected_month 
               AND YEAR(t.date) = $selected_year
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

<body>
    <div id="piggy-bg"></div>
    <div class="piggy-container" aria-hidden="true"></div>

    <main id="dashboardmain">
        <section id="dashboardtitle">
            <div class="card-month">
                <form id="monthForm" method="GET" action="admin_dashboard.php">
                    <select name="month" id="month-select" onchange="document.getElementById('monthForm').submit()">
                        <option value="2025-07" <?= $selected_month_str == '2025-07' ? 'selected' : '' ?>>July</option>
                        <option value="2025-06" <?= $selected_month_str == '2025-06' ? 'selected' : '' ?>>June</option>
                        <option value="2025-05" <?= $selected_month_str == '2025-05' ? 'selected' : '' ?>>May</option>
                        <option value="2025-04" <?= $selected_month_str == '2025-04' ? 'selected' : '' ?>>April</option>
                    </select>
                </form>
            </div>

            <h2>Monthly Summary (All Users)</h2>
        </section>
    
        <section id="dashboardsummary">
            <div class="dashboard-left">
                <div class="total-bar">
                    <h3>Total Budget Tracker (All Users)</h3>
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
                    <h2>Total Income (All Users)</h2>
                    <div class="incomesummarycards">

                        <div class="income-card">
                            <img src="../img/Salary_income.png" alt="Salary total">
                            <div class="income-info">
                                <h3>Salary</h3>
                                <p>RM<?= number_format($salary_total, 2) ?></p>
                            </div>
                        </div>

                        <div class="income-card">
                            <img src="../img/Bonus_income.png" alt="Bonus total">
                            <div class="income-info">
                                <h3>Bonus</h3>
                                <p>RM<?= number_format($bonus_total, 2) ?></p>
                            </div>
                        </div>

                        <div class="income-card">
                            <img src="../img/Investment_income.png" alt="Investment total">
                            <div class="income-info">
                                <h3>Investment</h3>
                                <p>RM<?= number_format($investment_total, 2) ?></p>
                            </div>
                        </div>

                        <div class="income-card">
                            <img src="../img/Others_income.png" alt="Other income">
                            <div class="income-info">
                                <h3>Others</h3>
                                <p>RM<?= number_format($others_total, 2) ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="dashboard-right">
                <h2>Total Expenses (All Users)</h2>
                <div class="expensesummary">
                    <div class="bar-card">
                        <img src="../img/Food.png" alt="Food summary">
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
                        <img src="../img/Transport.png" alt="Transport summary">
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
                        <img src="../img/Utilities.png" alt="Utilities summary">
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
                        <img src="../img/Entertainment.png" alt="Entertainment summary">
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
                        <img src="../img/Healthcare.png" alt="Healthcare summary">
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
                        <img src="../img/Others.png" alt="Others summary">
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

<script>

document.addEventListener('DOMContentLoaded', function() {
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
});


window.addEventListener("load", function () {
    setTimeout(() => {
        const piggyCount = 100; 
        const spacing = 100;
        const positions = [];
        const piggyContainer = document.querySelector('.piggy-container');
        const fullHeight = Math.max(
            document.documentElement.scrollHeight,
            document.body.scrollHeight
        );

        function isTooClose(x, y) {
            return positions.some(pos => {
                const dx = pos.x - x;
                const dy = pos.y - y;
                return Math.sqrt(dx * dx + dy * dy) < spacing;
            });
        }

        for (let i = 0; i < piggyCount; i++) {
            let x, y, attempts = 0;
            do {
                x = Math.random() * window.innerWidth;
                y = Math.random() * fullHeight;
                attempts++;
            } while (isTooClose(x, y) && attempts < 100);

            positions.push({ x, y });

            const piggy = document.createElement("div");
            piggy.className = "floating-piggy";
            const size = 2 + Math.random() * 3;
            piggy.innerHTML = `<i class="fas fa-piggy-bank" style="font-size: ${size}rem;"></i>`;
            piggy.style.left = `${x}px`;
            piggy.style.top = `${y}px`;
            piggy.style.animationDelay = `${Math.random() * 6}s`;
            piggy.style.opacity = 0.08 + Math.random() * 0.15;
            piggyContainer.appendChild(piggy);
        }


        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const scrollTop = window.pageYOffset;
                    const piggies = document.querySelectorAll('.floating-piggy');
                    piggies.forEach(piggy => {
                        const currentTop = parseInt(piggy.style.top);
                        const viewportTop = scrollTop - window.innerHeight;
                        const viewportBottom = scrollTop + window.innerHeight * 2;

                        if (currentTop < viewportTop) {
                            piggy.style.top = (viewportBottom + Math.random() * window.innerHeight) + 'px';
                        } else if (currentTop > viewportBottom) {
                            piggy.style.top = (viewportTop - Math.random() * window.innerHeight) + 'px';
                        }
                    });
                    ticking = false;
                });
                ticking = true;
            }
        });
    }, 0);
});
</script>
</body>
</html>