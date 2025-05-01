<?php session_start(); // Start session at the very beginning ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>AASTU University - Courses & Programs</title>
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

  <!-------- Course Hero Section  --------------->
  <section id="course-hero" class="hero-section" style="min-height: 50vh;">
     <div class="hero-background">
      <img src="./images/background.jpg" alt="Students in a lecture hall or studying">
    </div>
    <div class="hero container">
      <div>
        <h1>Our Academic <span>Programs</span></h1>
         <p style="font-size: 1.8rem; margin-top: 1rem; color: rgba(255,255,255,0.8);">Explore Bachelor's and Postgraduate Degrees</p>
        <a href="#services" type="button" class="cta">View Program Areas</a>
      </div>
    </div>
  </section>
  <!---------------- End Course Hero Section  ------------------->

  <!-------------- Courses / Programs Section ----------------->
  <section id="services" class="section-padding">
    <div class="services container">
      <div class="service-top">
        <h2 class="section-title">Programs <span>W</span>e Offer</h2>
         <p style="text-align: center; max-width: 700px; margin: -3rem auto 5rem;">AASTU provides a range of specialized programs at both undergraduate and postgraduate levels designed to foster innovation and expertise.</p>
      </div>
      <div class="service-bottom">
        <div class="service-item">
          <div class="icon"><i class="fas fa-cogs fa-3x" style="color: var(--primary-color);"></i></div>
          <h3>Engineering Programs</h3>
          <p>(Bachelor & Postgraduate) Chemical Engineering, Civil Engineering, Electrical and Computer Engineering, Mechanical Engineering, Environmental Engineering, Mining Engineering, Software Engineering, Electromechanical Engineering, Architecture</p>
        </div>
        <div class="service-item">
          <div class="icon"><i class="fas fa-flask fa-3x" style="color: var(--primary-color);"></i></div>
          <h3>Science & Technology</h3>
          <p>(Bachelor & Postgraduate) Biotechnology, Food Science and Applied Nutrition, Geology, Industrial Chemistry, Computer Science</p>
        </div>
        <div class="service-item">
          <div class="icon"><i class="fas fa-briefcase fa-3x" style="color: var(--primary-color);"></i></div>
          <h3>Business & Management</h3>
          <p>(Bachelor & Postgraduate) Offering programs like the Bachelor of Business Administration focused on technology and industry needs.</p>
        </div>
      </div>
       <div style="text-align: center; margin-top: 4rem;">
            <a href="contact.php" class="cta cta-outline">Inquire About Admissions</a> <!-- Link to contact.php -->
       </div>
    </div>
  </section>
  <!--------------------- End Courses Section ----------------->

  <!--------------- Facilities Section ------------------->
  <section id="projects" class="section-padding" style="background-color: var(--secondary-color);">
    <div class="projects container">
      <div class="projects-header">
        <h2 class="section-title">Our <span>Facilities</span></h2>
      </div>
      <div class="all-projects">
        <div class="project-item">
          <div class="project-info">
            <h3>Digital Library</h3>
            <p>Our fully equipped digital library features over 550 internet-connected desktops, granting students access to extensive online journals, databases, and academic resources for research and study.</p>
          </div>
          <div class="project-img"><img src="./images/library.png" alt="AASTU Digital Library interior with rows of computers"></div>
        </div>
        <div class="project-item">
          <div class="project-info">
            <h3>Laboratories & Research Centers</h3>
            <p>We house advanced, specialized laboratories and dedicated centers of excellence, such as the Nuclear Reactor Technology Center, enabling hands-on learning and cutting-edge research.</p>
          </div>
          <div class="project-img"><img src="./images/labratory.jpg" alt="Students working in a modern AASTU science laboratory"></div>
        </div>
        <div class="project-item">
          <div class="project-info">
            <h3>Student Accommodation</h3>
            <p>AASTU provides well-maintained dormitory accommodations, generally housing four students per room. These facilities offer a comfortable and secure living environment, often exceeding standards at other Ethiopian universities.</p>
          </div>
          <div class="project-img"><img src="./images/dorm.jpg" alt="Clean and organized student dormitory room at AASTU"></div>
        </div>
      </div>
    </div>
  </section>
  <!---------------- End Facilities Section ------------------>

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