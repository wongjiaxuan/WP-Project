<?php
session_start();
include 'includes/db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    // Check if type parameter is provided
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    
    if ($type && in_array($type, ['income', 'expense'])) {
        // Get categories filtered by type
        $stmt = $conn->prepare("SELECT category_id, name, type FROM categories WHERE type = ? ORDER BY name");
        $stmt->bind_param("s", $type);
    } else {
        // Get all categories if no type specified or invalid type
        $stmt = $conn->prepare("SELECT category_id, name, type FROM categories ORDER BY type, name");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    echo json_encode($categories);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>