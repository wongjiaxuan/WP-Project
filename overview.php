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

            <form method="GET" action="get_transactions.php">
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">All</option>
                    <!-- Categories should be loaded here -->
                    <?php
                        session_start();
                        include 'includes/db.php';
                        $result = $conn->query("SELECT * FROM categories");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['category_id'] . "'>" . $row['name'] . "</option>";
                        }
                    ?>
                </select>
                
                <label for="date_range">Date Range:</label>
                <input type="date" name="start_date">
                <input type="date" name="end_date">

                <button type="submit">Filter</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody id="transaction-list">
                    <!-- Transactions will be dynamically loaded here -->
                </tbody>
            </table>
            <!--Added by me-->
        </section>
    </main>

    <footer>
        <div class="footercontainer">
            <p>&copy; <span id="current-year"></span> SECV2223-05 Web Programming Group 4</p>
        </div>
    </footer>
</body>
</html>