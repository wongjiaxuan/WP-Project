<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>
        <?php
            if ($current_page === 'admin_dashboard.php') {
                echo "Dashboard - Admin Panel";
            } elseif ($current_page === 'admin_overview.php') {
                echo "All Transactions - Admin Panel";
            } else {
                echo "Admin Panel - Jimat Master";
            }
        ?>
    </title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
</head>
<body>

<!-- Unified header for all admin pages (dashboard-style layout) -->
<header>
    <nav>
        <div class="headername">Admin Panel</div>
        <ul class="menu">
            <li><a href="admin_dashboard.php" class="<?= $current_page === 'admin_dashboard.php' ? 'active' : '' ?>">Finance Statistics</a></li>
            <li><a href="admin_overview.php" class="<?= $current_page === 'admin_overview.php' ? 'active' : '' ?>">All Transactions</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>
