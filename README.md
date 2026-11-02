# Mini File Manager (PHP + MySQL)

A simple learning project to **upload, view, edit (rename/replace), and delete images**.

## Requirements
- Docker and Docker Compose

## Start
```bash
docker compose up --build -d
```
Visit **http://localhost:8080**.

Stop:
```bash
docker compose down
```
Reset all uploaded images AND database contents (destructive):
```bash
docker compose down -v
```

## How the app works
- `src/index.php`: UI and image gallery.
- `src/action.php`: handles upload, edit and delete requests.
- `src/image.php`: serves images stored outside the web root.
- `src/config.php`: database connection, validation and shared helpers.
- `src/style.css`: UI styles.
- `db/init.sql`: database table setup.
- `docker-compose.yml`: local PHP/Apache and MySQL services.
- Docker **files** volume stores uploads; Docker **db_data** volume stores database data.

## Database structure
The `files` table stores an image's title, original name, random stored name, MIME type, size and upload timestamp. The image itself is stored on disk, NOT inside MySQL.

## Next: move to AWS
**Phase 2: RDS (MySQL)**
1. Create a private Amazon RDS MySQL database with network access limited to your app host.
2. Execute `db/init.sql` against the RDS database.
3. Set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` for the PHP app via environment variables. Use Secrets Manager for deployed credentials.
4. Keep images stored locally for now; verify the gallery still works.

**Phase 3: S3**
1. Create a private S3 bucket in the intended AWS region.
2. Give the app an IAM role with narrowly scoped permissions for object upload, read and delete; don't hard-code AWS keys.
3. Install AWS SDK for PHP via Composer.
4. Replace local `move_uploaded_file` / `readfile` / `unlink` logic with S3 PutObject / GetObject (or presigned read URLs) / DeleteObject.
5. Keep `stored_name` as the S3 object key in MySQL/RDS. Migrate existing local files separately if necessary.

## Notes
- Supports JPG/PNG/WEBP/GIF, with a 5 MB limit.
- Uses PDO prepared statements, escaped HTML, CSRF tokens, random file names and content-type validation.
- **Learning/demo app only**: no user login, authorization, malware scanning, request throttling, backups or production hardening. Do not publish it openly on the internet before adding these.
- Change the demo DB passwords before deploying anywhere; ensure your database is not internet-accessible.
# php_file_mgr
