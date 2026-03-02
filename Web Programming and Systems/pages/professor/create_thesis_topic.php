<?php

require_once '../../includes/init.php';

if (!isset($_SESSION['user_id']) || User::getCurrentUserRole() !== 'professor') {
    redirect('../../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$page_title = "Δημιουργία Νέου Θέματος Διπλωματικής";

include_once '../../templates/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="fas fa-plus-circle me-2"></i> Δημιουργία Νέου Θέματος Διπλωματικής
            </h2>
        </div>
    </div>
    
    <div class="row mt-4">
        <!-- Φόρμα δημιουργίας νέου θέματος -->
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i> Στοιχεία Νέου Θέματος
                    </h5>
                </div>
                <div class="card-body">
                    <form id="new-topic-form" enctype="multipart/form-data">
                            <input type="hidden" id="topicId" name="topicId">
                        <div class="mb-3">
                            <label for="title" class="form-label">Τίτλος Θέματος <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="summary" class="form-label">Σύνοψη <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="summary" name="summary" rows="4" required></textarea>
                            <div class="form-text">Σύντομη περιγραφή του θέματος (έως 500 χαρακτήρες)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description_file" class="form-label">Αρχείο Περιγραφής (PDF)</label>
                            <input type="file" class="form-control" id="description_file" name="description_file" accept=".pdf">
                            <div class="form-text">Προαιρετικά, μπορείτε να ανεβάσετε ένα αρχείο PDF με αναλυτική περιγραφή του θέματος (μέγιστο μέγεθος: 5MB)</div>
                        </div>
                        <div id="current-description-file-container" class="mb-3" style="display: none;">
                            <!-- Το τρέχον αρχείο θα εμφανιστεί εδώ από το JS -->
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" id="submitThesisTopicBtn" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Αποθήκευση Θέματος
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Επιστροφή στο Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>

    
    <!-- Λίστα θεμάτων διπλωματικών εργασιών -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i> Λίστα Θεμάτων προς Ανάθεση
                    </h5>
                </div>
                <div class="card-body">
                    <div id="thesis-topics-content">
                        <!-- Μήνυμα ότι δεν υπάρχουν θέματα -->
                        <div id="no-topics" class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Δεν έχετε δημιουργήσει ακόμα θέματα προς ανάθεση. Χρησιμοποιήστε τη φόρμα παραπάνω για να δημιουργήσετε νέα θέματα.
                        </div>
                        
                        <!-- Πίνακας θεμάτων -->
                        <div id="topics-container" class="table-responsive mt-3" style="display: none;">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Τίτλος</th>
                                        <th>Σύνοψη</th>
                                        <th>Ημερομηνία Δημιουργίας</th>
                                        <th>Περιγραφή</th>
                                        <th>Ενέργειες</th>
                                    </tr>
                                </thead>
                                <tbody id="topics-list">
                                    <!-- Εδώ θα προστεθούν δυναμικά τα θέματα -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additionalScripts = "<script src='../../assets/js/professor/thesis_topics.js' defer></script>";

include_once '../../templates/footer.php';
?>
