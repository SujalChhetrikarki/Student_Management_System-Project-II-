<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

// ✅ Check DB connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// ✅ Fetch Notices
$notice_sql = "
    SELECT title, message, created_at 
    FROM notices 
    WHERE target IN ('students', 'both')
    ORDER BY created_at DESC 
    LIMIT 5
";
$notices = $conn->query($notice_sql);

// ✅ Fetch student info
$sql = "SELECT s.student_id, s.name, s.email, s.date_of_birth, s.gender, c.class_name
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE s.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['student_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// ✅ Fetch attendance summary
$attendance_sql = "SELECT date, status FROM attendance WHERE student_id = ? ORDER BY date ASC";
$stmt2 = $conn->prepare($attendance_sql);
$attendance_data = [];
if ($stmt2) {
    $stmt2->bind_param("s", $_SESSION['student_id']);
    $stmt2->execute();
    $res = $stmt2->get_result();
    while ($row = $res->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $stmt2->close();
}

$present = $absent = $late = 0;
foreach ($attendance_data as $a) {
    switch (strtolower($a['status'])) {
        case 'present': $present++; break;
        case 'absent': $absent++; break;
        case 'late': $late++; break;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fc;
            color: #333;
        }
        .sidebar {
            width: 240px;
            background: #00bfff;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            padding: 20px 15px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            padding: 12px 15px;
            margin-bottom: 10px;
            text-decoration: none;
            border-radius: 10px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
        }
        .sidebar a.logout {
            margin-top: auto;
            background: #dc3545;
        }
        .sidebar a.logout:hover {
            background: #c82333;
        }
        .main {
            margin-left: 240px;
            padding: 30px;
        }
        .header {
            background: linear-gradient(135deg, #00bfff 0%, #0099cc 100%);
            color: #fff;
            padding: 20px 25px;
            border-radius: 12px;
            font-size: 22px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,191,255,0.3);
            font-weight: 600;
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
        .notice-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-left: 4px solid #00bfff;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .notice-card:hover {
            transform: translateX(5px);
        }
        .notice-card h3 {
            margin: 0 0 8px 0;
            font-size: 17px;
            color: #00bfff;
            font-weight: 600;
        }
        .notice-card p {
            margin: 8px 0;
            color: #555;
            line-height: 1.6;
        }
        .notice-card small {
            color: #888;
            font-size: 13px;
        }
        .chart-container {
            width: 300px;
            height: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📚 Dashboard</h2>
        <a href="student_dashboard.php">🏠 Home</a>
        <a href="attendance.php">📅 Attendance</a>
        <a href="results.php">📊 Results</a>
        <a href="profile.php">👤 Profile</a>
        <a href="change_password.php">🔑 Change Password</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="header">🎓 Welcome, <?php echo htmlspecialchars($student['name']); ?></div>

        <div class="card">
            <h2>📌 Student Information</h2>
            <table>
                <tr><th>Student ID</th><td><?php echo htmlspecialchars($student['student_id']); ?></td></tr>
                <tr><th>Name</th><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
                <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($student['date_of_birth']); ?></td></tr>
                <tr><th>Gender</th><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
                <tr><th>Class</th><td><?php echo htmlspecialchars($student['class_name']); ?></td></tr>
            </table>
        </div>

        <div class="card">
            <h2>📢 Latest Notices</h2>
            <?php
            if ($notices && $notices->num_rows > 0) {
                while ($notice = $notices->fetch_assoc()) {
                    echo "<div class='notice-card'>";
                    echo "<h3>" . htmlspecialchars($notice['title']) . "</h3>";
                    echo "<p>" . nl2br(htmlspecialchars($notice['message'])) . "</p>";
                    echo "<small>🕒 " . date('d M Y, h:i A', strtotime($notice['created_at'])) . "</small>";
                    echo "</div>";
                }
            } else {
                echo "<p>No new notices.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
