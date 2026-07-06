<?php

/*
| One-step installer: creates the database, loads the schema, and seeds
| demo data. Replaces `php artisan migrate --seed`.
|
| Run:  php database/install.php
*/

use App\Core\Database;

require __DIR__ . '/_bootstrap.php';

$db = config('db');

echo "SAMS installer\n--------------\n";

// 1. Create the database if it does not exist.
try {
    $dsn = "mysql:host={$db['host']};port={$db['port']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['username'], $db['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✔ Database `{$db['database']}` ready.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "ERROR: Could not create database: " . $e->getMessage() . "\n");
    exit(1);
}

// 2. Load the schema.
$schema = file_get_contents(__DIR__ . '/schema.sql');
$statements = array_filter(array_map('trim', explode(';', $schema)));
$connection = Database::connection();
foreach ($statements as $statement) {
    if ($statement === '') {
        continue;
    }
    $connection->exec($statement);
}
echo "✔ Schema loaded.\n";

// 3. Seed demo data.
require __DIR__ . '/seed.php';
sams_seed();
echo "✔ Demo data seeded.\n\n";

echo "Done! Login with:\n";
echo "  Admin:      admin@vvu.edu.gh / admin123\n";
echo "  Officer:    officer1@vvu.edu.gh / officer123\n";
echo "  Supervisor: supervisor@vvu.edu.gh / supervisor123\n";
