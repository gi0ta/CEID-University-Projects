<?php

require_once '../../includes/config/config.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$user_id = User::getCurrentUserId();
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];

$pageTitle = 'Dashboard Καθηγητή';

$additionalScripts = '';
?>

<?php include '../../templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Καλώς ήρθατε, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>!</h2>
            <p class="lead">Διαχειριστείτε τα θέματα διπλωματικών εργασιών, τις αναθέσεις και τις επιτροπές σας.</p>
            <hr>
        </div>
    </div>
    
 
    <!-- Κατηγορία 1: Διαχείριση θεμάτων και αναθέσεων -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Διαχείριση θεμάτων και αναθέσεων</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-alt fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">Δημιουργία θεμάτων προς ανάθεση</h5>
                                    <p class="card-text">Καταχωρήστε ένα νέο θέμα διπλωματικής με τίτλο, περιγραφή και δυνατότητα επισύναψης αρχείου PDF.</p>
                                    <a href="create_thesis_topic.php" class="btn btn-primary">Δημιουργία θέματος</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-graduate fa-3x mb-3 text-success"></i>
                                    <h5 class="card-title">Ανάθεση θέματος σε φοιτητή</h5>
                                    <p class="card-text">Αναθέστε ένα ελεύθερο θέμα σε φοιτητή αναζητώντας τον με ΑΜ ή ονοματεπώνυμο. Το θέμα κατοχυρώνεται προσωρινά.</p>
                                    <a href="assign_topic_list.php" class="btn btn-success">Ανάθεση θέματος</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Κατηγορία 2: Προβολές -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Προβολές</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-list-alt fa-3x mb-3 text-info"></i>
                                    <h5 class="card-title">Προβολή λίστας διπλωματικών</h5>
                                    <p class="card-text">Δείτε τη λίστα όλων των διπλωματικών στις οποίες έχετε συμμετάσχει ως επιβλέπων ή μέλος τριμελούς.</p>
                                    <a href="<?php echo SITE_URL; ?>/pages/professor/thesis_list.php" class="btn btn-info">Προβολή λίστας</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-envelope-open-text fa-3x mb-3 text-warning"></i>
                                    <h5 class="card-title">Προβολή προσκλήσεων συμμετοχής σε τριμελή</h5>
                                    <p class="card-text">Δείτε τις ενεργές προσκλήσεις που έχετε λάβει για συμμετοχή σε τριμελείς επιτροπές.</p>
                                    <a href="view_invitations.php" class="btn btn-warning">Προβολή προσκλήσεων</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-bar fa-3x mb-3 text-success"></i>
                                    <h5 class="card-title">Στατιστικά διπλωματικών</h5>
                                    <p class="card-text">Δείτε στατιστικά στοιχεία για τις διπλωματικές που έχετε επιβλέψει ή συμμετάσχει ως μέλος τριμελούς.</p>
                                    <a href="<?php echo SITE_URL; ?>/pages/professor/statistics.php" class="btn btn-success">Προβολή στατιστικών</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
