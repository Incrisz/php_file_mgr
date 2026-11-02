<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) { http_response_code(404); exit; }
$stmt = db()->prepare('SELECT stored_name, mime_type FROM files WHERE id = ?');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) { http_response_code(404); exit; }
$path = storageDir() . '/' . $file['stored_name'];
if (!is_file($path)) { http_response_code(404); exit; }
header('Content-Type: ' . $file['mime_type']);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=60');
header('X-Content-Type-Options: nosniff');
readfile($path);
