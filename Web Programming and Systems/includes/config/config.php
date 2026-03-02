<?php

define('SITE_NAME', 'Σύστημα Διπλωματικών');
define('SITE_URL', 'http://localhost/web_php');

define('SESSION_NAME', 'diploma_system_session');
define('SESSION_LIFETIME', 7200);
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false);
define('SESSION_HTTP_ONLY', true);

define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRY', 3600);

define('PASSWORD_RESET_EXPIRY', 3600);

define('UPLOAD_MAX_SIZE', 10485760);
define('UPLOAD_ALLOWED_TYPES', 'pdf,doc,docx,ppt,pptx,zip,rar,jpg,jpeg,png');

function startSecureSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params(
        SESSION_LIFETIME,
        SESSION_PATH,
        SESSION_DOMAIN,
        SESSION_SECURE,
        SESSION_HTTP_ONLY
    );
    
    session_name(SESSION_NAME);
    
    session_start();
    
    if (!isset($_SESSION['last_regeneration'])) {
        regenerateSessionId();
    } else {
        $interval = 1800;
        if ($_SESSION['last_regeneration'] + $interval < time()) {
            regenerateSessionId();
        }
    }
}

function regenerateSessionId() {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

function generateCsrfToken() {
    if (!isset($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }
    
    $token = bin2hex(random_bytes(32));
    
    $_SESSION['csrf_tokens'][$token] = time() + CSRF_TOKEN_EXPIRY;
    
    foreach ($_SESSION['csrf_tokens'] as $t => $expiry) {
        if ($expiry < time()) {
            unset($_SESSION['csrf_tokens'][$t]);
        }
    }
    
    return $token;
}

function verifyCsrfToken($token) {
    if (empty($token) || !isset($_SESSION['csrf_tokens'][$token])) {
        return false;
    }
    
    if ($_SESSION['csrf_tokens'][$token] < time()) {
        unset($_SESSION['csrf_tokens'][$token]);
        return false;
    }
    
    unset($_SESSION['csrf_tokens'][$token]);
    
    return true;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function redirect($url) {
    echo "<script>window.location.href = '$url';</script>";
    exit;
}

function setFlashMessage($message, $type = 'info') {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayFlashMessages() {
    if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $message) {
            echo '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">';
            echo $message['message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        
        $_SESSION['flash_messages'] = [];
    }
}
?>
