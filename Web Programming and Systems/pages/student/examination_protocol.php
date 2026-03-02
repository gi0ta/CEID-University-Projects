<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || User::getCurrentUserRole() !== 'student') {
    header('Location: ../../index.php');
    exit;
}

$user_id = User::getCurrentUserId();
$db = getDbConnection();

$student_stmt = $db->prepare("
    SELECT s.id, u.first_name, u.last_name, s.registration_number
    FROM students s 
    JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ?
");
$student_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$student_stmt->execute();
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: ../../index.php');
    exit;
}

$student_id = $student['id'];

$thesis_id = isset($_GET['thesis_id']) ? (int)$_GET['thesis_id'] : 0;

$thesis_stmt = $db->prepare("
    SELECT 
        ta.id,
        ta.status,
        ta.examination_protocol_generated,
        ta.repository_link,
        ta.final_grade,
        tt.title AS thesis_title,
        u.first_name AS supervisor_first_name,
        u.last_name AS supervisor_last_name,
        p.specialty AS supervisor_specialty
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        professors p ON ta.supervisor_id = p.id
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        ta.student_id = ? AND (ta.status = 'examination' OR ta.status = 'completed')
        " . ($thesis_id > 0 ? " AND ta.id = ?" : "") . "
    ORDER BY 
        ta.id DESC
    LIMIT 1
");
$thesis_stmt->bindParam(1, $student_id, PDO::PARAM_INT);
if ($thesis_id > 0) {
    $thesis_stmt->bindParam(2, $thesis_id, PDO::PARAM_INT);
}
$thesis_stmt->execute();
$thesis = $thesis_stmt->fetch(PDO::FETCH_ASSOC);

$has_thesis = ($thesis !== false);
$protocol_generated = $has_thesis && $thesis['examination_protocol_generated'] == 1;
$has_repository_link = $has_thesis && !empty($thesis['repository_link']);

$protocol_html = '';
$protocol_number = '';
$protocol_date = '';

if ($protocol_generated && isset($thesis['id'])) {
    echo "<!-- Debug: thesis_id = " . $thesis['id'] . " -->";
    
    $protocol_stmt = $db->prepare("
        SELECT 
            ep.protocol_html,
            ep.protocol_number,
            ep.protocol_date
        FROM 
            examination_protocols ep
        WHERE 
            ep.thesis_assignment_id = ?
        ORDER BY 
            ep.id DESC
        LIMIT 1
    ");
    $protocol_stmt->bindParam(1, $thesis['id'], PDO::PARAM_INT);
    $protocol_stmt->execute();
    $protocol = $protocol_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($protocol) {
        $protocol_html = $protocol['protocol_html'];
        $protocol_number = $protocol['protocol_number'];
        $protocol_date = $protocol['protocol_date'];
    } else {
        echo "<!-- Debug: Δεν βρέθηκε πρωτόκολλο για thesis_id = " . $thesis['id'] . " -->";
    }
}

$page_title = 'Πρωτόκολλο Εξέτασης Διπλωματικής Εργασίας';

include '../../templates/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4"><?php echo $page_title; ?></h1>
    
    <?php if (!$has_thesis): ?>
        <div class="alert alert-warning">
            <p>Δεν βρέθηκε διπλωματική εργασία σε κατάσταση εξέτασης.</p>
        </div>
    <?php elseif (!$protocol_generated): ?>
        <div class="alert alert-info">
            <p>Το πρωτόκολλο εξέτασης για τη διπλωματική σας εργασία δεν έχει δημιουργηθεί ακόμα.</p>
            <p>Παρακαλώ περιμένετε μέχρι να ολοκληρωθεί η βαθμολόγηση από όλα τα μέλη της επιτροπής και τον επιβλέποντα καθηγητή.</p>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Στοιχεία Διπλωματικής Εργασίας</h5>
            </div>
            <div class="card-body">
                <p><strong>Τίτλος:</strong> <?php echo htmlspecialchars($thesis['thesis_title']); ?></p>
                <p><strong>Επιβλέπων/ουσα:</strong> <?php echo htmlspecialchars($thesis['supervisor_first_name'] . ' ' . $thesis['supervisor_last_name'] . ' (' . $thesis['supervisor_specialty'] . ')'); ?></p>
                <p><strong>Κατάσταση:</strong> <?php echo htmlspecialchars($thesis['status'] === 'examination' ? 'Σε εξέταση' : $thesis['status']); ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Πρωτόκολλο Εξέτασης</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($protocol_html)): ?>
                            <div class="mb-3">
                                <p><strong>Αριθμός Πρωτοκόλλου:</strong> <?php echo htmlspecialchars($protocol_number); ?></p>
                                <p><strong>Ημερομηνία:</strong> <?php echo date('d/m/Y', strtotime($protocol_date)); ?></p>
                                <p><strong>Τελικός Βαθμός:</strong> <?php echo htmlspecialchars($thesis['final_grade']); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <h6>Σύνδεσμος Αποθετηρίου (Νημερτής)</h6>
                                <?php if ($has_repository_link): ?>
                                    <p>Έχετε ήδη υποβάλει τον παρακάτω σύνδεσμο για το αποθετήριο:</p>
                                    <p><a href="<?php echo htmlspecialchars($thesis['repository_link']); ?>" target="_blank"><?php echo htmlspecialchars($thesis['repository_link']); ?></a></p>
                                    
                                    <form id="updateRepositoryForm" class="mt-3">
                                        <input type="hidden" name="thesis_id" value="<?php echo $thesis['id']; ?>">
                                        <div class="form-group">
                                            <label for="repository_link">Ενημέρωση συνδέσμου αποθετηρίου:</label>
                                            <input type="url" class="form-control" id="repository_link" name="repository_link" value="<?php echo htmlspecialchars($thesis['repository_link']); ?>" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">Ενημέρωση Συνδέσμου</button>
                                    </form>
                                <?php else: ?>
                                    <p>Παρακαλώ υποβάλετε τον σύνδεσμο για το αποθετήριο της διπλωματικής σας εργασίας:</p>
                                    
                                    <form id="repositoryForm">
                                        <input type="hidden" name="thesis_id" value="<?php echo $thesis['id']; ?>">
                                        <div class="form-group">
                                            <label for="repository_link">Σύνδεσμος αποθετηρίου:</label>
                                            <input type="url" class="form-control" id="repository_link" name="repository_link" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">Υποβολή Συνδέσμου</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="protocol-container">
                                <iframe id="protocolFrame" style="width:100%; height:600px; border:none;"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <p>Δεν βρέθηκε το περιεχόμενο του πρωτοκόλλου εξέτασης.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($protocol_html)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const iframe = document.getElementById('protocolFrame');
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        
        iframeDoc.open();
        iframeDoc.write(`<?php echo str_replace('`', '\\`', $protocol_html); ?>`);
        iframeDoc.close();
    });
    
    function printProtocol() {
        const iframe = document.getElementById('protocolFrame');
        iframe.contentWindow.print();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('repositoryForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitRepositoryLink(form);
            });
        }
        
        const updateForm = document.getElementById('updateRepositoryForm');
        if (updateForm) {
            updateForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitRepositoryLink(updateForm);
            });
        }
    });
    
    function submitRepositoryLink(form) {
        const formData = new FormData(form);
        
        fetch('../../api/student/save_repository_link.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Σφάλμα: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Σφάλμα κατά την υποβολή του συνδέσμου.');
        });
    }
</script>
<?php endif; ?>

<?php
include '../../templates/footer.php';
?>
