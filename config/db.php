<?php
/**
 * Database Configuration and PDO Connection
 * Physics Department Wall Magazine
 */

// Load environment variables from .env if present
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (getenv($key) === false) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') !== false ? getenv('DB_USER') : 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'phy_mag_db');

/**
 * Helper to attempt PDO connection with given credentials
 */
function try_create_pdo($dsn, $user, $pass, $options) {
    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        // If root with empty pass failed on localhost, try server default user
        if ($user === 'root' && empty($pass)) {
            try {
                return new PDO($dsn, 'rkmuser', 'Rkmvm#6202', $options);
            } catch (PDOException $fallbackEx) {
                // Return original exception
            }
        }
        throw $e;
    }
}

/**
 * Returns a PDO database instance.
 * Automatically attempts to create the database and tables if they do not exist.
 */
function get_db_connection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        // Try connecting directly to the target database
        $pdo = try_create_pdo($dsn . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // If database does not exist, connect without dbname and create it
        try {
            $tempPdo = try_create_pdo($dsn, DB_USER, DB_PASS, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Connect to newly created database
            $pdo = try_create_pdo($dsn . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $options);
            
            // Run automatic schema installation
            require_once __DIR__ . '/../database/seed_articles.php';
            seed_initial_database($pdo);
        } catch (PDOException $ex) {
            if (php_sapi_name() === 'cli') {
                fwrite(STDERR, "Database Connection Error: " . $ex->getMessage() . PHP_EOL);
                exit(1);
            } else {
                die("Database Connection Error: " . htmlspecialchars($ex->getMessage()) . "<br><small>Please verify database settings in .env or config/db.php.</small>");
            }
        }
    }

    return $pdo;
}

