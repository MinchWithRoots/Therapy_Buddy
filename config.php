<?php

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'therapy_buddy');
define('ASSET_VERSION', '20251110');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): mysqli {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die('DB connection failed: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
function isLoggedIn(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}


function refreshUserFromDB(): void {
    if (!isLoggedIn()) {
        return;
    }
    
    $userId = $_SESSION['user']['id'] ?? null;
    if (!$userId) {
        return;
    }
    
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id, email, username, role FROM users WHERE id = ? LIMIT 1');
    if (!$stmt) {
        return;
    }
    
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role'] ?? 'user',
        ];
    } else {
        unset($_SESSION['user']);
    }
}

function requireLogin(): void {
    if (!isLoggedIn() || !currentUser()) {
        header('Location: /');
        exit;
    }
    refreshUserFromDB();
}

function requireAdmin(): void {
    requireLogin();
    $user = currentUser();
    if (!$user || ($user['role'] ?? 'user') !== 'admin') {
        http_response_code(403);
        echo 'Доступ запрещён';
        exit;
    }
}
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
