<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <header>
        <nav class="navbar navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>">
                    <i class="fas fa-graduation-cap me-2"></i><?php echo SITE_NAME; ?>
                </a>
                
                <div class="d-flex">
                    <?php if (User::isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>/pages/auth/signout.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Αποσύνδεση
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container py-4 flex-grow-1">
