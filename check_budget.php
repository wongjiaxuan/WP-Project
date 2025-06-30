<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) return;

$user_id = $_SESSION['user_id'];
$current_month = date('Y-m'); 

$sql = "SELECT b.category_id, b.amount_limit, c.name AS category_name
        FROM budgets b
        JOIN categories c ON b.category_id = c.category_id
        WHERE b.user_id = ? AND b.month = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$budgets = $stmt->get_result();

while ($row = $budgets->fetch_assoc()) {
    $category_id = $row['category_id'];
    $limit = (float)$row['amount_limit'];
    $category_name = $row['category_name'];

    $sql_spent = "SELECT SUM(amount) AS total_spent
                  FROM transactions
                  WHERE user_id = ? AND category_id = ? AND type = 'expense' AND DATE_FORMAT(date, '%Y-%m') = ?";
    $stmt_spent = $conn->prepare($sql_spent);
    $stmt_spent->bind_param("iis", $user_id, $category_id, $current_month);
    $stmt_spent->execute();
    $spent_result = $stmt_spent->get_result();
    $spent = (float)($spent_result->fetch_assoc()['total_spent'] ?? 0);

    if ($limit > 0 && $spent >= 0.8 * $limit) {
        $percent = number_format(($spent / $limit) * 100, 0);
        echo "
        <div class='alert'>
            ⚠ You’ve used $percent% of your <strong>$category_name</strong> budget!
            <div class='progress-bar-container'>
                <div class='progress-bar' style='width: $percent%;'></div>
            </div>
        </div>";
    }
}
?>
