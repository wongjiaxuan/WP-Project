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
        
<!--I do ehhhhhhhh-->
    <main class="inputmain">
        <section id="input">
            <form method="POST" action="insert_transaction.php">
                
                <label for="amount">Amount:</label>
                <input type="number" name="amount" required><br>
                
                <label for="type">Type:</label>
                <select name="type" required>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select><br>
                
                <label for="category_id">Category:</label>
                <select name="category_id" required>
                    <!-- Categories will be loaded dynamically from DB -->
                    <?php
                        include 'includes/db.php';
                        $result = $conn->query("SELECT * FROM categories");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                        }
                    ?>
                </select><br>

                <label for="date">Date:</label>
                <input type="date" name="date" required><br>
                
                <label for="note">Note:</label>
                <textarea name="note"></textarea><br>
                
                <button type="submit">Add Transaction</button><br>
            </form>
        </section>
    </main>
<!--I do ehhhhhhhh-->

    <footer>
        <div class="footercontainer">
            <p>&copy; <span id="current-year"></span> SECV2223-05 Web Programming Group 4</p>
        </div>
    </footer>
</body>
</html>