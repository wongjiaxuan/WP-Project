<?php

$passwords = [
    'admin' => 'Admin@1234',
    'sara' => 'Sara@4321',
    'john' => 'John#Pass1',
    'fatimah' => 'Fatimah88!',
    'azmi' => 'Azmi321@'
];

echo "<pre>";
echo "Generated Hashes:\n";
echo "-------------------\n";

foreach ($passwords as $username => $plain_password) {
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    echo "Username: " . $username . "\n";
    echo "Plain Password: " . $plain_password . "\n";
    echo "Hashed Password: " . $hashed_password . "\n";
    echo "-------------------\n";
}
echo "</pre>";

?>