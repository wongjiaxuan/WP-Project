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

// Get user statistics (you'll need to implement these queries based on your database structure)
// For demo purposes, using placeholder values
$total_income = 5000; // Get from database
$total_expenses = 3200; // Get from database
$current_savings = 1800; // Get from database
$monthly_budget = 4000; // Get from database
$budget_used_percentage = ($total_expenses / $monthly_budget) * 100;
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
    <script src="script.js" defer></script>
    <style>
        /* Enhanced Dashboard Styles */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .hero-section {
            background: linear-gradient(135deg, #bcf7c4 0%, #ace8fb 50%, #c3f9e2 100%);
            padding: 2rem;
            border-radius: 20px;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: floatBubble 8s ease-in-out infinite;
        }

        @keyframes floatBubble {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: bold;
            color: #083c5f;
            margin-bottom: 1rem;
            animation: slideInLeft 1s ease-out;
        }

        .hero-subtitle {
            font-size: clamp(1.2rem, 3vw, 1.6rem);
            color: #0c6527;
            margin-bottom: 2rem;
            animation: slideInLeft 1s ease-out 0.2s both;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1aca2e, #117263, #2caa41);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: translateX(0);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }

        .stat-icon.income { color: #1aca2e; }
        .stat-icon.expense { color: #dc3545; }
        .stat-icon.savings { color: #117263; }
        .stat-icon.budget { color: #2caa41; }

        .stat-value {
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: bold;
            color: #083c5f;
            margin-bottom: 0.5rem;
            counter-reset: none;
        }

        .stat-label {
            font-size: 1rem;
            color: #0c6527;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .stat-change {
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .stat-change.positive { color: #1aca2e; }
        .stat-change.negative { color: #dc3545; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #117263;
            border-radius: 12px;
            text-decoration: none;
            color: #083c5f;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s ease;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(17, 114, 99, 0.2);
            border-color: #1aca2e;
        }

        .action-icon {
            font-size: 1.5rem;
            color: #117263;
        }

        .onboarding-tooltip {
            position: absolute;
            background: #083c5f;
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            z-index: 1000;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            pointer-events: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .onboarding-tooltip::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #083c5f;
        }

        .onboarding-tooltip.show {
            opacity: 1;
            transform: translateY(0);
        }

        .welcome-back {
            background: linear-gradient(135deg, #ffffff 0%, #f1f8ff 100%);
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            border-left: 5px solid #117263;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            animation: fadeInUp 1s ease-out 0.8s both;
        }

        .welcome-text {
            font-size: clamp(1.3rem, 3vw, 1.6rem);
            color: #083c5f;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #0c6527;
            font-size: 1rem;
        }

        .progress-overview {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin: 2rem 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease-out 1s both;
        }

        .progress-title {
            font-size: 1.5rem;
            color: #083c5f;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        .budget-progress {
            margin-bottom: 1.5rem;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e9ecef;
            border-radius: 6px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1aca2e, #117263);
            border-radius: 6px;
            transition: width 1s ease-out;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .big-piggy {
            position: fixed;
            bottom: 100px;
            right: 40px;
            z-index: 5;
            opacity: 0.15;
            animation: piggyFloat 6s ease-in-out infinite;
            transition: opacity 0.3s ease;
        }

        .big-piggy:hover {
            opacity: 0.3;
        }

        .big-piggy i {
            font-size: clamp(8vw, 120px, 150px);
            color: #117263;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        @keyframes piggyFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-15px) rotate(2deg); }
            66% { transform: translateY(-5px) rotate(-1deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-section {
                padding: 1.5rem;
                margin: 1rem 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .stat-card {
                padding: 1.5rem;
            }
            
            .big-piggy {
                bottom: 80px;
                right: 20px;
            }
        }

        /* Loading Animation */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
    </style>
</head>

<body>
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
                        <i class="fas fa-arrow-up"></i>
                        +12% from last month
                    </div>
                </div>

                <div class="stat-card" data-tooltip="Your total expenses this month">
                    <i class="fas fa-arrow-down stat-icon expense"></i>
                    <div class="stat-value" data-value="<?php echo $total_expenses; ?>">RM 0</div>
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i>
                        +8% from last month
                    </div>
                </div>

                <div class="stat-card" data-tooltip="Your current savings amount">
                    <i class="fas fa-piggy-bank stat-icon savings"></i>
                    <div class="stat-value" data-value="<?php echo $current_savings; ?>">RM 0</div>
                    <div class="stat-label">Current Savings</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +15% from last month
                    </div>
                </div>

                <div class="stat-card" data-tooltip="Your monthly budget limit">
                    <i class="fas fa-chart-pie stat-icon budget"></i>
                    <div class="stat-value" data-value="<?php echo $monthly_budget; ?>">RM 0</div>
                    <div class="stat-label">Monthly Budget</div>
                    <div class="stat-change <?php echo $budget_used_percentage > 80 ? 'negative' : 'positive'; ?>">
                        <i class="fas fa-info-circle"></i>
                        <?php echo round($budget_used_percentage, 1); ?>% used
                    </div>
                </div>
            </div>

            <!-- Budget Progress -->
            <div class="progress-overview">
                <h3 class="progress-title">Monthly Budget Progress</h3>
                <div class="budget-progress">
                    <div class="progress-header">
                        <span>Budget Usage</span>
                        <span><?php echo round($budget_used_percentage, 1); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%" data-width="<?php echo $budget_used_percentage; ?>%"></div>
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

            <!-- Floating Piggy Bank -->
            <div class="big-piggy">
                <i class="fas fa-piggy-bank"></i>
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
                const progressFill = document.querySelector('.progress-fill');
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
                    const tooltipText = e.target.getAttribute('data-tooltip');
                    tooltip.textContent = tooltipText;
                    tooltip.classList.add('show');
                    
                    const rect = e.target.getBoundingClientRect();
                    tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
                    tooltip.style.top = rect.bottom + 10 + 'px';
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
    </script>
    
</body>
</html>