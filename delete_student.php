<?php
session_start();
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: dashboard.php?message=Étudiant supprimé avec succès");
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?error=Erreur lors de la suppression de l'étudiant");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
} 