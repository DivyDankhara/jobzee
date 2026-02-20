<?php
/**
 * JobZee - Authentication Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ]);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user role
 */
function userRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user ID
 */
function userId(): ?int {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get current user name
 */
function userName(): ?string {
    return $_SESSION['user_name'] ?? null;
}

/**
 * Require login – redirect if not logged in
 */
function requireLogin(string $redirect = '/auth/login.php'): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to access that page.');
        redirect(ROOT_URL . $redirect);
    }
}

/**
 * Require specific role
 */
function requireRole(string $role): void {
    requireLogin();
    if (userRole() !== $role) {
        setFlash('error', 'Access denied.');
        redirect(ROOT_URL . '/index.php');
    }
}

/**
 * Login user – set session
 */
function loginUser(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email']= $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

/**
 * Logout user
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Find user by email
 */
function getUserByEmail(string $email): ?array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/**
 * Create new user
 */
function createUser(string $name, string $email, string $password, string $role): bool {
    $db   = getDB();
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hash, $role]);
}

// Determine ROOT_URL dynamically
if (!defined('ROOT_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = $_SERVER['SCRIPT_NAME'] ?? '';
    // Find depth of current script relative to project root
    $root = '';
    define('ROOT_URL', $root);
}
