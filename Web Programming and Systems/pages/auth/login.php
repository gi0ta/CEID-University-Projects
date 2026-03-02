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
$password = '';
$errors = [];
$csrfToken = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Μη έγκυρο αίτημα. Παρακαλώ προσπαθήστε ξανά.';
    } else {
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($username)) {
            $errors[] = 'Το όνομα χρήστη ή το email είναι υποχρεωτικό.';
        }
        
        if (empty($password)) {
            $errors[] = 'Ο κωδικός πρόσβασης είναι υποχρεωτικός.';
        }
        
        if (empty($errors)) {
            $user = new User($db);
            $loginResult = $user->login($username, $password, false);
            
            if ($loginResult === true) {
                logLoginAttempt($username, true, $_SERVER['REMOTE_ADDR']);
                
                redirectBasedOnRole(User::getCurrentUserRole());
            } else {
                logLoginAttempt($username, false, $_SERVER['REMOTE_ADDR']);
                
                $errors[] = $loginResult;
            }
        }
    }
}

$pageTitle = 'Σύνδεση';
?>

<?php include '../../templates/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Σύστημα Διπλωματικών Εργασιών</h4>
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
                        <label for="username" class="form-label">Όνομα χρήστη ή Email</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Κωδικός πρόσβασης</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Σύνδεση</button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <div class="d-flex justify-content-center">
                    <a href="register.php" class="btn btn-outline-primary">Εγγραφή</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../templates/footer.php'; ?>
