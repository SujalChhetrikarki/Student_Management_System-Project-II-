<?php
session_start();
if (!isset($_SESSION['teacher_id'])) {
    header("Location: teacher_login.php");
    exit;
}
include '../Database/db_connect.php';
$teacher_id = $_SESSION['teacher_id'];
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM teachers WHERE teacher_id = ?");
    $stmt->bind_param("s", $teacher_id);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hashed)) {
        $message = "❌ Current password is incorrect!";
    } elseif ($new !== $confirm) {
        $message = "⚠ New passwords do not match!";
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE teachers SET password = ? WHERE teacher_id = ?");
        $update->bind_param("ss", $new_hash, $teacher_id);
        if ($update->execute()) {
            $message = "✅ Password changed successfully!";
        } else {
            $message = "❌ Error updating password.";
        }
        $update->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher - Change Password</title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f9fafc;
    margin: 0;
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
    max-width: 600px;
    padding: 30px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
h2 {
    color: #0066cc;
    text-align: center;
    margin-bottom: 30px;
    font-size: 26px;
    padding-bottom: 15px;
    border-bottom: 3px solid #0066cc;
}
label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}
input {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 20px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.3s;
    box-sizing: border-box;
}
input:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0,102,204,0.1);
}
button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,102,204,0.3);
    margin-top: 10px;
}
button:hover {
    background: linear-gradient(135deg, #0052a3 0%, #004085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,102,204,0.4);
}
.msg {
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
    padding: 12px 20px;
    border-radius: 8px;
}
.success { 
    color: #155724; 
    background: #d4edda;
    border-left: 4px solid #28a745;
}
.error { 
    color: #721c24; 
    background: #f8d7da;
    border-left: 4px solid #dc3545;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    text-decoration: none;
    color: #0066cc;
    font-weight: 500;
    padding: 10px;
    border-radius: 6px;
    transition: background 0.3s;
}
.back-link:hover {
    background: #f0f7ff;
    text-decoration: none;
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
    <h2>Change Password</h2>

    <?php if ($message): ?>
        <div class="msg <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
            <?= htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Update Password</button>
    </form>

    <a href="teacher_dashboard.php" class="back-link">⬅ Back to Dashboard</a>
</div>

</body>
</html>
