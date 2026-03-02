<?php
require_once __DIR__ . '/../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRoleType() !== 'professor') {
    header("Location: ../../login.php");
    exit();
}

$pageTitle = "Προβολή Προσκλήσεων Συμμετοχής σε Τριμελή Επιτροπή";

include_once __DIR__ . '/../../templates/header.php';

?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="fas fa-envelope-open-text me-2"></i><?php echo $pageTitle; ?></h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-list-ul me-2"></i>Ενεργές Προσκλήσεις</h5>
        </div>
        <div class="card-body">
            <div id="invitations-list">
                <!-- Οι προσκλήσεις θα φορτωθούν εδώ μέσω JavaScript -->
                <p class="text-center text-muted" id="loading-invitations">Φόρτωση προσκλήσεων...</p>
                <div id="no-invitations" class="alert alert-info" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>Δεν υπάρχουν ενεργές προσκλήσεις αυτή τη στιγμή.
                </div>
            </div>
        </div>
    </div>

    <!-- Εδώ θα μπορούσε να προστεθεί μια ενότητα για το ιστορικό απαντημένων προσκλήσεων -->

</div>

<?php
include_once __DIR__ . '/../../templates/footer.php';
?>
<!-- Ειδικό JavaScript για αυτή τη σελίδα -->
<script>const SITE_URL = '<?php echo SITE_URL; ?>';</script>
<script src="<?php echo SITE_URL; ?>/assets/js/professor/invitations.js?v=<?php echo time(); // Για cache busting ?>"></script>

</body>
</html>
