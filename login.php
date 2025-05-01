<?php session_start(); // Start the session

// If user is already logged in, redirect them away from login page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Or a dashboard page
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <title>Login - AASTU University</title>
  <!-- REMOVED INLINE STYLE BLOCK - Styles should be in style.css -->
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

  <!------------------ Login Section ---------------->
  <section class="auth-section">
    <div class="auth-container">
        <h2>Login</h2>

         <?php
            // Display messages
            if (isset($_SESSION['error'])) {
                echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
        ?>

        <form action="login_process.php" method="POST" class="auth-form">
            <input type="text" name="username_or_email" placeholder="Username or Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="auth-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
  </section>

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

  <script src="./app.js"></script>
</body>
</html>