<?php
session_start();
require_once 'config/database.php';
require_once 'config/security.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format d'email invalide";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['login_attempts'] >= 5 && strtotime($user['last_attempt']) > strtotime('-15 minutes')) {
                    $error = "Compte temporairement bloqué. Veuillez réessayer dans 15 minutes.";
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, last_attempt = NULL WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['last_activity'] = time();
                    
                    if ($user['role'] === 'pointage') {
                        header("Location: pointage_dashboard.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit();
                }
            } else {
                if ($user) {
                    $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                }
                $error = "Email ou mot de passe incorrect";
            }
        } catch(PDOException $e) {
            $error = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculté des Sciences Semlalia</title>
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font: 400 1em/1.5 "Neuton", sans-serif;
            background: #090d00;
            color: rgba(255, 255, 255, .25);
            text-align: center;
            margin: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
        }

        html, head, body {
            background-image: url('/images/pic1.gif');
            background-size: cover;
            background-position: center;
        }

        .Logo {
            text-transform: uppercase;
            letter-spacing: .5em;
            display: inline-block;
            border: 4px double rgba(255, 255, 255, .25);
            border-width: 4px 0;
            padding: 1em 0;
            position: relative;
            margin: 0 auto;
            width: 40em;
            text-align: center;
        }

        .LogoSpan {
            font: 700 4em/1 "Oswald", sans-serif;
            letter-spacing: 0;
            padding: .25em 0 .325em;
            display: block;
            margin: 0 auto;
            text-shadow: 0 0 10px rgba(255, 255, 255, .5);
            background: url(https://i.ibb.co/RDTnNrT/animated-text-fill.png) repeat-y;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            -webkit-animation: aitf 80s linear infinite;
            -webkit-transform: translate3d(0, 0, 0);
            -webkit-backface-visibility: hidden;
        }

        @-webkit-keyframes aitf {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .Contenu {
            padding-top: 50px;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .Logo {
                width: 90%;
            }
        }

        .form-control {
            margin-bottom: 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            color: white;
            box-shadow: none;
        }

        .card {
            background-color: rgba(0, 0, 0, 0.7);
            border: none;
            backdrop-filter: blur(10px);
        }

        .form-label {
            color: white;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .alert {
            background-color: rgba(220, 53, 69, 0.8);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div id="app">
        <main class="py-4">
            <div class="container">
                <p class="Logo">
                    &mdash; Bienvenu à la &mdash;
                    <span class="LogoSpan">
                        Faculté des Sciences Semlalia
                    </span>
                    &mdash; Système de Pointage &mdash;
                </p>

                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card shadow-lg border-0 rounded">
                                <div class="card-body p-4">
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="form-group row mb-3">
                                            <label for="email" class="col-md-4 col-form-label text-md-end">Email</label>
                                            <div class="col-md-6">
                                                <input id="email" type="email" 
                                                    class="form-control" 
                                                    name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                                    required autocomplete="email" autofocus>
                                            </div>
                                        </div>

                                        <div class="form-group row mb-3">
                                            <label for="password" class="col-md-4 col-form-label text-md-end">Mot de passe</label>
                                            <div class="col-md-6">
                                                <input id="password" type="password" 
                                                    class="form-control" 
                                                    name="password" required autocomplete="current-password">
                                            </div>
                                        </div>

                                        <div class="form-group row mb-0">
                                            <div class="col-md-6 offset-md-4">
                                                <button type="submit" class="btn btn-primary w-100 btn-lg">
                                                    <i class="bi bi-box-arrow-in-right"></i> Se connecter
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>