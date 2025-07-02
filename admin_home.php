<?php

require_once 'admin_header.php';
require_once 'includes/db.php';

$admin_id = $_SESSION['user_id'];


$sql = "SELECT username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($admin_username);
$stmt->fetch();
$stmt->close();


$sql = "SELECT SUM(amount) FROM transactions WHERE type = 'income' AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$result = $conn->query($sql);
$total_income = $result->fetch_row()[0] ?? 0;


$sql = "SELECT SUM(amount) FROM transactions WHERE type = 'expense' AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$result = $conn->query($sql);
$total_expenses = $result->fetch_row()[0] ?? 0;


$current_savings = $total_income - $total_expenses;


$sql = "SELECT SUM(amount_limit) FROM budgets WHERE month = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')";
$result = $conn->query($sql);
$monthly_budget = $result->fetch_row()[0] ?? 0;


$budget_used_percentage = $monthly_budget > 0 ? ($total_expenses / $monthly_budget) * 100 : 0;


$sql = "SELECT COUNT(*) FROM users WHERE role = 'user'";
$result = $conn->query($sql);
$total_users = $result->fetch_row()[0] ?? 0;

$sql = "SELECT COUNT(*) FROM transactions WHERE MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())";
$result = $conn->query($sql);
$total_transactions = $result->fetch_row()[0] ?? 0;
?>
    <div id="piggy-bg"></div>
    <div class="piggy-container" aria-hidden="true"></div>

    <main class="homepagemain">
        <div class="dashboard-container">

            <div class="welcome-back">
                <div class="welcome-text">Welcome back, <?php echo htmlspecialchars($admin_username); ?>! 👋</div>
                <div class="welcome-subtitle">Here's the system-wide financial overview for today</div>
            </div>


            <section class="hero-section">
                <div class="hero-content">
                    <h1 class="hero-title">System Financial Overview</h1>
                    <p class="hero-subtitle">Monitor and manage all users' financial activities</p>
                </div>
            </section>


            <div class="stats-grid">
                <div class="stat-card" data-tooltip="Total income from all users this month">
                    <i class="fas fa-arrow-up stat-icon income"></i>
                    <div class="stat-value" data-value="<?php echo $total_income; ?>">RM 0</div>
                    <div class="stat-label">Total Income (All Users)</div>
                </div>

                <div class="stat-card" data-tooltip="Total expenses from all users this month">
                    <i class="fas fa-arrow-down stat-icon expense"></i>
                    <div class="stat-value" data-value="<?php echo $total_expenses; ?>">RM 0</div>
                    <div class="stat-label">Total Expenses (All Users)</div>
                </div>

                <div class="stat-card" data-tooltip="Total savings across all users">
                    <i class="fas fa-piggy-bank stat-icon savings"></i>
                    <div class="stat-value" data-value="<?php echo $current_savings; ?>">RM 0</div>
                    <div class="stat-label">Total Savings (All Users)</div>
                </div>

                <div class="stat-card" data-tooltip="Combined monthly budget from all users">
                    <i class="fas fa-chart-pie stat-icon budget"></i>
                    <div class="stat-value" data-value="<?php echo $monthly_budget; ?>">RM 0</div>
                    <div class="stat-label">Total Monthly Budget</div>
                </div>

                <div class="stat-card" data-tooltip="Total number of registered users">
                    <i class="fas fa-users stat-icon users"></i>
                    <div class="stat-value" data-value="<?php echo $total_users; ?>"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card" data-tooltip="Total transactions this month from all users">
                    <i class="fas fa-exchange-alt stat-icon transactions"></i>
                    <div class="stat-value" data-value="<?php echo $total_transactions; ?>"><?php echo $total_transactions; ?></div>
                    <div class="stat-label">Monthly Transactions</div>
                </div>
            </div>


            <div class="progress-overviewhm">
                <h3 class="progress-titlehm">System-wide Budget Progress</h3>
                <div class="budget-progress">
                    <div class="progress-headerhm">
                        <span>Budget Usage (All Users)</span>
                        <span><?php echo round($budget_used_percentage, 1); ?>%</span>
                    </div>
                    <div class="progress-barhm">
                        <div class="progress-fillhm" style="width: 0%" data-width="<?php echo $budget_used_percentage; ?>%"></div>
                    </div>
                </div>
            </div>


            <div class="quick-actions">
                <a href="admin_overview.php" class="action-btn" data-tooltip="View all user transactions">
                    <i class="fas fa-list action-icon"></i>
                    <span>All Transactions</span>
                </a>
                <a href="admin_dashboard.php" class="action-btn" data-tooltip="View detailed financial statistics">
                    <i class="fas fa-chart-bar action-icon"></i>
                    <span>Finance Statistics</span>
                </a>
            </div>


            <div class="onboarding-tooltip" id="tooltip"></div>
        </div>
    </main>

    <footer>
        <div class="footercontainer">
            <p>
                <span class="footeryear">&copy; <span id="current-year"></span> SECV2223-05</span>
                <span class="footergroup">Web Programming Group 4 - Admin Panel</span>
            </p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.getElementById('current-year').textContent = new Date().getFullYear();


            function animateValue(element, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    

                    if (element.textContent.includes('RM')) {
                        element.textContent = 'RM ' + value.toLocaleString();
                    } else {
                        element.textContent = value.toLocaleString();
                    }
                    
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }


            document.querySelectorAll('.stat-value[data-value]').forEach((element) => {
                const targetValue = parseInt(element.getAttribute('data-value'));

                if (element.textContent.includes('RM')) {
                    animateValue(element, 0, targetValue, 2000);
                }
            });


            setTimeout(() => {
                const progressFill = document.querySelector('.progress-fillhm');
                if (progressFill) {
                    const width = progressFill.getAttribute('data-width');
                    progressFill.style.width = width;
                }
            }, 1000);


            const tooltip = document.getElementById('tooltip');
            const elementsWithTooltips = document.querySelectorAll('[data-tooltip]');

            elementsWithTooltips.forEach(element => {
                element.addEventListener('mouseenter', (e) => {
                    const tooltipText = element.getAttribute('data-tooltip');
                    tooltip.textContent = tooltipText;
                    tooltip.classList.add('show');


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


            const menuIcon = document.getElementById('menuicon');
            const menu = document.querySelector('.menu');
            
            if (menuIcon && menu) {
                menuIcon.addEventListener('click', () => {
                    menu.classList.toggle('show');
                    menuIcon.classList.toggle('rotate');
                });
            }


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


            document.querySelectorAll('.stat-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });


        window.addEventListener("load", function () {
            setTimeout(() => {
                const piggyCount = 90; 
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