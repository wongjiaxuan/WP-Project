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
    
        $email = test_input($_POST['email']);
        $password = test_input($_POST['password']);
        $role = test_input($_POST['role']);

        if (empty($email)) {
            header("Location: index.php?error=Email is required.");
            exit();
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: index.php?error=Invalid email format.");
            exit();
        } else if (empty($password)) {
            header("Location: index.php?error=Password is required.");
            exit();
        } else if (empty($role)) {
            header("Location: index.php?error=Role is required.");
            exit();
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    if ($row['role'] === $role) {
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['role'] = $row['role'];
    
                        if ($row['role'] === 'admin') {
                            header("Location: admin_home.php");
                        } else {
                            header("Location: home.php");
                        }
                        exit();
                    } else {
                        header("Location: index.php?error=Incorrect role selected.");
                        exit();
                    }
                } else {
                    header("Location: index.php?error=Incorrect email or password.");
                    exit();
                }
            } else {
                header("Location: index.php?error=Incorrect email or password.");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Jimat Master Login Page</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="An online budget tracker simplifies user's work on managing income, expenses and savings.">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>

<body>
    <main class="loginmain">
        <section id="loginpage">
            <h1>Jimat Master</h1>
            <h2>Login</h2>
            <?php if (isset($_GET['error'])) { ?>
                <div class="alert" role="alert">
                    <?= $_GET['error'] ?>
                </div>
            <?php } ?>
            <form method="post" class="loginform" action="">
                <div class="formgroup">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="Your Email">
                </div>
                <div class="formgroup">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Your Password">
                </div>
                <div class="formgroup">
                    <label for="role">Role</label>
                    <select id="role" name="role">
                        <option value="" disabled selected hidden>Select Your Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="formgroupbtn">
                    <button type="submit" class="submitbtn">Login</button>
                </div>
                <div class="formlinks">
                    <p>Don't have an account?<a href="signup.php">Register</a></p>
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