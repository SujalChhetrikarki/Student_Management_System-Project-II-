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
  --sidebar-width: 240px;
  --primary: #2563eb;
  --dark: #0f172a;
  --bg: #f4f6f9;
  --card: #fff;
}

/* ===== Base ===== */
body{
  margin: 0;
  font-family: "Segoe UI", Arial, sans-serif;
  background: var(--bg);
  display: flex;
}

/* ===== Sidebar ===== */
.sidebar{
  width: var(--sidebar-width);
  background: var(--dark);
  color: #fff;
  height: 100vh;
  position: fixed;
  padding-top: 20px;
  display: flex;
  flex-direction: column;
}

.sidebar h2{
  text-align: center;
  margin-bottom: 30px;
  font-size: 20px;
  color: #60a5fa;
}

.sidebar a{
  display: block;
  padding: 12px 18px;
  margin: 8px 15px;
  color: #e5e7eb;
  text-decoration: none;
  border-radius: 10px;
  transition: 0.3s;
}

.sidebar a:hover{
  background: #1e293b;
}

.sidebar a.logout{
  background: #7f1d1d;
}

.sidebar a.logout:hover{
  background: #dc2626;
}

/* ===== Main ===== */
.main{
  margin-left: var(--sidebar-width);
  padding: 100px 30px 30px;
  width: calc(100% - var(--sidebar-width));
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

<!-- ===== Sidebar ===== -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">🏠 Home</a>
    <a href="../Admin/Manage_student/Managestudent.php">📚 Manage Students</a>
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
    <a href="./classes/classes.php">🏫 Manage Classes</a>
    <a href="subjects.php">📖 Manage Subjects</a>
    <a href="Managebook.php">📚 Manage Books</a>
    <a href="add_student.php">➕ Add Student</a>
    <a href="add_teacher.php">➕ Add Teacher</a>
    <a href="./Add_exam/add_exam.php">➕ Add Exam</a>
    <a href="admin_approve_results.php">✅ Approve Results</a>
    <a href="logout.php" class="logout">🚪 Logout</a>
</div>

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
