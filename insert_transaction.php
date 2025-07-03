<?php
include 'includes/db.php';
session_start();  


if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id']; 
$amount = $_POST['amount'];
$type = isset($_POST['type']) ? $_POST['type'] : 'income'; 
$category_id = $_POST['category_id'];
$date = $_POST['date'];
$note = $_POST['note'];


if ($amount <= 0 || empty($date)) {
    die("Invalid input. Please check the fields.");
}


$stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, type, amount, note, date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisdss", $user_id, $category_id, $type, $amount, $note, $date);

if ($stmt->execute()) {
    
    header("Location: overview.php?type=all&success=Transaction added.");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>