<?php

require_once '../../includes/init.php';

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$user_id = User::getCurrentUserId();

$db = new Database();
$pdo = $db->getConnection();

$user_id = User::getCurrentUserId();

$stmt = $pdo->prepare("SELECT * FROM students WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$thesis_assignment = null;
if ($student) {
    $stmt = $pdo->prepare("
        SELECT ta.*, tt.title, tt.summary, u.first_name as supervisor_first_name, u.last_name as supervisor_last_name
        FROM thesis_assignments ta 
        JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
        JOIN professors p ON tt.professor_id = p.id
        JOIN users u ON p.user_id = u.id
        WHERE ta.student_id = :student_id
        ORDER BY ta.assignment_date DESC
        LIMIT 1
    ");
    $stmt->bindParam(':student_id', $student['id'], PDO::PARAM_INT);
    $stmt->execute();
    $thesis_assignment = $stmt->fetch(PDO::FETCH_ASSOC);
}

$pageTitle = 'Dashboard Φοιτητή';

$additionalScripts = '<script src="' . SITE_URL . '/assets/js/student/dashboard.js"></script>';
?>

<?php include '../../templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Καλώς ήρθατε, <?php echo htmlspecialchars(User::getCurrentUserName()); ?>!</h2>
            <p class="lead">Διαχειριστείτε τη διπλωματική σας εργασία και το προφίλ σας.</p>
            <hr>
        </div>
    </div>
    
    <!-- Τα στατιστικά στοιχεία για εκκρεμείς αιτήσεις και διαθέσιμα θέματα έχουν αφαιρεθεί -->
    
    <!-- Ενεργή Διπλωματική Εργασία -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div id="active-thesis">
                <?php if ($thesis_assignment): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Ενεργή Διπλωματική Εργασία</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($thesis_assignment['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($thesis_assignment['summary'] ?? ''); ?></p>
                        <p><strong>Επιβλέπων:</strong> <?php echo htmlspecialchars($thesis_assignment['supervisor_first_name'] . ' ' . $thesis_assignment['supervisor_last_name']); ?></p>
                        <p><strong>Κατάσταση:</strong> 
                            <span class="badge <?php 
                                switch($thesis_assignment['status']) {
                                    case 'pending': echo 'bg-warning'; break;
                                    case 'active': echo 'bg-success'; break;
                                    case 'examination': echo 'bg-info'; break;
                                    case 'completed': echo 'bg-secondary'; break;
                                    case 'rejected': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                            ?> text-white">
                                <?php 
                                    switch($thesis_assignment['status']) {
                                        case 'pending': echo 'Εκκρεμεί'; break;
                                        case 'active': echo 'Ενεργή'; break;
                                        case 'examination': echo 'Υπό Εξέταση'; break;
                                        case 'completed': echo 'Ολοκληρώθηκε'; break;
                                        case 'rejected': echo 'Απορρίφθηκε'; break;
                                        default: echo $thesis_assignment['status'];
                                    }
                                ?>
                            </span>
                            <!-- Το κουμπί "Διαχείριση Υλικού Διπλωματικής" προστίθεται δυναμικά από το JavaScript -->
                        </p>
                        
                        <!-- Τμήμα διαχείρισης μελών επιτροπής - Θα ενημερωθεί από το JavaScript -->
                        <div id="committee-management" class="mt-3"></div>
                        

                        
                        <!-- Το modal μετακινήθηκε έξω από το card -->
                        
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <h5>Δεν έχετε ακόμα ενεργή διπλωματική εργασία.</h5>
                    <p>Παρακαλώ περιμένετε μέχρι να σας ανατεθεί διπλωματική εργασία από κάποιον καθηγητή.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Προφίλ και Ρυθμίσεις -->
    <div class="row mb-4">
        <div class="col-md-12 mb-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-edit fa-3x mb-3 text-info"></i>
                    <h5 class="card-title">Επεξεργασία Προφίλ</h5>
                    <p class="card-text">Ενημερώστε τα στοιχεία του προφίλ σας</p>
                    <a href="edit_profile.php" class="btn btn-info">Επεξεργασία Προφίλ</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal για την πρόσκληση καθηγητή -->
    <div class="modal fade" id="inviteProfessorModal" tabindex="-1" aria-labelledby="inviteProfessorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteProfessorModalLabel">Πρόσκληση Καθηγητή στην Επιτροπή</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="professorSelect" class="form-label">Επιλέξτε Καθηγητή</label>
                        <select class="form-select" id="professorSelect">
                            <option value="" selected disabled>Επιλέξτε...</option>
                            <!-- Οι καθηγητές θα φορτωθούν δυναμικά με JavaScript -->
                        </select>
                        <div id="professorSelectError" class="invalid-feedback">
                            Παρακαλώ επιλέξτε έναν καθηγητή.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                    <button type="button" class="btn btn-primary" id="sendInvitationBtn">Αποστολή Πρόσκλησης</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
