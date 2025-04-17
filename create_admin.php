<?php
require_once 'config/database.php';

try {
    // Données de l'administrateur
    $name = 'Administrateur';
    $email = 'admin@fssm.uca.ma';
    $password = 'Admin2024!';
    $role = 'admin';

    // Hachage du mot de passe avec bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Vérification si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "Un utilisateur avec cet email existe déjà.\n";
    } else {
        // Insertion du nouvel administrateur
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password, $role]);
        
        echo "Administrateur créé avec succès !\n";
        echo "Email: " . $email . "\n";
        echo "Mot de passe: " . $password . "\n";
    }
} catch(PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?> 