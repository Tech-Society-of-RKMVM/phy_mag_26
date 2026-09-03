<?php
/**
 * Database Configuration and PDO Connection
 * Physics Department Wall Magazine
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'phy_mag_db');

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
        $pdo = new PDO($dsn . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // If database does not exist, connect without dbname and create it
        try {
            $tempPdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Connect to newly created database
            $pdo = new PDO($dsn . ";dbname=" . DB_NAME, DB_USER, DB_PASS, $options);
            
            // Run automatic schema installation
            require_once __DIR__ . '/../database/seed_articles.php';
            seed_initial_database($pdo);
        } catch (PDOException $ex) {
            die("Database Connection Error: " . htmlspecialchars($ex->getMessage()) . "<br><small>Please ensure MySQL service in XAMPP is running.</small>");
        }
    }

    return $pdo;
}
