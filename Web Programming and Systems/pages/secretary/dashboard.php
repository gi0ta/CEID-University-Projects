<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'secretary') {
    redirect(SITE_URL . '/pages/auth/login.php');
}

$user_id = User::getCurrentUserId();
$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Dashboard Γραμματείας';
?><!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="site-url" content="<?php echo SITE_URL; ?>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="bg-primary text-white py-3">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?php echo SITE_URL; ?>" class="text-white text-decoration-none">
                        <h1 class="h4 mb-0">Σύστημα Διαχείρισης Διπλωματικών</h1>
                    </a>
                </div>
                <div class="d-flex align-items-center">
                    <span class="me-3 text-white">
                        <i class="fas fa-user me-2"></i> <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                    <a href="http://localhost/web_php/pages/auth/signout.php" class="btn btn-sm btn-danger">
                        <i class="fas fa-sign-out-alt me-1"></i> Αποσύνδεση
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Alert Container -->
    <div class="container mt-3">
        <div id="alertContainer"></div>
    </div>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Καλώς ήρθατε, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
                        <p class="card-text">Από αυτό το dashboard μπορείτε να διαχειριστείτε τις διπλωματικές εργασίες.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Μετρητές Διπλωματικών -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card bg-success text-white shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-check-circle me-2"></i>Ενεργές Διπλωματικές</h5>
                        <h3 class="display-4" id="active-count">-</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-warning shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-hourglass-half me-2"></i>Διπλωματικές Υπό Εξέταση</h5>
                        <h3 class="display-4" id="examination-count">-</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Φίλτρα και Λίστα Διπλωματικών -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Φίλτρα</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Κατάσταση</label>
                                <select id="status" class="form-select">
                                    <option value="all">Όλες (Ενεργές & Υπό Εξέταση)</option>
                                    <option value="active">Ενεργές</option>
                                    <option value="examination">Υπό Εξέταση</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Λίστα Διπλωματικών Εργασιών -->
        <div class="row">
            <div class="col-12" id="theses-container">
                <div class="text-center" id="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Φόρτωση...</span>
                    </div>
                    <p class="mt-2">Φόρτωση διπλωματικών εργασιών...</p>
                </div>
                
                <div class="table-responsive d-none" id="theses-table">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Τίτλος</th>
                                <th>Φοιτητής</th>
                                <th>Επιβλέπων</th>
                                <th>Κατάσταση</th>
                                <th>Ημ/νία Ανάθεσης</th>
                                <th>Διάρκεια</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody id="theses-table-body">
                            <!-- Εδώ θα φορτωθούν δυναμικά οι διπλωματικές εργασίες με AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Container για τα modals -->
        <div id="modal-container">
            <!-- Εδώ θα φορτωθούν δυναμικά τα modals με AJAX -->
        </div>
    </div>

<?php
$additionalScripts = '<script src="' . SITE_URL . '/assets/js/secretary/dashboard.js"></script>';
include_once '../../templates/footer.php';
?>
