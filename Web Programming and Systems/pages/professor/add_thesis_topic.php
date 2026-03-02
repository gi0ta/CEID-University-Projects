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

$title = '';
$summary = '';
$errors = [];
$success = false;
$csrfToken = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Μη έγκυρο αίτημα. Παρακαλώ προσπαθήστε ξανά.';
    } else {
        $title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
        $summary = isset($_POST['summary']) ? sanitizeInput($_POST['summary']) : '';
        
        if (empty($title)) {
            $errors[] = 'Ο τίτλος του θέματος είναι υποχρεωτικός.';
        } elseif (strlen($title) < 10) {
            $errors[] = 'Ο τίτλος του θέματος πρέπει να έχει τουλάχιστον 10 χαρακτήρες.';
        }
        
        if (empty($summary)) {
            $errors[] = 'Η περίληψη του θέματος είναι υποχρεωτική.';
        } elseif (strlen($summary) < 50) {
            $errors[] = 'Η περίληψη του θέματος πρέπει να έχει τουλάχιστον 50 χαρακτήρες.';
        }
        
        $description_file = '';
        $description_file_type = '';
        
        if (isset($_FILES['description_file']) && $_FILES['description_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $file_type = $_FILES['description_file']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'Ο τύπος του αρχείου περιγραφής δεν είναι αποδεκτός. Επιτρέπονται μόνο αρχεία PDF και Word.';
            } else {
                $upload_dir = '../../uploads/thesis_files/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = time() . '_' . basename($_FILES['description_file']['name']);
                $target_file = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['description_file']['tmp_name'], $target_file)) {
                    $description_file = $file_name;
                    $description_file_type = $file_type;
                } else {
                    $errors[] = 'Παρουσιάστηκε σφάλμα κατά την αποθήκευση του αρχείου περιγραφής.';
                }
            }
        }
        
        if (empty($errors)) {
            try {
                $query = "INSERT INTO thesis_topics (professor_id, title, summary, description_file, description_file_type, created_at) 
                          VALUES (:professor_id, :title, :summary, :description_file, :description_file_type, NOW())";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':professor_id', $professor['id']);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':summary', $summary);
                $stmt->bindParam(':description_file', $description_file);
                $stmt->bindParam(':description_file_type', $description_file_type);
                $stmt->execute();
                
                $success = true;
                
                $title = '';
                $summary = '';
                
                $csrfToken = generateCsrfToken();
            } catch (PDOException $e) {
                $errors[] = 'Σφάλμα κατά την προσθήκη του θέματος: ' . $e->getMessage();
            }
        }
    }
}

$pageTitle = 'Προσθήκη Νέου Θέματος Διπλωματικής';
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
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>Το θέμα διπλωματικής προστέθηκε με επιτυχία!
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Τίτλος Θέματος <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
                            <div class="form-text">Ο τίτλος πρέπει να είναι περιγραφικός και να έχει τουλάχιστον 10 χαρακτήρες.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="summary" class="form-label">Περίληψη <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="summary" name="summary" rows="6" required><?php echo htmlspecialchars($summary); ?></textarea>
                            <div class="form-text">Περιγράψτε το θέμα, τους στόχους και τις απαιτήσεις της διπλωματικής εργασίας (τουλάχιστον 50 χαρακτήρες).</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description_file" class="form-label">Αρχείο Αναλυτικής Περιγραφής</label>
                            <input type="file" class="form-control" id="description_file" name="description_file">
                            <div class="form-text">Προαιρετικά, μπορείτε να ανεβάσετε ένα αρχείο με αναλυτική περιγραφή του θέματος (PDF ή Word).</div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Αποθήκευση Θέματος
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
