<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

// Get student ID
$student_id = $_SESSION['student_id'];

// =======================
// 1️⃣ Fetch Student Info
// =======================
$sql_student = "
    SELECT s.student_id, s.name, s.email, s.date_of_birth, s.gender, c.class_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.class_id
    WHERE s.student_id = ?
";
$stmt = $conn->prepare($sql_student);
if (!$stmt) {
    die("SQL Prepare failed (student): " . $conn->error);
}
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found.");
}

// =======================
// 2️⃣ Fetch Attendance Summary
// =======================
$stmt2 = $conn->prepare("
    SELECT status FROM attendance WHERE student_id = ?
");
$stmt2->bind_param("s", $student_id);
$stmt2->execute();
$res = $stmt2->get_result();

$present = $absent = $late = 0;
while ($row = $res->fetch_assoc()) {
    switch (strtolower($row['status'])) {
        case 'present': $present++; break;
        case 'absent': $absent++; break;
        case 'late': $late++; break;
    }
}
$stmt2->close();

// =======================
// 3️⃣ Fetch Academic Performance
// =======================
$stmt3 = $conn->prepare("
    SELECT AVG(r.marks_obtained) AS avg_marks
    FROM results r
    JOIN exams e ON r.exam_id = e.exam_id
    WHERE r.student_id = ? AND r.status = 'Approved'
");
$stmt3->bind_param("s", $student_id);
$stmt3->execute();
$result = $stmt3->get_result();
$performance = $result->fetch_assoc();
$average_marks = $performance['avg_marks'] ?? 0;
$stmt3->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>👤 Student Profile</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
.main {
    padding: 30px;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
.card {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}
.card h2 {
    margin-top: 0;
    color: #00bfff;
    font-size: 20px;
    border-bottom: 3px solid #00bfff;
    padding-bottom: 12px;
    font-weight: 600;
}
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
    border-radius: 8px;
    overflow: hidden;
}
th, td {
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    text-align: left;
}
th {
    background: linear-gradient(135deg, #00bfff 0%, #0099cc 100%);
    color: #fff;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
}
tr:nth-child(even) {
    background: #f8f9fa;
}
tr:hover {
    background: #e6f7ff;
    transition: background 0.2s;
}
.chart-container {
    width: 300px;
    height: 300px;
    margin: 0 auto;
}
.performance-box {
    text-align: center;
    font-size: 18px;
    padding: 10px;
}
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

    <div class="main">
        <div class="card">
            <h2>👤 Student Profile</h2>
            <table>
                <tr><th>Student ID</th><td><?= htmlspecialchars($student['student_id']) ?></td></tr>
                <tr><th>Name</th><td><?= htmlspecialchars($student['name']) ?></td></tr>
                <tr><th>Email</th><td><?= htmlspecialchars($student['email']) ?></td></tr>
                <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['date_of_birth']) ?></td></tr>
                <tr><th>Gender</th><td><?= htmlspecialchars($student['gender']) ?></td></tr>
                <tr><th>Class</th><td><?= htmlspecialchars($student['class_name']) ?></td></tr>
            </table>
        </div>

        <div class="card">
            <h2>📊 Attendance Overview</h2>
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <div class="card performance-box">
            <h2>🎯 Academic Performance Summary</h2>
            <p><strong>Average Marks:</strong> <?= number_format($average_marks, 2) ?>%</p>
        </div>
    </div>

<script>
const ctx = document.getElementById('attendanceChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Present', 'Absent', 'Late'],
        datasets: [{
            data: [<?= $present ?>, <?= $absent ?>, <?= $late ?>],
            backgroundColor: [
                'rgba(40, 167, 69, 0.7)',
                'rgba(220, 53, 69, 0.7)',
                'rgba(255, 193, 7, 0.7)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(255, 193, 7, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
</body>
</html>
