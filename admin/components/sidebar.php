<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar-wrapper">
    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">RPK Textiles</h4>
            <button class="btn btn-link text-white d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Dashboard</span>
            </a>
            <a href="categories.php" class="<?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-folder"></i>
                <span class="menu-text">Categories</span>
            </a>
            <a href="sub_categories.php" class="<?php echo $current_page === 'sub_categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-layer-group"></i>
                <span class="menu-text">Sub-Categories</span>
            </a>
            <a href="products.php" class="<?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span class="menu-text">Products</span>
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Logout</span>
            </a>
        </nav>
    </div>
</div> 