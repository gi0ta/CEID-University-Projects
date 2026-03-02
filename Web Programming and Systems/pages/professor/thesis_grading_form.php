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

$user_id = User::getCurrentUserId();

$db = getDbConnection();

$professor_sql = "SELECT id FROM professors WHERE user_id = ?";
$professor_stmt = $db->prepare($professor_sql);
if ($professor_stmt) {
    $professor_stmt->bindParam(1, $user_id, PDO::PARAM_INT);
    $professor_stmt->execute();
    $professor = $professor_stmt->fetch(PDO::FETCH_ASSOC);
    if ($professor) {
        $professorId = $professor['id'];
    } else {
        header('Location: thesis_assignments.php');
        exit;
    }
} else {
    header('Location: thesis_assignments.php');
    exit;
}

$sql = "
    SELECT 
        ta.id AS assignment_id,
        tt.title AS thesis_title,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name,
        s.registration_number AS student_am,
        ta.status,
        ta.supervisor_id,
        ta.grading_enabled,
        ta.final_grade AS grade,
        ta.repository_link,
        (SELECT protocol_pdf FROM examination_protocols WHERE thesis_assignment_id = ta.id LIMIT 1) AS grade_protocol_file,
        CASE 
            WHEN ta.supervisor_id = ? THEN 'supervisor'
            ELSE 'committee_member'
        END AS professor_role
    FROM 
        thesis_assignments ta
    JOIN 
        thesis_topics tt ON ta.thesis_topic_id = tt.id
    JOIN 
        students s ON ta.student_id = s.id
    JOIN 
        users u ON s.user_id = u.id
    WHERE 
        ta.id = ?
        AND (ta.supervisor_id = ? OR EXISTS (
            SELECT 1 FROM committee_members cm 
            WHERE cm.thesis_assignment_id = ta.id AND cm.professor_id = ? AND cm.status = 'accepted'
        ))
        AND ta.status = 'examination'
        AND ta.grading_enabled = 1
";

$stmt = $db->prepare($sql);
$stmt->bindParam(1, $professorId, PDO::PARAM_INT);
$stmt->bindParam(2, $thesis_id, PDO::PARAM_INT);
$stmt->bindParam(3, $professorId, PDO::PARAM_INT);
$stmt->bindParam(4, $professorId, PDO::PARAM_INT);
$stmt->execute();
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    header('Location: thesis_list.php?error=thesis_not_found_or_grading_not_enabled');
    exit;
}

$page_title = 'Βαθμολόγηση Διπλωματικής Εργασίας';

include '../../templates/header.php';
?>

<main class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Αρχική</a></li>
                    <li class="breadcrumb-item"><a href="thesis_list.php">Διπλωματικές Εργασίες</a></li>
                    <li class="breadcrumb-item"><a href="thesis_details.php?id=<?php echo $thesis_id; ?>">Λεπτομέρειες Διπλωματικής</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Βαθμολόγηση</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Βαθμολόγηση Διπλωματικής Εργασίας</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Στοιχεία Διπλωματικής</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Τίτλος:</strong> <?php echo htmlspecialchars($thesis['thesis_title']); ?></p>
                            <p><strong>Φοιτητής:</strong> <?php echo htmlspecialchars($thesis['student_name']); ?> (<?php echo htmlspecialchars($thesis['student_am']); ?>)</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Κατάσταση:</strong> 
                                <span class="badge bg-info">Υπό Εξέταση</span>
                            </p>
                            <p>
                                <strong>Σύνδεσμος Αποθετηρίου:</strong> 
                                <?php if (!empty($thesis['repository_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($thesis['repository_link']); ?>" target="_blank" class="text-primary">
                                        <?php echo htmlspecialchars($thesis['repository_link']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Φόρμα Βαθμολόγησης -->
                    <h6 class="card-subtitle mb-3">Φόρμα Βαθμολόγησης</h6>
                    
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Κριτήρια Βαθμολόγησης</h6>
                        <ol>
                            <li>Ποιότητα της Δ.Ε. και βαθμός εκπλήρωσης των στόχων της (60%)</li>
                            <li>Χρονικό διάστημα εκπόνησής της (15%)</li>
                            <li>Ποιότητα και πληρότητα του κειμένου της εργασίας και των υπολοίπων παραδοτέων της (15%)</li>
                            <li>Συνολική εικόνα της παρουσίασης (10%)</li>
                        </ol>
                    </div>
                    
                    <div id="gradingFormMessages" class="alert" style="display: none;"></div>
                    
                    <form id="thesisGradingForm" method="POST" action="../../api/professor/save_thesis_grade.php" enctype="multipart/form-data">
                        <input type="hidden" name="thesis_id" value="<?php echo $thesis_id; ?>">
                        <input type="hidden" name="professor_id" value="<?php echo $professorId; ?>">
                        
                        <!-- Κριτήριο 1: Ποιότητα της Δ.Ε. και βαθμός εκπλήρωσης των στόχων της (60%) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="quality_score" class="form-label">1. Ποιότητα της Δ.Ε. και βαθμός εκπλήρωσης των στόχων της (60%)</label>
                                    <input type="number" class="form-control criteria-score" id="quality_score" name="quality_score" min="0" max="10" step="0.1" required data-weight="0.6">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Κριτήριο 2: Χρονικό διάστημα εκπόνησής της (15%) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="time_score" class="form-label">2. Χρονικό διάστημα εκπόνησής της (15%)</label>
                                    <input type="number" class="form-control criteria-score" id="time_score" name="time_score" min="0" max="10" step="0.1" required data-weight="0.15">
                                    <small class="form-text text-muted">Άριστα μόνο όταν η Δ.Ε. έχει εκπονηθεί σε διάστημα μικρότερο του 1.5 έτους</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Κριτήριο 3: Ποιότητα και πληρότητα του κειμένου της εργασίας και των υπολοίπων παραδοτέων της (15%) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="document_score" class="form-label">3. Ποιότητα και πληρότητα του κειμένου της εργασίας και των υπολοίπων παραδοτέων της (15%)</label>
                                    <input type="number" class="form-control criteria-score" id="document_score" name="document_score" min="0" max="10" step="0.1" required data-weight="0.15">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Κριτήριο 4: Συνολική εικόνα της παρουσίασης (10%) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="presentation_score" class="form-label">4. Συνολική εικόνα της παρουσίασης (10%)</label>
                                    <input type="number" class="form-control criteria-score" id="presentation_score" name="presentation_score" min="0" max="10" step="0.1" required data-weight="0.1">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="final_grade" class="form-label"><strong>Τελικός Βαθμός</strong></label>
                                    <input type="number" class="form-control" id="final_grade" name="final_grade" min="0" max="10" step="0.1" required readonly>
                                    <small class="form-text text-muted">Ο τελικός βαθμός υπολογίζεται αυτόματα με βάση τα παραπάνω κριτήρια</small>
                                </div>
                            </div>
                        </div>
                        
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="thesis_details.php?id=<?php echo $thesis_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Επιστροφή
                            </a>
                            <button type="submit" class="btn btn-success" id="submitGradeBtn">
                                <i class="fas fa-save"></i> Αποθήκευση Βαθμολογίας
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../templates/footer.php'; ?>

<!-- JavaScript για τη φόρμα βαθμολόγησης -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gradingForm = document.getElementById('thesisGradingForm');
    const messagesContainer = document.getElementById('gradingFormMessages');
    const criteriaScores = document.querySelectorAll('.criteria-score');
    const weightedScores = document.querySelectorAll('.weighted-score');
    const finalGradeInput = document.getElementById('final_grade');
    
    function calculateFinalGrade() {
        let totalScore = 0;
        let allFieldsFilled = true;
        
        criteriaScores.forEach((input) => {
            if (input.value === '') {
                allFieldsFilled = false;
                return;
            }
            
            const score = parseFloat(input.value);
            const weight = parseFloat(input.dataset.weight);
            const weightedScore = score * weight;
            
            totalScore += weightedScore;
        });
        
        if (allFieldsFilled) {
            finalGradeInput.value = totalScore.toFixed(1);
        }
    }
    
    criteriaScores.forEach((input) => {
        input.addEventListener('input', function() {
            let value = parseFloat(this.value);
            if (isNaN(value)) return;
            
            if (value < 0) {
                this.value = 0;
                value = 0;
            }
            if (value > 10) {
                this.value = 10;
                value = 10;
            }
            
            calculateFinalGrade();
        });
    });
    
    calculateFinalGrade();
    
    if (gradingForm) {
        console.log('Grading form found, adding submit event listener');
        gradingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submit event triggered');
            
            let isValid = true;
            criteriaScores.forEach((input) => {
                if (input.value === '') {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                messagesContainer.style.display = 'block';
                messagesContainer.className = 'alert alert-danger';
                messagesContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Παρακαλώ συμπληρώστε όλα τα πεδία βαθμολογίας.';
                return;
            }
            
            const submitBtn = document.getElementById('submitGradeBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Επεξεργασία...';
            
            const formData = new FormData(this);
            console.log('Form data created');
            
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            console.log('Sending request to API...');
            fetch('../../api/professor/save_thesis_grade.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                messagesContainer.style.display = 'block';
                
                if (data.success) {
                    messagesContainer.className = 'alert alert-success';
                    messagesContainer.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    
                    setTimeout(() => {
                        window.location.href = 'thesis_details.php?id=' + <?php echo $thesis_id; ?>;
                    }, 2000);
                } else {
                    messagesContainer.className = 'alert alert-danger';
                    messagesContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + data.message;
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Αποθήκευση Βαθμολογίας';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                messagesContainer.style.display = 'block';
                messagesContainer.className = 'alert alert-danger';
                messagesContainer.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Σφάλμα κατά την επικοινωνία με τον διακομιστή: ' + error.message;
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Αποθήκευση Βαθμολογίας';
            });
        });
    }
});
</script>
