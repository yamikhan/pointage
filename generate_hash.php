<?php
require_once 'config/security.php';

$password = 'Admin2024!';
$hashed_password = hashPassword($password);

echo "Mot de passe original: " . $password . "\n";
echo "Hash généré: " . $hashed_password . "\n";

// Vérification
echo "Vérification: " . (verifyPassword($password, $hashed_password) ? "OK" : "Échec") . "\n";
?> 