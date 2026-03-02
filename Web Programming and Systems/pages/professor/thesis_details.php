<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'professor') {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$thesis_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($thesis_id === 0) {
    header('Location: thesis_list.php');
    exit;
}

$professorId = User::getCurrentUserId();

$db = getDbConnection();

$all_graded = false;

$committee_sql = "
    SELECT 
        COUNT(cm.professor_id) AS total_members
    FROM 
        committee_members cm
    WHERE 
        cm.thesis_assignment_id = ? 
        AND cm.status = 'accepted'
";
$committee_stmt = $db->prepare($committee_sql);
$committee_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$committee_stmt->execute();
$committee_info = $committee_stmt->fetch(PDO::FETCH_ASSOC);

$graded_members_sql = "
    SELECT COUNT(DISTINCT tg.professor_id) AS graded_members
    FROM thesis_grades tg
    WHERE tg.thesis_assignment_id = ?
";
$graded_members_stmt = $db->prepare($graded_members_sql);
$graded_members_stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$graded_members_stmt->execute();
$graded_info = $graded_members_stmt->fetch(PDO::FETCH_ASSOC);

$total_required = $committee_info['total_members'] + 1;
$total_graded = $graded_info['graded_members'];

$all_graded = ($total_graded >= $total_required);

$sql = "
    SELECT 
        ta.id AS assignment_id,
        tt.title AS thesis_title,
        tt.summary AS thesis_summary,
        tt.description_file AS description_file,
        ta.status,
        ta.supervisor_id,
        CASE 
            WHEN ta.supervisor_id = ? THEN 'supervisor'
            ELSE 'committee_member'
        END AS professor_role,
        s.registration_number AS student_am,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name,
        ta.assignment_date AS created_at,
        ta.deadline_date AS deadline_date,
        ta.completion_date,
        ta.duration_days,
        ta.repository_link,
        ta.final_grade AS grade,
        ta.cancellation_date,
        ta.cancellation_reason,
        ta.grading_enabled,
        (SELECT protocol_pdf FROM examination_protocols WHERE thesis_assignment_id = ta.id LIMIT 1) AS grade_protocol_file,
        tp.presentation_date,
        tp.location AS presentation_location,
        tp.online_link,
        tp.presentation_type
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        students s ON ta.student_id = s.id
    JOIN 
        users u ON s.user_id = u.id
    LEFT JOIN
        thesis_presentations tp ON ta.id = tp.thesis_assignment_id
    WHERE 
        ta.id = ?
        AND (ta.supervisor_id = ? OR EXISTS (SELECT 1 FROM committee_members cm WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ?))
";

$stmt = $db->prepare($sql);
$stmt->bindParam(1, $professorId, PDO::PARAM_INT);
$stmt->bindParam(2, $thesis_id, PDO::PARAM_INT);
$stmt->bindParam(3, $professorId, PDO::PARAM_INT);
$stmt->bindParam(4, $professorId, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    header('Location: thesis_list.php?error=thesis_not_found');
    exit;
}

$stmt = $db->prepare("
    SELECT 
        p.id AS professor_id,
        CONCAT(u.first_name, ' ', u.last_name) AS professor_name
    FROM 
        professors p
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        p.id = ?
");
$stmt->bindParam(1, $thesis['supervisor_id'], PDO::PARAM_INT);
$stmt->execute();
$supervisor = $stmt->fetch(PDO::FETCH_ASSOC);
$thesis['supervisor'] = $supervisor;

$stmt = $db->prepare("
    SELECT 
        id,
        file_name,
        file_path,
        file_type,
        description,
        upload_date
    FROM 
        thesis_files
    WHERE 
        thesis_assignment_id = ?
    ORDER BY
        upload_date DESC
");
$stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$thesis_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
$thesis['thesis_files'] = $thesis_files;

$stmt = $db->prepare("
    SELECT 
        p.id AS professor_id,
        CONCAT(u.first_name, ' ', u.last_name) AS professor_name
    FROM 
        committee_members cm
    JOIN 
        professors p ON cm.professor_id = p.id
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        cm.thesis_assignment_id = ?
");
$stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$committee = $stmt->fetchAll(PDO::FETCH_ASSOC);
$thesis['committee_members'] = $committee;

$stmt = $db->prepare("
    SELECT 
        new_status AS status,
        changed_at AS created_at,
        notes,
        changed_by
    FROM 
        status_changes
    WHERE 
        thesis_assignment_id = ?
    ORDER BY 
        changed_at DESC
");
$stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$timeline = $stmt->fetchAll(PDO::FETCH_ASSOC);
$thesis['timeline'] = $timeline;

$stmt = $db->prepare("
    SELECT 
        ci.id,
        ci.invited_professor_id,
        CONCAT(u.first_name, ' ', u.last_name) AS professor_name,
        ci.status,
        ci.invitation_date,
        ci.response_date
    FROM 
        committee_invitations ci
    JOIN 
        professors p ON ci.invited_professor_id = p.id
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        ci.thesis_assignment_id = ?
    ORDER BY 
        ci.invitation_date DESC
");
$stmt->bindParam(1, $thesis_id, PDO::PARAM_INT);
$stmt->execute();
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$thesis['invitations'] = $invitations;

$pageTitle = "Λεπτομέρειες Διπλωματικής: {$thesis['thesis_title']}";

include_once '../../templates/header.php';
?>



<style>
    .timeline {
        position: relative;
        padding: 1rem;
        margin: 0 auto;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        height: 100%;
        border: 1px solid #dee2e6;
        left: 1rem;
        top: 0;
    }
    
    .timeline-item {
        position: relative;
        margin-left: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .timeline-marker {
        position: absolute;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #007bff;
        left: -2.25rem;
        top: 0.25rem;
    }
    
    .timeline-content {
        padding: 0.5rem;
        border-left: 2px solid #007bff;
    }
    
    .timeline-title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
</style>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="thesis_list.php">Λίστα Διπλωματικών</a></li>
            <li class="breadcrumb-item active" aria-current="page">Λεπτομέρειες Διπλωματικής</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo htmlspecialchars($thesis['thesis_title']); ?></h1>
        <a href="thesis_list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Επιστροφή στη λίστα
        </a>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <?php if ($thesis['professor_role'] === 'supervisor' && $thesis['status'] === 'pending'): ?>
            <div class="text-end mb-3">
                <button type="button" class="btn btn-danger" id="cancelAssignmentBtn">
                    <i class="bi bi-x-circle"></i> Ακύρωση Ανάθεσης
                </button>
            </div>
            <?php endif; ?>
            
            <h3>Βασικές Πληροφορίες</h3>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0"><?php echo htmlspecialchars($thesis['thesis_title']); ?></h4>
                        <span class="badge <?php 
                            switch($thesis['status']) {
                                case 'pending':
                                    echo 'bg-warning text-dark';
                                    break;
                                case 'active':
                                    echo 'bg-success';
                                    break;
                                case 'completed':
                                    echo 'bg-info text-dark';
                                    break;
                                case 'rejected':
                                case 'cancelled':
                                    echo 'bg-danger';
                                    break;
                                case 'examination':
                                    echo 'bg-primary';
                                    break;
                                default:
                                    echo 'bg-primary';
                            }
                        ?>">
                            <?php 
                                switch($thesis['status']) {
                                    case 'pending':
                                        echo 'Υπό ανάθεση';
                                        break;
                                    case 'active':
                                        echo 'Ενεργή';
                                        break;
                                    case 'completed':
                                        echo 'Περατωμένη';
                                        break;
                                    case 'rejected':
                                        echo 'Απορριφθείσα';
                                        break;
                                    case 'cancelled':
                                        echo 'Ακυρωμένη';
                                        break;
                                    case 'examination':
                                        echo 'Υπό εξέταση';
                                        break;
                                    default:
                                        echo $thesis['status'];
                                }
                            ?>
                        </span>
                    </div>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($thesis['thesis_summary'])); ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Φοιτητής:</strong> <?php echo htmlspecialchars($thesis['student_name']); ?> (<?php echo htmlspecialchars($thesis['student_am']); ?>)</p>
                            <p><strong>Ρόλος Καθηγητή:</strong> <?php echo $thesis['professor_role'] === 'supervisor' ? 'Επιβλέπων' : 'Μέλος τριμελούς'; ?></p>
                            <p><strong>Ημερομηνία Ανάθεσης:</strong> <?php echo date('d/m/Y', strtotime($thesis['created_at'])); ?></p>
                            <p><strong>Προθεσμία:</strong> <?php echo !empty($thesis['deadline_date']) ? date('d/m/Y', strtotime($thesis['deadline_date'])) : '-'; ?></p>
                            <?php if (!empty($thesis['completion_date'])): ?>
                                <p><strong>Ημερομηνία Ολοκλήρωσης:</strong> <?php echo date('d/m/Y', strtotime($thesis['completion_date'])); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($thesis['duration_days'])): ?>
                                <p><strong>Διάρκεια (ημέρες):</strong> <?php echo htmlspecialchars($thesis['duration_days']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Επιβλέπων:</strong> <?php echo isset($thesis['supervisor']) ? htmlspecialchars($thesis['supervisor']['professor_name']) : '-'; ?></p>
                            <?php if (!empty($thesis['repository_link'])): ?>
                                <p><strong>Σύνδεσμος Αποθετηρίου:</strong> <a href="<?php echo htmlspecialchars($thesis['repository_link']); ?>" target="_blank"><?php echo htmlspecialchars($thesis['repository_link']); ?></a></p>
                            <?php else: ?>
                                <p><strong>Σύνδεσμος Αποθετηρίου:</strong> -</p>
                            <?php endif; ?>
                            <?php if (!empty($thesis['grade'])): ?>
                                <p><strong>Βαθμός:</strong> <?php echo htmlspecialchars($thesis['grade']); ?></p>
                            <?php else: ?>
                                <p><strong>Βαθμός:</strong> -</p>
                            <?php endif; ?>
                            <?php if (!empty($thesis['cancellation_date'])): ?>
                                <p><strong>Ημερομηνία Ακύρωσης:</strong> <?php echo date('d/m/Y', strtotime($thesis['cancellation_date'])); ?></p>
                                <p><strong>Αιτία Ακύρωσης:</strong> <?php echo !empty($thesis['cancellation_reason']) ? htmlspecialchars($thesis['cancellation_reason']) : '-'; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Αρχεία -->
                    <div class="mt-4">
                        <h5>Αρχεία</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <?php if (!empty($thesis['description_file'])): ?>
                                    <p><strong>Αρχείο Περιγραφής:</strong> <a href="<?php echo SITE_URL . '/' . htmlspecialchars($thesis['description_file']); ?>" target="_blank">Προβολή</a></p>
                                <?php else: ?>
                                    <p><strong>Αρχείο Περιγραφής:</strong> -</p>
                                <?php endif; ?>
                                
                                <?php if (!empty($thesis['grade_protocol_file'])): ?>
                                    <p><strong>Πρωτόκολλο Βαθμολογίας:</strong> <a href="<?php echo SITE_URL . '/' . htmlspecialchars($thesis['grade_protocol_file']); ?>" target="_blank">Προβολή</a></p>
                                <?php else: ?>
                                    <p><strong>Πρωτόκολλο Βαθμολογίας:</strong> -</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Αρχεία που έχει αναρτήσει ο φοιτητής -->
                        <?php if (!empty($thesis['thesis_files'])): ?>
                            <h6 class="mt-3">Αρχεία Φοιτητή</h6>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Όνομα Αρχείου</th>
                                            <th>Τύπος</th>
                                            <th>Περιγραφή</th>
                                            <th>Ημερομηνία</th>
                                            <th>Ενέργεια</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($thesis['thesis_files'] as $file): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($file['file_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    switch($file['file_type']) {
                                                        case 'draft': echo '<span class="badge bg-secondary">Πρόχειρο</span>'; break;
                                                        case 'final': echo '<span class="badge bg-success">Τελικό</span>'; break;
                                                        case 'presentation': echo '<span class="badge bg-info">Παρουσίαση</span>'; break;
                                                        case 'code': echo '<span class="badge bg-secondary">Κώδικας</span>'; break;
                                                        case 'data': echo '<span class="badge bg-warning">Δεδομένα</span>'; break;
                                                        default: echo '<span class="badge bg-secondary">Άλλο</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($file['description'] ?? ''); ?></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($file['upload_date'])); ?></td>
                                                <td>
                                                    <a href="<?php echo SITE_URL . '/uploads/thesis_files/' . htmlspecialchars($file['file_path']); ?>" class="btn btn-sm btn-secondary" target="_blank">
                                                        <i class="bi bi-file-earmark"></i> Προβολή
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-3">
                                Δεν έχουν αναρτηθεί αρχεία από τον φοιτητή.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Τριμελής Επιτροπή -->
            <h3 class="mt-4">Τριμελής Επιτροπή</h3>
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($thesis['committee_members'])): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($thesis['committee_members'] as $member): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($member['professor_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Δεν έχουν οριστεί μέλη επιτροπής.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Προσκλήσεις σε μέλη επιτροπής (μόνο για διπλωματικές υπό ανάθεση) -->
            <?php if ($thesis['status'] === 'pending' && !empty($thesis['invitations'])): ?>
            <h3 class="mt-4">Προσκλήσεις σε Μέλη Επιτροπής</h3>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Καθηγητής</th>
                                    <th>Κατάσταση</th>
                                    <th>Ημερομηνία Πρόσκλησης</th>
                                    <th>Ημερομηνία Απάντησης</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($thesis['invitations'] as $invitation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($invitation['professor_name']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            switch($invitation['status']) {
                                                case 'pending':
                                                    echo 'bg-warning text-dark';
                                                    break;
                                                case 'accepted':
                                                    echo 'bg-success';
                                                    break;
                                                case 'declined':
                                                    echo 'bg-danger';
                                                    break;
                                                default:
                                                    echo 'bg-secondary';
                                            }
                                        ?>">
                                            <?php 
                                                switch($invitation['status']) {
                                                    case 'pending':
                                                        echo 'Εκκρεμεί';
                                                        break;
                                                    case 'accepted':
                                                        echo 'Αποδεκτή';
                                                        break;
                                                    case 'declined':
                                                        echo 'Απορριφθείσα';
                                                        break;
                                                    default:
                                                        echo $invitation['status'];
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($invitation['invitation_date'])); ?></td>
                                    <td><?php echo !empty($invitation['response_date']) ? date('d/m/Y H:i', strtotime($invitation['response_date'])) : '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Αλλαγή κατάστασης σε "Υπό Εξέταση" (μόνο για επιβλέποντες) -->
        <?php if ($thesis['status'] === 'active' && $thesis['professor_role'] === 'supervisor'): ?>
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Αλλαγή κατάστασης διπλωματικής</h5>
                    <p>Ως επιβλέπων/ουσα, μπορείτε να αλλάξετε την κατάσταση της διπλωματικής σε «Υπό Εξέταση» ώστε ο φοιτητής να προβεί στις περαιτέρω ενέργειες.</p>
                    <button id="changeToExaminationBtn" class="btn btn-primary">Αλλαγή σε «Υπό Εξέταση»</button>
                    <div id="statusChangeMessage" class="mt-3" style="display: none;"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Προσωπικές Σημειώσεις Καθηγητή -->
        <?php if ($thesis['status'] === 'active'): ?>
        <div class="col-md-4 mb-4">
            <h3>Προσωπικές Σημειώσεις</h3>
            <div class="card">
                <div class="card-body">
                    <div id="professor-notes-container">
                        <div class="mb-3">
                            <form id="add-note-form">
                                <div class="form-group">
                                    <label for="note-text">Νέα Σημείωση (έως 300 χαρακτήρες)</label>
                                    <textarea class="form-control" id="note-text" rows="2" maxlength="300" required></textarea>
                                    <small class="form-text text-muted">Οι σημειώσεις είναι ορατές μόνο σε εσάς.</small>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small id="char-count">0/300 χαρακτήρες</small>
                                        <button type="submit" class="btn btn-primary btn-sm">Προσθήκη</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <hr>
                        <h6>Οι σημειώσεις μου</h6>
                        <div id="notes-list" class="mt-3">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Φόρτωση...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Τμήμα βαθμολόγησης για διπλωματικές σε κατάσταση examination -->
        <?php if ($thesis['status'] === 'examination'): ?>
        <div class="container-fluid px-0 mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Βαθμολόγηση Διπλωματικής Εργασίας</h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($thesis['grading_enabled']) && $thesis['grading_enabled'] == 1): ?>
                            <!-- Κουμπί που παραπέμπει στη φόρμα βαθμολόγησης (για όλους τους καθηγητές) -->
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Η βαθμολόγηση έχει ενεργοποιηθεί επιτυχώς.
                            </div>
                            <!-- Προσθήκη του id="grading-status-section" για να μην αντικαθίσταται από το JavaScript -->
                            <div id="grading-status-section">
                            <?php if ($all_graded): ?>
                            <!-- Μήνυμα ότι όλα τα μέλη έχουν βαθμολογήσει -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Όλα τα μέλη της επιτροπής έχουν υποβάλει βαθμολογία. Η διαδικασία βαθμολόγησης έχει ολοκληρωθεί.
                            </div>
                            <?php else: ?>
                            <p>Μπορείτε τώρα να προχωρήσετε στη βαθμολόγηση της διπλωματικής εργασίας:</p>
                            <a href="thesis_grading_form.php?id=<?php echo $thesis_id; ?>" class="btn btn-success" id="grading-form-button">
                                <i class="fas fa-edit"></i> Φόρμα Βαθμολόγησης
                            </a>
                            <?php endif; ?>
                            </div>
                            <?php elseif ($thesis['supervisor_id'] == $professorId): ?>
                            <!-- Κουμπί ενεργοποίησης βαθμολόγησης (μόνο για τον επιβλέποντα) -->
                            <div id="gradingMessageContainer" style="display: none;" class="alert"></div>
                            <p>Για να προχωρήσετε στη βαθμολόγηση της διπλωματικής εργασίας, πατήστε το παρακάτω κουμπί:</p>
                            <button id="enableGradingBtn" class="btn btn-primary" data-assignment-id="<?php echo $thesis_id; ?>">
                                <i class="fas fa-unlock"></i> Ενεργοποίηση Βαθμολόγησης
                            </button>
                            <?php else: ?>
                            <!-- Μήνυμα για συνεπιβλέποντες όταν η βαθμολόγηση δεν είναι ενεργοποιημένη -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Η βαθμολόγηση δεν έχει ενεργοποιηθεί ακόμα από τον επιβλέποντα καθηγητή (<?php echo htmlspecialchars($thesis['supervisor']['professor_name']); ?>).
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Στοιχεία Παρουσίασης (για διπλωματικές σε κατάσταση examination) -->
        <?php if ($thesis['status'] === 'examination'): ?>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Στοιχεία Παρουσίασης</h5>
                    </div>
                    <div class="card-body">
                        <!-- Στοιχεία παρουσίασης -->
                        <?php if (!empty($thesis['presentation_date'])): ?>
                        <div class="mb-3">
                            <!-- Ημερομηνία και ώρα -->
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-calendar-event me-2 fs-4"></i>
                                <div>
                                    <strong>Ημερομηνία & Ώρα:</strong><br>
                                    <?php 
                                    $date = new DateTime($thesis['presentation_date']);
                                    echo htmlspecialchars($date->format('d/m/Y H:i')); 
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Τύπος παρουσίασης και τοποθεσία/σύνδεσμος -->
                            <?php if ($thesis['presentation_type'] === 'physical' && !empty($thesis['presentation_location'])): ?>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-geo-alt me-2 fs-4"></i>
                                <div>
                                    <strong>Τοποθεσία:</strong><br>
                                    <span class="badge bg-primary me-2">Φυσική παρουσία</span>
                                    <?php echo htmlspecialchars($thesis['presentation_location']); ?>
                                </div>
                            </div>
                            <?php elseif ($thesis['presentation_type'] === 'online' && !empty($thesis['online_link'])): ?>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-camera-video me-2 fs-4"></i>
                                <div>
                                    <strong>Σύνδεσμος τηλεδιάσκεψης:</strong><br>
                                    <span class="badge bg-success me-2">Διαδικτυακή παρουσίαση</span>
                                    <a href="<?php echo htmlspecialchars($thesis['online_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Σύνδεση στην τηλεδιάσκεψη
                                    </a>
                                </div>
                            </div>
                            <?php elseif ($thesis['presentation_type'] === 'hybrid'): ?>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-geo-alt me-2 fs-4"></i>
                                <div>
                                    <strong>Τοποθεσία:</strong><br>
                                    <span class="badge bg-info me-2">Υβριδική παρουσίαση</span>
                                    <?php echo !empty($thesis['presentation_location']) ? htmlspecialchars($thesis['presentation_location']) : 'Δεν έχει οριστεί τοποθεσία'; ?>
                                </div>
                            </div>
                            <?php if (!empty($thesis['online_link'])): ?>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-camera-video me-2 fs-4"></i>
                                <div>
                                    <strong>Σύνδεσμος τηλεδιάσκεψης:</strong><br>
                                    <a href="<?php echo htmlspecialchars($thesis['online_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Σύνδεση στην τηλεδιάσκεψη
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Δεν έχουν οριστεί ακόμα στοιχεία παρουσίασης από τον φοιτητή.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Βαθμολογίες Τριμελούς Επιτροπής (για διπλωματικές σε κατάσταση examination) -->
        <?php if ($thesis['status'] === 'examination' || $thesis['status'] === 'completed'): ?>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Βαθμολογίες Τριμελούς Επιτροπής</h5>
                        <?php if ($thesis['status'] === 'completed' && !empty($thesis['grade'])): ?>
                        <span class="badge bg-success fs-5">Τελικός Βαθμός: <?php echo number_format($thesis['grade'], 1); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div id="grades-loading" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Φόρτωση...</span>
                            </div>
                            <p class="mt-2">Φόρτωση βαθμολογιών...</p>
                        </div>
                        <div id="grades-container" class="d-none">
                            <!-- Εδώ θα εμφανίζονται οι βαθμολογίες από το JavaScript -->
                        </div>
                        <div id="grades-error" class="alert alert-danger d-none">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span id="grades-error-message">Σφάλμα κατά τη φόρτωση των βαθμολογιών.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Χρονολόγιο Ενεργειών -->
        <div class="col-md-4">
            <h3>Χρονολόγιο Ενεργειών</h3>
            <div class="card">
                <div class="card-body">
                    <div class="timeline">
                        <?php if (!empty($thesis['timeline'])): ?>
                            <?php foreach ($thesis['timeline'] as $event): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h3 class="timeline-title">
                                            <?php 
                                                switch($event['status']) {
                                                    case 'pending':
                                                        echo 'Υπό ανάθεση';
                                                        break;
                                                    case 'active':
                                                        echo 'Ενεργοποίηση';
                                                        break;
                                                    case 'completed':
                                                        echo 'Ολοκλήρωση';
                                                        break;
                                                    case 'rejected':
                                                        echo 'Απόρριψη';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Ακύρωση';
                                                        break;
                                                    case 'examination':
                                                        echo 'Εξέταση';
                                                        break;
                                                    default:
                                                        echo $event['status'];
                                                }
                                            ?>
                                        </h3>
                                        <p class="mb-1"><?php echo date('d/m/Y H:i', strtotime($event['created_at'])); ?></p>
                                        <?php if (!empty($event['changed_by'])): ?>
                                            <p class="mb-1"><small>Από: <?php echo htmlspecialchars($event['changed_by']); ?></small></p>
                                        <?php endif; ?>
                                        <?php if (!empty($event['notes'])): ?>
                                            <p class="text-muted"><?php echo htmlspecialchars($event['notes']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Δεν υπάρχουν καταγεγραμμένες ενέργειες.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additional_scripts = '<script src="../../assets/js/professor/thesis_details.js"></script>';

if ($thesis['status'] === 'examination') {
    $additional_scripts .= '<script src="../../assets/js/professor/thesis_grading.js"></script>';
}
?>
</main>

<!-- JavaScript για διπλωματικές με κατάσταση "active" -->
<?php if ($thesis['status'] === 'active'): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const changeStatusBtn = document.getElementById('changeToExaminationBtn');
        if (changeStatusBtn) {
            changeStatusBtn.addEventListener('click', function() {
                if (confirm('Είστε βέβαιοι ότι θέλετε να αλλάξετε την κατάσταση της διπλωματικής σε "Υπό Εξέταση"; Η ενέργεια αυτή δεν μπορεί να αναιρεθεί.')) {
                    const statusChangeMessage = document.getElementById('statusChangeMessage');
                    changeStatusBtn.disabled = true;
                    changeStatusBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Επεξεργασία...';
                    
                    fetch('/web_php/api/professor/change_thesis_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            thesis_id: <?php echo $thesis_id; ?>
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        statusChangeMessage.style.display = 'block';
                        
                        if (data.success) {
                            statusChangeMessage.className = 'alert alert-success mt-3';
                            statusChangeMessage.textContent = data.message;
                            
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            statusChangeMessage.className = 'alert alert-danger mt-3';
                            statusChangeMessage.textContent = data.message;
                            changeStatusBtn.disabled = false;
                            changeStatusBtn.innerHTML = 'Αλλαγή σε «Υπό Εξέταση»';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        statusChangeMessage.style.display = 'block';
                        statusChangeMessage.className = 'alert alert-danger mt-3';
                        statusChangeMessage.textContent = 'Σφάλμα κατά την επικοινωνία με τον διακομιστή.';
                        changeStatusBtn.disabled = false;
                        changeStatusBtn.innerHTML = 'Αλλαγή σε «Υπό Εξέταση»';
                    });
                }
            });
        }
        
        const noteTextarea = document.getElementById('note-text');
        const charCount = document.getElementById('char-count');
        
        if (noteTextarea && charCount) {
            noteTextarea.addEventListener('input', function() {
                const currentLength = this.value.length;
                charCount.textContent = `${currentLength}/300 χαρακτήρες`;
            });
        }
        
        const addNoteForm = document.getElementById('add-note-form');
        
        if (addNoteForm) {
            addNoteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const noteText = noteTextarea.value.trim();
                
                if (noteText.length === 0) {
                    alert('Παρακαλώ εισάγετε κείμενο για τη σημείωση.');
                    return;
                }
                
                if (noteText.length > 300) {
                    alert('Η σημείωση δεν μπορεί να υπερβαίνει τους 300 χαρακτήρες.');
                    return;
                }
                
                fetch('../../api/professor/add_note.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        thesis_id: <?php echo $thesis_id; ?>,
                        note_text: noteText
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        noteTextarea.value = '';
                        charCount.textContent = '0/300 χαρακτήρες';
                        
                        loadNotes();
                    } else {
                        alert('Σφάλμα: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Σφάλμα κατά την προσθήκη της σημείωσης.');
                });
            });
        }
        
        function loadNotes() {
            const notesList = document.getElementById('notes-list');
            
            notesList.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Φόρτωση...</span>
                    </div>
                </div>
            `;
            
            fetch(`../../api/professor/get_notes.php?thesis_id=<?php echo $thesis_id; ?>`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.notes.length === 0) {
                        notesList.innerHTML = '<p class="text-muted">Δεν έχετε προσθέσει ακόμα σημειώσεις.</p>';
                    } else {
                        let notesHtml = '';
                        
                        data.notes.forEach(note => {
                            const date = new Date(note.created_at);
                            const formattedDate = date.toLocaleDateString('el-GR') + ' ' + date.toLocaleTimeString('el-GR');
                            
                            notesHtml += `
                                <div class="card mb-2">
                                    <div class="card-body py-2 px-3">
                                        <p class="card-text mb-1">${note.note_text}</p>
                                        <small class="text-muted">${formattedDate}</small>
                                    </div>
                                </div>
                            `;
                        });
                        
                        notesList.innerHTML = notesHtml;
                    }
                } else {
                    notesList.innerHTML = `<p class="text-danger">Σφάλμα: ${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notesList.innerHTML = '<p class="text-danger">Σφάλμα κατά την ανάκτηση των σημειώσεων.</p>';
            });
        }
        
        loadNotes();
    });
</script>
<?php endif; ?>

<!-- JavaScript για διπλωματικές με κατάσταση "examination" -->
<?php if ($thesis['status'] === 'examination'): ?>
<!-- Η λειτουργικότητα διαχείρισης πρωτοκόλλων έχει αφαιρεθεί -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Φόρτωση στοιχείων παρουσίασης για διπλωματική σε κατάσταση εξέτασης');
        
        const presentationDetailsSection = document.getElementById('presentation-details-section');
        if (presentationDetailsSection) {
            presentationDetailsSection.classList.remove('d-none');
        }
        
        const presentationFilesSection = document.getElementById('presentation-files-section');
        if (presentationFilesSection) {
            presentationFilesSection.classList.remove('d-none');
        }
    });
</script>

<!-- Φόρτωση του JavaScript για τη βαθμολόγηση -->
<script src="../../assets/js/professor/thesis_grading.js"></script>

<!-- Φόρτωση του JavaScript για την εμφάνιση των βαθμολογιών της τριμελούς επιτροπής -->
<script src="../../assets/js/professor/thesis_grades.js"></script>
<?php endif; ?>



<?php include '../../templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cancelBtn = document.getElementById('cancelAssignmentBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                if (confirm('Είστε βέβαιοι ότι θέλετε να ακυρώσετε την ανάθεση της διπλωματικής εργασίας με τίτλο "<?php echo htmlspecialchars($thesis['thesis_title']); ?>"?')) {
                    fetch('../../api/professor/cancel_assignment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            thesis_id: <?php echo $thesis_id; ?>,
                            reason: 'Ακύρωση ανάθεσης από τον επιβλέποντα καθηγητή'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            if (confirm('Σφάλμα: ' + data.message + '\n\nΕπιθυμείτε να προσπαθήσετε ξανά;')) {
                                document.getElementById('cancelAssignmentBtn').click();
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        confirm('Σφάλμα κατά την ακύρωση της διπλωματικής.');
                    });
                }
            });
        }
    });
</script>
