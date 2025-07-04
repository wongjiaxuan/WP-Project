<?php
require_once 'admin_header.php';
require_once '../includes/db.php';


$transaction_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$date_filter = $_GET['date_filter'] ?? '';

?>

    <div id="piggy-bg"></div>
    <div class="piggy-container" aria-hidden="true"></div>

<main class="overviewmain">
    <section id="overview">
        <h2>All User Transactions</h2>

        <form method="GET" action="admin_overview.php">
            <div class="filtergroup">
                <label for="transactionType">Transaction Type:</label>
                <select id="OtransactionType" name="type" onchange="this.form.submit()" required>
                    <?php $selectedType = $_GET['type'] ?? 'all'; ?>
                    <option value="all" <?= $selectedType == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="expense" <?= $selectedType == 'expense' ? 'selected' : '' ?>>Expense</option>
                    <option value="income" <?= $selectedType == 'income' ? 'selected' : '' ?>>Income</option>
                </select>
            </div>

            <div class="filtergroup">
                <label for="category">Category:</label>
                <select name="category" id="Ocategory">
                    <option value="">All</option>
                    <?php

                    $type = isset($_GET['type']) ? $_GET['type'] : 'all'; 

 
                    if ($type == 'income') {
                        $categoryQuery = "SELECT * FROM categories WHERE type = 'income' ORDER BY name";
                    } elseif ($type == 'expense') {
                        $categoryQuery = "SELECT * FROM categories WHERE type = 'expense' ORDER BY name";
                    } else {
                        $categoryQuery = "SELECT * FROM categories ORDER BY name";
                    }

                    $categoryResult = $conn->query($categoryQuery);
                    
                    if ($categoryResult) {

                        while ($row = $categoryResult->fetch_assoc()) {
                            $selected = (isset($_GET['category']) && $_GET['category'] == $row['category_id']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['category_id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="filtergroup">
                <label for="date_filter">Date Filter:</label>
                <select name="date_filter" id="Odate_filter">
                    <option value="" <?= $date_filter == '' ? 'selected' : '' ?>>All Time</option>
                    <option value="7" <?= $date_filter == '7' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="14" <?= $date_filter == '14' ? 'selected' : '' ?>>Last 14 Days</option>
                    <option value="30" <?= $date_filter == '30' ? 'selected' : '' ?>>Last 30 Days</option>
                </select>
            </div>

            <div class="filtergroup">
                <button type="submit" class="filterbtn">Filter</button>
            </div>
        </form>


        <?php

        $selectedType = isset($_GET['type']) ? $_GET['type'] : 'all';
        
        if ($selectedType === 'all') {

            $transactionTypes = ['income', 'expense'];
            
            foreach ($transactionTypes as $currentType) {
                echo "<h3>" . ucfirst($currentType) . " Transactions</h3>";
                echo "<table>";
                echo "<thead>
                        <tr>
                            <th>User</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount (RM)</th>
                            <th>Note</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                

                $sql = "SELECT t.*, c.name AS category_name, u.username 
                        FROM transactions t 
                        JOIN categories c ON t.category_id = c.category_id
                        JOIN users u ON t.user_id = u.user_id
                        WHERE t.type = ?";
                
                $params = [$currentType];
                $types = "s";


                if (!empty($_GET['category'])) {
                    $sql .= " AND t.category_id = ?";
                    $params[] = $_GET['category'];
                    $types .= "i";
                }


                if (!empty($_GET['date_filter']) && is_numeric($_GET['date_filter'])) {
                    $days = (int)$_GET['date_filter'];
                    $sql .= " AND t.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
                    $params[] = $days;
                    $types .= "i";
                }


                $sql .= " ORDER BY t.date DESC, t.transaction_id DESC";


                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();


                    if ($result->num_rows > 0) {
                        $total = 0;

                        while ($row = $result->fetch_assoc()) {
                            $total += $row['amount'];
                            echo "<tr>
                                    <td><strong>" . htmlspecialchars($row['username']) . "</strong></td>
                                    <td>" . htmlspecialchars($row['date']) . "</td>
                                    <td>" . htmlspecialchars($row['category_name']) . "</td>
                                    <td>" . number_format($row['amount'], 2) . "</td>
                                    <td>" . htmlspecialchars($row['note']) . "</td>
                                  </tr>";
                        }

                        echo "<tr style='font-weight: bold; background-color: #f0f0f0;'>
                                <td colspan='3'>Total " . ucfirst($currentType) . "</td>
                                <td>RM " . number_format($total, 2) . "</td>
                                <td></td>
                              </tr>";
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No " . $currentType . " transactions found.</td></tr>";
                    }

                    $stmt->close();
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; padding: 20px; color: red;'>Error preparing query: " . htmlspecialchars($conn->error) . "</td></tr>";
                }
                
                echo "</tbody>";
                echo "</table>";
                echo "<br>"; 
            }
        } else {

            echo "<table>";
            echo "<thead>
                    <tr>
                        <th>User</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount (RM)</th>
                        <th>Note</th>
                    </tr>
                  </thead>";
            echo "<tbody>";
            

            $sql = "SELECT t.*, c.name AS category_name, u.username 
                    FROM transactions t 
                    JOIN categories c ON t.category_id = c.category_id
                    JOIN users u ON t.user_id = u.user_id
                    WHERE 1";
            
            $params = [];
            $types = "";


            if (!empty($_GET['category'])) {
                $sql .= " AND t.category_id = ?";
                $params[] = $_GET['category'];
                $types .= "i";
            }

            if (!empty($_GET['type']) && $_GET['type'] !== 'all') {
                $sql .= " AND t.type = ?";
                $params[] = $_GET['type'];
                $types .= "s";
            }


            if (!empty($_GET['date_filter']) && is_numeric($_GET['date_filter'])) {
                $days = (int)$_GET['date_filter'];
                $sql .= " AND t.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
                $params[] = $days;
                $types .= "i";
            }


            $sql .= " ORDER BY t.date DESC, t.transaction_id DESC";


            $stmt = $conn->prepare($sql);
            
            if ($stmt && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();


            if ($result->num_rows > 0) {

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td><strong>" . htmlspecialchars($row['username']) . "</strong></td>
                            <td>" . htmlspecialchars($row['date']) . "</td>
                            <td>" . htmlspecialchars(ucfirst($row['type'])) . "</td>
                            <td>" . htmlspecialchars($row['category_name']) . "</td>
                            <td>RM " . number_format($row['amount'], 2) . "</td>
                            <td>" . htmlspecialchars($row['note']) . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No transactions found.</td></tr>";
            }

            if ($stmt) {
                $stmt->close();
            }
            
            echo "</tbody>";
            echo "</table>";
        }

        $conn->close();
        ?>

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
    document.getElementById('current-year').textContent = new Date().getFullYear();

    document.getElementById('OtransactionType').addEventListener('change', function () {

        document.getElementById('Ocategory').value = '';
        document.getElementById('Odate_filter').value = '';
        this.form.submit();
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