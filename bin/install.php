#!/usr/bin/env php
<?php
// bin/install.php
// Basic installer script: checks environment and runs initial SQL migration path

echo "Bato Installer\n";

$envFile = __DIR__ . '/../.env.example';
if (!file_exists($envFile)) {
    echo "Error: .env.example not found\n";
    exit(1);
}

echo "Please copy .env.example to .env and fill values, then run this installer again.\n";

// if further automation desired, implement DB connection check and migrations

return 0;
