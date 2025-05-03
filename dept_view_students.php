<?php
session_start();
require_once 'db_connect.php';

// --- Authentication & Role Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'dept_head') {
    $_SESSION['error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

$page_title = "View Pending Registrations";

// --- Fetch Pending Students ---
// TODO: Add filtering by department if Department Heads are tied to specific departments
$students = [];
$sql = "SELECT sp.id as profile_id, sp.first_name, sp.last_name, sp.email_address, sp.registration_submitted_at, u.username, u.id as user_id
        FROM student_profiles sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.dept_approval_status = 'pending'
        ORDER BY sp.registration_submitted_at ASC";

$result = $conn->query($sql); // Using simple query for now, prepare if filtering added

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
} elseif (!$result) {
     $fetch_error = "Error fetching students: " . $conn->error;
     error_log($fetch_error);
}

$conn->close();
include 'header.php';
?>

<section class="list-section">
    <div class="list-container">
        <h1>Pending Student Registrations</h1>

         <?php
            // Display messages
            if (isset($_SESSION['success'])) { echo '<div class="message success">' . htmlspecialchars($_SESSION['success']) . '</div>'; unset($_SESSION['success']); }
            if (isset($_SESSION['error'])) { echo '<div class="message error">' . htmlspecialchars($_SESSION['error']) . '</div>'; unset($_SESSION['error']); }
            if (isset($fetch_error)) { echo '<div class="message error">' . htmlspecialchars($fetch_error) . '</div>'; }
        ?>

        <?php if (!empty($students)): ?>
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['email_address']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($student['registration_submitted_at']))); ?></td>
                            <td>
                                <!-- View details link (points back to student registration form maybe?) -->
                                <a href="student_registration_view.php?profile_id=<?php echo $student['profile_id']; ?>" class="cta cta-outline cta-small" title="View Details">View</a>
                                <!-- Approval Form/Link -->
                                <form action="process_dept_approval.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="profile_id" value="<?php echo $student['profile_id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $student['user_id']; ?>">
                                    <button type="submit" name="action" value="approve" class="cta cta-small" title="Approve Registration">Approve</button>
                                     <!-- Optional: Reject Button -->
                                     <!-- <button type="submit" name="action" value="reject" class="cta cta-outline cta-small" style="border-color: red; color: red;" title="Reject Registration">Reject</button> -->
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-students">No pending student registrations found.</p>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 3rem;">
            <a href="dept_dashboard.php" class="cta cta-outline">← Back to Dashboard</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>