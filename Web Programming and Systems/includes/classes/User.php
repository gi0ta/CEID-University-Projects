<?php
class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function login($username, $password, $rememberMe = false) {
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        
        $query = "SELECT u.*, 
                    CASE 
                        WHEN p.id IS NOT NULL THEN 'professor'
                        WHEN s.id IS NOT NULL THEN 'student'
                        ELSE 'secretary'
                    END as role_type
                  FROM users u
                  LEFT JOIN professors p ON u.id = p.user_id
                  LEFT JOIN students s ON u.id = s.user_id
                  WHERE " . ($isEmail ? "u.email = :username" : "u.username = :username");
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return 'Λάθος όνομα χρήστη ή κωδικός πρόσβασης.';
        }
        
        $user = $stmt->fetch();
        
        if (!password_verify($password, $user['password'])) {
            return 'Λάθος όνομα χρήστη ή κωδικός πρόσβασης.';
        }
        
        if ($user['is_active'] == 0) {
            return 'Ο λογαριασμός σας δεν είναι ενεργός. Παρακαλώ επικοινωνήστε με τη Γραμματεία.';
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['role_type'] = $user['role_type'];
        $_SESSION['last_login'] = time();
        
        $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->bindParam(':id', $user['id']);
        $updateStmt->execute();
        
        if ($rememberMe) {
            $this->setRememberMeToken($user['id']);
        }
        
        return true;
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->clearRememberMeToken($_SESSION['user_id']);
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    private function setRememberMeToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->execute();
        
        $cookieName = 'remember_token';
        $cookieValue = $userId . ':' . $token;
        $cookieExpires = time() + (86400 * 30);
        
        setcookie(
            $cookieName,
            $cookieValue,
            $cookieExpires,
            '/',
            '',
            false,
            true
        );
    }
    
    private function clearRememberMeToken($userId) {
        $query = "DELETE FROM remember_tokens WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function getCurrentUserId() {
        return self::isLoggedIn() ? $_SESSION['user_id'] : null;
    }
    
    public static function getCurrentUserRole() {
        return self::isLoggedIn() ? $_SESSION['role'] : null;
    }
    
    public static function getCurrentUserRoleType() {
        return self::isLoggedIn() ? $_SESSION['role_type'] : null;
    }
    
    public static function hasRole($roles) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (is_array($roles)) {
            return in_array($_SESSION['role'], $roles);
        }
        
        return $_SESSION['role'] === $roles;
    }
    
    public static function hasRoleType($roleTypes) {
        if (!self::isLoggedIn()) {
            return false;
        }
        
        if (is_array($roleTypes)) {
            return in_array($_SESSION['role_type'], $roleTypes);
        }
        
        return $_SESSION['role_type'] === $roleTypes;
    }
    
    public static function getCurrentUserName() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    
    public function register($username, $email, $password, $first_name, $last_name, $role = 'student') {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $this->db->beginTransaction();
            
            $query = "INSERT INTO users (username, password, email, role, first_name, last_name, created_at) 
                      VALUES (:username, :password, :email, :role, :first_name, :last_name, NOW())";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->execute();
            
            $user_id = $this->db->lastInsertId();
            
            if ($role === 'student') {
                $query = "INSERT INTO students (user_id) VALUES (:user_id)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
            } elseif ($role === 'professor') {
                $query = "INSERT INTO professors (user_id, department, university) VALUES (:user_id, 'Τμήμα Μηχανικών Η/Υ & Πληροφορικής', 'Πανεπιστήμιο Πατρών')";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
            }
            
            $this->db->commit();
            
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return 'Σφάλμα κατά την εγγραφή: ' . $e->getMessage();
        }
    }
}
?>
