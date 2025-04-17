<?php
session_start();
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté et a le rôle pointage
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pointage') {
    header("Location: login.php");
    exit();
}

// Traiter la recherche d'étudiant
$student = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $code = trim($_POST['code']);
    
    if (!empty($code)) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE code = ?");
        $stmt->execute([$code]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            $error = "Aucun étudiant trouvé avec ce code.";
        }
    }
}

// Traiter l'enregistrement du pointage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $student_id = $_POST['student_id'];
    $date = date('Y-m-d');
    $check_in = date('H:i:s');
    $benefie = isset($_POST['benefie']) ? 1 : 0;
    
    try {
        // Vérifier si l'étudiant est déjà bénéficiaire
        $stmt = $pdo->prepare("SELECT benefie FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $current_benefie = $stmt->fetchColumn();
        
        if ($benefie == 1 && $current_benefie == 1) {
            $error = "Cet étudiant est déjà bénéficiaire.";
        } else {
            // Commencer une transaction
            $pdo->beginTransaction();
            
            // Mettre à jour le statut benefie de l'étudiant
            $stmt = $pdo->prepare("UPDATE students SET benefie = ? WHERE id = ?");
            $stmt->execute([$benefie, $student_id]);
            
            // Enregistrer le pointage
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, check_in, status) VALUES (?, ?, ?, 'present')");
            $stmt->execute([$student_id, $date, $check_in]);
            
            // Valider la transaction
            $pdo->commit();
            
            $success = "Pointage enregistré avec succès.";
            $student = null; // Réinitialiser l'affichage de l'étudiant
        }
    } catch (PDOException $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Erreur lors de l'enregistrement du pointage: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pointage - CEIRS-UCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .search-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .student-info {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">CEIRS-UCA</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a class="nav-item nav-link" href="logout.php">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-container">
            <h2 class="text-center mb-4">Pointage des étudiants</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="mb-4">
                <div class="input-group">
                    <input type="text" name="code" class="form-control" placeholder="Entrez le code de l'étudiant" required>
                    <button type="submit" name="search" class="btn btn-primary">
                        <i class="bi bi-search"></i> Rechercher
                    </button>
                </div>
            </form>

            <?php if ($student): ?>
                <div class="student-info">
                    <h4>Informations de l'étudiant</h4>
                    <table class="table">
                        <tr>
                            <th>Code</th>
                            <td><?php echo htmlspecialchars($student['code']); ?></td>
                        </tr>
                        <tr>
                            <th>Nom complet</th>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        </tr>
                        <tr>
                            <th>CNE</th>
                            <td><?php echo htmlspecialchars($student['cne']); ?></td>
                        </tr>
                        <tr>
                            <th>Apogée</th>
                            <td><?php echo htmlspecialchars($student['apogee']); ?></td>
                        </tr>
                    </table>

                    <form method="POST">
                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="benefie" id="benefie" value="1">
                                <label class="form-check-label" for="benefie">
                                    Bénéficiaire
                                </label>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="register" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Enregistrer le pointage
                            </button>
                            <a href="pointage_dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 