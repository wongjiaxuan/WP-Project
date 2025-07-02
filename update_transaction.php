<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_POST['transaction_id']) || !isset($_POST['date']) || 
    !isset($_POST['category_id']) || !isset($_POST['amount']) || 
    !isset($_POST['note'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_id = $_SESSION['user_id'];
$transaction_id = intval($_POST['transaction_id']);
$date = $_POST['date'];
$category_id = intval($_POST['category_id']);
$amount = floatval($_POST['amount']);
$note = $_POST['note'];

if (empty($date) || $category_id <= 0 || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input values']);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

try {
    $checkStmt = $conn->prepare("SELECT user_id FROM transactions WHERE transaction_id = ?");
    $checkStmt->bind_param("i", $transaction_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    
    $transaction = $result->fetch_assoc();
    if ($transaction['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        $checkStmt->close();
        $conn->close();
        exit;
    }
    
    $checkStmt->close();
    
    $categoryStmt = $conn->prepare("SELECT type FROM categories WHERE category_id = ?");
    $categoryStmt->bind_param("i", $category_id);
    $categoryStmt->execute();
    $categoryResult = $categoryStmt->get_result();
    
    if ($categoryResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid category']);
        $categoryStmt->close();
        $conn->close();
        exit;
    }
    
    $categoryStmt->close();
    
    $updateStmt = $conn->prepare("UPDATE transactions SET date = ?, category_id = ?, amount = ?, note = ? WHERE transaction_id = ? AND user_id = ?");
    $updateStmt->bind_param("sidsii", $date, $category_id, $amount, $note, $transaction_id, $user_id);
    
    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaction updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $updateStmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>