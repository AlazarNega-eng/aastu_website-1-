<?php session_start(); // Start session at the very beginning ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>About AASTU University</title>
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

  <!-------- About Hero Section  --------------->
  <section id="about-hero" class="hero-section">
     <div class="hero-background">
      <img src="./images/background.jpg" alt="AASTU Campus or Graduation Scene">
    </div>
    <div class="hero container">
      <div>
        <h1>About <span>AASTU</span></h1>
        <a href="#about-content" type="button" class="cta">Explore Our Story</a>
      </div>
    </div>
  </section>
  <!---------------- End About Hero Section  ------------------->

  <!---------------- About Content Section ------------------------>
  <section id="about-content" class="section-padding">
    <div class="about container">
      <div class="about-top">
        <h2 class="section-title">Our <span>University</span></h2>
        <p>Addis Ababa Science and Technology University (AASTU) is Ethiopia's premier institution for science and technology education, dedicated to excellence in teaching, research, and innovation.</p>
      </div>
      <div class="about-bottom">
        <div class="about-item">
           <i class="fas fa-history fa-2x" style="color: var(--accent-color); margin-bottom: 1rem;"></i>
          <h3>History & Mission</h3>
          <p>Established in 2011 with the vision of becoming a center of excellence in Africa. Our mission is to produce competent graduates who contribute to Ethiopia's technological advancement through quality education and research.</p>
        </div>
        <div class="about-item">
           <i class="fas fa-university fa-2x" style="color: var(--accent-color); margin-bottom: 1rem;"></i>
          <h3>Campus & Facilities</h3>
          <p>Our modern Tulu Dimtu campus features state-of-the-art labs, a digital library, comfortable accommodations, and specialized research centers, providing an ideal environment for learning and innovation.</p>
        </div>
        <div class="about-item">
           <i class="fas fa-sitemap fa-2x" style="color: var(--accent-color); margin-bottom: 1rem;"></i>
          <h3>Leadership & Governance</h3>
          <p>Governed by a board of trustees and led by President Dr. Dereje Engida. Our structure includes six colleges focusing on engineering, applied sciences, and technology management.</p>
        </div>
      </div>
    </div>
  </section>
  <!--------------------- End About Content Section ----------------->

  <!--------------- Statistics Section ------------------->
  <section id="stats" class="section-padding">
    <div class="stats container">
      <div class="stats-header">
        <h2 class="section-title">AASTU <span>By Numbers</span></h2>
      </div>
      <div class="all-stats">
        <div class="stat-item"><h1>12,000+</h1><p>Students Enrolled</p></div>
        <div class="stat-item"><h1>800+</h1><p>Faculty Members</p></div>
        <div class="stat-item"><h1>25+</h1><p>Undergraduate Programs</p></div>
        <div class="stat-item"><h1>15+</h1><p>Postgraduate Programs</p></div>
      </div>
    </div>
  </section>
  <!---------------- End Statistics Section ------------------>

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