<?php session_start(); // Start session at the very beginning ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>AASTU University Website</title>
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
            <?php $current_page = basename($_SERVER['PHP_SELF']); // Get current page name ?>
            <li><a href="index.php" class="<?php echo ($current_page == 'index.php' ? 'active' : ''); ?>">Home</a></li>
            <li><a href="blog.php" class="<?php echo ($current_page == 'blog.php' ? 'active' : ''); ?>">Blog</a></li>
            <li><a href="course.php" class="<?php echo ($current_page == 'course.php' ? 'active' : ''); ?>">Courses</a></li>
            <li><a href="about.php" class="<?php echo ($current_page == 'about.php' ? 'active' : ''); ?>">About</a></li>
            <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php' ? 'active' : ''); ?>">Contact</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Show if user IS logged in -->
                <li style="margin-left: auto; display: flex; align-items: center;"> <!-- Aligns welcome message/logout to the right potentially -->
                   <span style="color: #eee; padding: 1rem 0; font-size: 1.6rem; margin-right: 1.5rem;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                   <a href="logout.php" style="padding-left: 1rem;">Logout</a>
                 </li>
                <!-- Optional: Add link to a profile/dashboard page -->
                <!-- <li><a href="dashboard.php">Dashboard</a></li> -->
            <?php else: ?>
                <!-- Show if user is NOT logged in -->
                <li><a href="login.php" class="<?php echo ($current_page == 'login.php' ? 'active' : ''); ?>">Login</a></li>
                <li><a href="register.php" class="<?php echo ($current_page == 'register.php' ? 'active' : ''); ?>">Register</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </section>
  <!------------------ End Header ---------------->

  <!-------- Hero Section --------------->
  <section id="hero" class="hero-section">
    <div class="hero-background">
        <img src="./images/AASTU.jpeg" alt="AASTU Campus Banner">
    </div>
    <div class="hero container">
      <div>
        <h1>AASTU<br><span>University</span></h1>
        <a href="#services" type="button" class="cta">Explore Programs</a>
      </div>
    </div>
  </section>
  <!---------------- End Hero Section ------------------->

  <!---------------- Service Section (Programs) ------------------------>
  <section id="services" class="section-padding">
    <div class="services container">
      <div class="service-top">
        <h2 class="section-title">Programs <span>W</span>e Offer</h2>
        <p style="text-align: center; max-width: 700px; margin: -3rem auto 5rem;">Explore our diverse range of Bachelor's and Postgraduate degrees in Engineering, Science & Technology, and Business.</p>
      </div>
      <div class="service-bottom">
        <div class="service-item">
          <div class="icon">
              <i class="fas fa-cogs fa-3x" style="color: var(--primary-color);"></i>
            </div>
          <h3>Engineering Programs</h3>
          <p>(Bachelor & Postgraduate) Chemical, Civil, Electrical & Computer, Mechanical, Environmental, Mining, Software, Electromechanical, Architecture</p>
        </div>
        <div class="service-item">
          <div class="icon">
              <i class="fas fa-flask fa-3x" style="color: var(--primary-color);"></i>
            </div>
          <h3>Science & Tech Programs</h3>
          <p>(Bachelor & Postgraduate) Biotechnology, Food Science & Applied Nutrition, Geology, Industrial Chemistry, Computer Science</p>
        </div>
        <div class="service-item">
          <div class="icon">
              <i class="fas fa-briefcase fa-3x" style="color: var(--primary-color);"></i>
            </div>
          <h3>Business Programs</h3>
           <p>(Bachelor & Postgraduate) Focused programs including Bachelor of Business Administration and related fields.</p>
        </div>
      </div>
    </div>
  </section>
  <!--------------------- End Service Section ----------------->

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
            <p>Equipped with over 550 internet-connected computers, providing access to vast academic resources and online journals to enhance research and learning.</p>
             <a href="course.php#projects" class="cta cta-outline">Learn More</a>
          </div>
          <div class="project-img">
            <img src="./images/library.png" alt="AASTU Digital Library with students using computers">
          </div>
        </div>
        <div class="project-item">
          <div class="project-info">
            <h3>Labs & Research Centers</h3>
            <p>State-of-the-art specialized laboratories and centers like the Nuclear Reactor Technology Center offer hands-on experience and advanced research opportunities.</p>
             <a href="course.php#projects" class="cta cta-outline">Learn More</a>
          </div>
          <div class="project-img">
            <img src="./images/labratory.jpg" alt="Modern science laboratory at AASTU">
          </div>
        </div>
        <div class="project-item">
          <div class="project-info">
            <h3>Student Accommodation</h3>
            <p>Comfortable and high-quality dormitory facilities, typically housing four students per room, contribute to a conducive living and learning environment.</p>
             <a href="course.php#projects" class="cta cta-outline">Learn More</a>
          </div>
          <div class="project-img">
            <img src="./images/dorm.jpg" alt="Student dormitory room at AASTU">
          </div>
        </div>
      </div>
    </div>
  </section>
  <!---------------- End Facilities Section ------------------>

  <!----------------- Testimonials Section ------------------->
  <section class="testimonials section-padding">
      <div class="container">
            <h2 class="section-title">What Our <span>Students Say</span></h2>
            <p class="testimonials-intro">Hear directly from students about their experiences with AASTU's supportive environment, dedicated faculty, and modern facilities.</p>
            <div class="row">
                <div class="testimonial-col">
                    <img src="./images/user3.jpg" alt="Photo of Bezawit Melese">
                    <div>
                       <p>"AASTU provided exceptional opportunities to develop technical skills and participate in cutting-edge research. The hands-on learning is truly valuable."</p>
                       <h3>Mahelet Assefa</h3>
                       <div class="rating">
                         <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="far fa-star"></i>
                       </div>
                       <p class="student-info">Software Engineering, 3rd Year</p>
                    </div>
                </div>
                <div class="testimonial-col">
                    <img src="./images/user4.jpg" alt="Photo of Dawit Solomon">
                    <div>
                       <p>"Choosing AASTU was a great decision. The focus on practical skills and industry connections prepared me well for my future career in engineering."</p>
                       <h3>Alazar Nega</h3>
                       <div class="rating">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                       </div>
                       <p class="student-info">Electrical Engineering, 4th Year</p>
                    </div>
                </div>
            </div>
      </div>
  </section>
  <!------------------ End Testimonials Section ---------------------->

  <!---------------------- Contact Info Section (Index Page) ---------------------->
  <section id="contact" class="section-padding" style="background-color: var(--secondary-color);">
    <div class="contact container">
      <div>
        <h2 class="section-title">Contact <span>Info</span></h2>
      </div>
      <div class="contact-items">
        <div class="contact-item">
          <div class="icon"><i class="fas fa-phone-alt fa-3x" style="color: var(--primary-color);"></i></div>
          <div class="contact-info">
            <h3>Phone</h3>
            <h2>+1 234 123 1234</h2>
            <h2>+1 234 123 1234</h2>
          </div>
        </div>
        <div class="contact-item">
          <div class="icon"><i class="fas fa-envelope fa-3x" style="color: var(--primary-color);"></i></div>
          <div class="contact-info">
            <h3>Email</h3>
            <h2>aastu@gmail.com</h2>
            <h2>aastu01@gmail.com</h2>
          </div>
        </div>
        <div class="contact-item">
          <div class="icon"><i class="fas fa-map-marker-alt fa-3x" style="color: var(--primary-color);"></i></div>
          <div class="contact-info">
            <h3>Address</h3>
            <h2>Addis Ababa, Tulu Dimtu<br>Ethiopia</h2>
          </div>
        </div>
      </div>
       <div style="text-align: center; margin-top: 4rem;">
            <a href="contact.php" class="cta">Get Full Details</a> <!-- Link to contact.php -->
       </div>
    </div>
  </section>
  <!--------------------- End Contact Section --------------------->

  <!--------------------- Footer ------------------------->
  <footer id="footer">
    <div class="footer container">
      <div class="brand">
        <img src="./images/logo2.png" alt="AASTU University Logo">
      </div>
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