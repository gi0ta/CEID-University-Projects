<?php
require_once '../../includes/config/config.php';

session_name(SESSION_NAME);
session_start();

$_SESSION = array();
session_destroy();

setcookie(
    SESSION_NAME,
    '',
    time() - 42000,
    SESSION_PATH,
    SESSION_DOMAIN,
    SESSION_SECURE,
    SESSION_HTTP_ONLY
);

setcookie('remember_token', '', time() - 3600, '/');

header("Location: " . SITE_URL . "/index.php");
exit;
?>
