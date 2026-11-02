<?php
declare(strict_types=1);
session_start();

function db(): PDO {
    static $connection = null;
    if ($connection === null) {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'file_manager';
        $user = getenv('DB_USER') ?: 'appuser';
        $pass = getenv('DB_PASSWORD') ?: '';
        $connection = new PDO(
            "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $connection;
}
function storageDir(): string { return rtrim(getenv('STORAGE_DIR') ?: __DIR__ . '/uploads', '/'); }
function escape(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function checkCsrf(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403); exit('Invalid form token. Refresh and try again.');
    }
}
function flash(string $message, string $type = 'ok'): void {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
    header('Location: index.php', true, 303); exit;
}
function imageUpload(array $file): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('Please choose an image to upload.');
    if (($file['size'] ?? 0) > 5 * 1024 * 1024 || ($file['size'] ?? 0) <= 0) throw new RuntimeException('Images must be smaller than 5 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) throw new RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $dir = storageDir();
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) throw new RuntimeException('Could not create the storage folder.');
    if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) throw new RuntimeException('Could not save the uploaded image.');
    return ['stored_name' => $name, 'original_name' => basename((string)$file['name']), 'mime_type' => $mime, 'file_size' => (int)$file['size']];
}
