<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php'; // DB connection

// Add Notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notice'])) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $target = $_POST['target'];

    if ($title && $message) {
        $stmt = $conn->prepare("INSERT INTO notices (title, message, target) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $message, $target);

        if ($stmt->execute()) {
            header("Location: manage_notices.php?success=1");
            exit;
        } else {
            header("Location: manage_notices.php?error=" . urlencode("Database error: " . $stmt->error));
            exit;
        }
    } else {
        header("Location: manage_notices.php?error=" . urlencode("Please fill in all fields."));
        exit;
    }
}

// Delete Notice
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($conn->query("DELETE FROM notices WHERE notice_id = $id")) {
        header("Location: manage_notices.php?deleted=1");
        exit;
    } else {
        header("Location: manage_notices.php?error=" . urlencode("Error deleting notice."));
        exit;
    }
}

// Feedback messages
$success = $error = "";
if (isset($_GET['success'])) $success = "✅ Notice added successfully!";
if (isset($_GET['deleted'])) $success = "🗑️ Notice deleted successfully!";
if (isset($_GET['error'])) $error = htmlspecialchars($_GET['error']);

// Fetch notices
$result = $conn->query("SELECT * FROM notices ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Notices</title>
<style>
:root {
    --sidebar-width: 240px;
    --primary: #00bfff;
    --sidebar-bg: #111; /* ✅ Sidebar color */
    --bg: #f4f6f9;
    --card: #fff;
}

/* ===== Body ===== */
body {
    margin:0;
    font-family: Arial, sans-serif;
    background: var(--bg);
    display:flex;
    color:#1f2937;
}

/* ===== Sidebar ===== */
.sidebar{
  width: var(--sidebar-width);
  background: var(--sidebar-bg);
  color:#fff;
  height:100vh;
  position:fixed;
  left:0;
  top:0;
  padding-top:20px;
  display:flex;
  flex-direction:column;
  z-index:1000;
}
.sidebar h2{
  text-align:center;
  margin-bottom:30px;
  font-size:20px;
  color:#60a5fa;
}
.sidebar a{
  display:block;
  padding:12px 18px;
  margin:8px 15px;
  color:#e5e7eb;
  text-decoration:none;
  border-radius:10px;
  transition:0.3s;
}
.sidebar a:hover{
  background: var(--primary);
  color:#fff;
}
.sidebar a.logout{
  background:#7f1d1d;
  margin-top:auto;
}
.sidebar a.logout:hover{
  background:#dc2626;
}

/* ===== Header ===== */
.header{
    position: fixed;
    top:0;
    left: var(--sidebar-width);
    right:0;
    height:70px;
    background: var(--primary);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    font-weight:600;
    z-index:900;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* ===== Main Content ===== */
.main{
    margin-left: var(--sidebar-width);
    padding: 100px 30px 30px 30px; /* space for header */
    width: calc(100% - var(--sidebar-width));
    display:flex;
    flex-direction:column;
    align-items:center;
    min-height:100vh;
}

/* Container */
.container{
    width: 100%;
    max-width: 900px;
    background: var(--card);
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Form */
form{
    display:flex;
    flex-direction: column;
    gap:12px;
}
input[type="text"], textarea, select{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}
button{
    background: var(--primary);
    color:#fff;
    border:none;
    padding:12px;
    border-radius:6px;
    cursor:pointer;
    font-weight:500;
    transition:0.3s;
}
button:hover{
    opacity:0.85;
}

/* Notices list */
.notice-list{
    margin-top:30px;
}
.notice-item{
    border:1px solid #ddd;
    border-radius:8px;
    padding:15px;
    margin-bottom:12px;
    background:#fafafa;
}
.notice-item h4{margin:0 0 5px; color:#333;}
.notice-item small{color:#666;}
.notice-item p{margin:8px 0;}
.notice-item a{color:#dc3545; text-decoration:none; font-weight:bold;}
.notice-item a:hover{text-decoration:underline;}

/* Alerts */
.alert{
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    text-align:center;
}
.success{background:#d4edda; color:#155724;}
.error{background:#f8d7da; color:#721c24;}

/* Back link */
.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    color: var(--primary);
    font-weight:bold;
}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="../Admin/index.php">🏠 Home</a>
  <a href="./Manage_student/Managestudent.php">📚 Manage Students</a>
  <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
  <a href="./classes/classes.php">🏫 Manage Classes</a>
  <a href="./subjects.php">📖 Manage Subjects</a>
  <a href="./Managebook.php">📚 Manage Books</a>
  <a href="./add_student.php">➕ Add Student</a>
  <a href="./add_teacher.php">➕ Add Teacher</a>
  <a href="./Add_exam/add_exam.php">➕ Add Exam</a>
  <a href="./admin_approve_results.php">✅ Approve Results</a>
  <a href="manage_notices.php">📢 Manage Notices</a>
  <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">📢 Manage Notices</div>

<div class="main">
    <div class="container">
        <a href="index.php" class="back">⬅ Back to Dashboard</a>
        <h2>📢 Manage Notices</h2>

        <?php if ($success) echo "<div class='alert success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>

        <form method="POST">
            <input type="text" name="title" placeholder="Enter notice title" required>
            <textarea name="message" rows="4" placeholder="Write your notice message here..." required></textarea>
            <select name="target" required>
                <option value="both">All (Students & Teachers)</option>
                <option value="students">Students Only</option>
                <option value="teachers">Teachers Only</option>
            </select>
            <button type="submit" name="add_notice">Post Notice</button>
        </form>

        <div class="notice-list">
            <h3>📄 Existing Notices</h3>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($n = $result->fetch_assoc()): ?>
                    <div class="notice-item">
                        <h4><?= htmlspecialchars($n['title']); ?></h4>
                        <small>🕒 <?= htmlspecialchars($n['created_at']); ?> | 🎯 <?= ucfirst($n['target']); ?></small>
                        <p><?= nl2br(htmlspecialchars($n['message'])); ?></p>
                        <a href="?delete=<?= $n['notice_id']; ?>" onclick="return confirm('Delete this notice?')">🗑️ Delete</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No notices available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
