<?php
// config.php - Supports both local (XAMPP) and production (InfinityFree)

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? null) == 443);

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Harden session cookies without changing the current auth flow.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/* ---------------------------
   BASE URL
----------------------------*/

// Prefer env override, else derive sensible local URL automatically.
if (!defined('BASE_URL')) {
    $baseUrlFromEnv = getenv('BASE_URL');
    if ($baseUrlFromEnv) {
        define('BASE_URL', rtrim($baseUrlFromEnv, '/') . '/');
    } else {
        $scheme = $isHttps ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $dir = dirname($scriptName);
        $path = ($dir === '/' || $dir === '\\') ? '/' : rtrim($dir, '/\\') . '/';
        define('BASE_URL', $scheme . '://' . $host . $path);
    }
}


/* ---------------------------
   DATABASE CONFIGURATION
----------------------------*/

// Detect if running on localhost
$isLocal = (($_SERVER['SERVER_NAME'] ?? '') == 'localhost' || ($_SERVER['SERVER_NAME'] ?? '') == '127.0.0.1');

if (getenv('MYSQLHOST')) {
    // Production database (Railway)
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD'));
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
} else if ($isLocal) {
    // Local XAMPP database
    // Use TCP host to avoid macOS socket path issues with `localhost`.
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'vishwacollab');
} else {
    // Production database (InfinityFree)
    define('DB_HOST', 'sql102.byetcluster.com');
    define('DB_USER', 'if0_41372253');
    define('DB_PASS', 'Shivani@250605'); // replace this
    define('DB_NAME', 'if0_41372253_vishwacollab');
}


/* ---------------------------
   DATABASE CONNECTION
----------------------------*/

$port = defined('DB_PORT') ? (int)DB_PORT : 3306;
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}


/* ---------------------------
   API KEYS (optional)
----------------------------*/

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('ADZUNA_APP_ID', getenv('ADZUNA_APP_ID') ?: '');
define('ADZUNA_APP_KEY', getenv('ADZUNA_APP_KEY') ?: '');


/* ---------------------------
   AUTHENTICATION FUNCTIONS
----------------------------*/

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


// Redirect to login if not authenticated
function requireAuth(array $roles = []): void {

    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }

    if ($roles && !in_array(getUserRole(), $roles, true)) {
        redirectToDashboard();
    }
}


// Get user role
function getUserRole() {
    return $_SESSION['role'] ?? null;
}


// Dashboard path by role
function getDashboardPath(?string $role = null): string {

    $role = $role ?? getUserRole();

    $map = [
        'student' => 'student-dashboard.php',
        'company' => 'company-dashboard.php',
        'tpo' => 'tpo-dashboard.php'
    ];

    if (!$role || !isset($map[$role])) {
        return 'index.php';
    }

    return $map[$role];
}


// Redirect user to dashboard
function redirectToDashboard(): void {

    if (!isLoggedIn()) {
        return;
    }

    header("Location: " . BASE_URL . getDashboardPath());
    exit();
}

/**
 * Generate and store a CSRF token for form submissions.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from POST requests.
 */
function verifyCsrfToken(?string $token): bool {
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Escape output for HTML contexts.
 */
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Record activity logs when the optional table exists.
 */
function logActivity(mysqli $conn, int $user_id, string $role, string $action, ?string $entity_type = null, ?int $entity_id = null, ?string $details = null): void {
    $check = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if (!$check || $check->num_rows === 0) {
        return;
    }
    $stmt = $conn->prepare(
        "INSERT INTO activity_logs (user_id, role, action, entity_type, entity_id, details)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("isssis", $user_id, $role, $action, $entity_type, $entity_id, $details);
    $stmt->execute();
    $stmt->close();
}

/**
 * Store a company notification when the optional table exists.
 */
function createCompanyNotification(mysqli $conn, int $company_user_id, string $title, string $message, string $type = 'info'): void {
    $check = $conn->query("SHOW TABLES LIKE 'company_notifications'");
    if (!$check || $check->num_rows === 0) {
        return;
    }
    $stmt = $conn->prepare(
        "INSERT INTO company_notifications (company_user_id, title, message, type)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $company_user_id, $title, $message, $type);
    $stmt->execute();
    $stmt->close();
}
?>