<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}

include '../Database/db_connect.php';
$teacher_id = $_SESSION['teacher_id'];

// 1️⃣ Fetch Teacher Details
$stmt = $conn->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
if (!$stmt) die("SQL Error: " . $conn->error);
$stmt->bind_param("s", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// 2️⃣ Fetch Notices
$notices = $conn->query("
    SELECT title, message, created_at 
    FROM notices 
    WHERE target IN ('teachers', 'both')
    ORDER BY created_at DESC 
    LIMIT 5
");

// 3️⃣ Fetch Classes
$stmt_classes = $conn->prepare("
    SELECT c.class_id, c.class_name, c.class_teacher_id
    FROM classes c
    JOIN class_teachers ct ON c.class_id = ct.class_id
    WHERE ct.teacher_id = ?
");
$stmt_classes->bind_param("s", $teacher_id);
$stmt_classes->execute();
$classes = $stmt_classes->get_result();
$stmt_classes->close();

// 4️⃣ Fetch Subjects
$stmt_subjects = $conn->prepare("
    SELECT s.subject_id, s.subject_name, c.class_id, c.class_name
    FROM class_subject_teachers cst
    JOIN subjects s ON cst.subject_id = s.subject_id
    JOIN classes c ON cst.class_id = c.class_id
    WHERE cst.teacher_id = ?
");
$stmt_subjects->bind_param("s", $teacher_id);
$stmt_subjects->execute();
$subjects = $stmt_subjects->get_result();
$stmt_subjects->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Dashboard</title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f9fafc;
    margin: 0;
    padding: 0;
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
    background: #f9fafc;
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
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
}
.section {
    margin-bottom: 30px;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.section h2 {
    border-bottom: 3px solid #0066cc;
    padding-bottom: 10px;
    margin-bottom: 20px;
    color: #0066cc;
    font-size: 22px;
}
.profile-box {
    background: linear-gradient(135deg, #eef4ff 0%, #e0f2fe 100%);
    padding: 20px 25px;
    border-radius: 10px;
    line-height: 1.8;
    border-left: 4px solid #0066cc;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.profile-box p {
    margin: 8px 0;
    font-size: 15px;
}
.profile-box strong {
    color: #0066cc;
    font-weight: 600;
}
.notice {
    background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
    border-left: 5px solid #ffc107;
    padding: 15px 20px;
    margin-bottom: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}
.notice:hover {
    transform: translateX(5px);
}
.notice h4 {
    margin: 0 0 8px 0;
    font-size: 17px;
    color: #856404;
}
.notice p {
    margin: 5px 0;
    color: #6c5700;
    line-height: 1.6;
}
.notice small {
    color: #856404;
    font-size: 13px;
}
.table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 15px;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.table th, .table td {
    border: none;
    padding: 14px 16px;
    text-align: left;
}
.table th {
    background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
    color: #fff;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
}
.table tr:nth-child(even) {
    background: #f8f9fa;
}
.table tr:hover {
    background: #e6f0ff;
    transition: background 0.2s;
}
.btn {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    display: inline-block;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}
.btn:hover {
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.4);
}
.status {
    font-weight: 600;
    color: #28a745;
    padding: 4px 10px;
    background: #d4edda;
    border-radius: 12px;
    font-size: 12px;
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

    <!-- Profile -->
    <div class="section">
        <h2>Your Profile</h2>
        <div class="profile-box">
            <p><strong>Name:</strong> <?= htmlspecialchars($teacher['name']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($teacher['email']); ?></p>
            <p><strong>Specialization:</strong> <?= htmlspecialchars($teacher['specialization']); ?></p>
        </div>
    </div>

    <!-- Notices -->
    <div class="section">
        <h2>Recent Notices</h2>
        <?php
        if ($notices && $notices->num_rows > 0):
            while ($n = $notices->fetch_assoc()):
        ?>
            <div class="notice">
                <h4><?= htmlspecialchars($n['title']); ?></h4>
                <p><?= nl2br(htmlspecialchars($n['message'])); ?></p>
                <small>Posted on: <?= date('d M Y, h:i A', strtotime($n['created_at'])); ?></small>
            </div>
        <?php
            endwhile;
        else:
            echo "<p>No notices available.</p>";
        endif;
        ?>
    </div>
    <!-- Classes -->
    <div class="section">
        <h2>Your Classes</h2>
        <?php if ($classes->num_rows > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Class ID</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $classes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['class_name']); ?></td>
                    <td><?= $row['class_id']; ?></td>
                    <td>
                        <?= ($row['class_teacher_id'] === $teacher_id)
                            ? "<span class='status'>Class Teacher</span>"
                            : "Subject Teacher"; ?>
                    </td>
                    <td>
                            <a class="btn" href="view_students.php?class_id=<?= $row['class_id']; ?>">View Students</a>
                            <a class="btn" href="manage_attendance.php?class_id=<?= $row['class_id']; ?>">Manage Attendance</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No classes assigned yet.</p>
        <?php endif; ?>
    </div>

<div class="section">
    <h2>Your Subjects</h2>
    <?php if ($subjects->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $subjects->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']); ?></td>
                <td><?= htmlspecialchars($row['class_name']); ?></td>
                <td>
                    <a class="btn" 
                       href="manage_marks.php?class_id=<?= $row['class_id']; ?>&subject_id=<?= $row['subject_id']; ?>">
                       Manage Marks
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No subjects assigned yet.</p>
    <?php endif; ?>
</div>
<td>
    <a class="btn" href="change_password.php">Change Password</a>
</td>
</div>
</body>
</html>
