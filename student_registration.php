<?php
session_start();
require_once 'db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$page_title = "Student Registration";

// --- Fetch existing data to pre-fill form (if user already started/submitted) ---
$profile_data = [];
$stmt_fetch = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ?");
if ($stmt_fetch) {
    $stmt_fetch->bind_param("i", $user_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    if ($result->num_rows > 0) {
        $profile_data = $result->fetch_assoc();
    }
    $stmt_fetch->close();
}
$conn->close();

// Helper function to get value or default
function getValue($field, $data, $default = '') {
    return isset($data[$field]) ? htmlspecialchars($data[$field]) : $default;
}

include 'header.php';
?>

<section class="form-section">
    <div class="form-container">
        <h1>Student Registration Form</h1>

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
             // Check if data exists from failed attempt
             $form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : $profile_data;
             unset($_SESSION['form_data']); // Clear it
        ?>

        <form action="process_student_reg.php" method="POST">

            <!-- == Student Information == -->
            <h2 class="form-section-header">Student Information</h2>

            <div class="form-group">
                <label>Name</label>
                <div class="form-row two-col">
                    <div>
                        <input type="text" id="first_name" name="first_name" value="<?php echo getValue('first_name', $form_data); ?>" required>
                        <span class="sub-label">First Name</span>
                    </div>
                    <div>
                        <input type="text" id="last_name" name="last_name" value="<?php echo getValue('last_name', $form_data); ?>" required>
                        <span class="sub-label">Last Name</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                 <label>Birth Date</label>
                 <div class="form-row four-col">
                     <div>
                         <input type="number" id="birth_month" name="birth_month" placeholder="MM" min="1" max="12" value="<?php echo getValue('birth_month', $form_data); ?>" required>
                         <span class="sub-label">Month</span>
                     </div>
                     <div>
                         <input type="number" id="birth_day" name="birth_day" placeholder="DD" min="1" max="31" value="<?php echo getValue('birth_day', $form_data); ?>" required>
                         <span class="sub-label">Day</span>
                     </div>
                     <div>
                         <input type="number" id="birth_year" name="birth_year" placeholder="YYYY" min="1950" max="<?php echo date('Y') - 10; ?>" value="<?php echo getValue('birth_year', $form_data); ?>" required>
                         <span class="sub-label">Year</span>
                     </div>
                     <div>
                        <!-- Placeholder for a potential calendar icon/picker if JS added -->
                         <input type="text" style="text-align: center; font-size: 1.8rem; cursor: default; background-color: #e9ecef;" value="📅" readonly>
                     </div>
                 </div>
             </div>

             <div class="form-row two-col">
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="" disabled <?php echo empty(getValue('gender', $form_data)) ? 'selected' : ''; ?>>Please Select</option>
                        <option value="Male" <?php echo (getValue('gender', $form_data) == 'Male' ? 'selected' : ''); ?>>Male</option>
                        <option value="Female" <?php echo (getValue('gender', $form_data) == 'Female' ? 'selected' : ''); ?>>Female</option>
                        <option value="Other" <?php echo (getValue('gender', $form_data) == 'Other' ? 'selected' : ''); ?>>Other</option>
                        <option value="Prefer not to say" <?php echo (getValue('gender', $form_data) == 'Prefer not to say' ? 'selected' : ''); ?>>Prefer not to say</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ethnicity">Ethnicity</label>
                    <select id="ethnicity" name="ethnicity"> <!-- Optional -->
                        <option value="" disabled <?php echo empty(getValue('ethnicity', $form_data)) ? 'selected' : ''; ?>>Please Select</option>
                        <!-- Add Ethiopian ethnicities here -->
                        <option value="Oromo" <?php echo (getValue('ethnicity', $form_data) == 'Oromo' ? 'selected' : ''); ?>>Oromo</option>
                        <option value="Amhara" <?php echo (getValue('ethnicity', $form_data) == 'Amhara' ? 'selected' : ''); ?>>Amhara</option>
                        <option value="Somali" <?php echo (getValue('ethnicity', $form_data) == 'Somali' ? 'selected' : ''); ?>>Somali</option>
                        <option value="Tigrayan" <?php echo (getValue('ethnicity', $form_data) == 'Tigrayan' ? 'selected' : ''); ?>>Tigrayan</option>
                        <option value="Sidama" <?php echo (getValue('ethnicity', $form_data) == 'Sidama' ? 'selected' : ''); ?>>Sidama</option>
                        <option value="Gurage" <?php echo (getValue('ethnicity', $form_data) == 'Gurage' ? 'selected' : ''); ?>>Gurage</option>
                        <option value="Welayta" <?php echo (getValue('ethnicity', $form_data) == 'Welayta' ? 'selected' : ''); ?>>Welayta</option>
                        <option value="Hadiya" <?php echo (getValue('ethnicity', $form_data) == 'Hadiya' ? 'selected' : ''); ?>>Hadiya</option>
                        <option value="Afar" <?php echo (getValue('ethnicity', $form_data) == 'Afar' ? 'selected' : ''); ?>>Afar</option>
                         <option value="Other" <?php echo (getValue('ethnicity', $form_data) == 'Other' ? 'selected' : ''); ?>>Other</option>
                    </select>
                </div>
             </div>

            <div class="form-row two-col">
                 <div class="form-group">
                    <label for="email_address">Email Address</label>
                    <input type="email" id="email_address" name="email_address" placeholder="ex: myname@example.com" value="<?php echo getValue('email_address', $form_data); ?>" required>
                </div>
                 <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" placeholder="(000) 000-0000" value="<?php echo getValue('phone_number', $form_data); ?>" required>
                    <span class="sub-label">Please enter a valid phone number.</span>
                </div>
            </div>

             <div class="form-row two-col">
                <div class="form-group">
                    <label for="grade_level">Grade</label>
                    <input type="text" id="grade_level" name="grade_level" placeholder="e.g., Year 1" value="<?php echo getValue('grade_level', $form_data); ?>" required>
                </div>
                <div class="form-group">
                    <label for="semester">Semester</label>
                    <input type="text" id="semester" name="semester" placeholder="e.g., Semester 1" value="<?php echo getValue('semester', $form_data); ?>" required>
                </div>
             </div>

            <!-- == Current Residence Information == -->
            <h2 class="form-section-header">Current Residence Information</h2>

            <div class="form-group">
                <label for="res_street_address">Address</label>
                <input type="text" id="res_street_address" name="res_street_address" value="<?php echo getValue('res_street_address', $form_data); ?>" required>
                <span class="sub-label">Street Address</span>
            </div>
            <div class="form-group">
                <input type="text" id="res_street_address_2" name="res_street_address_2" value="<?php echo getValue('res_street_address_2', $form_data); ?>">
                <span class="sub-label">Street Address Line 2</span>
            </div>
            <div class="form-row two-col">
                <div>
                    <input type="text" id="res_city" name="res_city" value="<?php echo getValue('res_city', $form_data); ?>" required>
                    <span class="sub-label">City</span>
                </div>
                <div>
                    <input type="text" id="res_state_province" name="res_state_province" value="<?php echo getValue('res_state_province', $form_data); ?>" required>
                    <span class="sub-label">State / Province</span>
                </div>
            </div>
            <div class="form-group">
                <input type="text" id="res_postal_code" name="res_postal_code" value="<?php echo getValue('res_postal_code', $form_data); ?>" required>
                <span class="sub-label">Postal / Zip Code</span>
            </div>

            <div class="form-group">
                <label>Home Phone Number</label>
                <div class="form-row split-col">
                    <div>
                        <input type="tel" id="res_home_area_code" name="res_home_area_code" placeholder="Area Code" value="<?php echo getValue('res_home_area_code', $form_data); ?>">
                        <span class="sub-label">Area Code</span>
                    </div>
                    <div>
                        <input type="tel" id="res_home_phone_number" name="res_home_phone_number" placeholder="Phone Number" value="<?php echo getValue('res_home_phone_number', $form_data); ?>">
                        <span class="sub-label">Phone Number</span>
                    </div>
                </div>
            </div>


            <!-- == Parent/Guardian Residence Information == -->
            <h2 class="form-section-header">Parent/Guardian Residence Information</h2>

            <div class="checkbox-group">
                <input type="checkbox" id="parent_diff_address_check" name="parent_diff_address_check" value="1" <?php echo !empty(getValue('parent_diff_address', $form_data)) ? 'checked' : ''; ?> >
                <label for="parent_diff_address_check">Check if different from student's current address</label>
            </div>
            <!-- Hidden section for parent address -->
            <div id="parent_residence_section" style="<?php echo !empty(getValue('parent_diff_address', $form_data)) ? 'display: block;' : 'display: none;'; ?>">
                 <div class="form-group">
                    <label for="parent_street_address">Address</label>
                    <input type="text" id="parent_street_address" name="parent_street_address" value="<?php echo getValue('parent_street_address', $form_data); ?>" >
                    <span class="sub-label">Street Address</span>
                </div>
                <div class="form-group">
                    <input type="text" id="parent_street_address_2" name="parent_street_address_2" value="<?php echo getValue('parent_street_address_2', $form_data); ?>" >
                    <span class="sub-label">Street Address Line 2</span>
                </div>
                <div class="form-row two-col">
                    <div>
                        <input type="text" id="parent_city" name="parent_city" value="<?php echo getValue('parent_city', $form_data); ?>" >
                        <span class="sub-label">City</span>
                    </div>
                    <div>
                        <input type="text" id="parent_state_province" name="parent_state_province" value="<?php echo getValue('parent_state_province', $form_data); ?>" >
                        <span class="sub-label">State / Province</span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="text" id="parent_postal_code" name="parent_postal_code" value="<?php echo getValue('parent_postal_code', $form_data); ?>" >
                    <span class="sub-label">Postal / Zip Code</span>
                </div>
            </div>

            <!-- == Submission == -->
            <div class="submit-button-container">
                 <button type="submit" class="cta">Submit Registration</button>
            </div>

        </form>
    </div>
</section>

<?php include 'footer.php'; ?>