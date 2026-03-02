<?php

function redirectBasedOnRole($role) {
    switch ($role) {
        case 'professor':
            redirect(SITE_URL . '/pages/professor/dashboard.php');
            break;
        case 'student':
            redirect(SITE_URL . '/pages/student/dashboard.php');
            break;
        case 'secretary':
            redirect(SITE_URL . '/pages/secretary/dashboard.php');
            break;
        default:
            redirect(SITE_URL . '/pages/auth/login.php');
            break;
    }
}


function logLoginAttempt($username, $success, $ip = null) {
    return;
    
}

function requireRole($allowedRoles, $redirectUrl = null) {
    if (!User::isLoggedIn()) {
        if ($redirectUrl === null) {
            $redirectUrl = SITE_URL . '/pages/auth/login.php';
        }
        
        setFlashMessage('Πρέπει να συνδεθείτε για να αποκτήσετε πρόσβαση σε αυτή τη σελίδα.', 'warning');
        redirect($redirectUrl);
    }
    
    if (!User::hasRole($allowedRoles)) {
        if ($redirectUrl === null) {
            $redirectUrl = SITE_URL;
        }
        
        setFlashMessage('Δεν έχετε πρόσβαση σε αυτή τη σελίδα.', 'danger');
        redirect($redirectUrl);
    }
}

function requireRoleType($allowedRoleTypes, $redirectUrl = null) {
    if (!User::isLoggedIn()) {
        if ($redirectUrl === null) {
            $redirectUrl = SITE_URL . '/pages/auth/login.php';
        }
        
        setFlashMessage('Πρέπει να συνδεθείτε για να αποκτήσετε πρόσβαση σε αυτή τη σελίδα.', 'warning');
        redirect($redirectUrl);
    }
    
    if (!User::hasRoleType($allowedRoleTypes)) {
        if ($redirectUrl === null) {
            $redirectUrl = SITE_URL;
        }
        
        setFlashMessage('Δεν έχετε πρόσβαση σε αυτή τη σελίδα.', 'danger');
        redirect($redirectUrl);
    }
}
?>
