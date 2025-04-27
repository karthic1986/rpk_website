<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

try {
    // Alter categories table
    $pdo->exec("ALTER TABLE categories
        ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD COLUMN created_by INT,
        ADD COLUMN modified_by INT,
        ADD FOREIGN KEY (created_by) REFERENCES users(id),
        ADD FOREIGN KEY (modified_by) REFERENCES users(id)");
    
    // Alter products table
    $pdo->exec("ALTER TABLE products
        ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        ADD COLUMN created_by INT,
        ADD COLUMN modified_by INT,
        ADD FOREIGN KEY (created_by) REFERENCES users(id),
        ADD FOREIGN KEY (modified_by) REFERENCES users(id)");
    
    $_SESSION['success'] = "Tables updated successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating tables: " . $e->getMessage();
}

header('Location: dashboard.php');
exit; 