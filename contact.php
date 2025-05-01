<?php session_start(); // Start session at the very beginning ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>Contact AASTU University</title>
</head>
<body>
  <!------------- Header --------------->
  <section id="header">
    <div class="header container">
      <nav class="nav-bar">
        <div class="brand">
          <a href="index.php"> <!-- Link to index.php -->
            <img src="./images/logo2.png" alt="AASTU University Logo">
          </a>
        </div>
        <div class="nav-list">
          <div class="hamburger">
            <div class="bar"></div>
          </div>
          <ul>
             <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
             <li><a href="index.php" class="<?php echo ($current_page == 'index.php' ? 'active' : ''); ?>">Home</a></li>
             <li><a href="blog.php" class="<?php echo ($current_page == 'blog.php' ? 'active' : ''); ?>">Blog</a></li>
             <li><a href="course.php" class="<?php echo ($current_page == 'course.php' ? 'active' : ''); ?>">Courses</a></li>
             <li><a href="about.php" class="<?php echo ($current_page == 'about.php' ? 'active' : ''); ?>">About</a></li>
             <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php' ? 'active' : ''); ?>">Contact</a></li>

             <?php if (isset($_SESSION['user_id'])): ?>
                <li style="margin-left: auto; display: flex; align-items: center;">
                   <span style="color: #eee; padding: 1rem 0; font-size: 1.6rem; margin-right: 1.5rem;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                   <a href="logout.php" style="padding-left: 1rem;">Logout</a>
                 </li>
             <?php else: ?>
                 <li><a href="login.php" class="<?php echo ($current_page == 'login.php' ? 'active' : ''); ?>">Login</a></li>
                 <li><a href="register.php" class="<?php echo ($current_page == 'register.php' ? 'active' : ''); ?>">Register</a></li>
             <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </section>
  <!------------------ End Header ---------------->

  <!-------- Contact Hero Section  --------------->
  <section id="contact-hero" class="hero-section">
    <div class="hero-background">
      <img src="./images/background.jpg" alt="AASTU campus building or entrance">
    </div>
    <div class="hero container">
      <div>
         <h1>Contact <span>Us</span></h1>
        <a href="#contact-content" type="button" class="cta">Get In Touch</a>
      </div>
    </div>
  </section>
  <!---------------- End Contact Hero Section  ------------------->

  <!--------------- Contact Content ---------------->
  <section id="contact-content" class="contact-section section-padding">
    <div class="container">
        <div class="location">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.9400012182355!2d38.80717847587176!3d8.885170691222042!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b82a7e392203f%3A0xb05f440eacc98f9f!2sAddis%20Ababa%20Science%20and%20Technology%20University!5e0!3m2!1sen!2set!4v1745789050082!5m2!1sen!2set" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <div class="contact-container">
            <div class="row">
                <div class="contact-col contact-info">
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="contact-details"><h5>Address</h5><p>Addis Ababa Science and Technology University (AASTU)<br>Tulu Dimtu, P.O. Box 2089<br>Addis Ababa, Ethiopia</p></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="contact-details"><h5>Phone</h5><p>+1 234 123 1234 <br> (Mon - Sat, 10 AM - 6 PM)</p></div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div class="contact-details"><h5>Email</h5><p>aastu@gmail.com <br> (Email us your queries)</p></div>
                    </div>
                </div>

                <div class="contact-col contact-form">
                   <h3>Send us a Message</h3>
                   <!-- NOTE: This form currently doesn't point to a processing script. -->
                   <!-- You would need a contact_process.php or similar if you want to handle this. -->
                    <form action="" method="POST">
                        <input type="text" name="full_name" placeholder="Enter your name" required>
                        <input type="email" name="email_address" placeholder="Enter your email address" required>
                        <input type="text" name="subject" placeholder="Enter your subject" required>
                        <textarea rows="8" name="message" placeholder="Message" required></textarea>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </section>
  <!--------------- End Contact Content ---------------->

  <!--------------------- Footer ------------------------->
  <footer id="footer">
    <div class="footer container">
        <div class="brand"><img src="./images/logo2.png" alt="AASTU University Logo"></div>
      <div class="social-icon">
        <div class="social-item"><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></div>
        <div class="social-item"><a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a></div>
        <div class="social-item"><a href="#" aria-label="Twitter / X"><i class="fab fa-x-twitter"></i></a></div>
        <div class="social-item"><a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></div>
        <div class="social-item"><a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a></div>
      </div>
      <p>Copyright © 2025 AASTU University. All rights reserved</p>
    </div>
  </footer>
  <!---------------- End Footer ------------------->

  <script src="./app.js"></script>
</body>
</html>