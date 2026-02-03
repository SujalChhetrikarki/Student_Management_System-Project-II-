<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';

$teacher_id = $_SESSION['teacher_id'];
$class_id   = $_GET['class_id'] ?? 0;
$subject_id = $_GET['subject_id'] ?? 0;

// ✅ Fetch class & subject name for header
$stmt_info = $conn->prepare("
    SELECT c.class_name, s.subject_name
    FROM classes c 
    JOIN subjects s 
    WHERE c.class_id = ? AND s.subject_id = ?
");
$stmt_info->bind_param("ii", $class_id, $subject_id);
$stmt_info->execute();
$info_result = $stmt_info->get_result();
$info = $info_result->fetch_assoc();
$stmt_info->close();

// ✅ Fetch exams assigned to this teacher
$stmt = $conn->prepare("
    SELECT e.exam_id, e.exam_date, e.max_marks, e.term
    FROM exams e
    JOIN class_subject_teachers cst
        ON cst.class_id = e.class_id 
       AND cst.subject_id = e.subject_id
    WHERE e.class_id = ? 
      AND e.subject_id = ? 
      AND cst.teacher_id = ?
    ORDER BY e.exam_date DESC
");
$stmt->bind_param("iii", $class_id, $subject_id, $teacher_id);
$stmt->execute();
$exams = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Marks - <?= htmlspecialchars($info['subject_name']) ?></title>
<style>
/* ===== General Styles ===== */
body {
    font-family: "Segoe UI", Arial, sans-serif;
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
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
h2 {
    color: #0066cc;
    margin-bottom: 20px;
}

h2 {
    color: #0066cc;
    margin-bottom: 25px;
    font-size: 24px;
    padding-bottom: 10px;
    border-bottom: 3px solid #0066cc;
}

/* ===== Table Styles ===== */
.table-wrapper {
    overflow-x: auto;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 600px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
}
th, td {
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
td a.btn {
    display: inline-block;
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(40,167,69,0.3);
}
td a.btn:hover {
    background: linear-gradient(135deg, #218838 0%, #1e7e34 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40,167,69,0.4);
}

/* ===== Notes / Messages ===== */
.note {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border-left: 5px solid #ffc107;
    padding: 15px 20px;
    border-radius: 8px;
    color: #856404;
    margin-top: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    font-weight: 500;
}

/* ===== Back Button ===== */
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

<!-- Main Container -->
<div class="container">

    <a class="back-btn" href="teacher_dashboard.php">⬅ Back to Dashboard</a>

    <h2>Manage Marks for <?= htmlspecialchars($info['class_name']); ?> — <?= htmlspecialchars($info['subject_name']); ?></h2>

    <?php if ($exams->num_rows > 0): ?>
    <div class="table-wrapper">
        <table>
            <tr>
                <th>Exam ID</th>
                <th>Date</th>
                <th>Term</th>
                <th>Max Marks</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $exams->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['exam_id']); ?></td>
                <td><?= htmlspecialchars($row['exam_date']); ?></td>
                <td><?= htmlspecialchars($row['term']); ?></td>
                <td><?= htmlspecialchars($row['max_marks']); ?></td>
                <td>
                    <a class="btn" href="add_marks.php?exam_id=<?= $row['exam_id']; ?>">Add / Update Marks</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php else: ?>
        <p class="note">⚠️ No exams assigned yet for this subject.</p>
    <?php endif; ?>

</div>
</body>
</html>
