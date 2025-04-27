<?php
require_once 'config/database.php';

// Get 5 most recent active products
try {
    $recent_products = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 1 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching recent products: " . $e->getMessage());
    $recent_products = [];
}
?>
<!DOCTYPE html>
<html lang="zxx">
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content="R.P.K. Textiles - Shirts and Dhotis" />
    <meta name="keywords" content="R.P.K. Textiles, unica, creative, html" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>R.P.K. Textiles - Shirts and Dhotis</title>

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
                <li class="active"><a href="./index.php">Home</a></li>
                <li><a href="./collections.php">Collections</a></li>
                <li><a href="./contact.php">Contact Us</a></li>
              </ul>
            </nav>
          </div>
        </div>
        <div class="canvas__open"><i class="fa fa-bars"></i></div>
      </div>
    </header>
    <!-- Header Section End -->

    <!-- Hero Section Begin -->
    <section class="hero">
      <div class="hero__slider owl-carousel">
        <div class="hero__items set-bg" data-setbg="img/hero/hero-1.jpg">
          <div class="container">
            <div class="row">
              <div class="col-xl-5 col-lg-7 col-md-8">
                <div class="hero__text">
                  <h2>Shirts and Dhotis</h2>
                  <p>Formal Wear, Casual wear, Work wear, Home furnishing and high end technical textile garments.</p>
                  <a href="./collections.php" class="primary-btn">Products<span class="arrow_right"></span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="hero__items set-bg" data-setbg="img/hero/hero-2.jpg">
          <div class="container">
            <div class="row">
              <div class="col-xl-5 col-lg-7 col-md-8">
                <div class="hero__text">
                <h2>Shirts and Dhotis</h2>
                <p>100% Cotton, Polyester Blends, Linen Blends and other materials can be offered.</p>
                  <a href="./collections.php" class="primary-btn">Products<span class="arrow_right"></span></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Hero Section End -->

    <section class="categories spad">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <div class="categories__text">
              <h2>About RPK Textiles</h2>
              <p class="categories-text">Welcome to RPK Textiles, where craftsmanship meets tradition and style. We take pride in being a leading manufacturer of high-quality cotton shirts for Men and Kids, as well as authentic Dhotis that showcase the rich textile heritage of Tamilnadu.</p>
            </div>
          </div>
          <div class="col-lg-4 offset-lg-1">
            <div class="categories__deal__countdown">
              <img src="img/rpk_textiles.png" alt="" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Banner Section Begin -->
    <section class="banner spad">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <h2>Our Story</h2>
            <p class="banner-text">Founded with a vision to seamlessly blend comfort, elegance, and tradition, RPK Textiles has become a trusted name in the textile industry. Our journey started with a passion for creating garments that stand out not just in design but also in the superior quality that defines our brand.</p>
            <br />
            <br />
            <h2>Craftsmanship and Quality</h2>
            <p class="banner-text">At RPK Textiles, we believe that true style is timeless. Each shirt and Dhoti is crafted with meticulous attention to detail, using the finest cotton fabrics. We fuse traditional craftsmanship with modern design, ensuring our products are not just garments but statements of style and cultural richness.</p>
          </div>
          <div class="col-lg-6">
            <img src="img/banner/banner-1.png" height="700px" alt="" class="rounded-image" />
          </div>
        </div>

        <div class="row banner-row">
          <div class="col-lg-6">
            <img src="img/banner/banner-2.png" height="700px" alt="" class="rounded-image" />
          </div>
          <div class="col-lg-6">
            <h2>Our Commitment</h2>
            <p class="banner-text">Our commitment goes beyond delivering excellent products. We are dedicated to providing a seamless and enjoyable shopping experience for our customers. Whether you're a retailer looking to stock our products or an individual seeking the perfect garment, we strive to meet and exceed your expectations.</p>
            <br />
            <br />
            <h2>Wholesale and Retail</h2>
            <p class="banner-text">Catering to both wholesale and retail markets, RPK Textiles has established itself as a reliable source for quality textiles. Our wholesale options come with flexible terms, and we take pride in nurturing long-lasting relationships with our business partners.</p>
          </div>
        </div>
      </div>
    </section>
    <!-- Banner Section End -->

    <!-- Product Section Begin -->
    <section class="product spad">
      <div class="container">
        <h2 style="font-weight: 700">Recent Collections</h2>
        <br />
        <div class="row">
          <div class="col-lg-12">
            <ul class="filter__controls">
              <li class="active" data-filter="*">All Products</li>
            </ul>
          </div>
        </div>
        <div class="row product__filter">
          <?php if (empty($recent_products)): ?>
            <div class="col-12">
              <div class="alert alert-info">
                No products available at the moment.
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($recent_products as $product): ?>
              <div class="col-lg-3 col-md-6 col-sm-6 col-md-6 col-sm-6 mix">
                <div class="product__item">
                  <div class="product__item__pic set-bg" data-setbg="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>">
                    <span class="label">New</span>
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
    </section>
    <!-- Product Section End -->

    <!-- Footer Section Begin -->
    <footer class="footer">
      <div class="container">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="footer__about">
              <div class="footer__logo">
                <a href="#"><img src="img/rpk_textiles.png" alt="" /></a>
              </div>
              <p>The customer is at the heart of our unique business model, which includes design.</p>
            </div>
          </div>
          <div class="col-lg-2 offset-lg-1 col-md-3 col-sm-6">
            <div class="footer__widget">
              <h6>Shop</h6>
              <ul>
                <li><a href="./index.php">Home</a></li>
                <li><a href="./collections.php">Products</a></li>
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