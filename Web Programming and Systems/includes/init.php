<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Database.php';

require_once __DIR__ . '/functions/auth_functions.php';

startSecureSession();
