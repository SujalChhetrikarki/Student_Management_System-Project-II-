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
   ADD SUBJECT
========================= */
if (isset($_POST['add_subject'])) {
    $new_subject = trim($_POST['new_subject']);
    $class_id = intval($_POST['class_id']);

    if (!empty($new_subject) && $class_id > 0) {
        $stmt = $conn->prepare(
            "INSERT INTO subjects (subject_name, class_id) VALUES (?, ?)"
        );
        $stmt->bind_param("si", $new_subject, $class_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Subject added successfully!";
        } else {
            $_SESSION['error'] = "Error adding subject: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Please enter subject name and select a class.";
    }

    header("Location: subjects.php");
    exit;
}

/* =========================
   DELETE SUBJECT
========================= */
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM subjects WHERE subject_id = $del_id");
    $_SESSION['success'] = "Subject deleted successfully!";
    header("Location: subjects.php");
    exit;
}

/* =========================
   FETCH SUBJECTS WITH CLASS
========================= */
$subjects = $conn->query("
    SELECT s.subject_id, s.subject_name, c.class_name
    FROM subjects s
    JOIN classes c ON s.class_id = c.class_id
    ORDER BY s.subject_id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Subjects</title>
<style>
:root{
  --primary: #2563eb;
  --bg: #f1f5f9;
  --card: #ffffff;
  --text: #1f2937;
  --nav-bg: #ffffff;
  --nav-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* ===== Base ===== */
body{
  margin: 0;
  font-family: "Segoe UI", Arial, sans-serif;
  background: var(--bg);
  color: var(--text);
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
  display: flex;
  justify-content: center;
}

/* ===== Container ===== */
.container{
  width: 700px;
  background: var(--card);
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
}

/* ===== Forms ===== */
form label{
  font-weight: 500;
  display: block;
  margin-top: 15px;
  margin-bottom: 5px;
}

form input, form select{
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

form button{
  margin-top: 15px;
  width: 100%;
  padding: 12px;
  border: none;
  border-radius: 8px;
  background: var(--primary);
  color: #fff;
  font-size: 16px;
  cursor: pointer;
}

form button:hover{
  background: #1d4ed8;
}

/* ===== Table ===== */
table{
  width: 100%;
  border-collapse: collapse;
  margin-top: 25px;
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
  background: #f8fafc;
}

/* ===== Action Buttons ===== */
a.delete{
  background: #ef4444;
  color: #fff;
  padding: 6px 12px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
}

a.delete:hover{
  background: #dc2626;
}

/* ===== Alerts ===== */
.success{ color: green; margin-bottom: 10px; font-weight: 500;}
.error{ color: red; margin-bottom: 10px; font-weight: 500; }
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

<div class="main">
<div class="container">

<?php if(isset($_SESSION['success'])): ?>
<p class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<p class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

<!-- ADD SUBJECT FORM -->
<form method="POST">
    <label>Subject Name</label>
    <input type="text" name="new_subject" required>

    <label>Class</label>
    <select name="class_id" required>
        <option value="">Select Class</option>
        <?php while($c = $classes->fetch_assoc()): ?>
            <option value="<?= $c['class_id']; ?>">
                <?= htmlspecialchars($c['class_name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit" name="add_subject">Add Subject</button>
</form>

<!-- SUBJECT LIST -->
<table>
<tr>
    <th>ID</th>
    <th>Subject</th>
    <th>Class</th>
    <th>Action</th>
</tr>

<?php if($subjects->num_rows > 0): ?>
<?php while($s = $subjects->fetch_assoc()): ?>
<tr>
    <td><?= $s['subject_id']; ?></td>
    <td><?= htmlspecialchars($s['subject_name']); ?></td>
    <td><?= htmlspecialchars($s['class_name']); ?></td>
    <td>
        <a class="delete"
           href="subjects.php?delete_id=<?= $s['subject_id']; ?>"
           onclick="return confirm('Delete this subject?');">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="4" style="text-align:center;">No subjects found</td></tr>
<?php endif; ?>
</table>

</div>
</div>

</body>
</html>
