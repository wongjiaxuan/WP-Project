<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master Input</title>
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
                <li><a href="input.php" class="active">Transaction Input</a></li>
                <li><a href="overview.php">Transaction Overview</a></li>
                <li><a href="dashboard.html">Finance Dashboard</a></li>
            </ul>
        </nav>
    </header>
        
    <main class="inputmain">
        <section id="input">
            <!-- Dropdown to choose between Income and Expense -->
            <label for="type">Select Transaction Type:</label>
            <select id="transactionType" onchange="togglePanels()">
                <option value="income" <?php echo (isset($_POST['type']) && $_POST['type'] == 'income') ? 'selected' : ''; ?>>Income</option>
                <option value="expense" <?php echo (isset($_POST['type']) && $_POST['type'] == 'expense') ? 'selected' : ''; ?>>Expense</option>
            </select>

            <!-- Income Form Panel -->
            <div id="incomeForm" style="display: none;">
                <br>
                <form method="POST" action="insert_transaction.php">
                    <input type="hidden" name="type" value="income"> <!-- Hidden field for type -->

                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" required><br>
                    
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" required>
                        <?php
                        include 'includes/db.php';

                        // Fetch categories based on 'income'
                        $result = $conn->query("SELECT * FROM categories WHERE type = 'income'");
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_POST['category_id']) && $_POST['category_id'] == $row['category_id']) ? 'selected' : '';
                            echo "<option value='" . $row['category_id'] . "' $selected>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="date">Date:</label>
                    <input type="date" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>" required><br>

                    <label for="note">Note:</label>
                    <textarea name="note"><?php echo isset($_POST['note']) ? $_POST['note'] : ''; ?></textarea><br>
                    
                    <button type="submit">Add Income Transaction</button><br>
                </form>
            </div>

            <!-- Expense Form Panel -->
            <div id="expenseForm" style="display: none;">
                <br>
                <form method="POST" action="insert_transaction.php">
                    <input type="hidden" name="type" value="expense"> <!-- Hidden field for type -->

                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" required><br>
                    
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" required>
                        <?php
                        include 'includes/db.php';

                        // Fetch categories based on 'expense'
                        $result = $conn->query("SELECT * FROM categories WHERE type = 'expense'");
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_POST['category_id']) && $_POST['category_id'] == $row['category_id']) ? 'selected' : '';
                            echo "<option value='" . $row['category_id'] . "' $selected>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="date">Date:</label>
                    <input type="date" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>" required><br>

                    <label for="note">Note:</label>
                    <textarea name="note"><?php echo isset($_POST['note']) ? $_POST['note'] : ''; ?></textarea><br>
                    
                    <button type="submit">Add Expense Transaction</button><br>
                </form>
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
        // Function to toggle between the Income and Expense panels based on selected type
        function togglePanels() {
            var selectedType = document.getElementById('transactionType').value;
            var incomeForm = document.getElementById('incomeForm');
            var expenseForm = document.getElementById('expenseForm');
            
            if (selectedType === 'income') {
                incomeForm.style.display = 'block';
                expenseForm.style.display = 'none';
            } else if (selectedType === 'expense') {
                incomeForm.style.display = 'none';
                expenseForm.style.display = 'block';
            }
        }

        // Initialize by showing the correct form based on the default or selected type
        window.onload = function() {
            togglePanels();  // Toggle the panels based on the initial selection
        };
    </script>

</body>
</html>
