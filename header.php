<?php
// session_start(); should be called *before* this include on each page
$current_page = basename($_SERVER['PHP_SELF']); // Get current page name for active links
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Dynamically set the title -->
  <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'AASTU University'; ?></title>
  <!-- Add any page-specific CSS links here if needed -->
</head>
<body>
  <!------------- Header --------------->
  <section id="header">
    <div class="header container">
      <nav class="nav-bar">
        <div class="brand">
          <a href="index.php">
            <img src="./images/logo2.png" alt="AASTU University Logo">
          </a>
        </div>
        <div class="nav-list">
          <div class="hamburger">
            <div class="bar"></div>
          </div>
          <ul>
             <!-- Main Nav Links -->
             <li><a href="index.php" class="<?php echo ($current_page == 'index.php' ? 'active' : ''); ?>">Home</a></li>
             <li><a href="blog.php" class="<?php echo ($current_page == 'blog.php' ? 'active' : ''); ?>">Blog</a></li>
             <li><a href="course.php" class="<?php echo ($current_page == 'course.php' ? 'active' : ''); ?>">Courses</a></li>
             <li><a href="about.php" class="<?php echo ($current_page == 'about.php' ? 'active' : ''); ?>">About</a></li>
             <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php' ? 'active' : ''); ?>">Contact</a></li>

             <!-- Dynamic Login/Logout/Dashboard Links -->
             <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Logged In User -->
                 <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php' ? 'active' : ''); ?>">Dashboard</a></li>
                 <li style="margin-left: auto; display: flex; align-items: center;">
                    <span style="color: #eee; padding: 1rem 0; font-size: 1.6rem; margin-right: 1.5rem;">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php" style="padding-left: 1rem;">Logout</a>
                 </li>
             <?php else: ?>
                 <!-- Logged Out User -->
                 <li><a href="login.php" class="<?php echo ($current_page == 'login.php' ? 'active' : ''); ?>">Login</a></li>
                 <li><a href="register.php" class="<?php echo ($current_page == 'register.php' ? 'active' : ''); ?>">Register</a></li>
             <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </section>
  <!------------------ End Header ---------------->

  <!-- Start of Main Content Area -->
  <main>