<?php
include 'includes/db.php';
session_start();  // Start session to get user_id

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Please log in first.");
    echo "<p><a href='login.html'>Link to Login</a></p>";
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
$amount = $_POST['amount'];
$type = $_POST['type'];
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
    // Redirect to the overview page after success
    header("Location: overview.php?success=Transaction added.");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
