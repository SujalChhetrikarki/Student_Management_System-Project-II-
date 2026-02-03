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
            color: #0066cc;
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
            background: #f4f6f9;
            color: #0066cc;
        }
        .nav-menu a.logout {
            background: #dc2626;
            color: #fff;
            margin-left: 10px;
        }
        .nav-menu a.logout:hover {
            background: #b91c1c;
        }
        .container {
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
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

<!-- Modern Top Navigation -->
<nav class="top-nav">
    <a href="teacher_dashboard.php" class="nav-brand">👨‍🏫 Teacher Panel</a>
    <div class="nav-menu">
        <a href="teacher_dashboard.php">🏠 Dashboard</a>
        <a href="view_students.php">👥 Students</a>
        <a href="manage_attendance.php">📅 Attendance</a>
        <a href="manage_marks.php">📊 Marks</a>
        <a href="change_password.php">🔑 Password</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>
</nav>

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
