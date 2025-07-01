<?php
include 'includes/db.php';
session_start();  // Start session to get user_id

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
$amount = $_POST['amount'];
$type = isset($_POST['type']) ? $_POST['type'] : 'income'; // Default to 'income' if type is not set
$category_id = $_POST['category_id'];
$date = $_POST['date'];
$note = $_POST['note'];

// Validate input
if ($amount <= 0 || empty($date)) {
    die("Invalid input. Please check the fields.");
}

// Insert the transaction into the database
$stmt = $conn->prepare("INSERT INTO transactions (user_id, category_id, type, amount, note, date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisdss", $user_id, $category_id, $type, $amount, $note, $date);

if ($stmt->execute()) {
    // Redirect to the overview page with type=all to show separate tables
    header("Location: overview.php?type=all&success=Transaction added.");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>