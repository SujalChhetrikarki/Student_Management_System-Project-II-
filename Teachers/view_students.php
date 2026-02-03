<?php
session_start();
include '../Database/db_connect.php';

// Get class_id from request (GET or POST)
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    die("Class ID not provided.");
}

// Fetch class name
$sql = "SELECT class_name FROM classes WHERE class_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$classRow = $result->fetch_assoc();
$class_name = $classRow['class_name'] ?? 'Unknown Class';

// Fetch students in this class
$sqlStudents = "SELECT student_id, name FROM students WHERE class_id = ?";
$stmtStudents = $conn->prepare($sqlStudents);
$stmtStudents->bind_param("i", $class_id);
$stmtStudents->execute();
$students = $stmtStudents->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Students in <?= htmlspecialchars($class_name); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
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
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0066cc;
            margin-bottom: 25px;
            font-size: 24px;
            padding-bottom: 10px;
            border-bottom: 3px solid #0066cc;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px 20px;
            margin: 10px 0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            border-left: 4px solid #0066cc;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        ul li:hover {
            background: linear-gradient(135deg, #e6f0ff 0%, #d0e7ff 100%);
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        ul li small {
            color: #6c757d;
            font-size: 13px;
            margin-left: 10px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,102,204,0.3);
        }
        .btn:hover {
            background: linear-gradient(135deg, #0052a3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,102,204,0.4);
        }
        .empty {
            color: #6c757d;
            font-style: italic;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px dashed #dee2e6;
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
        <h2>Students in <?= htmlspecialchars($class_name); ?> (ID: <?= htmlspecialchars($class_id); ?>)</h2>

        <?php if ($students->num_rows > 0): ?>
            <ul>
                <?php while ($row = $students->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($row['name']); ?> <small>(ID: <?= $row['student_id']; ?>)</small></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="empty">No students in this class.</p>
        <?php endif; ?>

        <a href="teacher_dashboard.php" class="btn">⬅ Back to teacher dashboard</a>
    </div>

</body>
</html>
