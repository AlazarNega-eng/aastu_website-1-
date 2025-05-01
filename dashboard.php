<?php
session_start(); // Start the session
require_once 'db_connect.php'; // Include db connection (might not be needed here, but good practice)

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page_title = "Student Dashboard"; // For header include

// You could fetch some basic user info here if needed (e.g., check registration status)
$reg_status_msg = "Complete your registration.";
$cost_sharing_status_msg = "Submit your cost sharing form.";

// Check registration status (Example Query)
$stmt_reg = $conn->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
if ($stmt_reg) {
    $stmt_reg->bind_param("i", $user_id);
    $stmt_reg->execute();
    $result_reg = $stmt_reg->get_result();
    if ($result_reg->num_rows > 0) {
        $reg_status_msg = "View/Update Registration";
    }
    $stmt_reg->close();
}

// Check cost sharing status (Example Query)
$stmt_cost = $conn->prepare("SELECT id FROM cost_sharing_forms WHERE user_id = ?");
if ($stmt_cost) {
    $stmt_cost->bind_param("i", $user_id);
    $stmt_cost->execute();
    $result_cost = $stmt_cost->get_result();
    if ($result_cost->num_rows > 0) {
        $cost_sharing_status_msg = "View Submitted Form";
    }
    $stmt_cost->close();
}

$conn->close(); // Close connection if opened

include 'header.php'; // Include the header
?>

<section class="dashboard-section">
    <div class="dashboard-container">
        <div class="dashboard-welcome">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>This is your student portal. Access forms, view grades, and manage your information.</p>
             <?php
                // Display messages from other processes (like form submissions)
                if (isset($_SESSION['success'])) {
                    echo '<div class="message success" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="message error" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
            ?>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <i class="fas fa-user-edit"></i>
                <h3>Student Registration</h3>
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

            <!-- Add more cards as needed -->

        </div>
    </div>
</section>

<?php include 'footer.php'; // Include the footer ?>