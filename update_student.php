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
    $code = $_POST['code'];
    $full_name = $_POST['full_name'];
    $cne = $_POST['cne'];
    $apogee = $_POST['apogee'];

    try {
        $stmt = $pdo->prepare("UPDATE students SET code = ?, full_name = ?, cne = ?, apogee = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$code, $full_name, $cne, $apogee, $id]);
        
        header("Location: dashboard.php?message=Étudiant mis à jour avec succès");
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?error=Erreur lors de la mise à jour de l'étudiant");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
} 