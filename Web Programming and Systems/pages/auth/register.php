<?php

require_once '../../includes/config/config.php';
require_once '../../includes/config/database.php';
require_once '../../includes/classes/User.php';
require_once '../../includes/functions/auth_functions.php';

startSecureSession();

if (User::isLoggedIn()) {
    redirectBasedOnRole(User::getCurrentUserRole());
}

$db = getDbConnection();

$username = '';
$email = '';
$password = '';
$confirm_password = '';
$first_name = '';
$last_name = '';
$role = 'student';
$errors = [];
$csrfToken = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Μη έγκυρο αίτημα. Παρακαλώ προσπαθήστε ξανά.';
    } else {
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $first_name = isset($_POST['first_name']) ? sanitizeInput($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitizeInput($_POST['last_name']) : '';
        $role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : 'student';
        
        if (empty($username)) {
            $errors[] = 'Το όνομα χρήστη είναι υποχρεωτικό.';
        } elseif (strlen($username) < 4) {
            $errors[] = 'Το όνομα χρήστη πρέπει να έχει τουλάχιστον 4 χαρακτήρες.';
        }
        
        if (empty($email)) {
            $errors[] = 'Το email είναι υποχρεωτικό.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Παρακαλώ εισάγετε ένα έγκυρο email.';
        }
        
        if (empty($password)) {
            $errors[] = 'Ο κωδικός πρόσβασης είναι υποχρεωτικός.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Ο κωδικός πρόσβασης πρέπει να έχει τουλάχιστον 8 χαρακτήρες.';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Οι κωδικοί πρόσβασης δεν ταιριάζουν.';
        }
        
        if (empty($first_name)) {
            $errors[] = 'Το όνομα είναι υποχρεωτικό.';
        }
        
        if (empty($last_name)) {
            $errors[] = 'Το επώνυμο είναι υποχρεωτικό.';
        }
        
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Το όνομα χρήστη χρησιμοποιείται ήδη.';
        }
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $errors[] = 'Το email χρησιμοποιείται ήδη.';
        }
        
        if (empty($errors)) {
            $user = new User($db);
            $result = $user->register($username, $email, $password, $first_name, $last_name, $role);
            
            if ($result === true) {
                setFlashMessage('success', 'Η εγγραφή σας ολοκληρώθηκε με επιτυχία. Παρακαλώ συνδεθείτε.');
                
                header('Location: login.php');
                exit;
            } else {
                $errors[] = $result;
            }
        }
    }
}

$pageTitle = 'Εγγραφή';
?>

<?php include '../../templates/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Εγγραφή Νέου Χρήστη</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php displayFlashMessages(); ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Όνομα χρήστη</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">Όνομα</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Επώνυμο</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Κωδικός πρόσβασης</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="form-text text-muted">Τουλάχιστον 8 χαρακτήρες</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Επιβεβαίωση κωδικού</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Ιδιότητα</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="student" <?php echo $role === 'student' ? 'selected' : ''; ?>>Φοιτητής</option>
                            <option value="professor" <?php echo $role === 'professor' ? 'selected' : ''; ?>>Καθηγητής</option>
                            <option value="secretary" <?php echo $role === 'secretary' ? 'selected' : ''; ?>>Γραμματεία</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Εγγραφή</button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <div class="d-flex justify-content-center">
                    <span>Έχετε ήδη λογαριασμό; </span>
                    <a href="login.php" class="text-decoration-none ms-2">Συνδεθείτε</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
