//ONLY FOR TESTING PURPOSES, DELETE LATER
//ONLY FOR TESTING PURPOSES, DELETE LATER
//ONLY FOR TESTING PURPOSES, DELETE LATER

<?php
// Start session (optional if you want to use session in future)
session_start();

// Include your database connection
include 'includes/db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];   
    $password = $_POST['password'];

    // Prepare SQL to get user by email
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            echo "<p style='color:green'>✅ Login successful! Welcome, " . htmlspecialchars($user['username']) . "</p>";
        } else {
            echo "<p style='color:red'>❌ Incorrect password.</p>";
        }
    } else {
        echo "<p style='color:red'>❌ No user found with that email.</p>";
    }
}
?>

<!-- Simple HTML Form -->
<h2>Test Login</h2>
<form method="POST" action="">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Test Login</button>
</form>
