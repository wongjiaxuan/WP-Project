<?php
include '../includes/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    die("Please log in first.");
}

$user_id = $_SESSION['user_id']; 


$sql = "SELECT t.*, c.name AS category_name FROM transactions t 
        JOIN categories c ON t.category_id = c.category_id
        WHERE t.user_id = ?";


if (!empty($_GET['category'])) {
    $category = $_GET['category'];
    $sql .= " AND t.category_id = ?";
}

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $sql .= " AND t.date BETWEEN ? AND ?";
}

$stmt = $conn->prepare($sql);
if (!empty($category) && !empty($start_date) && !empty($end_date)) {
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
} elseif (!empty($category)) {
    $stmt->bind_param("ii", $user_id, $category);
} elseif (!empty($start_date) && !empty($end_date)) {
    $stmt->bind_param("iss", $user_id, $start_date, $end_date);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row['date'] . "</td>
            <td>" . ucfirst($row['type']) . "</td>
            <td>" . $row['category_name'] . "</td>
            <td>" . $row['amount'] . "</td>
            <td>" . $row['note'] . "</td>
          </tr>";
}

$stmt->close();
$conn->close();
?>
