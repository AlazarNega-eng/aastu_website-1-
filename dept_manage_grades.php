<?php
session_start();
require_once 'db_connect.php'; // Establishes $conn

// --- Authentication & Role Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$page_title = "Manage Student Grades";
$fetch_error = null; // Initialize error variable

// --- Fetch Students (e.g., Approved Students) ---
// TODO: Add department filtering based on logged-in Head's dept if needed
$students = [];

// Ensure connection is valid before querying
if ($conn && $conn instanceof mysqli) {
    // Make sure to select u.id and alias it as user_id
    $sql = "SELECT sp.id as profile_id, sp.first_name, sp.last_name, u.username, u.id as user_id
            FROM student_profiles sp
            JOIN users u ON sp.user_id = u.id
            WHERE sp.dept_approval_status = 'approved' /* Or filter by department */
            ORDER BY sp.last_name ASC, sp.first_name ASC";

    $result = $conn->query($sql); // Use query() for simple selects without user input

    if ($result) {
        while($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $result->free(); // Free result set
    } else {
         $fetch_error = "Error fetching approved students: " . $conn->error;
         error_log($fetch_error);
    }
    $conn->close(); // Close connection after use
} else {
    $fetch_error = "Database connection error.";
    error_log("DB connection error on dept_manage_grades.");
}

include 'header.php';
?>

<section class="list-section">
    <div class="list-container">
        <h1>Manage Student Grades</h1>

        <?php
            // Display messages
            if (isset($_SESSION['success'])) { echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
            if (isset($_SESSION['error'])) { echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
            if ($fetch_error) { echo '<div class="message error">' . htmlspecialchars($fetch_error) . '</div>'; }
        ?>

        <!-- Add Filtering Form Here Later -->

        <?php if (!empty($students)): ?>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td>
                                <!-- ** Crucial Link ** Ensure $student['user_id'] exists and is correct -->
                                <?php if (isset($student['user_id'])): ?>
                                    <a href="dept_enter_grades.php?user_id=<?php echo $student['user_id']; ?>" class="cta cta-outline cta-small" title="Enter/View Grades">Manage Grades</a>
                                <?php else: ?>
                                    <span style="color: red;">Error: Missing User ID</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif (!$fetch_error): // Only show 'no students' if there wasn't a DB error ?>
            <p class="no-students">No approved students found.</p>
        <?php endif; ?>

         <div style="text-align: center; margin-top: 3rem;">
            <a href="dept_dashboard.php" class="cta cta-outline">← Back to Dashboard</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>