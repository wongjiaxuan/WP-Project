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
    <?php
    // Start session at the very beginning and include database connection
    session_start();
    include 'includes/db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("Please log in first.");
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
                <li><a href="overview.php" class="active">Transaction Overview</a></li>
                <li><a href="dashboard.html">Finance Dashboard</a></li>
            </ul>
        </nav>
    </header>
    
    <main class="overviewmain">
        <section id="overview">
            <!--Added by me-->
            <h2>Transaction History</h2>

            <!-- Dropdown to choose transaction type -->
            <form method="GET" action="overview.php">
                <label for="transactionType">Transaction Type:</label>
                <select id="transactionType" name="type" onchange="this.form.submit()" required>
                    <?php 
                    $selectedType = isset($_GET['type']) ? $_GET['type'] : 'all'; // Default to 'all'
                    ?>
                    <option value="all" <?php echo ($selectedType == 'all') ? 'selected' : ''; ?>>All</option>
                    <option value="expense" <?php echo ($selectedType == 'expense') ? 'selected' : ''; ?>>Expense</option>
                    <option value="income" <?php echo ($selectedType == 'income') ? 'selected' : ''; ?>>Income</option>
                </select>

                <!-- Category Filter -->
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">All</option>
                    <?php
                    // Get the selected type - default to 'all' if not set
                    $type = isset($_GET['type']) ? $_GET['type'] : 'all'; 

                    // Dynamically load categories based on the selected type
                    if ($type == 'income') {
                        $categoryQuery = "SELECT * FROM categories WHERE type = 'income'";
                    } elseif ($type == 'expense') {
                        $categoryQuery = "SELECT * FROM categories WHERE type = 'expense'";
                    } else {
                        $categoryQuery = "SELECT * FROM categories";
                    }

                    $categoryResult = $conn->query($categoryQuery);
                    
                    if ($categoryResult) {
                        // Loop through and display category options
                        while ($row = $categoryResult->fetch_assoc()) {
                            $selected = (isset($_GET['category']) && $_GET['category'] == $row['category_id']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['category_id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>

                <button type="submit">Filter</button>
            </form>

            <!-- Display the Transactions Table -->
            <?php
            // Check if "All" is selected to show separate tables - default to 'all' if not set
            $selectedType = isset($_GET['type']) ? $_GET['type'] : 'all';
            
            if ($selectedType === 'all') {
                // Show separate tables for Income and Expenses
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
                            </tr>
                          </thead>";
                    echo "<tbody>";
                    
                    // Prepare the SQL query for current type
                    $sql = "SELECT t.*, c.name AS category_name FROM transactions t 
                            JOIN categories c ON t.category_id = c.category_id
                            WHERE t.user_id = ? AND t.type = ?";
                    
                    $params = [$user_id, $currentType];
                    $types = "is";

                    // Add category filter if provided
                    if (!empty($_GET['category'])) {
                        $sql .= " AND t.category_id = ?";
                        $params[] = $_GET['category'];
                        $types .= "i";
                    }

                    // Add ORDER BY to sort by date (latest to oldest), then by transaction_id
                    $sql .= " ORDER BY t.date DESC, t.transaction_id DESC";

                    // Execute the query
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Check if there are any transactions
                        if ($result->num_rows > 0) {
                            $total = 0;
                            // Output the transactions
                            while ($row = $result->fetch_assoc()) {
                                $total += $row['amount'];
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['date']) . "</td>
                                        <td>" . htmlspecialchars($row['category_name']) . "</td>
                                        <td>RM " . number_format($row['amount'], 2) . "</td>
                                        <td>" . htmlspecialchars($row['note']) . "</td>
                                      </tr>";
                            }
                            // Add total row
                            echo "<tr style='font-weight: bold; background-color: #f0f0f0;'>
                                    <td colspan='2'>Total " . ucfirst($currentType) . "</td>
                                    <td>RM " . number_format($total, 2) . "</td>
                                    <td></td>
                                  </tr>";
                        } else {
                            echo "<tr><td colspan='4' style='text-align: center; padding: 20px;'>No " . $currentType . " transactions found.</td></tr>";
                        }

                        $stmt->close();
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center; padding: 20px; color: red;'>Error preparing query: " . htmlspecialchars($conn->error) . "</td></tr>";
                    }
                    
                    echo "</tbody>";
                    echo "</table>";
                    echo "<br>"; // Add space between tables
                }
            } else {
                // Show single table for specific type (income or expense)
                echo "<table>";
                echo "<thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount (RM)</th>
                            <th>Note</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                
                // Prepare the SQL query with filters
                $sql = "SELECT t.*, c.name AS category_name FROM transactions t 
                        JOIN categories c ON t.category_id = c.category_id
                        WHERE t.user_id = ?";
                
                $params = [$user_id];
                $types = "i";

                // Add filters if provided
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

                // Add ORDER BY to sort by date (latest to oldest), then by transaction_id
                $sql .= " ORDER BY t.date DESC, t.transaction_id DESC";

                // Execute the query
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Check if there are any transactions
                    if ($result->num_rows > 0) {
                        // Output the transactions
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['date']) . "</td>
                                    <td>" . htmlspecialchars(ucfirst($row['type'])) . "</td>
                                    <td>" . htmlspecialchars($row['category_name']) . "</td>
                                    <td>RM " . number_format($row['amount'], 2) . "</td>
                                    <td>" . htmlspecialchars($row['note']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No transactions found.</td></tr>";
                    }

                    $stmt->close();
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; padding: 20px; color: red;'>Error preparing query: " . htmlspecialchars($conn->error) . "</td></tr>";
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
        // Set current year in footer
        document.getElementById('current-year').textContent = new Date().getFullYear();

        // Form auto-submit on type change for better UX
        document.getElementById('transactionType').addEventListener('change', function() {
            // Reset category selection when type changes
            document.getElementById('category').value = '';
            this.form.submit();
        });
    </script>
</body>
</html>