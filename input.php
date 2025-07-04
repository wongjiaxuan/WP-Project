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
    <?php
    session_start();
    date_default_timezone_set('Asia/Kuala_Lumpur');
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?error=Please log in first.");
        exit();
    }
    
    if (isset($_GET['error'])) {
        echo "<div class='error-message' style='color: red; padding: 10px; margin: 10px 0; border: 1px solid red; background-color: #ffe6e6;'>";
        echo htmlspecialchars($_GET['error']);
        echo "</div>";
    }
    
    if (isset($_GET['success'])) {
        echo "<div class='success-message' style='color: green; padding: 10px; margin: 10px 0; border: 1px solid green; background-color: #e6ffe6;'>";
        echo htmlspecialchars($_GET['success']);
        echo "</div>";
    }
    ?>

    <div id="piggy-bg"></div>
    <div class="piggy-container" aria-hidden="true"></div>

    <header>
        <nav aria-label="Main Navigation">
            <div class="headername">Jimat Master</div>
            <i class="fa-solid fa-bars" id="menuicon"></i>
            <ul class="menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="input.php" class="active">Transaction Input</a></li>
                <li><a href="set_budget.html">Monthly Budget</a></li>
                <li><a href="overview.php">Transaction Overview</a></li>
                <li><a href="dashboard.php">Finance Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
        
    <main class="inputmain">
        <section id="input">
            <label for="type" class="transactiontypelabel">Select Transaction Type:</label>
            <select id="transactionType" onchange="togglePanels()">
                <option value="income" <?php echo (isset($_POST['type']) && $_POST['type'] == 'income') ? 'selected' : ''; ?>>Income</option>
                <option value="expense" <?php echo (isset($_POST['type']) && $_POST['type'] == 'expense') ? 'selected' : ''; ?>>Expense</option>
            </select>

            <div id="incomeForm" style="display: none;">
                <br>
                <form method="POST" action="insert_transaction.php">
                    <input type="hidden" name="type" value="income">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" min="0.01" step="0.01" 
                           value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" 
                           required placeholder="Enter amount"><br>
                
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" required>
                        <?php
                        include 'includes/db.php';
                        $result = $conn->query("SELECT * FROM categories WHERE type = 'income'");
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_POST['category_id']) && $_POST['category_id'] == $row['category_id']) ? 'selected' : '';
                            echo "<option value='" . $row['category_id'] . "' $selected>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="date">Date:</label>
                    <input type="date" name="date" max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>" required><br>

                    <label for="note">Note:</label>
                    <textarea name="note"><?php echo isset($_POST['note']) ? $_POST['note'] : ''; ?></textarea><br>
                    
                    <button type="submit">Add Income Transaction</button><br>
                </form>
            </div>

            <div id="expenseForm" style="display: none;">
                <br>
                <form method="POST" action="insert_transaction.php">
                    <input type="hidden" name="type" value="expense">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" min="0.01" step="0.01" 
                           value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" 
                           required placeholder="Enter amount"><br>
                    
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" required>
                        <?php
                        include 'includes/db.php';
                        $result = $conn->query("SELECT * FROM categories WHERE type = 'expense'");
                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_POST['category_id']) && $_POST['category_id'] == $row['category_id']) ? 'selected' : '';
                            echo "<option value='" . $row['category_id'] . "' $selected>" . $row['name'] . "</option>";
                        }
                        ?>
                    </select><br>

                    <label for="date">Date:</label>
                    <input type="date" name="date" max="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo isset($_POST['date']) ? $_POST['date'] : ''; ?>" required><br>

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
        document.getElementById('current-year').textContent = new Date().getFullYear();
        
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
        
        window.onload = function() {
            togglePanels();
        };

        const amountInputs = document.querySelectorAll('input[name="amount"]');
        amountInputs.forEach(input => {
            input.addEventListener('input', function() {
                this.setCustomValidity('');
            });
            
            input.addEventListener('blur', function() {
                if (this.value && parseFloat(this.value) <= 0) {
                    this.setCustomValidity('Please enter a valid amount greater than 0.');
                } else {
                    this.setCustomValidity('');
                }
            });
        });
        
        window.addEventListener("load", function () {
            setTimeout(() => {
                const piggyCount = 100;
                const spacing = 100;
                const positions = [];
                const piggyContainer = document.querySelector('.piggy-container');
                const fullHeight = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);

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