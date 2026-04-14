<?php
$pdo = new PDO('mysql:host=localhost;dbname=resultadosfutbol;charset=utf8mb4', 'root', '');
$hash = password_hash('Admin123+', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE email = 'admin@resultadosfutbol.ec'");
$stmt->execute(['hash' => $hash]);
echo "Password updated successfully.\n";
