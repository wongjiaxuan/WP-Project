<?php
require_once 'admin_header.php';
require_once 'includes/db.php';

// Filters
$transaction_type = $_GET['transaction_type'] ?? '';
$category = $_GET['category'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = $_GET['page'] ?? 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($transaction_type)) {
    $where_conditions[] = "t.type = ?";
    $params[] = $transaction_type;
    $param_types .= 's';
}
if (!empty($category)) {
    $where_conditions[] = "t.category_id = ?";
    $params[] = $category;
    $param_types .= 'i';
}
if (!empty($start_date)) {
    $where_conditions[] = "t.date >= ?";
    $params[] = $start_date;
    $param_types .= 's';
}
if (!empty($end_date)) {
    $where_conditions[] = "t.date <= ?";
    $params[] = $end_date;
    $param_types .= 's';
}
if (!empty($search)) {
    $where_conditions[] = "(t.note LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $param_types .= 'ss';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Count total
$count_sql = "
    SELECT COUNT(*) as total 
    FROM transactions t 
    JOIN users u ON t.user_id = u.user_id 
    $where_clause
";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get transactions
$sql = "
    SELECT t.*, u.username, c.name as category_name 
    FROM transactions t 
    JOIN users u ON t.user_id = u.user_id 
    JOIN categories c ON t.category_id = c.category_id 
    $where_clause 
    ORDER BY t.date DESC, t.transaction_id DESC 
    LIMIT ? OFFSET ?
";
$params[] = $records_per_page;
$params[] = $offset;
$param_types .= 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Category options
$cat_stmt = $conn->prepare("SELECT category_id, name FROM categories ORDER BY name");
$cat_stmt->execute();
$categories = $cat_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<main class="overviewmain">
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-list"></i> All User Transactions</h2>
            <p>Overview of all transactions from all users</p>
        </div>

        <!-- Filter Form -->
        <div class="filter-section">
            <form method="GET" class="filter-form" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <div class="filter-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group">
                        <label for="transaction_type">Transaction Type:</label>
                        <select name="transaction_type" id="transaction_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="income" <?php echo $transaction_type === 'income' ? 'selected' : ''; ?>>Income</option>
                            <option value="expense" <?php echo $transaction_type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                            <option value="budget" <?php echo $transaction_type === 'budget' ? 'selected' : ''; ?>>Budget</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Category:</label>
                        <select name="category" id="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" 
                                    <?php echo $category == $cat['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                </div>

                <div class="filter-row" style="display: flex; gap: 1rem; align-items: end;">
                    <div class="form-group" style="flex: 1;">
                        <label for="search">Search:</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Search by description or username..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 2rem;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="admin_overview.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        <div class="results-summary" style="margin-bottom: 1rem; padding: 1rem; background: #e3f2fd; border-radius: 5px;">
            <p><strong>Total Records:</strong> <?php echo $total_records; ?> transactions found</p>
        </div>

        <!-- Transactions Table -->
        <div class="table-container" style="overflow-x: auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead style="background: #667eea; color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Date</th>
                        <th style="padding: 1rem; text-align: left;">User</th>
                        <th style="padding: 1rem; text-align: left;">Type</th>
                        <th style="padding: 1rem; text-align: left;">Category</th>
                        <th style="padding: 1rem; text-align: left;">Note</th>
                        <th style="padding: 1rem; text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="6" style="padding: 2rem; text-align: center; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; display: block; margin-bottom: 1rem;"></i>
                            No transactions found matching your criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 1rem;"><?php echo date('M d, Y', strtotime($transaction['date'])); ?></td>
                            <td style="padding: 1rem;"><strong><?php echo htmlspecialchars($transaction['username']); ?></strong></td>
                            <td style="padding: 1rem;">
                                <span class="badge" style="padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.8rem; text-transform: capitalize;
                                    <?php 
                                    switch($transaction['type']) {
                                        case 'income': echo 'background: #d4edda; color: #155724;'; break;
                                        case 'expense': echo 'background: #f8d7da; color: #721c24;'; break;
                                        case 'budget': echo 'background: #d1ecf1; color: #0c5460;'; break;
                                        default: echo 'background: #e2e3e5; color: #383d41;';
                                    }
                                    ?>">
                                    <?php echo ucfirst($transaction['type']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;"><?php echo htmlspecialchars($transaction['category_name'] ?? 'N/A'); ?></td>
                            <td style="padding: 1rem;"><?php echo htmlspecialchars($transaction['note'] ?? ''); ?></td>
                            <td style="padding: 1rem; text-align: right; font-weight: bold; color: <?php echo $transaction['type'] === 'expense' ? '#dc3545' : '#28a745'; ?>;">
                                <?php echo $transaction['type'] === 'expense' ? '-' : '+'; ?>
                                RM <?php echo number_format($transaction['amount'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination" style="margin-top: 2rem; text-align: center;">
                <?php
                $query_params = $_GET;
                unset($query_params['page']);
                $base_url = 'admin_overview.php?' . http_build_query($query_params);
                ?>
                <?php if ($page > 1): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $page - 1; ?>" class="btn btn-outline">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>" 
                       class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-outline'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="<?php echo $base_url; ?>&page=<?php echo $page + 1; ?>" class="btn btn-outline">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
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
</script>
