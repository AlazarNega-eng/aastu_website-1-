</main>
  <!-- End of Main Content Area (Started in header.php) -->

  <!--------------------- Footer ------------------------->
  <footer id="footer">
    <div class="footer container">
        <div class="brand">
            <!-- Ensure the image path is correct relative to your PHP files -->
            <img src="./images/logo2.png" alt="AASTU University Logo">
        </div>
      <div class="social-icon">
        <div class="social-item">
            <a href="#" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f"></i></a>
        </div>
        <div class="social-item">
            <a href="#" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
        <div class="social-item">
            <a href="#" aria-label="Twitter / X" title="Twitter / X"><i class="fab fa-x-twitter"></i></a>
        </div>
        <div class="social-item">
            <a href="#" aria-label="LinkedIn" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
        <div class="social-item">
            <a href="#" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <p>Copyright © <?php echo date("Y"); ?> AASTU University. All rights reserved</p>
    </div>
  </footer>
  <!---------------- End Footer ------------------->

  <!-- Main JavaScript File -->
  <!-- Ensure the path is correct relative to your PHP files -->
  <script src="./app.js"></script>

  <!-- Add any page-specific JS files or inline scripts needed AFTER the main app.js if necessary -->
  <?php
    // Example: Output page-specific JS variable if set
    // if (isset($page_specific_js_data)) {
    //     echo "<script> const pageData = " . json_encode($page_specific_js_data) . "; </script>";
    // }
  ?>

</body>
</html>
<?php
 // --- Optional: Close DB connection if it wasn't closed before including footer ---
 // Generally better to close it *before* including footer unless footer needs it.
 // if (isset($conn) && $conn instanceof mysqli && $conn->thread_id) { // Check if connection still exists and is valid
 //    $conn->close();
 //    // echo "<!-- DB Connection closed in footer -->"; // Debug comment
 // }

 // --- Optional: Flush output buffer if used ---
 // if (ob_get_level() > 0) {
 //    ob_end_flush();
 // }

 // NO closing PHP tag needed if this is the absolute end of the file.