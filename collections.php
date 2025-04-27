<?php
require_once 'config/database.php';

// Get all categories
try {
    $categories = $pdo->query("SELECT * FROM categories WHERE status = 1 ORDER BY sort_order, name")->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Get selected category (default to first category if none selected)
$selected_category_id = isset($_GET['category']) ? (int)$_GET['category'] : ($categories[0]['id'] ?? null);

// Get products for selected category
try {
    if ($selected_category_id) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND status = 1 ORDER BY sort_order, name");
        $stmt->execute([$selected_category_id]);
        $products = $stmt->fetchAll();
    } else {
        $products = [];
    }
} catch (PDOException $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="zxx">
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content="RPK Textiles" />
    <meta name="keywords" content="RPK Textiles, unica, creative, html" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>RPK Textiles - Collections</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
    <link rel="apple-touch-icon" href="img/favicon.ico" />

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet" />

    <!-- Css Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="css/elegant-icons.css" type="text/css" />
    <link rel="stylesheet" href="css/magnific-popup.css" type="text/css" />
    <link rel="stylesheet" href="css/nice-select.css" type="text/css" />
    <link rel="stylesheet" href="css/owl.carousel.min.css" type="text/css" />
    <link rel="stylesheet" href="css/slicknav.min.css" type="text/css" />
    <link rel="stylesheet" href="css/style.css" type="text/css" />
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

        .shop__sidebar {
            background: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1.5rem;
        }

        .shop__sidebar__categories ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .shop__sidebar__categories li {
            margin-bottom: 0.5rem;
        }

        .shop__sidebar__categories a {
            color: var(--dark-color);
            text-decoration: none;
            padding: 0.5rem 1rem;
            display: block;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .shop__sidebar__categories a:hover,
        .shop__sidebar__categories a.active {
            background-color: var(--primary-color);
            color: white;
        }

        .product__item {
            background: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: transform 0.3s;
            overflow: hidden;
        }

        .product__item:hover {
            transform: translateY(-5px);
        }

        .product__item__pic {
            height: 250px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .product__item__text {
            padding: 1.5rem;
        }

        .product__item__text h6 {
            color: var(--dark-color);
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .product__item__text p {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .shop__product__option {
            background: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .shop__product__option__left p {
            color: var(--secondary-color);
            margin: 0;
            font-size: 0.9rem;
        }

        .breadcrumb__text h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .breadcrumb__links a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb__links span {
            color: var(--secondary-color);
        }
    </style>
  </head>

  <body>
    <!-- Page Preloder -->
    <div id="preloder">
      <div class="loader"></div>
    </div>

    <!-- Offcanvas Menu Begin -->
    <div class="offcanvas-menu-overlay"></div>
    <div class="offcanvas-menu-wrapper">
      <div class="offcanvas__option"></div>
      <div id="mobile-menu-wrap"></div>
    </div>
    <!-- Offcanvas Menu End -->

    <!-- Header Section Begin -->
    <header class="header">
      <div class="container">
        <div class="row">
          <div class="col-lg-5 col-md-5">
            <div class="header__logo">
              <a href="./index.php"><img src="img/rpk_textiles.png" alt="" /></a>
            </div>
          </div>
          <div class="col-lg-7 col-md-7">
            <nav class="header__menu mobile-menu">
              <ul>
                <li><a href="./index.php">Home</a></li>
                <li class="active"><a href="./collections.php">Collections</a></li>
                <li><a href="./contact.php">Contact Us</a></li>
              </ul>
            </nav>
          </div>
        </div>
        <div class="canvas__open"><i class="fa fa-bars"></i></div>
      </div>
    </header>
    <!-- Header Section End -->

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-option">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="breadcrumb__text">
              <h4>Collections</h4>
              <div class="breadcrumb__links">
                <a href="./index.php">Home</a>
                <span>Collections</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Shop Section Begin -->
    <section class="shop spad">
      <div class="container">
        <div class="row">
          <div class="col-lg-3">
            <div class="shop__sidebar">
              <div class="shop__sidebar__accordion">
                <div class="accordion" id="accordionExample">
                  <div class="card">
                    <div class="card-heading">
                      <h5 class="mb-0">Categories</h5>
                    </div>
                    <div id="collapseOne" class="collapse show" data-parent="#accordionExample">
                      <div class="card-body">
                        <div class="shop__sidebar__categories">
                          <ul class="nice-scroll">
                            <?php foreach ($categories as $category): ?>
                            <li>
                              <a href="?category=<?php echo $category['id']; ?>" 
                                 class="<?php echo $selected_category_id == $category['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                              </a>
                            </li>
                            <?php endforeach; ?>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-9">
            <!-- <div class="shop__product__option">
              <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                  <div class="shop__product__option__left">
                    <p>Showing <?php echo count($products); ?> products</p>
                  </div>
                </div>
              </div>
            </div> -->
            <div class="row">
              <?php if (empty($products)): ?>
                <div class="col-12">
                  <div class="alert alert-info">
                    No products found in this category.
                  </div>
                </div>
              <?php else: ?>
                <?php foreach ($products as $product): ?>
                <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                  <div class="product__item">
                    <div class="product__item__pic set-bg" 
                         data-setbg="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>">
                    </div>
                    <div class="product__item__text">
                      <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                      <p><?php echo htmlspecialchars($product['short_description']); ?></p>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Shop Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="footer__about">
              <div class="footer__logo">
                <a href="#"><img src="img/footer-logo.png" alt="" /></a>
              </div>
              <p>The customer is at the heart of our unique business model, which includes design.</p>
            </div>
          </div>
          <div class="col-lg-2 offset-lg-1 col-md-3 col-sm-6">
            <div class="footer__widget">
              <h6>Shop</h6>
              <ul>
                <li><a href="./index.php">Home</a></li>
                <li><a href="./collections.php">Collections</a></li>
                <li><a href="./contact.php">Contact Us</a></li>
              </ul>
            </div>
          </div>
          <div class="col-lg-2 col-md-3 col-sm-6">
            <div class="footer__widget">
              <h6>Contact Us</h6>
              <ul>
                <li><a href="#">47, Madhavi St,</a></li>
                <li><a href="#">Teachers Colony,</a></li>
                <li><a href="#">Erode - 638 011.</a></li>
                <li><a href="#">+91 944 329 0888</a></li>
              </ul>
            </div>
          </div>
          <div class="col-lg-3 offset-lg-1 col-md-6 col-sm-6">
            <div class="footer__widget">
              <h6>Social Media</h6>
              <div class="hero__social">
                <a href="#"><i class="fa fa-facebook"></i></a>
                <a href="#"><i class="fa fa-twitter"></i></a>
                <a href="#"><i class="fa fa-instagram"></i></a>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12 text-center">
            <div class="footer__copyright__text">
              <p>Copyright © RPK Textiles <?php echo date('Y'); ?>. All rights reserved | Created by Admire Solution.</p>
            </div>
          </div>
        </div>
      </div>
    </footer>
    <!-- Footer Section End -->

    <!-- Search Begin -->
    <div class="search-model">
      <div class="h-100 d-flex align-items-center justify-content-center">
        <div class="search-close-switch">+</div>
        <form class="search-model-form">
          <input type="text" id="search-input" placeholder="Search here....." />
        </form>
      </div>
    </div>
    <!-- Search End -->

    <!-- Js Plugins -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery.nicescroll.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.countdown.min.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/mixitup.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
  </body>
</html> 