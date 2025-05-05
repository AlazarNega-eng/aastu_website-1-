<?php
session_start();
require_once 'db_connect.php'; // Establishes $conn
require_once 'helpers.php';    // Include helpers (contains getValue)

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to access the Cost Sharing Form.";
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$page_title = "Cost Sharing Agreement Form";

// --- Fetch existing data ---
$cost_data = [];
$db_fetch_error = null;
$conn_fetch = null; // Use a separate variable for this connection instance

// Establish connection ONLY for fetching (will be closed after)
if ($conn && $conn instanceof mysqli) { // Check if initial $conn from db_connect is valid
    $conn_fetch = $conn; // Use the existing connection if valid
} else {
    // Attempt to reconnect if initial $conn failed (maybe db_connect had an issue?)
    try {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Ensure errors throw exceptions
        $conn_fetch = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($conn_fetch, "utf8mb4");
    } catch (mysqli_sql_exception $e) {
         $db_fetch_error = "Database connection failed: " . $e->getMessage();
         error_log($db_fetch_error);
         $conn_fetch = null; // Ensure connection is null on failure
    }
     mysqli_report(MYSQLI_REPORT_OFF); // Turn off strict reporting after connection attempt
}

// Proceed with fetching only if connection is valid
if ($conn_fetch && $conn_fetch instanceof mysqli) {
    $stmt_fetch = $conn_fetch->prepare("SELECT * FROM cost_sharing_forms WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id);
        if ($stmt_fetch->execute()) {
            $result = $stmt_fetch->get_result();
            if ($result->num_rows > 0) {
                $cost_data = $result->fetch_assoc();
            }
        } else {
            $db_fetch_error="DB error fetching data: ".$stmt_fetch->error; error_log($db_fetch_error);
        }
        $stmt_fetch->close();
    } else {
        $db_fetch_error="DB prepare error fetching data: ".$conn_fetch->error; error_log($db_fetch_error);
    }
    $conn_fetch->close(); // Close the connection used for fetching
} // else $db_fetch_error already set if connection failed


// Prioritize session data (from failed attempt) over fetched DB data
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : $cost_data;
unset($_SESSION['form_data']); // Clear session data after use

// --- Prepare checkbox arrays correctly for checking ---
//$demand_kind_str = $form_data['demand_service_kind'] ?? ''; // Get string or empty
//$demand_kind_array = !empty($demand_kind_str) ? explode(',', $demand_kind_str) : [];

//$demand_cash_str = $form_data['demand_service_cash'] ?? ''; // Get string or empty
//$demand_cash_array = !empty($demand_cash_str) ? explode(',', $demand_cash_str) : [];

// --- Prepare checkbox arrays correctly for checking ---

// --- Demand Service Kind ---
$demand_kind_value = $form_data['demand_service_kind'] ?? null; // Get value from form_data (could be array or string or null)
$demand_kind_array = []; // Initialize as empty array
if (is_array($demand_kind_value)) {
    // If it's already an array (from failed POST saved in session), use it directly (sanitize!)
    $demand_kind_array = array_map('htmlspecialchars', $demand_kind_value);
    // Optional Debug log: error_log("DEBUG: demand_service_kind was an array in session.");
} elseif (is_string($demand_kind_value) && $demand_kind_value !== '') {
    // If it's a string (from DB), explode it
    $demand_kind_array = explode(',', $demand_kind_value);
    // Optional Debug log: error_log("DEBUG: demand_service_kind was a string, exploded: " . print_r($demand_kind_array, true));
}
// else: it remains an empty array if null or empty string


// --- Demand Service Cash ---
$demand_cash_value = $form_data['demand_service_cash'] ?? null; // Get value
$demand_cash_array = []; // Initialize
if (is_array($demand_cash_value)) {
    // If it's already an array, use it directly (sanitize!)
    $demand_cash_array = array_map('htmlspecialchars', $demand_cash_value);
    // Optional Debug log: error_log("DEBUG: demand_service_cash was an array in session.");
} elseif (is_string($demand_cash_value) && $demand_cash_value !== '') {
    // If it's a string, explode it
    $demand_cash_array = explode(',', $demand_cash_value);
    // Optional Debug log: error_log("DEBUG: demand_service_cash was a string, exploded: " . print_r($demand_cash_array, true));
}
// else: remains empty array

// Decide if form should be disabled
$form_disabled = !empty($cost_data) && !isset($_SESSION['form_data']); // Disable only if DB data exists AND no session form data
$disabled_attr = $form_disabled ? 'disabled' : '';

include 'header.php'; // Include header AFTER data fetching and processing
?>

<section class="form-section">
    <div class="form-container">
        <!-- Header Section -->
        <div class="form-header">
            <div class="logo-and-title">
                <img src="./images/logo2.png" alt="AASTU Logo" class="form-logo">
                <h1>አዲስ አበባ ሳይንስና ቴክኖሎጂ ዩኒቨርሲቲ</h1>
                <h2>ADDIS ABABA SCIENCE AND TECHNOLOGY UNIVERSITY</h2>
                <h3>COST-SHARING BENEFICIARIES AGREEMENT FORM</h3>
            </div>
            <div class="doc-info">
                <span>Document No.: VPAA/REG/OF/013</span>
                <span>Issue No.: 1</span>
                <span>Page No.: 1 of 5</span>
            </div>
        </div>
        <!-- Issue History -->
        <table class="issue-history-table">
             <thead><tr><th>Issue No:</th><th>Description of Change</th><th>Status</th><th>Originator</th><th>Effective Date</th></tr></thead>
             <tbody><tr><td>1</td><td>Initial release</td><td>Draft stage</td><td>Task force</td><td>March 1, 2022</td></tr></tbody>
        </table>
        <h2 class="form-section-title">FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA<br><span class="amharic">የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፑብሊክ ትምህርት ሚኒስቴር</span></h2>
        <h3 style="text-align: center; font-size: 1.5rem; margin-bottom: 1rem;">HIGHER EDUCATION COST SHARING REGULATION<br><span class="amharic">የከፍተኛ ትምህርት የወጪ መጋራት ሥርዓት</span></h3>
        <h3 style="text-align: center; font-size: 1.5rem; margin-bottom: 2rem;">BENEFICIARIES AGREEMENT FORM<br><span class="amharic">የተጠቃሚ የውል ቅፅ</span></h3>

        <?php
            // Display messages
             if ($db_fetch_error) { echo '<div class="message error">' . htmlspecialchars($db_fetch_error) . '</div>'; }
             if (isset($_SESSION['error'])) { echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
             if (isset($_SESSION['success']) && !$form_disabled) { echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
             if ($form_disabled) { echo '<div class="message success">You have already submitted this form. Data is shown below. Please contact the administration office if changes are needed.</div>'; }
        ?>

        <!-- Add class if disabled -->
        <form action="process_cost_sharing.php" method="POST" enctype="multipart/form-data" class="<?php echo $form_disabled ? 'form-disabled' : ''; ?>">
            <input type="hidden" name="user_id_hidden" value="<?php echo htmlspecialchars($user_id); ?>">

            <!-- == PAGE 1 CONTENT == -->
             <div class="form-row">
                <div class="form-group" style="flex-basis: calc(65% - 0.75rem);">
                    <label for="full_name">1. Full Name (including grand father's name) <span class="amharic">ሙሉ ስም/እስከ አያት</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo getValue('full_name', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                </div>
                 <div class="form-group" style="flex-basis: calc(35% - 0.75rem);">
                    <label for="identity_no">Identity No. <span class="amharic">የመታወቂያ ቁጥር</span></label>
                    <input type="text" id="identity_no" name="identity_no" value="<?php echo getValue('identity_no', $form_data); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
             </div>
              <div class="form-row">
                 <div class="form-group fixed-width" style="min-width: 200px;">
                    <label>2. Sex: <span class="amharic">ፆታ</span></label>
                    <div class="radio-group" style="display: flex;">
                        <label for="sex_male"><input type="radio" id="sex_male" name="sex" value="Male" <?php echo (getValue('sex', $form_data) == 'Male') ? 'checked' : ''; ?> required <?php echo $disabled_attr; ?>> Male <span class="amharic">ወንድ</span></label>
                        <label for="sex_female"><input type="radio" id="sex_female" name="sex" value="Female" <?php echo (getValue('sex', $form_data) == 'Female') ? 'checked' : ''; ?> required <?php echo $disabled_attr; ?>> Female <span class="amharic">ሴት</span></label>
                    </div>
                 </div>
                  <div class="form-group">
                    <label for="nationality">Nationality <span class="amharic">ዜግነት</span></label>
                    <input type="text" id="nationality" name="nationality" value="<?php echo getValue('nationality', $form_data, 'Ethiopian'); ?>" required <?php echo $disabled_attr; ?>>
                 </div>
             </div>
              <div class="form-group">
                 <label for="birth_date_str">3. Date of Birth: Date / Month / Year <span class="amharic">የትውልድ ዘመን: ቀን / ወር / ዓ.ም</span></label>
                 <input type="text" id="birth_date_str" name="birth_date_str" placeholder="DD / MM / YYYY" value="<?php echo getValue('birth_date_str', $form_data); ?>" required <?php echo $disabled_attr; ?>>
             </div>
             <div class="form-group">
                <label>Place of Birth: <span class="amharic">የትውልድ ቦታ፡</span></label>
                <div class="form-row">
                    <div class="form-group third-width"><label for="birth_place_region">Region <span class="amharic">ክልል</span></label><input type="text" id="birth_place_region" name="birth_place_region" value="<?php echo getValue('birth_place_region', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group third-width"><label for="birth_place_zone">Zone <span class="amharic">ዞን</span></label><input type="text" id="birth_place_zone" name="birth_place_zone" value="<?php echo getValue('birth_place_zone', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group third-width"><label for="birth_place_wereda">Wereda <span class="amharic">ወረዳ</span></label><input type="text" id="birth_place_wereda" name="birth_place_wereda" value="<?php echo getValue('birth_place_wereda', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group third-width"><label for="birth_place_town">Town <span class="amharic">ከተማ</span></label><input type="text" id="birth_place_town" name="birth_place_town" value="<?php echo getValue('birth_place_town', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group third-width"><label for="birth_place_kebele">Kebele <span class="amharic">ቀበሌ</span></label><input type="text" id="birth_place_kebele" name="birth_place_kebele" value="<?php echo getValue('birth_place_kebele', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group third-width"><label for="birth_place_house_no">House No. <span class="amharic">የቤት ቁጥር</span></label><input type="text" id="birth_place_house_no" name="birth_place_house_no" value="<?php echo getValue('birth_place_house_no', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group half-width"><label for="birth_place_po_box">P.O.Box <span class="amharic">የፖ.ሣ.ቁ</span></label><input type="text" id="birth_place_po_box" name="birth_place_po_box" value="<?php echo getValue('birth_place_po_box', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-group half-width"><label for="birth_place_phone_no">Phone No. <span class="amharic">ስ.ቁ.</span></label><input type="tel" id="birth_place_phone_no" name="birth_place_phone_no" value="<?php echo getValue('birth_place_phone_no', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                </div>
            </div>
            <div class="form-group full-width">
                <label for="mother_adopter_full_name">4. Mother's/Adopter's- full name <span class="amharic">የወላጅ/አሳዳጊ እናት ሙሉ ስም</span></label>
                <input type="text" id="mother_adopter_full_name" name="mother_adopter_full_name" value="<?php echo getValue('mother_adopter_full_name', $form_data); ?>" required <?php echo $disabled_attr; ?>>
            </div>
             <div class="form-group">
                 <label>Mother's/Adopter's Address:</label>
                 <div class="form-row">
                     <div class="form-group quarter-width"><label for="mother_adopter_region">Region <span class="amharic">ክልል</span></label><input type="text" id="mother_adopter_region" name="mother_adopter_region" value="<?php echo getValue('mother_adopter_region', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                     <div class="form-group quarter-width"><label for="mother_adopter_zone">Zone <span class="amharic">ዞን</span></label><input type="text" id="mother_adopter_zone" name="mother_adopter_zone" value="<?php echo getValue('mother_adopter_zone', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                     <div class="form-group quarter-width"><label for="mother_adopter_wereda">Wereda <span class="amharic">ወረዳ</span></label><input type="text" id="mother_adopter_wereda" name="mother_adopter_wereda" value="<?php echo getValue('mother_adopter_wereda', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                     <div class="form-group quarter-width"><label for="mother_adopter_city_town">City/Town <span class="amharic">ከተማ</span></label><input type="text" id="mother_adopter_city_town" name="mother_adopter_city_town" value="<?php echo getValue('mother_adopter_city_town', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                 </div>
             </div>
             <div class="form-group full-width">
                 <label for="profile_photo">Attach 3x4 size photo <span class="amharic">ፎቶ</span></label>
                 <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg, image/png" <?php echo $disabled_attr; ?>>
                 <span class="sub-label">Write full name, ID No. and department at the back. (Upload JPG or PNG)</span>
                 <?php $photoFile = getValue('photo_filename', $form_data); if (!empty($photoFile)): ?>
                    <p class="sub-label" style="color:green; margin-top: 5px;">Current file: <?php echo $photoFile; ?></p>
                 <?php endif; ?>
             </div>

            <!-- == PAGE 2 CONTENT == -->
             <h2 class="form-section-title">Page 2 Information</h2>
              <div class="form-group full-width">
                <label for="prep_school_name">5. School Name (Where you completed your preparatory programmed): <span class="amharic">የመሰናዶ ትምህርት ያጠናቀቁበት ት/ቤት ስም</span></label>
                <input type="text" id="prep_school_name" name="prep_school_name" value="<?php echo getValue('prep_school_name', $form_data); ?>" required <?php echo $disabled_attr; ?>>
            </div>
             <div class="form-group">
                <label for="prep_date_completed">Date completed (Date / Month / Year): <span class="amharic">ያጠናቀቁበት ዘመን (ቀን / ወር / ዓ.ም)</span></label>
                <input type="text" id="prep_date_completed" name="prep_date_completed" placeholder="DD / MM / YYYY" value="<?php echo getValue('prep_date_completed', $form_data); ?>" required <?php echo $disabled_attr; ?>>
            </div>
             <div class="form-group full-width">
                <label>6. University/College/Institute: <span class="amharic">ዩኒቨርሲቲ/ኮሌጅ/ኢንስቲትዩት</span></label>
                <input type="text" value="Addis Ababa Science and Technology University" readonly style="background-color: #e9ecef;">
                <input type="hidden" name="university_name" value="Addis Ababa Science and Technology University">
             </div>
              <div class="form-row two-col">
                 <div class="form-group"><label for="faculty_school">Faculty/School: <span class="amharic">ፋኩልቲ/ት/ቤት</span></label><input type="text" id="faculty_school" name="faculty_school" value="<?php echo getValue('faculty_school', $form_data); ?>" required <?php echo $disabled_attr; ?>></div>
                 <div class="form-group"><label for="year_of_entrance">Year of entrance <span class="amharic">የገባበት ዓ.ም</span></label><input type="text" id="year_of_entrance" name="year_of_entrance" placeholder="YYYY E.C." value="<?php echo getValue('year_of_entrance', $form_data); ?>" required <?php echo $disabled_attr; ?>></div>
            </div>
             <div class="form-row">
                 <div class="form-group" style="flex-grow:2;"><label for="department">Department <span class="amharic">ትምህርት ክፍል</span></label><input type="text" id="department" name="department" value="<?php echo getValue('department', $form_data); ?>" required <?php echo $disabled_attr; ?>></div>
                  <div class="form-group fixed-width"><label>Year <span class="amharic">የትም ዓመት</span></label><select id="study_year_circle" name="study_year_circle" required <?php echo $disabled_attr; ?>><option value="" disabled <?php echo empty(getValue('study_year_circle', $form_data)) ? 'selected' : ''; ?>>Select</option><option value="I" <?php echo (getValue('study_year_circle', $form_data) == 'I') ? 'selected' : ''; ?>>I</option><option value="II" <?php echo (getValue('study_year_circle', $form_data) == 'II') ? 'selected' : ''; ?>>II</option><option value="III" <?php echo (getValue('study_year_circle', $form_data) == 'III') ? 'selected' : ''; ?>>III</option><option value="IV" <?php echo (getValue('study_year_circle', $form_data) == 'IV') ? 'selected' : ''; ?>>IV</option><option value="V" <?php echo (getValue('study_year_circle', $form_data) == 'V') ? 'selected' : ''; ?>>V</option><option value="VI" <?php echo (getValue('study_year_circle', $form_data) == 'VI') ? 'selected' : ''; ?>>VI</option></select></div>
            </div>
             <div class="form-group full-width checkbox-group">
                <input type="checkbox" id="withdrawn_previously_check" name="withdrawn_previously_check" value="1" <?php echo !empty(getValue('withdrawn_previously', $form_data)) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>>
                <label for="withdrawn_previously_check" style="font-weight:normal;">7. Have you withdrawn from the University previously? <span class="amharic">ከዚህ በፊት አቋርጠው ነበር?</span></label>
             </div>
             <div id="withdraw_details" style="<?php echo !empty(getValue('withdrawn_previously', $form_data)) ? 'display:block;' : 'display:none;'; ?>">
                 <div class="form-row two-col"> <!-- Changed to two-col -->
                     <div class="form-group"><label for="date_of_withdraw">Date of withdraw (Date/Month/Year) <span class="amharic">ያቋረጡበት ዘመን</span></label><input type="text" id="date_of_withdraw" name="date_of_withdraw" placeholder="DD/MM/YYYY" value="<?php echo getValue('date_of_withdraw', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                     <div class="form-group"><label for="withdraw_semester">Semester <span class="amharic">ሴሚስተር</span></label><input type="text" id="withdraw_semester" name="withdraw_semester" value="<?php echo getValue('withdraw_semester', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                 </div>
             </div>
              <div class="form-group full-width checkbox-group">
                  <input type="checkbox" id="transferred_from_other_check" name="transferred_from_other_check" value="1" <?php echo !empty(getValue('transferred_from_other', $form_data)) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>>
                  <label for="transferred_from_other_check" style="font-weight:normal;">8. Have you transferred from another university/institute? <span class="amharic">ከሌላ ዩኒቨርሲቲ ተዛውረዋል?</span></label>
              </div>
              <div id="transfer_details" style="<?php echo !empty(getValue('transferred_from_other', $form_data)) ? 'display:block;' : 'display:none;'; ?>">
                    <div class="form-group full-width"><label for="transfer_source_institute">Name of University/College/Institute <span class="amharic">የዩኒቨርሲቲ/ኮሌጅ/ኢንስቲትዩት ስም</span></label><input type="text" id="transfer_source_institute" name="transfer_source_institute" value="<?php echo getValue('transfer_source_institute', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div class="form-row two-col"><div class="form-group"><label for="transfer_college_faculty">College/Faculty <span class="amharic">ኮሌጅ/ፋኩልቲ</span></label><input type="text" id="transfer_college_faculty" name="transfer_college_faculty" value="<?php echo getValue('transfer_college_faculty', $form_data); ?>" <?php echo $disabled_attr; ?>></div><div class="form-group"><label for="transfer_department">Department <span class="amharic">ትም/ክፍል</span></label><input type="text" id="transfer_department" name="transfer_department" value="<?php echo getValue('transfer_department', $form_data); ?>" <?php echo $disabled_attr; ?>></div></div>
                    <div class="form-row two-col"><div class="form-group"><label for="date_of_transfer">Date of transfer (Date/Month/Year) <span class="amharic">የተዛወሩበት ዘመን</span></label><input type="text" id="date_of_transfer" name="date_of_transfer" placeholder="DD / MM / YYYY" value="<?php echo getValue('date_of_transfer', $form_data); ?>" <?php echo $disabled_attr; ?>></div><div class="form-group"><label for="transfer_semester">Semester <span class="amharic">ሴሚስተር</span></label><input type="text" id="transfer_semester" name="transfer_semester" value="<?php echo getValue('transfer_semester', $form_data); ?>" <?php echo $disabled_attr; ?>></div></div>
              </div>

             <!-- == PAGE 3 CONTENT == -->
             <h2 class="form-section-title">Page 3 Information</h2>
             <div class="form-group full-width">
                <label>Total cost used during your previous stay (if transferred). <span class="amharic">ከዚህ በፊት በነበረው ቆይታዎ ወቅት የተጠቀሙበት የወጪ መጠን</span></label>
                 <div class="form-row two-col">
                     <div><label for="prev_stay_cost_figures">In figures (Birr) <span class="amharic">በአሃዝ (ብር)</span></label><input type="number" step="0.01" id="prev_stay_cost_figures" name="prev_stay_cost_figures" value="<?php echo getValue('prev_stay_cost_figures', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                     <div><label for="prev_stay_cost_words">In words <span class="amharic">በፊደል</span></label><input type="text" id="prev_stay_cost_words" name="prev_stay_cost_words" value="<?php echo getValue('prev_stay_cost_words', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                 </div>
              </div>
             <div class="form-group full-width">
                <label>9. What services would you demand? <span class="amharic">የሚጠየቀው አገልግሎት</span></label>
                 <div style="display: flex; flex-wrap: wrap; gap: 2rem; margin-top: 1rem;">
                     <div style="border: 1px solid #eee; padding: 1rem; flex: 1; min-width: 250px;"><strong>A) In kind <span class="amharic">በዓይነት/አገልግሎት</span></strong><div class="checkbox-group" style="margin-top: 0.5rem;"><input type="checkbox" id="demand_kind_food" name="demand_service_kind[]" value="food_only" <?php echo in_array('food_only', $demand_kind_array) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>><label for="demand_kind_food">Food only <span class="amharic">የምግብ ብቻ</span></label></div><div class="checkbox-group"><input type="checkbox" id="demand_kind_boarding" name="demand_service_kind[]" value="boarding_only" <?php echo in_array('boarding_only', $demand_kind_array) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>><label for="demand_kind_boarding">Boarding only <span class="amharic">የመኝታ ብቻ</span></label></div><div class="checkbox-group"><input type="checkbox" id="demand_kind_both" name="demand_service_kind[]" value="food_boarding" <?php echo in_array('food_boarding', $demand_kind_array) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>><label for="demand_kind_both">Food & Board <span class="amharic">የምግብ ና የመኝታ</span></label></div></div>
                     <div style="border: 1px solid #eee; padding: 1rem; flex: 1; min-width: 250px;"><strong>B) In cash <span class="amharic">በጥሬ ገንዘብ</span></strong><div class="checkbox-group" style="margin-top: 0.5rem;"><input type="checkbox" id="demand_cash_food" name="demand_service_cash[]" value="food_only" <?php echo in_array('food_only', $demand_cash_array) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>><label for="demand_cash_food">Food only <span class="amharic">የምግብ ብቻ</span></label></div><div class="checkbox-group"><input type="checkbox" id="demand_cash_boarding" name="demand_service_cash[]" value="boarding_only" <?php echo in_array('boarding_only', $demand_cash_array) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>><label for="demand_cash_boarding">Boarding only <span class="amharic">የመኝታ ብቻ</span></label></div><div class="checkbox-group"><input type="checkbox" id="demand_cash_both" name="demand_service_cash[]" value="food_boarding" <?php echo in_array('food_boarding', $demand_cash_array) ? 'checked' : ''; ?> <?php echo $disabled_attr; ?>><label for="demand_cash_both">Food & Board <span class="amharic">የምግብ ና የመኝታ</span></label></div></div>
                </div>
            </div>
             <div class="form-group full-width">
                 <label>10. Estimated cost (Current Academic Year). <span class="amharic">የአመቱ ወጭ ግምት፡</span></label>
                 <div class="cost-item"><span>15% tuition fee</span> <strong><?php echo number_format(getValue('estimated_cost_tuition', $form_data, 1382.11), 2); ?> Birr</strong> <span class="amharic">15%የት/ት ወጪ</span></div>
                 <div class="cost-item"><span>Food expense</span> <strong><?php echo number_format(getValue('estimated_cost_food', $form_data, 4500.00), 2); ?> Birr</strong> <span class="amharic">የምግብ ወጪ</span></div>
                 <div class="cost-item"><span>Boarding expense</span> <strong><?php echo number_format(getValue('estimated_cost_boarding', $form_data, 600.00), 2); ?> Birr</strong> <span class="amharic">የመኝታ ወጪ</span></div>
                 <div class="cost-item" style="border-top: 1px solid #ccc; padding-top: 0.5rem; margin-top: 0.5rem;"><span>Total</span> <strong><?php echo number_format(getValue('estimated_cost_total', $form_data, 6482.11), 2); ?> Birr</strong> <span class="amharic">ጠቅላላ</span></div>
                 <input type="hidden" name="estimated_cost_tuition" value="<?php echo getValue('estimated_cost_tuition', $form_data, 1382.11); ?>"> <input type="hidden" name="estimated_cost_food" value="<?php echo getValue('estimated_cost_food', $form_data, 4500.00); ?>"> <input type="hidden" name="estimated_cost_boarding" value="<?php echo getValue('estimated_cost_boarding', $form_data, 600.00); ?>"> <input type="hidden" name="estimated_cost_total" value="<?php echo getValue('estimated_cost_total', $form_data, 6482.11); ?>">
            </div>
             <div class="form-group full-width">
                <label>11. Advance payment, if any</label>
                <div class="form-row three-col">
                    <div><label for="advance_payment_date">Date (D/M/Y)</label><input type="text" id="advance_payment_date" name="advance_payment_date" placeholder="DD/MM/YYYY" value="<?php echo getValue('advance_payment_date', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div><label for="advance_payment_discount">Discount</label><input type="text" id="advance_payment_discount" name="advance_payment_discount" value="<?php echo getValue('advance_payment_discount', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                    <div><label for="advance_payment_receipt_no">Receipt No. <span class="amharic">ደረሰኝ ቁጥር</span></label><input type="text" id="advance_payment_receipt_no" name="advance_payment_receipt_no" value="<?php echo getValue('advance_payment_receipt_no', $form_data); ?>" <?php echo $disabled_attr; ?>></div>
                </div>
             </div>

             <!-- == PAGE 4 CONTENT == -->
             <h2 class="form-section-title">Page 4 Information</h2>
              <div class="form-group full-width">
                 <label>12. Agreement Options <span class="amharic">(ከምረቃ በኋላ)</span></label>
                 <div class="payment-option">
                     <input type="radio" id="pay_income" name="payment_option" value="income" <?php echo (getValue('payment_option', $form_data) == 'income') ? 'checked' : ''; ?> required <?php echo $disabled_attr; ?>>
                     <label for="pay_income">A) To be paid from my income <span class="amharic">ከገቢዬ ተቆራጭ ሆኖ እንዲከፈል</span></label>
                 </div>
                 <div class="payment-option">
                     <input type="radio" id="pay_service" name="payment_option" value="service" <?php echo (getValue('payment_option', $form_data) == 'service') ? 'checked' : ''; ?> required <?php echo $disabled_attr; ?>>
                     <label for="pay_service">B) To provide service not more than the training period in my profession <span class="amharic">ከሰለጠንኩበት ዓመት ላልበለጠ ጊዜ በሙያዬ አገልግሎት ለመስጠት</span></label>
                     <p>(For health and teaching Professions only) <span class="amharic">(ለጤና እና ለመምህርነት ሠልጣኞች ብቻ)</span></p>
                 </div>
                  <p style="font-weight: bold; margin-top: 1rem; font-size: 1.3rem;">**Please select only one option. <span class="amharic">ከሁለቱ ምርጫዎች አንዱን ይምረጡ</span></p>
             </div>
              <div class="form-group full-width">
                 <label>Certification: <span class="amharic">ማረጋገጫ</span></label>
                 <p style="font-size: 1.4rem;">I also certify that the above information is true. <span class="amharic">/ተስማምቼ በፈቃዴ ይህን ውል የፈረምኩ እና ከላይ የሰፈረው መረጃ ትክክለኛ መሆኑን አረጋግጣለሁ፡፡</span></p>
                 <div class="form-row two-col" style="margin-top: 1rem;">
                     <div><label>Beneficiary's Signature: <span class="amharic">የተጠቃሚ ፊርማ</span></label><div class="signature-line" style="background: #eee;">(Form Submission Acts as Digital Signature)</div></div>
                     <div><label for="beneficiary_signature_date">Date <span class="amharic">ቀን/ወር/ዓ.ም</span></label><input type="text" id="beneficiary_signature_date" name="beneficiary_signature_date" placeholder="DD / MM / YYYY" value="<?php echo getValue('beneficiary_signature_date', $form_data); ?>" required <?php echo $disabled_attr; ?>></div>
                 </div>
            </div>
            <div class="form-group full-width" style="margin-top: 2rem;">
                 <label>13. Head/Representative of the institute Certification (For Office Use)</label>
                 <p style="font-size:1.3rem; color: #555;"> We certify the beneficiary signed this contract in our office and the stated amount is correct. <span class="amharic">ጽ/ቤታችን ቀርበው ውሉን የፈረሙ መሆኑን እና... ወጪ ትክክለኛ መሆኑን እናረጋግጣለን፡፡</span></p>
            </div>

             <!-- == PAGE 5 CONTENT (Mostly Informational) == -->
            <h2 class="form-section-title">Page 5 Information (Notes)</h2>
            <div class="form-notes">
                <p><strong>ማሳሰቢያ</strong></p>
                <p>ሀ. ተጠቃሚው በማንኛውም ምክንያት ከተቋሙ ቢለቅ እስካቋረጠበት ጊዜ የሚፈለግበትን ወጪ የመክፈል ግዴታ ይኖርበታል ወይም የወጪ መጋራት ክፍያ አስቀድሞ የፈጸመ ተጠቃሚ ትምህርቱን ቢያቋርጥ በተቋሙ ለቆየበት ጊዜ የሚፈለግበትን ሂሳብ ተቀናሽ ተደርጎ ቀሪው ተመላሽ ይደረግለታል፡፡</p>
                <p>ለ. ይህ ቅፅ በ3 ኮፒ ተሠርቶ አንዱ ለተማሪው እንዲደርስ ይደረጋል፣ አንዱ በሬጅስትራር ፣ አንዱ በወጪ መጋራት ቢሮ ይቀመጣል፡፡</p>
                 <p><strong>ማበረታቻዎች</strong></p>
                 <p>1. ትምህርቱ ለሚፈጀው ጊዜ ጠቅላላ የወጪ መጋራት መጠን ሙሉ በሙሉ ቅድሚያ ለሚከፍሉ ተቀቃሚዎች አስር በመቶ (10%)</p>
                 <p>2. በየዓመቱ መጀመሪያ ላይ ቅድሚያ ለሚከፍሉ አምስት በመቶ (5%) እና</p>
                 <p>3. ከምረቃ በኋላ በእፎይታ ጊዜ ውስጥ እስከ አንድ ዓመት ለሚከፍሉ ሦስት በመቶ (3%) ተቀናሽ ይደረግላቸዋል፡፡</p>
                 <p>*** ለበለጠ ማብራሪያ የኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ ፌዴራል ነጋሪት ጋዜጣ አስራ አራተኛ ዓመት 2 ቁጥር 48 ውስጥ ደንብ ቁጥር 154/2ዐዐዐን ይመልከቱ፡፡</p>
            </div>


             <!-- Submission Button -->
            <div class="submit-button-container">
                <button type="submit" class="cta" <?php echo $disabled_attr; ?>>Submit Agreement Form</button>
            </div>
        </form>

        <!-- Back Button -->
        <div style="text-align: center; margin-top: 3rem;">
            <a href="dashboard.php" class="cta cta-outline">← Back to Dashboard</a>
        </div>

    </div> <!-- End form-container -->
</section>

<script>
// JS to toggle conditional sections (Withdraw / Transfer)
document.addEventListener('DOMContentLoaded', function() {
    const withdrawCheck = document.getElementById('withdrawn_previously_check');
    const withdrawDetails = document.getElementById('withdraw_details');
    const transferCheck = document.getElementById('transferred_from_other_check');
    const transferDetails = document.getElementById('transfer_details');

    function toggleSectionVisibility(checkbox, section) {
        if (!checkbox || !section) return;
        section.style.display = checkbox.checked ? 'block' : 'none';
        // Note: We are NOT setting 'required' with JS here as backend needs to handle optional fields
    }

    if(withdrawCheck) {
        withdrawCheck.addEventListener('change', () => toggleSectionVisibility(withdrawCheck, withdrawDetails));
        // Initial check based on PHP pre-fill handled by inline style
    }
    if(transferCheck) {
        transferCheck.addEventListener('change', () => toggleSectionVisibility(transferCheck, transferDetails));
        // Initial check handled by inline style
    }
});
</script>


<?php
// Close DB connection if it was opened for fetching and not already closed
if (isset($conn_fetch) && $conn_fetch instanceof mysqli) {
   //$conn_fetch->close(); // Already closed after fetch
} elseif (isset($conn) && $conn instanceof mysqli) {
    $conn->close(); // Close main connection if open
}

include 'footer.php'; // Include the footer
?>