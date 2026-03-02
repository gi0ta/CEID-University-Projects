<?php


require_once '../../includes/init.php';

if (!User::isLoggedIn() || !User::hasRole('secretary')) {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$thesis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$thesis = null;
$error = '';
$success = '';



if ($thesis_id <= 0) {
    header('Location: ' . SITE_URL . '/pages/secretary/dashboard.php');
    exit;
}



try {

    $db = new Database();
    $conn = $db->getConnection();

    
    $stmt = $conn->prepare("
        SELECT 
            ta.id, 
            ta.status, 
            ta.gs_number, 
            ta.gs_year, 
            ta.assignment_date,
            ta.completion_date,
            ta.deadline_date,
            ta.final_grade,
            tt.title,
            tt.summary AS description,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            st.registration_number AS student_reg_number,
            s.email AS student_email,
            st.mobile_phone AS student_phone,
            'CEID' AS student_department,
            st.semester AS student_semester,
            CONCAT(p.first_name, ' ', p.last_name) AS supervisor_name,
            p.email AS supervisor_email,
            pr.specialty AS supervisor_specialty,
            pr.department AS supervisor_department
        FROM 
            thesis_assignments ta
        JOIN 
            thesis_topics tt ON ta.thesis_topic_id = tt.id
        JOIN 
            students st ON ta.student_id = st.id
        JOIN 
            users s ON st.user_id = s.id
        JOIN 
            professors pr ON ta.supervisor_id = pr.id
        JOIN 
            users p ON pr.user_id = p.id
        WHERE 
            ta.id = :thesis_id
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Προέκυψε σφάλμα κατά την ανάκτηση των στοιχείων της διπλωματικής εργασίας.';
}

if (!$thesis) {
    $error = 'Η διπλωματική εργασία με ID: ' . $thesis_id . ' δεν βρέθηκε στη βάση δεδομένων.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $thesis && (empty($thesis['gs_number']) || empty($thesis['gs_year']))) {
    $gs_number = isset($_POST['gs_number']) ? trim($_POST['gs_number']) : '';
    $gs_year = isset($_POST['gs_year']) ? (int)$_POST['gs_year'] : 0;
    
    if (empty($gs_number)) {
        $error = 'Παρακαλώ συμπληρώστε τον Αριθμό Πρωτοκόλλου ΓΣ.';
    } elseif ($gs_year <= 0) {
        $error = 'Παρακαλώ συμπληρώστε ένα έγκυρο έτος για το Πρωτόκολλο ΓΣ.';
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE thesis_assignments 
                SET gs_number = :gs_number, gs_year = :gs_year 
                WHERE id = :thesis_id
            ");
            $stmt->bindParam(':gs_number', $gs_number, PDO::PARAM_STR);
            $stmt->bindParam(':gs_year', $gs_year, PDO::PARAM_INT);
            $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $success = 'Τα στοιχεία του Πρωτοκόλλου ΓΣ καταχωρήθηκαν με επιτυχία.';
                $thesis['gs_number'] = $gs_number;
                $thesis['gs_year'] = $gs_year;
            } else {
                $error = 'Προέκυψε σφάλμα κατά την καταχώρηση των στοιχείων.';
            }
        } catch (PDOException $e) {
            $error = 'Προέκυψε σφάλμα κατά την καταχώρηση των στοιχείων: ' . $e->getMessage();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $thesis && !empty($thesis['gs_number']) && !empty($thesis['gs_year'])) {
    $error = 'Τα στοιχεία του Πρωτοκόλλου ΓΣ έχουν ήδη καταχωρηθεί και δεν μπορούν να τροποποιηθούν.';
}

$pageTitle = 'Καταχώρηση ΑΠ ΓΣ Διπλωματικής Εργασίας';
?><?php

require_once '../../includes/config/config.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/classes/Database.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (!User::isLoggedIn() || !User::hasRole('secretary')) {
    header('Location: ' . SITE_URL . '/pages/auth/login.php');
    exit;
}

$pageTitle = "Καταχώρηση Αριθμού Πρωτοκόλλου ΓΣ";
include_once '../../templates/header.php';
?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/secretary/dashboard.php">Αρχική</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Καταχώρηση ΑΠ ΓΣ</li>
                    </ol>
                </nav>
                
                <h1 class="mb-4"><?php echo $pageTitle; ?></h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($thesis): ?>
                    <!-- Στοιχεία Διπλωματικής Εργασίας -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Στοιχεία Διπλωματικής Εργασίας</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Τίτλος:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['title']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Περιγραφή:</div>
                                <div class="col-md-9"><?php echo nl2br(htmlspecialchars($thesis['description'] ?? 'Δεν υπάρχει διαθέσιμη περιγραφή')); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Ημερομηνία Ανάθεσης:</div>
                                <div class="col-md-9">
                                    <?php 
                                    $assignment_date = new DateTime($thesis['assignment_date']);
                                    echo $assignment_date->format('d/m/Y'); 
                                    ?>
                                </div>
                            </div>
                            <?php if (!empty($thesis['deadline_date'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Ημερομηνία Λήξης:</div>
                                <div class="col-md-9">
                                    <?php 
                                    $deadline_date = new DateTime($thesis['deadline_date']);
                                    echo $deadline_date->format('d/m/Y'); 
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($thesis['completion_date'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Ημερομηνία Ολοκλήρωσης:</div>
                                <div class="col-md-9">
                                    <?php 
                                    $completion_date = new DateTime($thesis['completion_date']);
                                    echo $completion_date->format('d/m/Y'); 
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($thesis['final_grade'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Βαθμός:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['final_grade']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Στοιχεία Φοιτητή -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Στοιχεία Φοιτητή</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Ονοματεπώνυμο:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['student_name']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Αριθμός Μητρώου:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['student_reg_number']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Email:</div>
                                <div class="col-md-9">
                                    <a href="mailto:<?php echo htmlspecialchars($thesis['student_email']); ?>">
                                        <?php echo htmlspecialchars($thesis['student_email']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php if (!empty($thesis['student_phone'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Τηλέφωνο:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['student_phone']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($thesis['student_department'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Τμήμα:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['student_department']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($thesis['student_semester'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Εξάμηνο:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['student_semester']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Στοιχεία Επιβλέποντα -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Στοιχεία Επιβλέποντα</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Ονοματεπώνυμο:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['supervisor_name']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Email:</div>
                                <div class="col-md-9">
                                    <a href="mailto:<?php echo htmlspecialchars($thesis['supervisor_email']); ?>">
                                        <?php echo htmlspecialchars($thesis['supervisor_email']); ?>
                                    </a>
                                </div>
                            </div>
                            <?php if (!empty($thesis['supervisor_specialty'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Ειδικότητα:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['supervisor_specialty']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($thesis['supervisor_department'])): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Τμήμα:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['supervisor_department']); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (empty($thesis['gs_number']) || empty($thesis['gs_year'])): ?>
                    <!-- Φόρμα καταχώρησης αν δεν υπάρχει ήδη αριθμός πρωτοκόλλου -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Καταχώρηση Αριθμού Πρωτοκόλλου ΓΣ</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="gs_number" class="form-label">Αριθμός Πρωτοκόλλου ΓΣ</label>
                                        <input type="text" class="form-control" id="gs_number" name="gs_number" 
                                               value="<?php echo htmlspecialchars($thesis['gs_number'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gs_year" class="form-label">Έτος</label>
                                        <input type="number" class="form-control" id="gs_year" name="gs_year" 
                                               value="<?php echo htmlspecialchars($thesis['gs_year'] ?? date('Y')); ?>" 
                                               min="2000" max="2100" required>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo SITE_URL; ?>/pages/secretary/dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Επιστροφή
                                    </a>
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Αποθήκευση
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Εμφάνιση στοιχείων πρωτοκόλλου αν έχουν ήδη καταχωρηθεί -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Στοιχεία Πρωτοκόλλου ΓΣ</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Αριθμός Πρωτοκόλλου ΓΣ:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['gs_number']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold">Έτος:</div>
                                <div class="col-md-9"><?php echo htmlspecialchars($thesis['gs_year']); ?></div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo SITE_URL; ?>/pages/secretary/dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Επιστροφή
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
<?php
include_once '../../templates/footer.php';
?>
