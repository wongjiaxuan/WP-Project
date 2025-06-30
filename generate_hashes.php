<?php
$passwords = [
    'admin1234',
    'sara4321',
    'johnpass',
    'fatimah88',
    'azmi321'
];

echo "<h2>Hashed Passwords for Sample Users</h2><hr>";

foreach ($passwords as $index => $plain) {
    $hash = password_hash($plain, PASSWORD_DEFAULT);
    echo "<strong>User " . ($index + 1) . "</strong><br>";
    echo "Plain Password: <code>$plain</code><br>";
    echo "Hashed: <code>$hash</code><br><br>";
}
?>
