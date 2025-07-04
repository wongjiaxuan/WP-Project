<?php
session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['role'])) {
        
        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $username = test_input($_POST['username']);
        $email = test_input($_POST['email']);
        $password = test_input($_POST['password']);
        $confirmpassword = test_input($_POST['confirmpassword']);
        $role = test_input($_POST['role']);
        
        if (empty($username)) {
            header("Location: signup.php?error=Username is required.");
            exit();
        } else if (empty($email)) {
            header("Location: signup.php?error=Email is required.");
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: signup.php?error=Invalid email format.");
            exit();
        } else if (empty($password)) {
            header("Location: signup.php?error=Password is required.");
            exit();
        } else if (empty($confirmpassword)) {
            header("Location: signup.php?error=Confirm Password is required.");
            exit();
        } else if ($password !== $confirmpassword) {
            header("Location: signup.php?error=Passwords do not match.");
            exit();
        } else if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[\W]/', $password) 
        ) {
            header("Location: signup.php?error=Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number and one special character.");
            exit();
        } else if (empty($role)) {
            header("Location: signup.php?error=Role is required.");
            exit();
        } else {
            $checkStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                header("Location: signup.php?error=This email is already registered.");
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                header("Location: home.php");
                exit();
            } else {
                header("Location: signup.php?error=Registration failed. Please try again.");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master Register Page</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="An online budget tracker simplifies user's work on managing income, expenses and savings.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>

<body>
    <main class="registermain">
        <section id="registerpage">
            <h1>Jimat Master</h1>
            <h2>Register</h2>
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert" role="alert">
                    <?= $_GET['error'] ?>
                </div>
            <?php } ?>
            <form method="post" class="registerform" action="">
                <div class="formgroup">
                    <label for="email">Username</label>
                    <input type="text" id="username" name="username" placeholder="Your Username">
                </div>
                <div class="formgroup">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="Your Email">
                </div>
                <div class="formgroup">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Your Password">
                </div>
                <div class="formgroup">
                    <label for="confirmpassword">Confirm Password</label>
                    <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Your Confirm Password">
                </div>
                <div class="formgroup">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="" disable selected hidden>Select Your Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="formgroupbtn">
                    <button type="submit" class="submitbtn">Register</button>
                </div>
                <div class="formlinks">
                    <p>Already have an account?<a href="index.php">Login here</a></p>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <div class="footercontainer">
            <p>
                <span class="footeryear">&copy; <span id="current-year"></span> SECV2223-05</span>
                <span class="footergroup">Web Programming Group 4</span>
            </p>
        </div>
    </footer>
</body>
</html>