<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';

/* =========================
   FETCH CLASSES
========================= */
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name");

/* =========================
   FETCH SUBJECTS BY CLASS
========================= */
$subjects = null;
$selected_class = '';

if (isset($_GET['class_id']) && $_GET['class_id'] !== '') {
    $selected_class = intval($_GET['class_id']);

    $stmt = $conn->prepare("
        SELECT subject_id, subject_name
        FROM subjects
        WHERE class_id = ?
        ORDER BY subject_name
    ");
    $stmt->bind_param("i", $selected_class);
    $stmt->execute();
    $subjects = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Class Wise Subjects</title>
<style>
:root{
  --primary: #2563eb;
  --bg: #f4f6f9;
  --card: #fff;
  --text: #1f2937;
  --nav-bg: #ffffff;
  --nav-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* ===== Base ===== */
body{
  margin: 0;
  font-family: "Segoe UI", Arial, sans-serif;
  background: var(--bg);
  padding-top: 70px;
}

/* ===== Modern Top Navigation ===== */
.top-nav{
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: var(--nav-bg);
  box-shadow: var(--nav-shadow);
  z-index: 1000;
  padding: 0 30px;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.nav-brand{
  font-size: 22px;
  font-weight: 700;
  color: var(--primary);
  text-decoration: none;
}

.nav-menu{
  display: flex;
  gap: 5px;
  align-items: center;
}

.nav-menu a{
  padding: 10px 18px;
  text-decoration: none;
  color: var(--text);
  border-radius: 8px;
  transition: all 0.3s;
  font-size: 14px;
  font-weight: 500;
}

.nav-menu a:hover{
  background: var(--bg);
  color: var(--primary);
}

.nav-menu a.logout{
  background: #dc2626;
  color: #fff;
  margin-left: 10px;
}

.nav-menu a.logout:hover{
  background: #b91c1c;
}

/* ===== Main ===== */
.main{
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

/* ===== Header ===== */
.header{
  background: var(--card);
  padding: 15px 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  margin-bottom: 20px;
  font-size: 22px;
  font-weight: 600;
  color: #1f2937;
}

/* ===== Card ===== */
.card{
  background: var(--card);
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

/* ===== Form ===== */
form label{
  font-weight: 500;
  display: block;
  margin-top: 10px;
  margin-bottom: 5px;
}

select{
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  margin-bottom: 20px;
}

/* ===== Table ===== */
table{
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

th, td{
  padding: 12px;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
}

th{
  background: var(--primary);
  color: #fff;
  font-weight: 600;
}

tr:hover{
  background: #f1f5f9;
}

.empty{
  text-align: center;
  padding: 20px;
  color: #777;
  font-size: 15px;
}
</style>
</head>

<body>

<!-- Modern Top Navigation -->
<nav class="top-nav">
  <a href="index.php" class="nav-brand">🎓 Admin Panel</a>
  <div class="nav-menu">
    <a href="index.php">🏠 Home</a>
    <a href="./Manage_student/Managestudent.php">📚 Students</a>
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Teachers</a>
    <a href="./classes/classes.php">🏫 Classes</a>
    <a href="subjects.php">📖 Subjects</a>
    <a href="Managebook.php">📚 Books</a>
    <a href="add_student.php">➕ Add Student</a>
    <a href="add_teacher.php">➕ Add Teacher</a>
    <a href="./Add_exam/add_exam.php">➕ Exam</a>
    <a href="admin_approve_results.php">✅ Results</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
  </div>
</nav>

<!-- ===== Main Content ===== -->
<div class="main">

    <div class="header">
        📖 Class-wise Subjects
    </div>

    <div class="card">

        <!-- Class Selection -->
        <form method="GET">
            <label><strong>Select Class</strong></label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="">-- Choose Class --</option>
                <?php while ($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['class_id']; ?>"
                        <?= ($selected_class == $c['class_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <!-- Subject Table -->
        <?php if ($subjects !== null): ?>
            <?php if ($subjects->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                    </tr>
                    <?php while ($s = $subjects->fetch_assoc()): ?>
                        <tr>
                            <td><?= $s['subject_id']; ?></td>
                            <td><?= htmlspecialchars($s['subject_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <div class="empty">
                    No subjects assigned to this class.
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
