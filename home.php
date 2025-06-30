<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master Home Page</title>
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
                <li><a href="home.php" class="active">Home</a></li>
                <li><a href="input.php">Transaction Input</a></li>
                <li><a href="set_budget.html">Monthly Budget</a></li>
                <li><a href="overview.php">Transaction Overview</a></li>
                <li><a href="dashboard.php">Finance Dashboard</a></li>
            </ul>
        </nav>
    </header>
    <main class="homepagemain">
        <section id="homepage">
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
</body>
</html>
