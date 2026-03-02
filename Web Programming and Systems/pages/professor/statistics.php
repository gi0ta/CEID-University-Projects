<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$userId = User::getCurrentUserId();

$pageTitle = "Στατιστικά Διπλωματικών Εργασιών";
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Σύστημα Διαχείρισης Διπλωματικών</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Συμπερίληψη του header -->
    <?php include '../../templates/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4">
                    <i class="fas fa-chart-bar me-2"></i>
                    <?php echo $pageTitle; ?>
                </h1>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Εδώ μπορείτε να δείτε στατιστικά για τις διπλωματικές εργασίες που έχετε επιβλέψει ή συμμετάσχει ως μέλος τριμελούς επιτροπής.
                </div>
                
                <!-- Loader -->
                <div id="statistics-loader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Φόρτωση...</span>
                    </div>
                    <p class="mt-2">Φόρτωση στατιστικών...</p>
                </div>
                
                <!-- Περιεχόμενο στατιστικών -->
                <div id="statistics-content" class="row" style="display: none;">
                    <!-- Συνολικό πλήθος διπλωματικών -->
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-calculator me-2"></i>
                                    Συνολικό πλήθος διπλωματικών
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="thesisCountChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Μέσος χρόνος περάτωσης διπλωματικών -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    Μέσος χρόνος περάτωσης (σε μήνες)
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="thesisDurationChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Μέσος βαθμός διπλωματικών -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-star me-2"></i>
                                    Μέσος βαθμός διπλωματικών
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="thesisGradeChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Πίνακας με αναλυτικά στοιχεία -->
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-table me-2"></i>
                                    Αναλυτικά στοιχεία
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Κατηγορία</th>
                                                <th>Συνολικό πλήθος</th>
                                                <th>Μέσος χρόνος περάτωσης (μήνες)</th>
                                                <th>Μέσος βαθμός</th>
                                            </tr>
                                        </thead>
                                        <tbody id="statistics-table-body">
                                            <!-- Θα συμπληρωθεί δυναμικά από το JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Μήνυμα σφάλματος -->
                <div id="statistics-error" class="alert alert-danger" style="display: none;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="error-message">Προέκυψε σφάλμα κατά τη φόρτωση των στατιστικών.</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Συμπερίληψη του footer -->
    <?php include '../../templates/footer.php'; ?>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="../../assets/js/professor/statistics.js"></script>
</body>
</html>
