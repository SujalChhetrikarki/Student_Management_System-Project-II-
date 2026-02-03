<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

$student_id = $_SESSION['student_id'];

// Fetch student info
$stmt_student = $conn->prepare("
    SELECT s.name, c.class_name 
    FROM students s 
    JOIN classes c ON s.class_id=c.class_id 
    WHERE s.student_id=?
");
$stmt_student->bind_param("s", $student_id);
$stmt_student->execute();
$student = $stmt_student->get_result()->fetch_assoc();
if (!$student) die("Student not found.");

// Fetch attendance records
$sql_attendance = "
    SELECT a.date, sub.subject_name, a.status
    FROM attendance a
    JOIN subjects sub ON a.subject_id=sub.subject_id
    WHERE a.student_id=?
    ORDER BY a.date DESC
";
$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("s", $student_id);
$stmt_attendance->execute();
$attendance = $stmt_attendance->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Attendance</title>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #f8f9fc;
    color: #333;
    padding-top: 70px;
}

/* Modern Top Navigation */
.top-nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: 0 30px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nav-brand {
    font-size: 22px;
    font-weight: 700;
    color: #00bfff;
    text-decoration: none;
}
.nav-menu {
    display: flex;
    gap: 5px;
    align-items: center;
}
.nav-menu a {
    padding: 10px 18px;
    text-decoration: none;
    color: #333;
    border-radius: 8px;
    transition: all 0.3s;
    font-size: 14px;
    font-weight: 500;
}
.nav-menu a:hover {
    background: #f8f9fc;
    color: #00bfff;
}
.nav-menu a.logout {
    background: #dc3545;
    color: #fff;
    margin-left: 10px;
}
.nav-menu a.logout:hover {
    background: #c82333;
}

/* ✅ Main content wrapper */
.container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 70px);
    background: #f8f9fc;
    padding: 20px;
}

/* ✅ Inner card - centers your attendance table */
.content-box {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    width: 80%;
    max-width: 900px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.content-box h2 {
    text-align: center;
    color: #007bff;
    margin-top: 0;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 20px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
th, td {
    padding: 14px 18px;
    border-bottom: 1px solid #eee;
    text-align: center;
}
th {
    background: linear-gradient(135deg, #00bfff 0%, #0099cc 100%);
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
    background: #e6f7ff;
    transition: background 0.2s;
}
a {
    color: #00bfff;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}
a:hover {
    color: #0099cc;
    text-decoration: underline;
}
.content-box h2 {
    color: #00bfff;
    font-size: 24px;
    padding-bottom: 10px;
    border-bottom: 3px solid #00bfff;
}


    </style>
</style>
</head>
<body>
    <!-- Modern Top Navigation -->
    <nav class="top-nav">
        <a href="student_dashboard.php" class="nav-brand">🎓 Student Portal</a>
        <div class="nav-menu">
            <a href="student_dashboard.php">🏠 Home</a>
            <a href="attendance.php">📅 Attendance</a>
            <a href="results.php">📊 Results</a>
            <a href="profile.php">👤 Profile</a>
            <a href="change_password.php">🔑 Password</a>
            <a href="logout.php" class="logout">🚪 Logout</a>
        </div>
    </nav>

<!-- Content -->
<div class="container">
    <div class="content-box">
        <h2>📋 My Attendance Records</h2>
        <p style="text-align:center;">
            <strong>Student:</strong> <?= htmlspecialchars($student['name']); ?> | 
            <strong>Class:</strong> <?= htmlspecialchars($student['class_name']); ?>
        </p>

        <table>
            <tr>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php if($attendance->num_rows == 0): ?>
                <tr><td colspan="3">No attendance records available yet.</td></tr>
            <?php else: ?>
                <?php while ($a = $attendance->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['date']); ?></td>
                        <td><?= htmlspecialchars($a['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>

        <p style="text-align:center; margin-top:20px;">
            <a href="student_dashboard.php">⬅ Back to Dashboard</a>
        </p>
    </div>
</div>

</body>
</html>
