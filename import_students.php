<?php
require_once 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file'];
    
    if ($file['error'] == 0) {
        try {
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Supprimer l'en-tête
            array_shift($rows);
            
            $success = 0;
            $errors = [];
            
            foreach ($rows as $row) {
                if (count($row) >= 4) {
                    $code = trim($row[0]);
                    $full_name = trim($row[1]);
                    $cne = trim($row[2]);
                    $apogee = trim($row[3]);
                    
                    try {
                        $stmt = $pdo->prepare("INSERT INTO students (code, full_name, cne, apogee) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$code, $full_name, $cne, $apogee]);
                        $success++;
                    } catch (PDOException $e) {
                        $errors[] = "Erreur pour l'étudiant $full_name: " . $e->getMessage();
                    }
                }
            }
            
            $message = "$success étudiants importés avec succès.";
            if (!empty($errors)) {
                $message .= " Erreurs: " . implode(", ", $errors);
            }
            
            header("Location: dashboard.php?message=" . urlencode($message));
            exit();
            
        } catch (Exception $e) {
            $error = "Erreur lors de la lecture du fichier Excel: " . $e->getMessage();
        }
    } else {
        $error = "Erreur lors du téléchargement du fichier.";
    }
}
?> 