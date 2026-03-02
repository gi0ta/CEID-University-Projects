<?php

require_once '../../includes/config/config.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$pageTitle = "Προβολή Λίστας Διπλωματικών";
include_once '../../templates/header.php';

echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/professor/thesis_list.css">';
?>

<div class="container mt-4">
    <h2>Προβολή Λίστας Διπλωματικών</h2>
    
    <!-- Φίλτρα -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Φίλτρα</h5>
        </div>
        <div class="card-body">
            <form id="filter-form" class="row g-3">
                <div class="col-md-4">
                    <label for="status-filter" class="form-label">Κατάσταση</label>
                    <select id="status-filter" class="form-select">
                        <option value="">Όλες</option>
                        <option value="pending">Υπό ανάθεση</option>
                        <option value="active">Ενεργή</option>
                        <option value="completed">Περατωμένη</option>
                        <option value="cancelled">Ακυρωμένη</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="role-filter" class="form-label">Ρόλος</label>
                    <select id="role-filter" class="form-select">
                        <option value="">Όλοι</option>
                        <option value="supervisor">Επιβλέπων</option>
                        <option value="committee_member">Μέλος Τριμελούς</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Εφαρμογή Φίλτρων</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Εξαγωγή -->
    <div class="mb-3">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                Εξαγωγή Λίστας
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" id="export-csv">CSV</a></li>
                <li><a class="dropdown-item" href="#" id="export-json">JSON</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Λίστα διπλωματικών -->
    <div class="card">
        <div class="card-header">
            <h5>Διπλωματικές Εργασίες</h5>
        </div>
        <div class="card-body">
            <div id="thesis-list-container">
                <div class="text-center py-5">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Φόρτωση...</span>
                    </div>
                    <p class="mt-2">Φόρτωση διπλωματικών εργασιών...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal προβολής λεπτομερειών διπλωματικής -->
<div class="modal fade" id="thesis-details-modal" tabindex="-1" aria-labelledby="thesis-details-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="thesis-details-modal-label">Λεπτομέρειες Διπλωματικής</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="thesis-details-content">
                    <!-- Εδώ θα φορτωθούν οι λεπτομέρειες της διπλωματικής -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Κλείσιμο</button>
            </div>
        </div>
    </div>
</div>

<?php
$additionalScripts = '<script src="' . SITE_URL . '/assets/js/professor/thesis_actions.js"></script>\n<script src="' . SITE_URL . '/assets/js/professor/thesis_list.js"></script>';
include_once '../../templates/footer.php';
?>
