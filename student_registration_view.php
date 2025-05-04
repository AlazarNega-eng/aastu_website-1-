<?php
session_start();
require_once 'db_connect.php';

// --- Authentication & Role Check (Dept Head or maybe Registrar too?) ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    // Add check for other roles like 'registrar' if they should also view this
    // if ($_SESSION['role'] !== 'registrar') { ... }
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

// --- Get Profile ID from URL ---
$profile_id = null;
if (isset($_GET['profile_id'])) {
    $profile_id = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT);
}

if (!$profile_id) {
    $_SESSION['error'] = "Invalid or missing profile ID.";
    header("Location: dept_view_students.php"); // Back to list
    exit;
}

// --- Fetch Profile Data ---
$profile_data = null;
$fetch_error = null;

if ($conn && $conn instanceof mysqli) {
    // Select data from student_profiles JOINING users to get username etc.
    $sql = "SELECT sp.*, u.username
            FROM student_profiles sp
            JOIN users u ON sp.user_id = u.id
            WHERE sp.id = ?"; // Select by profile ID
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $profile_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $profile_data = $result->fetch_assoc();
        } else {
            $fetch_error = "Student profile with ID " . htmlspecialchars($profile_id) . " not found.";
        }
        $stmt->close();
    } else {
        $fetch_error = "Database error preparing profile fetch: " . $conn->error;
        error_log("Profile View Prepare Error (ID: $profile_id): " . $conn->error);
    }
    $conn->close(); // Close connection
} else {
    $fetch_error = "Database connection error.";
    error_log("DB Connection error on student_registration_view.");
}

// --- Set Page Title (Do this *after* potentially fetching data) ---
$page_title = "View Student Registration";
if ($profile_data) {
    $page_title .= " - " . htmlspecialchars($profile_data['first_name'] . ' ' . $profile_data['last_name']);
}


include 'header.php';
?>

<section class="view-section">
    <div class="view-container">
        <h1>Student Registration Details</h1>

         <?php
            // Display messages or fetch errors
            if (isset($_SESSION['error'])) { echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
            if (isset($_SESSION['success'])) { echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
            if ($fetch_error) { echo '<div class="message error">' . htmlspecialchars($fetch_error) . '</div>'; }
        ?>

        <?php if ($profile_data): // Only display if data was found ?>
            <h2 class="view-section-header">Student Information</h2>
            <dl class="view-grid">
                <div class="view-item"><dt>Full Name</dt><dd><?php echo htmlspecialchars($profile_data['first_name'] . ' ' . $profile_data['last_name']); ?></dd></div>
                <div class="view-item"><dt>Username</dt><dd><?php echo htmlspecialchars($profile_data['username']); ?></dd></div>
                <div class="view-item"><dt>Birth Date</dt><dd><?php echo htmlspecialchars(sprintf("%02d/%02d/%d", $profile_data['birth_month'], $profile_data['birth_day'], $profile_data['birth_year'])); ?></dd></div>
                <div class="view-item"><dt>Gender</dt><dd><?php echo htmlspecialchars($profile_data['gender']); ?></dd></div>
                <div class="view-item"><dt>Ethnicity</dt><dd><?php echo htmlspecialchars($profile_data['ethnicity'] ?? 'N/A'); ?></dd></div>
                <div class="view-item"><dt>Email Address</dt><dd><?php echo htmlspecialchars($profile_data['email_address']); ?></dd></div>
                <div class="view-item"><dt>Phone Number</dt><dd><?php echo htmlspecialchars($profile_data['phone_number']); ?></dd></div>
                <div class="view-item"><dt>Grade/Year</dt><dd><?php echo htmlspecialchars($profile_data['grade_level']); ?></dd></div>
                <div class="view-item"><dt>Semester</dt><dd><?php echo htmlspecialchars($profile_data['semester']); ?></dd></div>
                <div class="view-item"><dt>Approval Status</dt><dd style="font-weight: bold; color: <?php echo ($profile_data['dept_approval_status'] == 'approved' ? 'green' : ($profile_data['dept_approval_status'] == 'rejected' ? 'red' : 'orange')); ?>;"><?php echo htmlspecialchars(ucfirst($profile_data['dept_approval_status'])); // Capitalize first letter ?></dd></div>
            </dl>

            <h2 class="view-section-header">Current Residence Information</h2>
            <dl class="view-grid">
                 <div class="view-item" style="grid-column: 1 / -1;"><dt>Address</dt><dd><?php echo htmlspecialchars($profile_data['res_street_address']); ?><?php echo !empty($profile_data['res_street_address_2']) ? '<br>' . htmlspecialchars($profile_data['res_street_address_2']) : ''; ?></dd></div>
                 <div class="view-item"><dt>City</dt><dd><?php echo htmlspecialchars($profile_data['res_city']); ?></dd></div>
                 <div class="view-item"><dt>State / Province</dt><dd><?php echo htmlspecialchars($profile_data['res_state_province']); ?></dd></div>
                 <div class="view-item"><dt>Postal / Zip Code</dt><dd><?php echo htmlspecialchars($profile_data['res_postal_code']); ?></dd></div>
                 <div class="view-item"><dt>Home Phone</dt><dd><?php echo htmlspecialchars(($profile_data['res_home_area_code'] ? '(' . $profile_data['res_home_area_code'] . ') ' : '') . $profile_data['res_home_phone_number']); ?></dd></div>
            </dl>

            <?php if (!empty($profile_data['parent_diff_address'])): // Show parent section only if different ?>
                <h2 class="view-section-header">Parent/Guardian Residence Information</h2>
                 <dl class="view-grid">
                     <div class="view-item" style="grid-column: 1 / -1;"><dt>Address</dt><dd><?php echo htmlspecialchars($profile_data['parent_street_address']); ?><?php echo !empty($profile_data['parent_street_address_2']) ? '<br>' . htmlspecialchars($profile_data['parent_street_address_2']) : ''; ?></dd></div>
                     <div class="view-item"><dt>City</dt><dd><?php echo htmlspecialchars($profile_data['parent_city']); ?></dd></div>
                     <div class="view-item"><dt>State / Province</dt><dd><?php echo htmlspecialchars($profile_data['parent_state_province']); ?></dd></div>
                     <div class="view-item"><dt>Postal / Zip Code</dt><dd><?php echo htmlspecialchars($profile_data['parent_postal_code']); ?></dd></div>
                 </dl>
            <?php endif; ?>

            <!-- Approval Buttons (only if status is pending) -->
             <?php if ($profile_data['dept_approval_status'] == 'pending'): ?>
                <div class="action-buttons">
                     <form action="process_dept_approval.php" method="POST" style="display:inline;">
                        <input type="hidden" name="profile_id" value="<?php echo $profile_data['id']; ?>">
                        <!-- Pass user ID too if needed by processing script, though profile ID should be enough -->
                        <input type="hidden" name="user_id" value="<?php echo $profile_data['user_id']; ?>">
                        <button type="submit" name="action" value="approve" class="cta">Approve Registration</button>
                        <!-- Optional: Reject Button -->
                        <button type="submit" name="action" value="reject" class="cta cta-outline" style="border-color: red; color: red;">Reject Registration</button>
                    </form>
                </div>
             <?php endif; ?>

        <?php endif; // End if profile_data exists ?>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="dept_view_students.php" class="cta cta-outline">← Back to Pending List</a>
        </div>

    </div>
</section>

<?php
// Connection closed earlier
include 'footer.php';
?>