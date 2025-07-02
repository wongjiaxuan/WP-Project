<?php
// Start session and include DB - MUST be at the very top
session_start();
include 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=Please log in first.");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get username
$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Get Total Income
$sql = "SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'income' AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_income);
$stmt->fetch();
$stmt->close();
$total_income = $total_income ?? 0;

// Get Total Expenses
$sql = "SELECT SUM(amount) FROM transactions WHERE user_id = ? AND type = 'expense' AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($total_expenses);
$stmt->fetch();
$stmt->close();
$total_expenses = $total_expenses ?? 0;

// Calculate Current Savings
$current_savings = $total_income - $total_expenses;

// Get Monthly Budget
$sql = "SELECT SUM(amount_limit) FROM budgets WHERE user_id = ? AND month = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($monthly_budget);
$stmt->fetch();
$stmt->close();
$monthly_budget = $monthly_budget ?? 0;

// Calculate Budget Usage
$budget_used_percentage = $monthly_budget > 0 ? ($total_expenses / $monthly_budget) * 100 : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master - Dashboard Home</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="An online budget tracker simplifies user's work on managing income, expenses and savings.">
    <meta name="author" content="Jimat Master Team">
    <meta property="og:title" content="Jimat Master - Personal Finance Dashboard">
    <meta property="og:description" content="Track your income, expenses, and achieve your financial goals with Jimat Master.">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <!-- <script src="script.js" defer></script> -->

</head>

<div class="piggy-container" aria-hidden="true"></div>
<div id="piggy-bg"></div>


<body>
<div class="piggy-container" aria-hidden="true"></div>
<div id="piggy-bg"></div>
    <header>
        <nav aria-label="Main Navigation">
            <div class="headername">Jimat Master</div>
            <i class="fa-solid fa-bars" id="menuicon"></i>
            <ul class="menu">
                <li><a href="home.php" class="active">Home</a></li>
                <li><a href="input.php">Transaction Input</a></li>
                <li><a href="set_budget.html">Monthly Budget</a></li>
                <li><a href="overview.php">Transaction Overview</a></li>
                <li><a href="dashboard.php">Finance Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="homepagemain">
        <div class="dashboard-container">
            <!-- Welcome Section -->
            <div class="welcome-back">
                <div class="welcome-text">Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</div>
                <div class="welcome-subtitle">Here's your financial overview for today</div>
            </div>

            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <h1 class="hero-title">Your Financial Journey</h1>
                    <p class="hero-subtitle">Take control of your money and build a better future</p>
                </div>
            </section>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card" data-tooltip="Your total income this month">
                    <i class="fas fa-arrow-up stat-icon income"></i>
                    <div class="stat-value" data-value="<?php echo $total_income; ?>">RM 0</div>
                    <div class="stat-label">Total Income</div>
                    <div class="stat-change positive">
                    </div>
                </div>

                <div class="stat-card" data-tooltip="Your total expenses this month">
                    <i class="fas fa-arrow-down stat-icon expense"></i>
                    <div class="stat-value" data-value="<?php echo $total_expenses; ?>">RM 0</div>
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-change negative">

                    </div>
                </div>

                <div class="stat-card" data-tooltip="Your current savings amount">
                    <i class="fas fa-piggy-bank stat-icon savings"></i>
                    <div class="stat-value" data-value="<?php echo $current_savings; ?>">RM 0</div>
                    <div class="stat-label">Current Savings</div>
                    <div class="stat-change positive">

                    </div>
                </div>

                <div class="stat-card" data-tooltip="Your monthly budget limit">
                    <i class="fas fa-chart-pie stat-icon budget"></i>
                    <div class="stat-value" data-value="<?php echo $monthly_budget; ?>">RM 0</div>
                    <div class="stat-label">Monthly Budget</div>
                    <div class="stat-change <?php echo $budget_used_percentage > 80 ? 'negative' : 'positive'; ?>">
                    </div>
                </div>
            </div>

            <!-- Budget Progress -->
            <div class="progress-overviewhm">
                <h3 class="progress-titlehm">Monthly Budget Progress</h3>
                <div class="budget-progress">
                    <div class="progress-headerhm">
                        <span>Budget Usage</span>
                        <span><?php echo round($budget_used_percentage, 1); ?>%</span>
                    </div>
                    <div class="progress-barhm">
                        <div class="progress-fillhm" style="width: 0%" data-width="<?php echo $budget_used_percentage; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="input.php" class="action-btn" data-tooltip="Add a new income or expense transaction">
                    <i class="fas fa-plus action-icon"></i>
                    <span>Add Transaction</span>
                </a>
                <a href="set_budget.html" class="action-btn" data-tooltip="Set or update your monthly budget">
                    <i class="fas fa-target action-icon"></i>
                    <span>Set Budget</span>
                </a>
                <a href="overview.php" class="action-btn" data-tooltip="View all your transactions">
                    <i class="fas fa-list action-icon"></i>
                    <span>View Transactions</span>
                </a>
                <a href="dashboard.php" class="action-btn" data-tooltip="See detailed financial analytics">
                    <i class="fas fa-chart-bar action-icon"></i>
                    <span>Analytics</span>
                </a>
            </div>

            <!-- Tooltip for onboarding -->
            <div class="onboarding-tooltip" id="tooltip"></div>
        </div>
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
        // Enhanced JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Set current year
            document.getElementById('current-year').textContent = new Date().getFullYear();

            // Animate counter values
            function animateValue(element, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = 'RM ' + value.toLocaleString();
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }

            // Animate all stat values
            document.querySelectorAll('.stat-value[data-value]').forEach((element) => {
                const targetValue = parseInt(element.getAttribute('data-value'));
                animateValue(element, 0, targetValue, 2000);
            });

            // Animate progress bar
            setTimeout(() => {
                const progressFill = document.querySelector('.progress-fillhm');
                if (progressFill) {
                    const width = progressFill.getAttribute('data-width');
                    progressFill.style.width = width;
                }
            }, 1000);

            // Tooltip functionality
            const tooltip = document.getElementById('tooltip');
            const elementsWithTooltips = document.querySelectorAll('[data-tooltip]');

            elementsWithTooltips.forEach(element => {
                element.addEventListener('mouseenter', (e) => {
                    const tooltipText = element.getAttribute('data-tooltip');
                    tooltip.textContent = tooltipText;
                    tooltip.classList.add('show');

                    // Allow tooltip to be visible before calculating width
                    requestAnimationFrame(() => {
                        const rect = element.getBoundingClientRect();
                        const tooltipRect = tooltip.getBoundingClientRect();

                        tooltip.style.left = `${rect.left + (rect.width - tooltipRect.width) / 2}px`;
                        tooltip.style.top = `${rect.bottom + 10}px`;
                    });
                });

                element.addEventListener('mouseleave', () => {
                    tooltip.classList.remove('show');
                });
            });

            // Enhanced menu toggle
            const menuIcon = document.getElementById('menuicon');
            const menu = document.querySelector('.menu');
            
            if (menuIcon && menu) {
                menuIcon.addEventListener('click', () => {
                    menu.classList.toggle('show');
                    menuIcon.classList.toggle('rotate');
                });
            }

            // Add subtle parallax effect to hero section
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const heroSection = document.querySelector('.hero-section');
                if (heroSection) {
                    heroSection.style.transform = `translateY(${scrolled * 0.1}px)`;
                }
            });

            // Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe stat cards for staggered animation
            document.querySelectorAll('.stat-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });

         window.addEventListener("load", function () {
    setTimeout(() => {
        const piggyCount = 50;
        const spacing = 100;
        const positions = [];

        const piggyContainer = document.querySelector('.piggy-container');
        const fullHeight = document.documentElement.scrollHeight;
        piggyContainer.style.height = fullHeight + 'px';

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

            const size = 2 + Math.random() * 4; // 2rem to 6rem
            piggy.innerHTML = `<i class="fas fa-piggy-bank" style="font-size: ${size}rem;"></i>`;

            piggy.style.left = `${x}px`;
            piggy.style.top = `${y}px`;
            piggy.style.position = 'absolute';
            piggy.style.animationDelay = `${Math.random() * 6}s`;
            piggy.style.opacity = 0.1 + Math.random() * 0.2;
            piggy.style.pointerEvents = 'none';

            piggyContainer.appendChild(piggy);
        }
    }, 500); // delay to ensure DOM height is final
});




    </script>
    
</body>
</html>