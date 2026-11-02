<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
$error = null;
try { $files = db()->query('SELECT * FROM files ORDER BY created_at DESC, id DESC')->fetchAll(); }
catch (Throwable $e) { $files = []; $error = 'Could not connect to MySQL. Check Docker and database configuration.'; }
$notice = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mini File Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
  <header><span class="eyebrow">PHP + MySQL learning project</span><h1>Mini File Manager</h1><p>Upload images, change their titles, replace them, or delete them.</p></header>
  <?php if ($error): ?><div class="notice error"><?= escape($error) ?></div><?php endif; ?>
  <?php if ($notice): ?><div class="notice <?= $notice['type'] === 'error' ? 'error' : 'ok' ?>"><?= escape($notice['message']) ?></div><?php endif; ?>
  <section class="panel"><h2>Upload an image</h2>
    <form action="action.php" method="post" enctype="multipart/form-data" class="upload-form">
      <input type="hidden" name="csrf" value="<?= escape(csrf()) ?>"><input type="hidden" name="action" value="upload">
      <label>Image title<input name="title" placeholder="e.g. My holiday photo" maxlength="150" required></label>
      <label>Choose image<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required></label>
      <button type="submit">Upload image</button>
    </form><small>JPG, PNG, WEBP or GIF • Maximum 5 MB</small>
  </section>
  <section class="library"><div class="section-title"><h2>My images</h2><span><?= count($files) ?> item<?= count($files) === 1 ? '' : 's' ?></span></div>
  <?php if (!$files): ?><div class="empty">No images yet. Upload your first image above.</div><?php endif; ?>
  <div class="grid">
    <?php foreach ($files as $file): ?>
      <article class="card"><img src="image.php?id=<?= (int)$file['id'] ?>" alt="<?= escape($file['title']) ?>" loading="lazy">
        <div class="card-body"><div class="card-heading"><strong><?= escape($file['title']) ?></strong><small><?= escape(date('M j, Y', strtotime($file['created_at']))) ?></small></div>
          <details><summary>Edit image</summary>
            <form action="action.php" method="post" enctype="multipart/form-data" class="edit-form">
              <input type="hidden" name="csrf" value="<?= escape(csrf()) ?>"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>">
              <label>New title<input name="title" maxlength="150" value="<?= escape($file['title']) ?>" required></label>
              <label>Replace image (optional)<input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label>
              <button type="submit">Save changes</button>
            </form>
          </details>
          <form action="action.php" method="post" onsubmit="return confirm('Delete this image permanently?')">
            <input type="hidden" name="csrf" value="<?= escape(csrf()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$file['id'] ?>">
            <button class="delete" type="submit">Delete image</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div></section>
  <footer>Learning path: local MySQL → Amazon RDS | local image storage → Amazon S3</footer>
</main>
</body></html>
