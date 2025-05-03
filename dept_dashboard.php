<?php
// --- Session MUST be started before ANY output ---
session_start();

// --- Includes ---
require_once 'db_connect.php'; // Establishes $conn (might not be needed directly here, but good practice)

// --- Authentication & Role Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    $_SESSION['error'] = "Access denied. Please log in as Department Head.";
    header("Location: login.php");
    exit;
}

// --- Variables ---
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$page_title = "Department Dashboard"; // For header include

// --- Close DB Connection if not needed further ---
// Since this page just shows links, we probably don't need the DB connection open.
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}

// --- Include Header ---
// MUST come after all session/header logic and potential redirects
include 'header.php';
?>

<!-- Add specific styles if needed, or rely on style.css -->
<style>
/* Styles are assumed to be in style.css */
.dashboard-section { padding: 6rem 0; background-color: var(--secondary-color); min-height: calc(100vh - var(--header-height) - 150px); }
.dashboard-container { max-width: 900px; margin: 0 auto; padding: 0 15px; }
.dashboard-welcome { background-color: #fff; padding: 2rem 3rem; border-radius: 8px; box-shadow: 0 3px 15px rgba(0,0,0,0.07); margin-bottom: 3rem; }
.dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2.5rem; }
.dashboard-card { background-color: #fff; padding: 2.5rem; border-radius: 8px; box-shadow: 0 3px 15px rgba(0,0,0,0.07); text-align: center; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; }
.dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.dashboard-card i { font-size: 3.5rem; color: var(--accent-color); margin-bottom: 1.5rem; }
.dashboard-card h3 { color: var(--primary-color); margin-bottom: 1rem; font-size: 2rem; }
.dashboard-card p { color: var(--text-muted); font-size: 1.5rem; margin-bottom: 2rem; flex-grow: 1; }
.dashboard-card .cta { margin-top: auto; font-size: 1.5rem; padding: 10px 25px; }
</style>

<!-- Page Specific Content START -->
<section class="dashboard-section">
    <div class="dashboard-container">
        <div class="dashboard-welcome">
            <h1>Department Head Portal</h1>
            <p>Welcome, <?php echo htmlspecialchars($username); ?>! Manage student registrations and grades for your department.</p>
             <?php
                // Display messages if redirected here after an action
                if (isset($_SESSION['success'])) { echo '<div class="message success" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
                if (isset($_SESSION['error'])) { echo '<div class="message error" style="margin-top: 1rem;">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
            ?>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <i class="fas fa-users"></i>
                <h3>View Students</h3>
                <p>Review registered student profiles pending approval.</p>
                <a href="dept_view_students.php" class="cta cta-outline">View List</a>
            </div>

            <!-- Removed Approve card - Action is on View List page -->
            <!-- <div class="dashboard-card"> -->
                <!-- <i class="fas fa-user-check"></i> -->
                <!-- <h3>Approve Registrations</h3> -->
                <!-- <p>Approve completed student registrations for the Registrar.</p> -->
                <!-- <a href="dept_view_students.php" class="cta cta-outline">Approve Now</a> Link to same page -->
            <!-- </div> -->

            <div class="dashboard-card">
                <i class="fas fa-edit"></i>
                <h3>Manage Grades</h3>
                <p>Enter or upload grades for students in your department.</p>
                <a href="dept_manage_grades.php" class="cta cta-outline">Manage Grades</a>
            </div>

            <div class="dashboard-card">
                 <i class="fas fa-chart-bar"></i>
                 <h3>Reports</h3>
                 <p>View departmental statistics or generate reports (Future Feature).</p>
                 <a href="#" class="cta cta-outline disabled" title="Coming Soon">View Reports</a> <!-- Example disabled link -->
             </div>

        </div>
    </div>
</section>
<!-- Page Specific Content END -->

<?php
// --- Include Footer ---
include 'footer.php';
?>