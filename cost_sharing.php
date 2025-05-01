<?php
session_start();
// Use require_once to prevent multiple inclusions if called elsewhere
require_once 'db_connect.php'; // This establishes $conn

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    // Store the intended destination to redirect after login
    // $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Optional: Redirect back here after login
    $_SESSION['error'] = "Please log in to access the Cost Sharing Form.";
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$page_title = "Cost Sharing Form"; // Title for the header include

// --- Helper function to safely get form data ---
function getValue($field, $data, $default = '') {
    // Ensure $data is an array before accessing
    if (!is_array($data)) {
        return htmlspecialchars($default); // Return default if data is not an array
    }
    // Check if the key exists and is not null, then sanitize output
    return isset($data[$field]) && $data[$field] !== null ? htmlspecialchars($data[$field]) : htmlspecialchars($default);
}

// --- Fetch existing data (if any) ---
$cost_data = [];
$db_fetch_error = null; // Variable to store fetch error message

// Check if connection object exists and is valid before using it
if ($conn && $conn instanceof mysqli) {
    $stmt_fetch = $conn->prepare("SELECT * FROM cost_sharing_forms WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1"); // Get the latest submission
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id);
        if ($stmt_fetch->execute()) {
            $result = $stmt_fetch->get_result();
            if ($result->num_rows > 0) {
                $cost_data = $result->fetch_assoc(); // Fetch the existing data
            }
            // No error message needed if simply no data found
        } else {
            $db_fetch_error = "Error executing fetch statement: " . $stmt_fetch->error;
            error_log("User ID " . $user_id . ": " . $db_fetch_error); // Log specific error
        }
        $stmt_fetch->close(); // Close the statement *after* processing results
    } else {
        $db_fetch_error = "Error preparing fetch statement: " . $conn->error;
        error_log("User ID " . $user_id . ": " . $db_fetch_error); // Log specific error
    }
    // --- DO NOT CLOSE $conn HERE if footer needs it ---

} else {
     $db_fetch_error = "Database connection failed or is not valid when trying to fetch cost sharing data.";
     error_log($db_fetch_error); // Log critical error
     // Consider showing a user-friendly error or exiting
     // echo "<p style='color:red; text-align:center;'>Could not connect to database.</p>";
}

// Prioritize session data (from failed attempt) over fetched DB data
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : $cost_data;
unset($_SESSION['form_data']); // Clear session data after use

// --- Decide if form should be disabled ---
// Disable if DB data exists AND there's no session data (meaning not a failed attempt)
// Add more logic here if updates should be allowed under certain conditions
$form_disabled = !empty($cost_data) && !isset($_SESSION['form_data']); // Check was slightly off before
$disabled_attr = $form_disabled ? 'disabled' : '';


// --- Include Header ---
// This outputs opening HTML, head, opening body, header nav, and opening <main> tag
include 'header.php';
?>

<!-- Styles for this specific form -->
<style>
.form-section { padding: 6rem 0; background-color: #f8f9fa; }
.form-container { max-width: 750px; margin: 0 auto; background: #fff; padding: 3rem 4rem; border-radius: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
.form-container h1 { text-align: center; margin-bottom: 1rem; color: var(--primary-color); }
.form-container .sub-heading { text-align: center; font-size: 1.8rem; color: #555; margin-bottom: 3rem; }

.form-group { margin-bottom: 1.8rem; }
.form-group label { display: block; margin-bottom: 0.6rem; font-weight: 600; font-size: 1.5rem; color: #555; }
.form-group input[type="text"], .form-group input[type="tel"] {
    width: 100%; padding: 1rem 1.2rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1.5rem; font-family: var(--font-secondary); box-sizing: border-box;
}
.form-group input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(10, 35, 81, 0.1); outline: none; }
.form-row { display: grid; gap: 1.5rem; }
.form-row.two-col { grid-template-columns: 1fr 1fr; }

.form-notes { margin-top: 2.5rem; font-size: 1.4rem; color: #444; line-height: 1.6; padding: 1.5rem; background-color: var(--secondary-color); border-radius: 4px;}
.form-notes strong { color: var(--primary-color); }

.submit-button-container { text-align: center; margin-top: 3rem; }
.submit-button-container button { padding: 1.2rem 4rem; font-size: 1.6rem; }

/* Added simple CSS to show if form is disabled */
.form-disabled input, .form-disabled button {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.7;
}
.form-disabled label {
     color: #999; /* Dim label when disabled */
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .form-row.two-col { grid-template-columns: 1fr; } /* Stack columns on small screens */
    .form-container { padding: 2rem; }
}
</style>

<section class="form-section">
    <div class="form-container">
        <h1>AASTU University</h1> <!-- Consider changing to AASTU? -->
        <p class="sub-heading">Cost Sharing Office - Personal Data Form</p>

        <?php
            // Display messages (including potential DB fetch error)
             if ($db_fetch_error) { echo '<div class="message error">' . htmlspecialchars($db_fetch_error) . '</div>'; }
             if (isset($_SESSION['error'])) { echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
             if (isset($_SESSION['success']) && !$form_disabled) { echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); } // Show success only if form NOT disabled

             // Message shown when form is disabled due to previous submission
             if ($form_disabled) {
                 echo '<div class="message success">You have already submitted this form. Data is shown below. Please contact the administration office if changes are needed.</div>';
             }
        ?>

        <!-- Add class if disabled -->
        <form action="process_cost_sharing.php" method="POST" class="<?php echo $form_disabled ? 'form-disabled' : ''; ?>">
             <div class="form-row two-col">
                 <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo getValue('full_name', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
                 <div class="form-group">
                    <label for="grandfather_name">Grand Father</label>
                    <input type="text" id="grandfather_name" name="grandfather_name" value="<?php echo getValue('grandfather_name', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
             </div>

             <div class="form-row two-col">
                 <div class="form-group">
                    <label for="id_number">ID Number</label>
                    <input type="text" id="id_number" name="id_number" value="<?php echo getValue('id_number', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
                 <div class="form-group">
                    <label for="tin_number">TIN (Tax payer Identity Number)</label>
                    <input type="text" id="tin_number" name="tin_number" value="<?php echo getValue('tin_number', $form_data); ?>" <?php echo $disabled_attr; ?>> <!-- Optional field -->
                 </div>
             </div>

             <div class="form-row two-col">
                 <div class="form-group">
                    <label for="birth_year_ec">Year of Birth (E.C)</label>
                    <input type="text" id="birth_year_ec" name="birth_year_ec" value="<?php echo getValue('birth_year_ec', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
                 <div class="form-group">
                    <label for="place_of_birth">Place of Birth</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" value="<?php echo getValue('place_of_birth', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
             </div>

            <div class="form-row two-col">
                 <div class="form-group">
                    <label for="faculty">Faculty</label>
                    <input type="text" id="faculty" name="faculty" value="<?php echo getValue('faculty', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
                 <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="<?php echo getValue('department', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
            </div>

             <div class="form-row two-col">
                  <div class="form-group">
                    <label for="admission_year_ec">Year of Admission (E.C)</label>
                    <input type="text" id="admission_year_ec" name="admission_year_ec" value="<?php echo getValue('admission_year_ec', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
                 <div class="form-group">
                    <label for="mobile_phone">Mobile phone No:</label>
                    <input type="tel" id="mobile_phone" name="mobile_phone" value="<?php echo getValue('mobile_phone', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
             </div>

             <div class="form-group">
                <label for="prep_school_last_attended">Name of preparatory School Last Attended</label>
                <input type="text" id="prep_school_last_attended" name="prep_school_last_attended" value="<?php echo getValue('prep_school_last_attended', $form_data); ?>" required <?php echo $disabled_attr; ?>>
             </div>

             <div class="form-group">
                <label for="clearance_reason">Reason for Clearance</label>
                <input type="text" id="clearance_reason" name="clearance_reason" value="<?php echo getValue('clearance_reason', $form_data, 'Graduation'); ?>" required <?php echo $disabled_attr; ?>>
             </div>

             <div class="form-notes">
                <p><strong>N.B</strong><br>
                Please attach one passport size photo 3x4 cm (Name written on the back). <br>
                (Photo upload functionality is not implemented in this version. Please submit physically if required).</p>
             </div>

             <div class="submit-button-container">
                 <!-- Disable button if form is disabled -->
                 <button type="submit" class="cta" <?php echo $disabled_attr; ?>>Submit Form</button>
            </div>
        </form>

         <div style="text-align: center; margin-top: 3rem;">
            <a href="dashboard.php" class="cta cta-outline">← Back to Dashboard</a>
        </div>
    </div>
</section>

<?php
// Close DB connection only if it was successfully opened
if ($conn && $conn instanceof mysqli) {
   $conn->close();
}

include 'footer.php'; // Include the footer
?>