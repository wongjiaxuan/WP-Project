<?php
session_start();
include 'includes/db.php';


header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}


if (!isset($_POST['transaction_id'])) {
    echo json_encode(['success' => false, 'message' => 'Transaction ID not provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$transaction_id = intval($_POST['transaction_id']);

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
    
  
    $deleteStmt = $conn->prepare("DELETE FROM transactions WHERE transaction_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $transaction_id, $user_id);
    
    if ($deleteStmt->execute()) {
        if ($deleteStmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No transaction was deleted']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
    $deleteStmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>