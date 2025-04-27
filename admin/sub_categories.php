<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get all categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if (($_POST['action'] === 'add' || $_POST['action'] === 'edit') && 
                (empty($_POST['name']) || empty($_POST['category_id']))) {
                throw new Exception('Name and parent category are required');
            }
            switch ($_POST['action']) {
                case 'add':
                    $stmt = $pdo->prepare("INSERT INTO sub_categories (category_id, name, description, sort_order, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['category_id'],
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['sort_order'],
                        isset($_POST['status']) ? 1 : 0,
                        $_SESSION['admin_id']
                    ]);
                    break;
                case 'edit':
                    $stmt = $pdo->prepare("UPDATE sub_categories SET category_id=?, name=?, description=?, sort_order=?, status=?, modified_by=? WHERE id=?");
                    $stmt->execute([
                        $_POST['category_id'],
                        $_POST['name'],
                        $_POST['description'],
                        $_POST['sort_order'],
                        isset($_POST['status']) ? 1 : 0,
                        $_SESSION['admin_id'],
                        $_POST['id']
                    ]);
                    break;
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM sub_categories WHERE id=?");
                    $stmt->execute([$_POST['id']]);
                    break;
            }
            header('Location: sub_categories.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: sub_categories.php');
        exit;
    }
}

// Get all sub-categories with parent category name
$sub_categories = $pdo->query("SELECT sc.*, c.name as category_name FROM sub_categories sc LEFT JOIN categories c ON sc.category_id = c.id ORDER BY c.name, sc.name")->fetchAll();

// Get sub-category for editing if specified
$edit_sub_category = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM sub_categories WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_sub_category = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sub-Categories - RPK Textiles</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'components/sidebar.php'; ?>
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title">Manage Sub-Categories</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subCategoryModal">
                        <i class="fas fa-plus"></i> Add New Sub-Category
                    </button>
                </div>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sub-Categories List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category</th>
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
                                    <?php foreach ($sub_categories as $sub): ?>
                                    <tr>
                                        <td><?php echo $sub['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sub['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sub['name']); ?></td>
                                        <td><?php echo htmlspecialchars($sub['description']); ?></td>
                                        <td><?php echo htmlspecialchars($sub['sort_order']); ?></td>
                                        <td><span class="badge <?php echo $sub['status'] ? 'bg-success' : 'bg-danger'; ?>"><?php echo $sub['status'] ? 'Active' : 'Inactive'; ?></span></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($sub['modified_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($sub['modified_by']); ?></td>
                                        <td>
                                            <a href="?edit=<?php echo $sub['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this sub-category?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Modal -->
                <div class="modal fade" id="subCategoryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo $edit_sub_category ? 'Edit' : 'Add'; ?> Sub-Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="<?php echo $edit_sub_category ? 'edit' : 'add'; ?>">
                                    <?php if ($edit_sub_category): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_sub_category['id']; ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Parent Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_sub_category && $edit_sub_category['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Sub-Category Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $edit_sub_category ? htmlspecialchars($edit_sub_category['name']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="2"><?php echo $edit_sub_category ? htmlspecialchars($edit_sub_category['description']) : ''; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="sort_order" class="form-label">Sort Order</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?php echo $edit_sub_category ? $edit_sub_category['sort_order'] : '0'; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="status" name="status" <?php echo (!$edit_sub_category || $edit_sub_category['status']) ? 'checked' : ''; ?>>
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
                <?php if ($edit_sub_category): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var modal = new bootstrap.Modal(document.getElementById('subCategoryModal'));
                        modal.show();
                    });
                </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html> 