<?php
require_once 'admin_header.php';
require_once 'includes/db.php';

// Filters
$transaction_type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? '';
$date_filter = $_GET['date_filter'] ?? '';

// For admin, get all users’ data
$sql_base = "SELECT t.*, c.name AS category_name, u.username 
             FROM transactions t 
             JOIN categories c ON t.category_id = c.category_id
             JOIN users u ON t.user_id = u.user_id
             WHERE 1";

$params = [];
$types = '';

// Type filter
if ($transaction_type !== 'all') {
    $sql_base .= " AND t.type = ?";
    $params[] = $transaction_type;
    $types .= 's';
}

// Category filter
if (!empty($category)) {
    $sql_base .= " AND t.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

// Date filter
if (!empty($date_filter) && is_numeric($date_filter)) {
    $sql_base .= " AND t.date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
    $params[] = (int)$date_filter;
    $types .= 'i';
}

$sql_base .= " ORDER BY t.date DESC, t.transaction_id DESC";

$stmt = $conn->prepare($sql_base);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<main class="overviewmain">
    <section id="overview">
        <h2>All User Transactions</h2>

        <form method="GET" action="admin_overview.php">
            <div class="filtergroup">
                <label for="transactionType">Transaction Type:</label>
                <select id="transactionType" name="type" onchange="this.form.submit()" required>
                    <?php $selectedType = $_GET['type'] ?? 'all'; ?>
                    <option value="all" <?= $selectedType == 'all' ? 'selected' : '' ?>>All</option>
                    <option value="expense" <?= $selectedType == 'expense' ? 'selected' : '' ?>>Expense</option>
                    <option value="income" <?= $selectedType == 'income' ? 'selected' : '' ?>>Income</option>
                </select>
            </div>

            <div class="filtergroup">
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">All</option>
                    <?php
                    $type_filter = $transaction_type === 'all' ? '' : "WHERE type = '$transaction_type'";
                    $cat_result = $conn->query("SELECT * FROM categories $type_filter ORDER BY name");
                    while ($row = $cat_result->fetch_assoc()) {
                        $selected = ($category == $row['category_id']) ? 'selected' : '';
                        echo "<option value='{$row['category_id']}' $selected>" . htmlspecialchars($row['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filtergroup">
                <label for="date_filter">Date Filter:</label>
                <select name="date_filter" id="date_filter">
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

        <table>
    <thead>
        <tr>
            <th>User</th>
            <th>Date</th>
            <th>Type</th>
            <th>Category</th>
            <th>Amount (RM)</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ($result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= ucfirst($row['type']) ?></td>
                <td><?= htmlspecialchars($row['category_name']) ?></td>
                <td>RM <?= number_format($row['amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['note']) ?></td>
            </tr>
            <?php
        endwhile;
    else:
        echo "<tr><td colspan='6' style='text-align:center;'>No transactions found.</td></tr>";
    endif;
    ?>
    </tbody>
</table>

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

    document.getElementById('transactionType').addEventListener('change', function () {
        document.getElementById('category').value = '';
        document.getElementById('date_filter').value = '';
        this.form.submit();
    });
</script>

