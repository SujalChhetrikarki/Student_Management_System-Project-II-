<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];
$exam_id = $_GET['exam_id'] ?? 0;

/* 1️⃣ Fetch exam details + class and subject (verify teacher) */
$stmt = $conn->prepare("
    SELECT e.exam_id, e.exam_date, e.max_marks, e.term,
           c.class_id, c.class_name,
           s.subject_id, s.subject_name
    FROM exams e
    JOIN classes c ON e.class_id = c.class_id
    JOIN subjects s ON e.subject_id = s.subject_id
    JOIN class_subject_teachers cst
        ON cst.class_id = e.class_id AND cst.subject_id = e.subject_id
    WHERE e.exam_id = ? AND cst.teacher_id = ?
");
$stmt->bind_param("ii", $exam_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h3 style='color:red;'>❌ Access Denied or Exam Not Found.</h3>");
}

$exam = $result->fetch_assoc();
$stmt->close();

/* 2️⃣ Fetch students in this class */
$stmt = $conn->prepare("SELECT student_id, name FROM students WHERE class_id = ?");
$stmt->bind_param("i", $exam['class_id']);
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();

/* 3️⃣ Handle form submission */
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($_POST['marks'] as $student_id => $marks) {

        $marks = trim($marks);
        if ($marks === '') continue;

        /* INSERT / UPDATE marks — FIXED BINDING */
        $stmt = $conn->prepare("
            INSERT INTO results (student_id, exam_id, marks_obtained, status)
            VALUES (?, ?, ?, 'Pending')
            ON DUPLICATE KEY UPDATE
                marks_obtained = VALUES(marks_obtained),
                status = 'Pending'
        ");
        // 🔧 FIX: student_id is VARCHAR
        $stmt->bind_param("sid", $student_id, $exam_id, $marks);
        $stmt->execute();
        $stmt->close();

        /* UPDATE progressive average marks — FIXED BINDING */
        $stmt_avg = $conn->prepare("
            UPDATE results r
            JOIN (
                SELECT r2.result_id,
                       ROUND((
                           SELECT AVG(r3.marks_obtained)
                           FROM results r3
                           JOIN exams e3 ON r3.exam_id = e3.exam_id
                           WHERE r3.student_id = r2.student_id
                             AND e3.subject_id = e2.subject_id
                             AND e3.exam_date <= e2.exam_date
                       ), 2) AS avg_marks
                FROM results r2
                JOIN exams e2 ON r2.exam_id = e2.exam_id
                WHERE r2.student_id = ?
            ) t ON r.result_id = t.result_id
            SET r.average_marks = t.avg_marks
            WHERE r.student_id = ?
        ");
        // 🔧 FIX: both are VARCHAR
        $stmt_avg->bind_param("ss", $student_id, $student_id);
        $stmt_avg->execute();
        $stmt_avg->close();
    }
    $success = "✅ Marks updated successfully! Average marks calculated automatically.";
}
/* 4️⃣ Fetch existing marks */
$existing_marks = [];
$stmt = $conn->prepare("SELECT student_id, marks_obtained FROM results WHERE exam_id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $existing_marks[$row['student_id']] = $row['marks_obtained'];
}
$stmt->close();

/* 5️⃣ Re-fetch students */
$stmt = $conn->prepare("SELECT student_id, name FROM students WHERE class_id = ?");
$stmt->bind_param("i", $exam['class_id']);
$stmt->execute();
$students = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add/Update Marks - <?= htmlspecialchars($exam['subject_name']) ?></title>

<style>
body { 
    font-family: "Segoe UI", Arial; 
    background: #f4f6fa; 
    margin: 0; 
    padding: 0; 
    color: #333; 
    display: flex;
}
/* Sidebar */
.sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: #0066cc;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    padding: 20px 15px;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}
.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 20px;
    color: #fff;
}
.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 15px;
    margin-bottom: 10px;
    text-decoration: none;
    color: #e5e7eb;
    border-radius: 10px;
    transition: background 0.3s;
}
.sidebar a:hover {
    background: rgba(255,255,255,0.2);
    color: #ffffff;
}
.sidebar a.logout {
    margin-top: auto;
    background: #7f1d1d;
}
.sidebar a.logout:hover {
    background: #dc2626;
}
.container { 
    margin-left: 240px;
    width: calc(100% - 240px);
    padding: 30px; 
    background: #fff; 
    border-radius: 8px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
}
h2 { 
    color: #0066cc; 
    font-size: 24px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #0066cc;
}
p {
    color: #666;
    margin-bottom: 20px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #0066cc;
}
table { 
    width: 100%; 
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 25px; 
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
th, td { 
    border: none;
    padding: 14px 18px; 
    text-align: center; 
}
th { 
    background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
    color: #fff; 
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}
tr:nth-child(even) {
    background: #f8f9fa;
}
tr:hover {
    background: #e6f0ff;
    transition: background 0.2s;
}
input[type="number"] { 
    width: 100px; 
    padding: 10px 12px; 
    border: 2px solid #dee2e6;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}
input[type="number"]:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
}
.btn { 
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: #fff; 
    padding: 12px 24px; 
    border-radius: 8px; 
    cursor: pointer; 
    border: none;
    font-size: 16px;
    font-weight: 500;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(40,167,69,0.3);
    margin-top: 20px;
}
.btn:hover { 
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40,167,69,0.4);
}
.success { 
    color: #28a745; 
    margin-top: 15px; 
    font-weight: 600;
    padding: 12px 20px;
    background: #d4edda;
    border-left: 4px solid #28a745;
    border-radius: 8px;
}
.back-btn { 
    display: inline-block; 
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white; 
    padding: 10px 18px; 
    border-radius: 8px; 
    text-decoration: none; 
    margin-bottom: 20px; 
    font-weight: 500;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}
.back-btn:hover { 
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.4);
}
</style>
</head>

<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>👨‍🏫 Teacher Panel</h2>
    <a href="teacher_dashboard.php">🏠 Dashboard</a>
    <a href="view_students.php">👥 View Students</a>
    <a href="manage_attendance.php">📅 Manage Attendance</a>
    <a href="manage_marks.php">📊 Manage Marks</a>
    <a href="change_password.php">🔑 Change Password</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="container">
<a class="back-btn" href="manage_marks.php?class_id=<?= $exam['class_id'] ?>&subject_id=<?= $exam['subject_id'] ?>">⬅ Back to Results</a>

<h2>Add / Update Marks for <?= htmlspecialchars($exam['subject_name']) ?> — <?= htmlspecialchars($exam['class_name']) ?></h2>
<p>
Exam Date: <?= htmlspecialchars($exam['exam_date']) ?> |
Term: <?= htmlspecialchars($exam['term']) ?> |
Max Marks: <?= htmlspecialchars($exam['max_marks']) ?>
</p>

<?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

<form method="post">
<table>
<tr>
<th>Student ID</th>
<th>Student Name</th>
<th>Marks Obtained</th>
</tr>

<?php while ($student = $students->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($student['student_id']) ?></td>
<td><?= htmlspecialchars($student['name']) ?></td>
<td>
<input type="number"
       step="0.01"
       min="0"
       max="<?= $exam['max_marks'] ?>"
       name="marks[<?= htmlspecialchars($student['student_id']) ?>]"
       value="<?= $existing_marks[$student['student_id']] ?? '' ?>">
</td>
</tr>
<?php endwhile; ?>

</table>
<br>
<button type="submit" class="btn">Save Marks</button>
</form>
</div>

</body>
</html>
