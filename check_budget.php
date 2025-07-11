<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/db.php';

if (!isset($_SESSION['user_id'])) return;

$user_id = $_SESSION['user_id'];
$selected_month = $_GET['month'] ?? date('Y-m');

$sql = "SELECT b.category_id, b.amount_limit, b.month, c.name AS category_name
        FROM budgets b
        JOIN categories c ON b.category_id = c.category_id
        WHERE b.user_id = ? AND b.month = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $selected_month);

$stmt->execute();
$budgets = $stmt->get_result();

$messages = [];

while ($row = $budgets->fetch_assoc()) {
    $category_id = $row['category_id'];
    $limit = (float)$row['amount_limit'];
    $budget_month = $row['month']; 
    $category_name = $row['category_name'];


    $sql_spent = "SELECT SUM(amount) AS total_spent
                  FROM transactions
                  WHERE user_id = ? 
                  AND category_id = ? 
                  AND type = 'expense' 
                  AND DATE_FORMAT(date, '%Y-%m') = ?";
    $stmt_spent = $conn->prepare($sql_spent);
   $stmt_spent->bind_param("iis", $user_id, $category_id, $selected_month);

    $stmt_spent->execute();
    $spent_result = $stmt_spent->get_result();
    $spent = (float)($spent_result->fetch_assoc()['total_spent'] ?? 0);

    if ($limit > 0 && $spent >= 0.8 * $limit) {
        $percent = ($spent / $limit) * 100;
        $monthName = DateTime::createFromFormat('Y-m', $budget_month)->format('F');

        if ($percent >= 100) {
            $messages[] = "❗ You’ve exceeded 100% of your \"$category_name\" budget for $monthName.";
        } else {
            $rounded = number_format($percent, 0);
            $messages[] = "⚠ You’ve used $rounded% of your \"$category_name\" budget for $monthName.";
        }
    }
}


if (!empty($messages)) {
    $combinedMessage = implode("\\n", array_map('addslashes', $messages));
    echo "<script>alert('$combinedMessage');</script>";
}
?>
