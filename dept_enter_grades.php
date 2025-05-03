<?php
session_start();
require_once 'db_connect.php'; // Establishes $conn

// --- Authentication & Role Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

// --- Get Student User ID from URL ---
$student_user_id = null; // Initialize
if (isset($_GET['user_id'])) {
    $student_user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
}

// Redirect if ID is missing or invalid
if (!$student_user_id) {
    $_SESSION['error'] = "Invalid or missing student ID provided.";
    header("Location: dept_manage_grades.php");
    exit;
}

// --- Fetch Student Information for Display ---
$student_info = null;
$fetch_student_error = null;
$fetch_grade_error = null;

if ($conn && $conn instanceof mysqli) {
    $sql_student = "SELECT u.username, sp.first_name, sp.last_name
                    FROM users u
                    LEFT JOIN student_profiles sp ON u.id = sp.user_id
                    WHERE u.id = ?";
    $stmt_student = $conn->prepare($sql_student);
    if ($stmt_student) {
        $stmt_student->bind_param("i", $student_user_id);
        $stmt_student->execute();
        $result_student = $stmt_student->get_result();
        if ($result_student->num_rows > 0) {
            $student_info = $result_student->fetch_assoc();
        } else {
            // Student ID is valid integer, but no user found
            $_SESSION['error'] = "Student with ID " . htmlspecialchars($student_user_id) . " not found.";
            $stmt_student->close();
            $conn->close();
            header("Location: dept_manage_grades.php");
            exit;
        }
        $stmt_student->close();
    } else {
        $fetch_student_error = "Database error fetching student info.";
        error_log("Prepare Error fetching student (ID: $student_user_id): ".$conn->error);
    }

    // --- Fetch Existing Grades for this Student (Only if student fetch was ok) ---
    $existing_grades = [];
    if(!$fetch_student_error) {
        $sql_grades = "SELECT course_code, course_title, semester, academic_year, grade_letter, grade_points, credit_hours
                       FROM student_grades WHERE user_id = ? ORDER BY academic_year DESC, semester, course_code";
        $stmt_grades = $conn->prepare($sql_grades);
        if ($stmt_grades){
            $stmt_grades->bind_param("i", $student_user_id);
            $stmt_grades->execute();
            $result_grades = $stmt_grades->get_result();
            while($row = $result_grades->fetch_assoc()){
                $existing_grades[] = $row;
            }
            $stmt_grades->close();
        } else {
             $fetch_grade_error = "Could not fetch existing grades.";
             error_log("Prepare Error fetching grades (ID: $student_user_id): ".$conn->error);
        }
    }
    // Close connection after all fetches for this page
    $conn->close();

} else {
    // Handle connection error from db_connect.php
    $fetch_student_error = "Database connection error.";
    error_log("DB connection error in dept_enter_grades.");
}

// If student fetch failed critically, redirect before including header
if ($student_info === null && !$fetch_student_error) {
     $_SESSION['error'] = "Could not load student data."; // Generic if not caught before
     header("Location: dept_manage_grades.php");
     exit;
}


$page_title = "Enter Grades for " . htmlspecialchars($student_info['first_name'] ?? '') . ' ' . htmlspecialchars($student_info['last_name'] ?? 'Student');
include 'header.php';
?>

<section class="form-section">
    <div class="form-container">
        <h1>Enter Student Grades</h1>
        <!-- Check if student_info was fetched successfully before displaying -->
        <?php if ($student_info): ?>
        <h2>For Student: <span class="student-name"><?php echo htmlspecialchars($student_info['first_name'] . ' ' . $student_info['last_name']); ?> (<?php echo htmlspecialchars($student_info['username']); ?>)</span></h2>
        <?php else: ?>
        <h2>Student Information Not Found</h2>
        <?php endif; ?>

        <?php
            // Display messages
             if (isset($_SESSION['error'])) { echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
             if (isset($_SESSION['success'])) { echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
             if ($fetch_student_error) { echo '<div class="message error">' . htmlspecialchars($fetch_student_error) . '</div>'; }
             if ($fetch_grade_error) { echo '<div class="message error">' . htmlspecialchars($fetch_grade_error) . '</div>'; }
        ?>

        <!-- Only show form if student info loaded -->
        <?php if ($student_info): ?>
        <form action="process_dept_grades.php" method="POST">
            <!-- ** Crucial Hidden Field ** Ensure $student_user_id has the correct value -->
            <input type="hidden" name="student_user_id" value="<?php echo htmlspecialchars($student_user_id); ?>">

            <table class="grade-entry-table" id="grade-entry-table">
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Semester</th>
                        <th>Acad. Year</th>
                        <th>Credits</th>
                        <th>Grade Letter</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Grade Entry Row Template -->
                    <tr class="grade-row">
                        <td><input type="text" name="course_code[]" placeholder="e.g., CS101" required></td>
                        <td><input type="text" name="course_title[]" placeholder="e.g., Intro to Prog." required></td>
                        <td><input type="text" name="semester[]" placeholder="e.g., Year 1, Sem 1" required></td>
                        <td><input type="text" name="academic_year[]" placeholder="e.g., 2024/2025" required></td>
                        <td><input type="number" step="0.1" min="0" name="credit_hours[]" placeholder="e.g., 3.0" required></td>
                        <td>
                            <select name="grade_letter[]">
                                <option value="">N/A</option>
                                <option value="A+">A+</option>
                                <option value="A">A</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B">B</option>
                                <option value="B-">B-</option>
                                <option value="C+">C+</option>
                                <option value="C">C</option>
                                <option value="C-">C-</option>
                                <option value="D">D</option>
                                <option value="F">F</option>
                                <option value="P">P (Pass)</option>
                                <option value="NG">NG (No Grade)</option>
                            </select>
                        </td>
                         <td><button type="button" class="remove-row-btn">X</button></td>
                    </tr>
                    <!-- Add more rows here via PHP if needed for editing, or rely on JS -->
                </tbody>
            </table>

            <div class="add-row-container">
                <button type="button" class="add-row-btn" id="add-grade-row-btn">+ Add Another Course</button>
            </div>

            <div class="submit-button-container">
                 <button type="submit" class="cta">Submit Grades</button>
            </div>
        </form>
        <?php endif; ?> <!-- End check for $student_info -->

        <!-- Display Existing Grades -->
        <div class="existing-grades-section">
             <h3>Existing Grades for this Student</h3>
             <?php if (!empty($existing_grades)): ?>
                <table class="grades-table">
                     <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th style="text-align: center;">Credits</th>
                            <th>Semester</th>
                            <th style="text-align: center;">Grade</th>
                            <th style="text-align: center;">Points</th>
                            <th>Acad. Year</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existing_grades as $grade): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($grade['course_title']); ?></td>
                                <td><?php echo htmlspecialchars(number_format((float)$grade['credit_hours'], 1)); ?></td>
                                <td><?php echo htmlspecialchars($grade['semester']); ?></td>
                                <td><?php echo htmlspecialchars($grade['grade_letter'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(isset($grade['grade_points']) ? number_format((float)$grade['grade_points'], 2) : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($grade['academic_year'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
             <?php elseif (!$fetch_grade_error): ?>
                <p class="no-grades">No grades previously recorded for this student.</p>
             <?php endif; ?>
        </div>


        <div style="text-align: center; margin-top: 4rem;">
            <a href="dept_manage_grades.php" class="cta cta-outline">← Back to Student List</a>
        </div>
    </div>
</section>

<script>
// JavaScript to Add/Remove Grade Entry Rows (Same as before)
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('grade-entry-table')?.getElementsByTagName('tbody')[0]; // Added optional chaining '?'
    const addRowBtn = document.getElementById('add-grade-row-btn');

    // Exit if table elements aren't found (e.g., if student info failed to load)
    if (!tableBody || !addRowBtn) {
        // console.warn("Grade entry table or add button not found. JS listeners not added.");
        return;
    }

    const addRemoveListener = (button) => {
        button.addEventListener('click', function() {
            if (tableBody.rows.length > 1) {
                this.closest('tr').remove();
            } else {
                alert("Cannot remove the last row.");
            }
        });
    };

    tableBody.querySelectorAll('.remove-row-btn').forEach(addRemoveListener);

    if (tableBody.rows.length > 0) {
        addRowBtn.addEventListener('click', function() {
            const firstRow = tableBody.rows[0];
            if (!firstRow) return; // Safety check

            const newRow = firstRow.cloneNode(true);

            newRow.querySelectorAll('input, select').forEach(input => {
                 if (input.type !== 'button' && input.type !== 'hidden') {
                     input.value = '';
                 }
                 if (input.tagName === 'SELECT') {
                     input.selectedIndex = 0;
                 }
            });

            const newRemoveBtn = newRow.querySelector('.remove-row-btn');
            if (newRemoveBtn) {
                addRemoveListener(newRemoveBtn);
            }

            tableBody.appendChild(newRow);
        });
    } else {
         console.error("Grade entry table body has no initial row to use as a template.");
         // You might want to dynamically create the first row here if needed
    }
});
</script>

<?php
// Connection was closed earlier in the script
include 'footer.php';
?>