<?php session_start(); // Start session at the very beginning ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>Blog - AASTU University</title>
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

  <!-------- Blog Hero Section  --------------->
  <section id="blog-hero" class="hero-section" style="min-height: 50vh;">
     <div class="hero-background">
      <img src="./images/background.jpg" alt="Abstract technology or campus background">
    </div>
    <div class="hero container">
      <div>
        <h1>University <span>Blog</span></h1>
         <p style="font-size: 1.8rem; margin-top: 1rem; color: rgba(255,255,255,0.8);">News, Insights, and Program Updates</p>
      </div>
    </div>
  </section>
  <!---------------- End Blog Hero Section  ------------------->

  <!-------------- Blog Page Content ---------------->
  <section class="blog-content section-padding">
    <div class="container">
        <div class="row">
          <div class="blog-left">
            <img src="./images/certificate.jpg" alt="Sample AASTU Certificate">
            <h2>Our Certificate & Programs for 2025</h2>
            <p>At Addis Ababa Science and Technology University (AASTU), we believe in the power of innovation to transform societies and economies. As Ethiopia's premier institution for science and technology education, AASTU is committed to fostering a vibrant learning environment that encourages critical thinking, creativity, and the pursuit of knowledge.</p>
            <p>Our university is more than just a place of learning; it's a hub of innovation where students, faculty, and researchers collaborate to address the pressing challenges of our time. With state-of-the-art facilities, including advanced laboratories and a digital library, AASTU provides the resources necessary for groundbreaking research and development.</p>
            <p>We take pride in our diverse and dynamic community, which includes over 12,000 students and 800 faculty members from various backgrounds and disciplines. This diversity is a driving force behind our innovative spirit, allowing for cross-pollination of ideas and holistic solutions.</p>
            <p>AASTU's academic programs are designed to equip students with the skills needed for the global economy. Our undergraduate and postgraduate programs cover engineering, applied sciences, and technology management, preparing graduates to lead and innovate.</p>
            <p>As we look to the future, AASTU remains dedicated to excellence in teaching, research, and innovation. We are committed to producing competent graduates who contribute to Ethiopia's technological advancement and become leaders locally and globally.</p>
            <p>Join us at AASTU as we continue to push the boundaries of what's possible and shape the future through the power of innovation and education.</p>

            <div class="comment-box">
               <h3>Leave a comment</h3>
               <!-- NOTE: This form currently doesn't point to a processing script. -->
               <!-- You would need a comment_process.php or similar. -->
               <form class="comment-form" action="" method="POST">
                  <input type="text" name="commenter_name" placeholder="Enter Name" required>
                  <input type="email" name="commenter_email" placeholder="Enter Email" required>
                  <textarea rows="5" name="comment_text" placeholder="Your comment" required></textarea>
                  <button type="submit">Post Comment</button>
               </form>
            </div>
          </div>

          <div class="blog-right">
            <h3>Post Categories</h3>
            <div><span>Software Engineering</span><span>21</span></div>
            <div><span>Electrical & Computer Eng.</span><span>28</span></div>
            <div><span>Electromechanical Eng.</span><span>15</span></div>
            <div><span>Environmental Engineering</span><span>34</span></div>
            <div><span>Civil Engineering</span><span>42</span></div>
            <div><span>Mining Engineering</span><span>22</span></div>
            <div><span>Architecture</span><span>30</span></div>
          </div>
        </div>
    </div>
  </section>
   <!-------------- End Blog Page Content ---------------->

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