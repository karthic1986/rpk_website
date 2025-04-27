<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verify categories table exists and has correct structure
try {
    // First, let's verify the database connection
    $pdo->query("SELECT 1");
    error_log("Database connection successful");
    
    // Check if table exists
    $table_check = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($table_check->rowCount() == 0) {
        // Create table if it doesn't exist
        $sql = "CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            sort_order INT DEFAULT 0,
            status BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_by INT,
            modified_by INT,
            FOREIGN KEY (created_by) REFERENCES users(id),
            FOREIGN KEY (modified_by) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        error_log("Categories table created successfully");
    } else {
        // Check if description column exists
        $column_check = $pdo->query("SHOW COLUMNS FROM categories LIKE 'description'");
        if ($column_check->rowCount() == 0) {
            // Add description column if it doesn't exist
            $pdo->exec("ALTER TABLE categories ADD COLUMN description TEXT AFTER name");
            error_log("Added description column to categories table");
        }
    }
} catch (PDOException $e) {
    error_log("Database error details: " . $e->getMessage());
    die("Database error: " . $e->getMessage() . ". Please check the error log for details.");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Print POST data
        error_log("POST data: " . print_r($_POST, true));
        
        if (isset($_POST['action'])) {
            // Validate required fields
            if (($_POST['action'] === 'add' || $_POST['action'] === 'edit') && 
                (empty($_POST['name']) || !isset($_POST['description']))) {
                throw new Exception('Name and description are required fields');
            }

            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO categories (name, description, sort_order, status, created_by) 
                                         VALUES (?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['sort_order'],
                        isset($_POST['status']) ? 1 : 0,
                        $_SESSION['admin_id']
                    ]);
                    
                    if (!$result) {
                        error_log("Insert failed. Error: " . print_r($stmt->errorInfo(), true));
                        throw new Exception('Failed to add category');
                    }
                    
                    // Check if the insert was successful
                    if ($stmt->rowCount() > 0) {
                        $_SESSION['success'] = 'Category added successfully';
                    } else {
                        error_log("No rows affected by insert");
                        throw new Exception('No rows were inserted');
                    }
                    break;
                    
                case 'edit':
                    if (empty($_POST['id'])) {
                        throw new Exception('Category ID is required for editing');
                    }
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, sort_order = ?, 
                                         status = ?, modified_by = ? WHERE id = ?");
                    if (!$stmt->execute([
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['sort_order'],
                        isset($_POST['status']) ? 1 : 0,
                        $_SESSION['admin_id'],
                        $_POST['id']
                    ])) {
                        throw new Exception('Failed to update category');
                    }
                    break;
                    
                case 'delete':
                    if (empty($_POST['id'])) {
                        throw new Exception('Category ID is required for deletion');
                    }
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    if (!$stmt->execute([$_POST['id']])) {
                        throw new Exception('Failed to delete category');
                    }
                    break;
                    
                default:
                    throw new Exception('Invalid action specified');
            }
            
            // If we get here, the operation was successful
            header('Location: categories.php');
            exit;
        }
    } catch (Exception $e) {
        // Log the error
        error_log('Category management error: ' . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: categories.php');
        exit;
    }
}

// Get all categories
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Get category for editing if specified
$edit_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_category = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - RPK Textiles</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            padding-top: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .sidebar h4 {
            color: white;
            padding: 0 1rem;
            font-weight: 800;
            font-size: 1.2rem;
        }
        
        .sidebar a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.8rem 1rem;
            display: block;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .sidebar a:hover, .sidebar a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
            font-weight: 700;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            padding: 0.5rem 1rem;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .modal-content {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .modal-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .alert {
            border: none;
            border-radius: 0.35rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h4 class="mb-4">RPK Textiles</h4>
                <nav>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="categories.php" class="active">Categories</a>
                    <a href="products.php">Products</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title">Manage Categories</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="fas fa-plus"></i> Add New Category
                    </button>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php 
                            echo $_SESSION['error'];
                            unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Categories Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Categories List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Modified At</th>
                                        <th>Modified By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                                        <td><?php echo htmlspecialchars($category['sort_order']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $category['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $category['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($category['modified_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($category['modified_by']); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $category['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $edit_category ? 'Edit' : 'Add'; ?> Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_category ? 'edit' : 'add'; ?>">
                        <?php if ($edit_category): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; 
                            ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   value="<?php echo $edit_category ? $edit_category['sort_order'] : '0'; ?>">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="status" name="status" 
                                       <?php echo (!$edit_category || $edit_category['status']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <?php if ($edit_category): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html> 