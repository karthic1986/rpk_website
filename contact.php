<?php
require_once 'config/database.php';

session_start();

$success_message = '';
$error_message = '';

// Generate captcha numbers
if (!isset($_SESSION['captcha'])) {
    $_SESSION['captcha'] = [
        'num1' => rand(1, 10),
        'num2' => rand(1, 10)
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $captcha_answer = $_POST['captcha_answer'] ?? '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (empty($captcha_answer) || $captcha_answer != ($_SESSION['captcha']['num1'] + $_SESSION['captcha']['num2'])) {
        $error_message = 'Incorrect captcha answer. Please try again.';
        // Generate new captcha numbers
        $_SESSION['captcha'] = [
            'num1' => rand(1, 10),
            'num2' => rand(1, 10)
        ];
    } else {
        // Prepare email content
        $to = 'e.karthick@gmail.com';
        $subject = 'New Contact Form Submission - RPK Textiles';
        
        $email_content = "Name: $name\n";
        $email_content .= "Email: $email\n";
        $email_content .= "Message:\n$message\n";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Send email
        if (mail($to, $subject, $email_content, $headers)) {
            $success_message = 'Thank you for your message. We will get back to you soon!';
            // Generate new captcha numbers after successful submission
            $_SESSION['captcha'] = [
                'num1' => rand(1, 10),
                'num2' => rand(1, 10)
            ];
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
  <head>
    <meta charset="UTF-8" />
    <meta name="description" content="Male_Fashion Template" />
    <meta name="keywords" content="Male_Fashion, unica, creative, html" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>RPK Textils</title>

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
                <li><a href="./index.php">Home</a></li>
                <li><a href="./collections.php">Collections</a></li>
                <li class="active"><a href="./contact.php">Contact Us</a></li>
              </ul>
            </nav>
          </div>
        </div>
        <div class="canvas__open"><i class="fa fa-bars"></i></div>
      </div>
    </header>
    <!-- Header Section End -->

    <!-- Contact Section Begin -->
    <section class="contact spad">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 col-md-6">
            <div class="contact__text">
              <div class="section-title">
                <h2>Connect with Us</h2>
                <p>At RPK Textiles, we cherish the relationships we build with our customers. Reach out to us for inquiries, feedback, or assistance. We're not just a brand; we're a community of individuals who appreciate the beauty of well-crafted garments.</p>
                <br />
                <p>Thank you for choosing RPK Textiles. We look forward to being a part of your wardrobe, adding a touch of timeless elegance to your style.</p>
              </div>
              <ul>
                <li>
                  <h4>Address</h4>
                  <p>47, Madhavi St, Teachers Colony,<br />Erode, Tamil Nadu 638011.</p>
                </li>
              </ul>
            </div>
          </div>
          <div class="col-lg-6 col-md-6">
            <div class="contact__form">
              <?php if ($success_message): ?>
                <div class="alert alert-success">
                  <?php echo htmlspecialchars($success_message); ?>
                </div>
              <?php endif; ?>
              
              <?php if ($error_message): ?>
                <div class="alert alert-danger">
                  <?php echo htmlspecialchars($error_message); ?>
                </div>
              <?php endif; ?>
              
              <form action="contact.php" method="POST">
                <div class="row">
                  <div class="col-lg-6">
                    <input type="text" name="name" placeholder="Name" required />
                  </div>
                  <div class="col-lg-6">
                    <input type="email" name="email" placeholder="Email" required />
                  </div>
                  <div class="col-lg-12">
                    <textarea name="message" placeholder="Message" required></textarea>
                  </div>
                  <div class="col-lg-12">
                    <div class="captcha-container">
                      <label for="captcha_answer">Please solve: <?php echo $_SESSION['captcha']['num1']; ?> + <?php echo $_SESSION['captcha']['num2']; ?> = ?</label>
                      <input type="number" name="captcha_answer" id="captcha_answer" placeholder="Enter the answer" required />
                    </div>
                  </div>
                  <div class="col-lg-12">
                    <button type="submit" class="site-btn">Send Message</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Contact Section End -->

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

    <style>
    .captcha-container {
      margin-bottom: 20px;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 5px;
    }

    .captcha-container label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
    }

    .captcha-container input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    </style>
  </body>
</html> 