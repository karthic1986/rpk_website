<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'add':
                    // Handle image uploads
                    $main_image = '';
                    $image2 = '';
                    $image3 = '';

                    // Process main image (mandatory)
                    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
                        $main_image = uploadImage($_FILES['main_image']);
                        if (empty($main_image)) {
                            throw new Exception('Failed to upload main image');
                        }
                    } else {
                        throw new Exception('Main image is required');
                    }

                    // Process optional images
                    if (isset($_FILES['image2']) && $_FILES['image2']['error'] === 0) {
                        $image2 = uploadImage($_FILES['image2']);
                    }
                    if (isset($_FILES['image3']) && $_FILES['image3']['error'] === 0) {
                        $image3 = uploadImage($_FILES['image3']);
                    }

                    $stmt = $pdo->prepare("INSERT INTO products (name, description, short_description, price, category_id, main_image, image2, image3, sort_order, status, created_by) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['short_description'],
                        $_POST['price'],
                        $_POST['category_id'],
                        $main_image,
                        $image2,
                        $image3,
                        $_POST['sort_order'],
                        isset($_POST['status']) ? 1 : 0,
                        $_SESSION['admin_id']
                    ]);

                    if (!$result) {
                        throw new Exception('Failed to add product');
                    }
                    break;

                case 'edit':
                    $main_image = $_POST['existing_main_image'];
                    $image2 = $_POST['existing_image2'];
                    $image3 = $_POST['existing_image3'];

                    // Process new image uploads if provided
                    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
                        $main_image = uploadImage($_FILES['main_image']);
                    }
                    if (isset($_FILES['image2']) && $_FILES['image2']['error'] === 0) {
                        $image2 = uploadImage($_FILES['image2']);
                    }
                    if (isset($_FILES['image3']) && $_FILES['image3']['error'] === 0) {
                        $image3 = uploadImage($_FILES['image3']);
                    }

                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, short_description = ?, price = ?, 
                                         category_id = ?, main_image = ?, image2 = ?, image3 = ?, sort_order = ?, 
                                         status = ?, modified_by = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['short_description'],
                        $_POST['price'],
                        $_POST['category_id'],
                        $main_image,
                        $image2,
                        $image3,
                        $_POST['sort_order'],
                        isset($_POST['status']) ? 1 : 0,
                        $_SESSION['admin_id'],
                        $_POST['id']
                    ]);
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    break;
            }
            $_SESSION['success'] = 'Operation completed successfully';
        } catch (Exception $e) {
            error_log("Product operation error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: products.php');
        exit;
    }
}

// Function to handle image uploads
function uploadImage($file) {
    $target_dir = "../uploads/products/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if file is an actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        error_log("File is not an image: " . $file["name"]);
        return '';
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        error_log("File is too large: " . $file["name"]);
        return '';
    }
    
    // Allow certain file formats
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if(!in_array($file_extension, $allowed_types)) {
        error_log("Invalid file type: " . $file_extension);
        return '';
    }
    
    // Try to upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        error_log("File uploaded successfully: " . $new_filename);
        return $new_filename;
    } else {
        error_log("Failed to upload file: " . $file["name"]);
        return '';
    }
}

// Get all products with category names
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name
")->fetchAll();

// Get all categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get product for editing if specified
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - RPK Textiles</title>
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
        
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .stat-card {
            background-color: white;
            border-radius: 0.35rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            color: var(--secondary-color);
            margin-bottom: 0;
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
                    <a href="categories.php">Categories</a>
                    <a href="products.php" class="active">Products</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title">Manage Products</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                        <i class="fas fa-plus"></i> Add New Product
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

                <!-- Products Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Products List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Short Description</th>
                                        <th>Description</th>
                                        <th>Sort Order</th>
                                        <th>Status</th>
                                        <th>Modified At</th>
                                        <th>Modified By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <?php if ($product['main_image']): ?>
                                                <img src="../uploads/products/<?php echo $product['main_image']; ?>" 
                                                     alt="Product Image" class="product-image">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($product['short_description']); ?></td>
                                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                                        <td><?php echo htmlspecialchars($product['sort_order']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $product['status'] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $product['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($product['modified_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($product['modified_by']); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
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

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $edit_product ? 'Edit' : 'Add'; ?> Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $edit_product ? 'edit' : 'add'; ?>">
                        <?php if ($edit_product): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                            <input type="hidden" name="existing_main_image" value="<?php echo $edit_product['main_image']; ?>">
                            <input type="hidden" name="existing_image2" value="<?php echo $edit_product['image2']; ?>">
                            <input type="hidden" name="existing_image3" value="<?php echo $edit_product['image3']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <input type="text" class="form-control" id="short_description" name="short_description" 
                                           value="<?php echo $edit_product ? htmlspecialchars($edit_product['short_description']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                           value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="main_image" class="form-label">Main Image (Required)</label>
                                    <input type="file" class="form-control" id="main_image" name="main_image" 
                                           <?php echo !$edit_product ? 'required' : ''; ?>>
                                    <?php if ($edit_product && $edit_product['main_image']): ?>
                                        <img src="../uploads/products/<?php echo $edit_product['main_image']; ?>" 
                                             alt="Current Main Image" class="mt-2 product-image">
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="image2" class="form-label">Additional Image 1 (Optional)</label>
                                    <input type="file" class="form-control" id="image2" name="image2">
                                    <?php if ($edit_product && $edit_product['image2']): ?>
                                        <img src="../uploads/products/<?php echo $edit_product['image2']; ?>" 
                                             alt="Current Image 2" class="mt-2 product-image">
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <label for="image3" class="form-label">Additional Image 2 (Optional)</label>
                                    <input type="file" class="form-control" id="image3" name="image3">
                                    <?php if ($edit_product && $edit_product['image3']): ?>
                                        <img src="../uploads/products/<?php echo $edit_product['image3']; ?>" 
                                             alt="Current Image 3" class="mt-2 product-image">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; 
                            ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   value="<?php echo $edit_product ? $edit_product['sort_order'] : '0'; ?>">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="status" name="status" 
                                       <?php echo (!$edit_product || $edit_product['status']) ? 'checked' : ''; ?>>
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
    <?php if ($edit_product): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html> 