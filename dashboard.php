<?php
// --- Session MUST be started before ANY output ---
session_start();

// --- Includes ---
require_once 'db_connect.php'; // Establishes $conn

// --- Authentication Check (Student Role) ---
if (!isset($_SESSION['user_id'])) {
    // Not logged in
    header("Location: login.php");
    exit;
}
// Optional: Redirect non-students away if this is strictly for students
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
     // Logged in, but wrong role for this dashboard
     $_SESSION['error'] = "Access denied to student dashboard.";
     // Redirect them to their correct dashboard or login
     if ($_SESSION['role'] === 'dept_head') {
         header("Location: dept_dashboard.php");
     } else {
         header("Location: login.php"); // Fallback
     }
     exit;
}

// --- Variables ---
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page_title = "Student Dashboard"; // For header include

// --- Fetch Student Specific Info (including approval status) ---
$reg_status_msg = "Complete your registration.";
$cost_sharing_status_msg = "Submit your cost sharing form.";
$approval_status_text = "Not Submitted"; // Default text
$approval_status_class = "status-pending"; // Default CSS class
$db_fetch_error = null; // Initialize error variable

// Check connection before proceeding
if ($conn && $conn instanceof mysqli) {

    // Check registration status AND approval status
    $stmt_profile = $conn->prepare("SELECT id, dept_approval_status FROM student_profiles WHERE user_id = ?");
    if ($stmt_profile) {
        $stmt_profile->bind_param("i", $user_id);
        $stmt_profile->execute();
        $result_profile = $stmt_profile->get_result();
        if ($result_profile->num_rows > 0) {
            $profile = $result_profile->fetch_assoc();
            $reg_status_msg = "View/Update Registration"; // They have a profile

            // Set approval status text and class based on DB value
            switch ($profile['dept_approval_status']) {
                 case 'approved':
                     $approval_status_text = "Approved";
                     $approval_status_class = "status-approved";
                     break;
                 case 'rejected':
                      $approval_status_text = "Rejected";
                      $approval_status_class = "status-rejected";
                      break;
                 case 'pending':
                 default:
                      $approval_status_text = "Pending Approval";
                      $approval_status_class = "status-pending";
                      break;
            }
        } else {
             // Keep defaults: Registration not submitted
             $reg_status_msg = "Complete your registration.";
             $approval_status_text = "Not Submitted";
             $approval_status_class = "status-pending";
        }
        $stmt_profile->close();
    } else {
         $db_fetch_error = "Error checking registration status."; // User-friendly
         error_log("Profile Check Error (User ID: $user_id): " . $conn->error); // Detailed log
    }

    // Check cost sharing status (only if no previous error)
    if (!$db_fetch_error) {
        $stmt_cost = $conn->prepare("SELECT id FROM cost_sharing_forms WHERE user_id = ?");
        if ($stmt_cost) {
            $stmt_cost->bind_param("i", $user_id);
            $stmt_cost->execute();
            $result_cost = $stmt_cost->get_result();
            if ($result_cost->num_rows > 0) {
                $cost_sharing_status_msg = "View Submitted Form";
            } else {
                 $cost_sharing_status_msg = "Submit your cost sharing form.";
            }
            $stmt_cost->close();
        } else {
             $db_fetch_error = "Error checking cost sharing status."; // User-friendly
             error_log("Cost Check Error (User ID: $user_id): " . $conn->error); // Detailed log
        }
    }

    // Close connection now that DB operations for this page are done
    $conn->close();

} else {
    // Connection failed in db_connect.php
    $db_fetch_error = "Database connection error. Please try again later.";
    error_log("DB Connection error on student dashboard load.");
}


// --- Include Header ---
// MUST come after all session/header logic and potential redirects
include 'header.php';
?>

<!-- Page Specific Content START -->
<section class="dashboard-section">
    <div class="dashboard-container">
        <div class="dashboard-welcome">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>This is your student portal. Access forms, view grades, and manage your information.</p>
             <?php
                // Display messages from other processes or DB errors
                if ($db_fetch_error) { echo '<div class="message error" style="margin-top: 1rem;">' . htmlspecialchars($db_fetch_error) . '</div>'; }
                if (isset($_SESSION['success'])) { echo '<div class="message success" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
                if (isset($_SESSION['error'])) { echo '<div class="message error" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
            ?>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <i class="fas fa-user-edit"></i>
                <h3>Student Registration</h3>
                <!-- Display Approval Status -->
                <span class="status <?php echo $approval_status_class; ?>"><?php echo $approval_status_text; ?></span>
                <p><?php echo $reg_status_msg; ?></p>
                <a href="student_registration.php" class="cta cta-outline">Go to Form</a>
            </div>

            <div class="dashboard-card">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3>Cost Sharing</h3>
                <p><?php echo $cost_sharing_status_msg; ?></p>
                <a href="cost_sharing.php" class="cta cta-outline">Go to Form</a>
            </div>

            <div class="dashboard-card">
                <i class="fas fa-graduation-cap"></i>
                <h3>View Grades</h3>
                <p>Check your academic performance and semester results.</p>
                <a href="view_grades.php" class="cta cta-outline">View Now</a>
            </div>

             <div class="dashboard-card">
                <i class="fas fa-calendar-alt"></i>
                <h3>More Activities</h3>
                <p>Find university events, news, or other resources here.</p>
                <a href="blog.php" class="cta cta-outline">University Blog</a>
            </div>
        </div>
    </div>
</section>
<!-- Page Specific Content END -->

<?php
// --- Include Footer ---
include 'footer.php';
?>