<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
$type = isset($_POST['type']) ? $_POST['type'] : 'income';
$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$date = isset($_POST['date']) ? $_POST['date'] : '';
$note = isset($_POST['note']) ? $_POST['note'] : '';

if ($amount <= 0) {
    header("Location: input.php?error=Invalid amount. Please enter a number greater than 0.");
    exit();
}

if (empty($date)) {
    header("Location: input.php?error=Date is required.");
    exit();
}

if ($category_id <= 0) {
    header("Location: input.php?error=Please select a valid category.");
    exit();
}

$inputDate = DateTime::createFromFormat('Y-m-d', $date);
$today = new DateTime();
$today->setTime(23, 59, 59);

if (!$inputDate || $inputDate > $today) {
    header("Location: input.php?error=Invalid date. Please select a valid date that is not in the future.");
    exit();
}

$stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, type, amount, note, date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisdss", $user_id, $category_id, $type, $amount, $note, $date);

if ($stmt->execute()) {
    header("Location: overview.php?type=all&success=Transaction added successfully.");
} else {
    header("Location: input.php?error=Error adding transaction: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>