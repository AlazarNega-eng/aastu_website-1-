<?php
session_start();
require_once 'db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$page_title = "My Grades";

// --- Fetch Grades ---
$grades = [];
$semesters = []; // To group grades by semester
$total_credits = 0;
$total_grade_points = 0;

$sql = "SELECT course_code, course_title, semester, credit_hours, grade_letter, grade_points, academic_year
        FROM student_grades
        WHERE user_id = ?
        ORDER BY academic_year DESC, semester, course_code"; // Sensible ordering
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
        // Group by semester
        if (!isset($semesters[$row['semester']])) {
            $semesters[$row['semester']] = [
                'courses' => [],
                'total_credits' => 0,
                'total_points' => 0,
                'academic_year' => $row['academic_year'] ?? 'N/A'
            ];
        }
        $semesters[$row['semester']]['courses'][] = $row;
        if (is_numeric($row['grade_points']) && is_numeric($row['credit_hours'])) {
             $semesters[$row['semester']]['total_credits'] += $row['credit_hours'];
             $semesters[$row['semester']]['total_points'] += $row['grade_points'] * $row['credit_hours'];
             $total_credits += $row['credit_hours'];
             $total_grade_points += $row['grade_points'] * $row['credit_hours'];
        }

    }
    $stmt->close();
} else {
    $db_error = "Error fetching grades: " . $conn->error;
}
$conn->close();

// Calculate Overall GPA
$overall_gpa = ($total_credits > 0) ? round($total_grade_points / $total_credits, 2) : 0;


include 'header.php';
?>

<section class="grades-section">
    <div class="grades-container">
        <h1>My Academic Grades</h1>

        <?php if (isset($db_error)): ?>
            <div class="message error"><?php echo htmlspecialchars($db_error); ?></div>
        <?php elseif (empty($semesters)): ?>
            <p class="no-grades">No grades have been recorded yet.</p>
        <?php else: ?>
            <?php foreach ($semesters as $semester_name => $semester_data): ?>
                <div class="semester-block">
                    <div class="semester-header">
                        <?php echo htmlspecialchars($semester_name); ?> (<?php echo htmlspecialchars($semester_data['academic_year']); ?>)
                    </div>
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Title</th>
                                <th style="text-align: center;">Credits</th>
                                <th style="text-align: center;">Grade</th>
                                <th style="text-align: center;">Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($semester_data['courses'] as $course): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                    <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($course['credit_hours'], 1)); ?></td>
                                    <td><?php echo htmlspecialchars($course['grade_letter'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(isset($course['grade_points']) ? number_format($course['grade_points'], 2) : 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                     <?php
                         $semester_gpa = ($semester_data['total_credits'] > 0)
                                         ? round($semester_data['total_points'] / $semester_data['total_credits'], 2)
                                         : 0;
                     ?>
                    <div class="semester-summary">
                        Semester Credits: <?php echo number_format($semester_data['total_credits'], 1); ?> |
                        Semester GPA: <?php echo number_format($semester_gpa, 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>

             <div class="overall-summary">
                Overall Cumulative GPA: <?php echo number_format($overall_gpa, 2); ?>
                (Based on <?php echo number_format($total_credits, 1); ?> credits)
            </div>

        <?php endif; ?>
         <div style="text-align: center; margin-top: 3rem;">
            <a href="dashboard.php" class="cta cta-outline">← Back to Dashboard</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>