<?php
/**
 * Reset demo voter passwords for local development.
 * Run: php scripts/reset_demo_passwords.php
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';

$hash = password_hash('voter123', PASSWORD_BCRYPT);
$db   = Database::getInstance();

$updated = $db->execute(
    "UPDATE users SET password_hash = ? WHERE role = 'voter' AND username REGEXP '^voter[0-9]+$'",
    [$hash]
);

echo "Updated {$updated} demo voter account(s) (voter1–voter20) to password: voter123\n";
