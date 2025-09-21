<?php
// Simple migration script
require_once __DIR__ . '/config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

function run_sql_file($pdo, $filepath) {
    echo "Running SQL file: " . basename($filepath) . "...\n";
    try {
        $sql = file_get_contents($filepath);
        // PDO does not support multiple queries with exec or query, so we need to split them.
        $commands = preg_split('/;(?=\s*[^;]*$)/', $sql);
        foreach ($commands as $command) {
            if (trim($command) === '') continue;
            $stmt = $pdo->prepare($command);
            $stmt->execute();
        }
        echo "SQL file " . basename($filepath) . " applied successfully.\n";
    } catch (PDOException $e) {
        echo "Error applying SQL file " . basename($filepath) . ": " . $e->getMessage() . "\n";
        // exit(1); // Exit on error
    }
}

// Run the main database schema first
$databaseSqlFile = __DIR__ . '/database.sql';
if (file_exists($databaseSqlFile)) {
    run_sql_file($pdo, $databaseSqlFile);
} else {
    echo "Main database.sql file not found. Skipping.\n";
}


$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');

if (empty($files)) {
    echo "No migration files found.\n";
} else {
    // Sort files to run them in order
    sort($files);

    foreach ($files as $file) {
        run_sql_file($pdo, $file);
    }
}

echo "All migrations completed.\n";
?>
