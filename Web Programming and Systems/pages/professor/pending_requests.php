<?php

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$db = getDbConnection();

$user_id = User::getCurrentUserId();
$stmt = $db->prepare("
    SELECT p.* FROM professors p 
    WHERE p.user_id = :user_id
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$professor = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
    SELECT ta.*, tt.title, s.registration_number, u.first_name, u.last_name, u.email
    FROM thesis_assignments ta
    JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN students s ON ta.student_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE ta.supervisor_id = :professor_id AND ta.status = 'pending'
    ORDER BY ta.assignment_date DESC
");
$stmt->bindParam(':professor_id', $professor['id']);
$stmt->execute();
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token']) && verifyCsrfToken($_POST['csrf_token'])) {
    if (isset($_POST['approve_request']) && isset($_POST['assignment_id'])) {
        $assignment_id = $_POST['assignment_id'];
        
        $stmt = $db->prepare("
            SELECT ta.*, tt.id as topic_id
            FROM thesis_assignments ta
            JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
            WHERE ta.id = :assignment_id AND ta.supervisor_id = :professor_id AND ta.status = 'pending'
        ");
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':professor_id', $professor['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("
                    UPDATE thesis_assignments 
                    SET status = 'active' 
                    WHERE id = :assignment_id
                ");
                $stmt->bindParam(':assignment_id', $assignment_id);
                $stmt->execute();
                
                $stmt = $db->prepare("
                    UPDATE thesis_topics 
                    SET is_assigned = 1 
                    WHERE id = :topic_id
                ");
                $stmt->bindParam(':topic_id', $assignment['topic_id']);
                $stmt->execute();
                
                $stmt = $db->prepare("
                    INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_at, changed_by, notes)
                    VALUES (:assignment_id, 'pending', 'active', NOW(), :user_id, 'Έγκριση αίτησης ανάληψης θέματος')
                ");
                $stmt->bindParam(':assignment_id', $assignment_id);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                $db->commit();
                
                setFlashMessage('success', 'Η αίτηση εγκρίθηκε με επιτυχία.');
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                setFlashMessage('error', 'Παρουσιάστηκε σφάλμα κατά την έγκριση της αίτησης: ' . $e->getMessage());
            }
        } else {
            setFlashMessage('error', 'Η αίτηση δεν βρέθηκε ή δεν έχετε δικαίωμα να την εγκρίνετε.');
        }
    } elseif (isset($_POST['reject_request']) && isset($_POST['assignment_id']) && isset($_POST['rejection_reason'])) {
        $assignment_id = $_POST['assignment_id'];
        $rejection_reason = sanitizeInput($_POST['rejection_reason']);
        
        $stmt = $db->prepare("
            SELECT ta.*, tt.id as topic_id
            FROM thesis_assignments ta
            JOIN thesis_topics tt ON ta.thesis_topic_id = tt.id
            WHERE ta.id = :assignment_id AND ta.supervisor_id = :professor_id AND ta.status = 'pending'
        ");
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':professor_id', $professor['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            try {
                $db->beginTransaction();
                
                $stmt = $db->prepare("
                    UPDATE thesis_assignments 
                    SET status = 'cancelled', cancellation_date = NOW(), cancellation_reason = :rejection_reason 
                    WHERE id = :assignment_id
                ");
                $stmt->bindParam(':assignment_id', $assignment_id);
                $stmt->bindParam(':rejection_reason', $rejection_reason);
                $stmt->execute();
                
                $stmt = $db->prepare("
                    INSERT INTO status_changes (thesis_assignment_id, old_status, new_status, changed_at, changed_by, notes)
                    VALUES (:assignment_id, 'pending', 'cancelled', NOW(), :user_id, :notes)
                ");
                $stmt->bindParam(':assignment_id', $assignment_id);
                $stmt->bindParam(':user_id', $user_id);
                $notes = 'Απόρριψη αίτησης: ' . $rejection_reason;
                $stmt->bindParam(':notes', $notes);
                $stmt->execute();
                
                $db->commit();
                
                setFlashMessage('success', 'Η αίτηση απορρίφθηκε με επιτυχία.');
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                $db->rollBack();
                setFlashMessage('error', 'Παρουσιάστηκε σφάλμα κατά την απόρριψη της αίτησης: ' . $e->getMessage());
            }
        } else {
            setFlashMessage('error', 'Η αίτηση δεν βρέθηκε ή δεν έχετε δικαίωμα να την απορρίψετε.');
        }
    }
}

$csrfToken = generateCsrfToken();

$pageTitle = 'Εκκρεμείς Αιτήσεις Ανάληψης Θέματος';
?>

<?php include '../../templates/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><?php echo $pageTitle; ?></h2>
                <a href="dashboard.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Επιστροφή στο Dashboard
                </a>
            </div>
            <hr>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php displayFlashMessages(); ?>
            
            <?php if (empty($pending_requests)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Δεν υπάρχουν εκκρεμείς αιτήσεις ανάληψης θέματος αυτή τη στιγμή.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-warning">
                            <tr>
                                <th>Φοιτητής</th>
                                <th>Θέμα</th>
                                <th>Ημερομηνία Αίτησης</th>
                                <th>Ενέργειες</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_requests as $request): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                        </div>
                                        <div class="text-muted small">
                                            <?php echo htmlspecialchars($request['registration_number']); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($request['email']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($request['assignment_date'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view_student_profile.php?student_id=<?php echo $request['student_id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-user me-1"></i>Προφίλ
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $request['id']; ?>">
                                                <i class="fas fa-check me-1"></i>Έγκριση
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $request['id']; ?>">
                                                <i class="fas fa-times me-1"></i>Απόρριψη
                                            </button>
                                        </div>
                                        
                                        <!-- Modal για έγκριση αίτησης -->
                                        <div class="modal fade" id="approveModal<?php echo $request['id']; ?>" tabindex="-1" aria-labelledby="approveModalLabel<?php echo $request['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="approveModalLabel<?php echo $request['id']; ?>">Έγκριση Αίτησης</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>Είστε βέβαιοι ότι θέλετε να εγκρίνετε την αίτηση του φοιτητή:</p>
                                                        <p class="fw-bold"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name'] . ' (' . $request['registration_number'] . ')'); ?></p>
                                                        <p>για το θέμα:</p>
                                                        <p class="fw-bold"><?php echo htmlspecialchars($request['title']); ?></p>
                                                        <p class="text-info">Με την έγκριση της αίτησης, το θέμα θα ανατεθεί στον φοιτητή και δεν θα είναι πλέον διαθέσιμο για άλλους φοιτητές.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $request['id']; ?>">
                                                            <button type="submit" name="approve_request" class="btn btn-success">Έγκριση</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal για απόρριψη αίτησης -->
                                        <div class="modal fade" id="rejectModal<?php echo $request['id']; ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?php echo $request['id']; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="rejectModalLabel<?php echo $request['id']; ?>">Απόρριψη Αίτησης</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                        <div class="modal-body">
                                                            <p>Είστε βέβαιοι ότι θέλετε να απορρίψετε την αίτηση του φοιτητή:</p>
                                                            <p class="fw-bold"><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name'] . ' (' . $request['registration_number'] . ')'); ?></p>
                                                            <p>για το θέμα:</p>
                                                            <p class="fw-bold"><?php echo htmlspecialchars($request['title']); ?></p>
                                                            
                                                            <div class="mb-3">
                                                                <label for="rejection_reason<?php echo $request['id']; ?>" class="form-label">Αιτιολογία Απόρριψης <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="rejection_reason<?php echo $request['id']; ?>" name="rejection_reason" rows="3" required></textarea>
                                                                <div class="form-text">Παρακαλώ εξηγήστε τους λόγους απόρριψης της αίτησης.</div>
                                                            </div>
                                                            
                                                            <p class="text-danger">Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ακύρωση</button>
                                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $request['id']; ?>">
                                                            <button type="submit" name="reject_request" class="btn btn-danger">Απόρριψη</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
