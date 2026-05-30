<?php
// ============================================================
//  config.php — Konfigurasi Database & Helper Global MiniFut
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'minifut_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_DIR', __DIR__ . '/admin/uploads/');
define('UPLOAD_URL', 'admin/uploads/');

// ── PDO Connection ──────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Koneksi database gagal. Sistem sedang dalam pemeliharaan.");
        }
    }
    return $pdo;
}

// ── Session Helper ─────────────────────────────────────────
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 7200,
            'path'     => '/',
            'secure'   => false, // set true jika pakai HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// ── Auth Helpers ────────────────────────────────────────────
function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function isAdmin(): bool {
    startSecureSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isPelanggan(): bool {
    startSecureSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'pelanggan';
}

function requireLogin(string $redirectTo = '/auth/login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}

function requireAdmin(): void {
    startSecureSession();
    if (!isAdmin()) {
        header("Location: /admin/login.php");
        exit;
    }
}

// ── Upload Helper ────────────────────────────────────────────
function uploadFoto(array $file, string $prefix = 'foto'): string|false {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if (!in_array($file['type'], $allowedTypes)) return false;
    if ($file['size'] > $maxSize) return false;

    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = $prefix . '_' . uniqid() . '.' . strtolower($ext);
    $dest = UPLOAD_DIR . $name;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $name;
    }
    return false;
}

// ── Sanitize ────────────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function formatRupiah(int $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>