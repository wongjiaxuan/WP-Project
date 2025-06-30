<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];
$category_id = $_POST['category_id'] ?? null;
$amount_limit = $_POST['amount_limit'] ?? null;
$month = $_POST['month'] ?? null; // format: YYYY-MM

if (!$category_id || !$amount_limit || !$month) {
    die("Missing input. Please go back and fill all fields.");
}

$sql_check = "SELECT budget_id FROM budgets WHERE user_id = ? AND category_id = ? AND month = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("iis", $user_id, $category_id, $month);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $sql_update = "UPDATE budgets SET amount_limit = ? WHERE user_id = ? AND category_id = ? AND month = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("diis", $amount_limit, $user_id, $category_id, $month);
    $stmt_update->execute();
} else {
    $sql_insert = "INSERT INTO budgets (user_id, category_id, month, amount_limit) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iisd", $user_id, $category_id, $month, $amount_limit);
    $stmt_insert->execute();
}

header("Location: dashboard.php?msg=Budget+saved+successfully");
exit;
?>
