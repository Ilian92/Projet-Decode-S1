<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$databaseUrl = $_SERVER['DATABASE_URL'] ?? getenv('DATABASE_URL') ?? null;
// For SQLite tests, recreate schema from scratch; for other drivers (e.g. PostgreSQL),
// schema is managed separately (migrations already run in CI), so skip this step.
if ($databaseUrl && str_starts_with($databaseUrl, 'sqlite')) {
    $dbPath = dirname(__DIR__) . '/var/data_test.db';
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }

    $console = dirname(__DIR__) . '/bin/console';

    (new Process(['php', $console, 'doctrine:schema:create', '--env=test', '--no-interaction']))
        ->mustRun();
}

