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
            ta.assignment_date, 
            ta.final_grade, 
            ta.repository_link,
            ta.completion_date,
            tt.title, 
            tt.summary,
            CONCAT(s.first_name, ' ', s.last_name) AS student_name,
            st.registration_number AS student_reg_number,
            s.email AS student_email,
            CONCAT(p.first_name, ' ', p.last_name) AS supervisor_name,
            p.email AS supervisor_email
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
            ta.id = :thesis_id AND ta.status = 'examination'
    ");
    $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
    $stmt->execute();
    $thesis = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = 'Προέκυψε σφάλμα κατά την ανάκτηση των στοιχείων της διπλωματικής εργασίας: ' . $e->getMessage();
} catch (Exception $e) {
    $error = 'Προέκυψε γενικό σφάλμα: ' . $e->getMessage();
}

if (!$thesis) {
    $error = 'Η διπλωματική εργασία με ID: ' . $thesis_id . ' δεν βρέθηκε ή δεν είναι σε κατάσταση εξέτασης.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_thesis']) && $thesis) {
    if (empty($thesis['final_grade'])) {
        $error = 'Δεν έχει καταχωρηθεί βαθμός για τη διπλωματική εργασία.';
    } elseif (empty($thesis['repository_link'])) {
        $error = 'Δεν έχει αναρτηθεί σύνδεσμος προς το Νημερτής από το φοιτητή.';
    } else {
        try {
            $stmt = $conn->prepare("
                UPDATE thesis_assignments 
                SET status = 'completed', completion_date = CURRENT_DATE() 
                WHERE id = :thesis_id
            ");
            $stmt->bindParam(':thesis_id', $thesis_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $success = 'Η διπλωματική εργασία ολοκληρώθηκε επιτυχώς και χαρακτηρίστηκε ως περατωμένη.';
                
                $thesis['status'] = 'completed';
                $thesis['completion_date'] = date('Y-m-d');
                
                echo "<script>setTimeout(function() { window.location.reload(); }, 2000);</script>";
            } else {
                $error = 'Προέκυψε σφάλμα κατά την ενημέρωση της κατάστασης.';
            }
        } catch (PDOException $e) {
            $error = 'Προέκυψε σφάλμα κατά την ενημέρωση της κατάστασης: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Διαχείριση Διπλωματικής Υπό Εξέταση';
include_once '../../templates/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/secretary/dashboard.php">Αρχική</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Διαχείριση Διπλωματικής Υπό Εξέταση</li>
                </ol>
            </nav>
            
            <h2 class="mb-4">Διαχείριση Διπλωματικής Υπό Εξέταση</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($thesis): ?>
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
                            <div class="col-md-3 fw-bold">Κατάσταση:</div>
                            <div class="col-md-9">
                                <?php if ($thesis['status'] === 'examination'): ?>
                                    <span class="badge bg-warning text-dark">Υπό Εξέταση</span>
                                <?php elseif ($thesis['status'] === 'completed'): ?>
                                    <span class="badge bg-success">Περατωμένη</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Ημερομηνία Ανάθεσης:</div>
                            <div class="col-md-9"><?php echo date('d/m/Y', strtotime($thesis['assignment_date'])); ?></div>
                        </div>
                        <?php if (!empty($thesis['summary'])): ?>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Περίληψη:</div>
                            <div class="col-md-9"><?php echo nl2br(htmlspecialchars($thesis['summary'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Στοιχεία Φοιτητή</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Ονοματεπώνυμο:</div>
                            <div class="col-md-9">
                                <?php echo htmlspecialchars($thesis['student_name']); ?>
                            </div>
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
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Στοιχεία Επιβλέποντα</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Ονοματεπώνυμο:</div>
                            <div class="col-md-9">
                                <?php echo htmlspecialchars($thesis['supervisor_name']); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Email:</div>
                            <div class="col-md-9">
                                <a href="mailto:<?php echo htmlspecialchars($thesis['supervisor_email']); ?>">
                                    <?php echo htmlspecialchars($thesis['supervisor_email']); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Στοιχεία Ολοκλήρωσης</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Βαθμός:</div>
                            <div class="col-md-9">
                                <?php if (!empty($thesis['final_grade'])): ?>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($thesis['final_grade']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Δεν έχει καταχωρηθεί</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 fw-bold">Σύνδεσμος Νημερτής:</div>
                            <div class="col-md-9">
                                <?php if (!empty($thesis['repository_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($thesis['repository_link']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-2"></i>Άνοιγμα συνδέσμου
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-danger">Δεν έχει αναρτηθεί</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($thesis['status'] === 'examination'): ?>
                            <form method="post" action="" class="mt-4">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Για να χαρακτηριστεί η διπλωματική εργασία ως περατωμένη, πρέπει να έχει καταχωρηθεί βαθμός και να έχει αναρτηθεί σύνδεσμος προς το Νημερτής.
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo SITE_URL; ?>/pages/secretary/dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Επιστροφή
                                    </a>
                                    <button type="submit" name="complete_thesis" class="btn btn-success" 
                                            <?php echo (empty($thesis['final_grade']) || empty($thesis['repository_link'])) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check-circle"></i> Ολοκλήρωση Διπλωματικής
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                Η διπλωματική εργασία έχει χαρακτηριστεί ως περατωμένη.
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="<?php echo SITE_URL; ?>/pages/secretary/dashboard.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Επιστροφή
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../templates/footer.php'; ?>
