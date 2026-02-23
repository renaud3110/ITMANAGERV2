<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur - IT Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h1>Oops! Une erreur s'est produite</h1>
            <p>Nous sommes désolés, mais quelque chose s'est mal passé.</p>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>

    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .error-content {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.8;
        }

        .error-content h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .error-content p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
    </style>
</body>
</html> 