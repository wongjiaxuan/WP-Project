<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master Overview</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="An online budget tracker simplifies user's work on managing income, expenses and savings.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>

<body>
    <div id="piggy-bg"></div>
    <div class="piggy-container" aria-hidden="true"></div>
    
    <?php

    session_start();
    include 'includes/db.php';

 
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?error=Please log in first.");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    ?>

    <header>
        <nav aria-label="Main Navigation">
            <div class="headername">Jimat Master</div>
            <i class="fa-solid fa-bars" id="menuicon"></i>
            <ul class="menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="input.php">Transaction Input</a></li>
                <li><a href="set_budget.html">Monthly Budget</a></li>
                <li><a href="overview.php" class="active">Transaction Overview</a></li>
                <li><a href="dashboard.php">Finance Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    
    <main class="overviewmain">
        <section id="overview">
            <h2>Transaction History</h2>


            <form method="GET" action="overview.php">
                <div class="filtergroup">
                    <label for="transactionType">Transaction Type:</label>
                    <select id="OtransactionType" name="type" onchange="this.form.submit()" required>
                        <?php 
                        $selectedType = isset($_GET['type']) ? $_GET['type'] : 'all'; 
                        ?>
                        <option value="all" <?php echo ($selectedType == 'all') ? 'selected' : ''; ?>>All</option>
                        <option value="expense" <?php echo ($selectedType == 'expense') ? 'selected' : ''; ?>>Expense</option>
                        <option value="income" <?php echo ($selectedType == 'income') ? 'selected' : ''; ?>>Income</option>
                    </select>
                </div>


                <div class="filtergroup">
                    <label for="category">Category:</label>
                    <select name="category" id="Ocategory">
                        <option value="">All</option>
                        <?php

                        $type = isset($_GET['type']) ? $_GET['type'] : 'all'; 
    
  
                        if ($type == 'income') {
                            $categoryQuery = "SELECT * FROM categories WHERE type = 'income'";
                        } elseif ($type == 'expense') {
                            $categoryQuery = "SELECT * FROM categories WHERE type = 'expense'";
                        } else {
                            $categoryQuery = "SELECT * FROM categories";
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
                        <option value="" <?php echo (!isset($_GET['date_filter']) || $_GET['date_filter'] == '') ? 'selected' : ''; ?>>All Time</option>
                        <option value="7" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == '7') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="14" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == '14') ? 'selected' : ''; ?>>Last 14 Days</option>
                        <option value="30" <?php echo (isset($_GET['date_filter']) && $_GET['date_filter'] == '30') ? 'selected' : ''; ?>>Last 30 Days</option>
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
                                <th>Date</th>
                                <th>Category</th>
                                <th>Amount (RM)</th>
                                <th>Note</th>
                                <th>Actions</th>
                            </tr>
                          </thead>";
                    echo "<tbody>";
                    
                    $sql = "SELECT t.*, c.name AS category_name FROM transactions t 
                            JOIN categories c ON t.category_id = c.category_id
                            WHERE t.user_id = ? AND t.type = ?";
                    
                    $params = [$user_id, $currentType];
                    $types = "is";

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
                                echo "<tr id='row-{$row['transaction_id']}'>
                                        <td class='date-cell'>" . htmlspecialchars($row['date']) . "</td>
                                        <td class='category-cell' data-category-id='" . htmlspecialchars($row['category_id']) . "'>" . htmlspecialchars($row['category_name']) . "</td>
                                        <td class='amount-cell'>" . number_format($row['amount'], 2) . "</td>
                                        <td class='note-cell'>" . htmlspecialchars($row['note']) . "</td>
                                        <td class='action-cell'>
                                            <div class='action-buttons'>
                                                <button class='btn-edit' onclick='editTransaction({$row['transaction_id']})'>Edit</button>
                                                <button class='btn-delete' onclick='deleteTransaction({$row['transaction_id']})'>Delete</button>
                                            </div>
                                        </td>
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
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount (RM)</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                
                $sql = "SELECT t.*, c.name AS category_name FROM transactions t 
                        JOIN categories c ON t.category_id = c.category_id
                        WHERE t.user_id = ?";
                
                $params = [$user_id];
                $types = "i";

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
                
                if ($stmt) {
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='row-{$row['transaction_id']}'>
                                    <td class='date-cell'>" . htmlspecialchars($row['date']) . "</td>
                                    <td class='type-cell'>" . htmlspecialchars(ucfirst($row['type'])) . "</td>
                                    <td class='category-cell' data-category-id='" . htmlspecialchars($row['category_id']) . "'>" . htmlspecialchars($row['category_name']) . "</td>
                                    <td class='amount-cell'>" . number_format($row['amount'], 2) . "</td>
                                    <td class='note-cell'>" . htmlspecialchars($row['note']) . "</td>
                                    <td class='action-cell'>
                                        <div class='action-buttons'>
                                            <button class='btn-edit' onclick='editTransaction({$row['transaction_id']})'>Edit</button>
                                            <button class='btn-delete' onclick='deleteTransaction({$row['transaction_id']})'>Delete</button>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No transactions found.</td></tr>";
                    }

                    $stmt->close();
                } else {
                    echo "<tr><td colspan='6' style='text-align: center; padding: 20px; color: red;'>Error preparing query: " . htmlspecialchars($conn->error) . "</td></tr>";
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

        document.getElementById('OtransactionType').addEventListener('change', function() {
            document.getElementById('Ocategory').value = '';
            document.getElementById('Odate_filter').value = '';
            this.form.submit();
        });

        function deleteTransaction(transactionId) {
            if (confirm("Are you sure that you want to delete this transaction?")) {
                fetch('delete_transaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'transaction_id=' + transactionId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('row-' + transactionId).remove();
                        alert('Transaction deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error deleting transaction: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting transaction');
                });
            }
        }

        function editTransaction(transactionId) {
            const row = document.getElementById('row-' + transactionId);
            const dateCell = row.querySelector('.date-cell');
            const categoryCell = row.querySelector('.category-cell');
            const amountCell = row.querySelector('.amount-cell');
            const noteCell = row.querySelector('.note-cell');
            const actionCell = row.querySelector('.action-cell');

            const originalDate = dateCell.textContent.trim();
            const originalCategoryId = categoryCell.getAttribute('data-category-id');
            const originalCategoryName = categoryCell.textContent.trim();
            const originalAmount = amountCell.textContent.replace(/,/g, '').trim(); 
            const originalNote = noteCell.textContent.trim();

            let transactionType = 'all'; 
            const tables = document.querySelectorAll('table');
            
            for (let table of tables) {
                if (table.contains(row)) {
                    let currentElement = table.previousElementSibling;
                    while (currentElement && currentElement.tagName !== 'H3') {
                        currentElement = currentElement.previousElementSibling;
                    }
                    if (currentElement && currentElement.textContent) {
                        if (currentElement.textContent.toLowerCase().includes('income')) {
                            transactionType = 'income';
                        } else if (currentElement.textContent.toLowerCase().includes('expense')) {
                            transactionType = 'expense';
                        }
                    }
                    break;
                }
            }

            dateCell.innerHTML = `<input type="date" class="edit-input" value="${originalDate}" id="edit-date-${transactionId}">`;
            
            const categoryUrl = transactionType !== 'all' ? `get_categories.php?type=${transactionType}` : 'get_categories.php';
            
            fetch(categoryUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(categories => {
                    if (categories.error) {
                        throw new Error(categories.error);
                    }
                    let categoryOptions = '';
                    categories.forEach(category => {
                        const selected = category.category_id == originalCategoryId ? 'selected' : '';
                        categoryOptions += `<option value="${category.category_id}" ${selected}>${category.name}</option>`;
                    });
                    categoryCell.innerHTML = `<select class="edit-select" id="edit-category-${transactionId}">${categoryOptions}</select>`;
                })
                .catch(error => {
                    console.error('Error fetching categories:', error);
                    alert('Error loading categories: ' + error.message);
                });

            amountCell.innerHTML = `<input type="number" step="0.01" min="0.01" class="edit-input" value="${originalAmount}" id="edit-amount-${transactionId}">`;
            noteCell.innerHTML = `<input type="text" class="edit-input" value="${originalNote}" id="edit-note-${transactionId}">`;
            
            actionCell.innerHTML = `
                <div class="action-buttons">
                    <button class="btn-confirm" onclick="confirmEdit(${transactionId})">Confirm</button>
                    <button class="btn-cancel" onclick="cancelEdit(${transactionId}, '${originalDate}', '${originalCategoryId}', '${originalCategoryName.replace(/'/g, "\\'")}', '${originalAmount}', '${originalNote.replace(/'/g, "\\'")}')">Cancel</button>
                </div>
            `;
        }

        function confirmEdit(transactionId) {
            const newDate = document.getElementById(`edit-date-${transactionId}`).value;
            const newCategoryId = document.getElementById(`edit-category-${transactionId}`).value;
            const newAmount = document.getElementById(`edit-amount-${transactionId}`).value;
            const newNote = document.getElementById(`edit-note-${transactionId}`).value;

            fetch('update_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `transaction_id=${transactionId}&date=${newDate}&category_id=${newCategoryId}&amount=${newAmount}&note=${encodeURIComponent(newNote)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaction updated successfully!');
                    location.reload(); 
                } else {
                    alert('Error updating transaction: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating transaction');
            });
        }

        function cancelEdit(transactionId, originalDate, originalCategoryId, originalCategoryName, originalAmount, originalNote) {
            const row = document.getElementById('row-' + transactionId);
            const dateCell = row.querySelector('.date-cell');
            const categoryCell = row.querySelector('.category-cell');
            const amountCell = row.querySelector('.amount-cell');
            const noteCell = row.querySelector('.note-cell');
            const actionCell = row.querySelector('.action-cell');

            dateCell.textContent = originalDate;
            categoryCell.textContent = originalCategoryName;
            categoryCell.setAttribute('data-category-id', originalCategoryId);
            amountCell.textContent = originalAmount;
            noteCell.textContent = originalNote;
            
            actionCell.innerHTML = `
                <div class="action-buttons">
                    <button class="btn-edit" onclick="editTransaction(${transactionId})">Edit</button>
                    <button class="btn-delete" onclick="deleteTransaction(${transactionId})">Delete</button>
                </div>
            `;
        }

        window.addEventListener("load", function () {
            setTimeout(() => {
                const piggyCount = 200; 
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