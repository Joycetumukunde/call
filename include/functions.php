<?php
session_start();

require_once __DIR__ . '/db.php';

// ─── Auth Helpers ────────────────────────────────────────────────

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /auth/login.php");
        exit();
    }
}

function require_guest() {
    if (is_logged_in()) {
        header("Location: /index.php");
        exit();
    }
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_current_user() {
    global $conn;
    if (!is_logged_in()) return null;
    $id = (int) $_SESSION['user_id'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $id LIMIT 1");
    return mysqli_fetch_assoc($result);
}

// ─── Input Helpers ───────────────────────────────────────────────

function sanitize($value) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($value)));
}

function post($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

// ─── Flash Messages ──────────────────────────────────────────────

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function render_flash() {
    $flash = get_flash();
    if (!$flash) return '';
    $type = $flash['type']; // 'success' | 'error' | 'info'
    $msg  = htmlspecialchars($flash['message']);
    return "<div class=\"flash flash--{$type}\">{$msg}</div>";
}

// ─── CSRF ────────────────────────────────────────────────────────

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("CSRF token mismatch.");
    }
}