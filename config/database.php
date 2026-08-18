<?php
/**
 * Database Configuration & Connection Handler
 * Supports auto-provisioning of database and tables if MySQL is running,
 * and gracefully flags availability for JSON fallback if MySQL is offline.
 */

$host = "localhost";
$username = "root";
$password = "";
$database = "lost_found";

$conn = null;
$dbAvailable = false;

// Disable default mysqli exception mode to handle connection gracefully
mysqli_report(MYSQLI_REPORT_OFF);

try {
    // Attempt connection to MySQL server
    $tempConn = @new mysqli($host, $username, $password);

    if ($tempConn && !$tempConn->connect_errno) {
        // Create database if not exists
        $tempConn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $tempConn->select_db($database);

        $conn = $tempConn;
        $conn->set_charset("utf8mb4");
        $dbAvailable = true;

        // Auto-provision users table
        $conn->query("CREATE TABLE IF NOT EXISTS `users` (
            `id` VARCHAR(64) PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // Auto-provision items table
        $conn->query("CREATE TABLE IF NOT EXISTS `items` (
            `id` VARCHAR(64) PRIMARY KEY,
            `user_id` VARCHAR(64) NULL,
            `title` VARCHAR(200) NOT NULL,
            `category` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `location` VARCHAR(255) NOT NULL,
            `date` VARCHAR(50) NOT NULL,
            `type` ENUM('Lost', 'Found') NOT NULL DEFAULT 'Lost',
            `contact` VARCHAR(150) NOT NULL,
            `status` ENUM('Active', 'Resolved') NOT NULL DEFAULT 'Active',
            `image` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
} catch (Throwable $e) {
    $conn = null;
    $dbAvailable = false;
}