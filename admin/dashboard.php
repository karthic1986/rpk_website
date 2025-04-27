<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get counts for dashboard
$categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$products_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RPK Textiles</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'components/sidebar.php'; ?>
            <div class="main-content">
                <h1 class="page-title">Dashboard</h1>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <i class="fas fa-folder"></i>
                            <h3><?php echo $categories_count; ?></h3>
                            <p>Total Categories</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card">
                            <i class="fas fa-box"></i>
                            <h3><?php echo $products_count; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <h5>Quick Actions</h5>
                    <div class="d-flex">
                        <a href="categories.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Category
                        </a>
                        <a href="products.php?action=add" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html> 