<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed'); }
checkCsrf();
$action = (string)($_POST['action'] ?? '');
$title = trim((string)($_POST['title'] ?? ''));
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$uploaded = null;
try {
    if (in_array($action, ['upload', 'edit'], true) && ($title === '' || mbSafeLength($title) > 150)) {
        throw new RuntimeException('Enter a title (up to 150 characters).');
    }
    if ($action === 'upload') {
        $uploaded = imageUpload($_FILES['image'] ?? []);
        $stmt = db()->prepare('INSERT INTO files (title, original_name, stored_name, mime_type, file_size) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$title, $uploaded['original_name'], $uploaded['stored_name'], $uploaded['mime_type'], $uploaded['file_size']]);
        flash('Image uploaded successfully.');
    }
    if (!$id || !in_array($action, ['edit', 'delete'], true)) throw new RuntimeException('Invalid action or image ID.');
    $stmt = db()->prepare('SELECT * FROM files WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) throw new RuntimeException('Image not found.');
    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM files WHERE id = ?');
        $stmt->execute([$id]);
        @unlink(storageDir() . '/' . $existing['stored_name']);
        flash('Image deleted.');
    }
    $replace = isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if ($replace) {
        $uploaded = imageUpload($_FILES['image']);
        $stmt = db()->prepare('UPDATE files SET title=?, original_name=?, stored_name=?, mime_type=?, file_size=? WHERE id=?');
        $stmt->execute([$title, $uploaded['original_name'], $uploaded['stored_name'], $uploaded['mime_type'], $uploaded['file_size'], $id]);
        @unlink(storageDir() . '/' . $existing['stored_name']);
    } else {
        $stmt = db()->prepare('UPDATE files SET title=? WHERE id=?');
        $stmt->execute([$title, $id]);
    }
    flash('Image updated.');
} catch (Throwable $error) {
    if ($uploaded !== null) @unlink(storageDir() . '/' . $uploaded['stored_name']);
    flash($error instanceof PDOException ? 'Database error. Check the database connection.' : $error->getMessage(), 'error');
}
function mbSafeLength(string $text): int { return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text); }
