<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$response = [
    'active' => isLoggedIn(),
    'user' => null,
    'dashboard' => BASE_URL . getDashboardPath(),
    'timestamp' => time()
];

if (isLoggedIn()) {
    $response['user'] = [
        'id' => (int)($_SESSION['user_id'] ?? 0),
        'name' => $_SESSION['name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? ''
    ];

    if (isset($_GET['keepAlive'])) {
        $_SESSION['last_keep_alive'] = time();
    }
}

echo json_encode($response);
exit;

