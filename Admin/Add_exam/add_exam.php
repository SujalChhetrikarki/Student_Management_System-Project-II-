<?php
session_start();
include '../../Database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

// Handle Add Exam Form
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_exam'])) {
    $class_ids = isset($_POST['class_id']) ? (array)$_POST['class_id'] : [];
    $subject_ids = isset($_POST['subject_id']) ? (array)$_POST['subject_id'] : [];
    $exam_date = $_POST['exam_date'];
    $max_marks = $_POST['max_marks'];
    $term = $_POST['term'];

    $inserted = 0;
    foreach ($class_ids as $class_id) {
        foreach ($subject_ids as $subject_id) {
            $sql = "INSERT INTO exams (subject_id, class_id, exam_date, max_marks, term) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iisis", $subject_id, $class_id, $exam_date, $max_marks, $term);
                if ($stmt->execute()) $inserted++;
            }
        }
    }
    header("Location: add_exam.php?msg=" . urlencode("✅ {$inserted} Exam(s) added successfully!"));
    exit;
}

// Fetch classes and subjects
$classes = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
$subjects = $conn->query("SELECT subject_id, subject_name FROM subjects ORDER BY subject_name");

// Fetch all exams
$exams_result = $conn->query("
    SELECT e.exam_id, e.exam_date, e.max_marks, e.term, c.class_name, s.subject_name
    FROM exams e
    JOIN classes c ON e.class_id=c.class_id
    JOIN subjects s ON e.subject_id=s.subject_id
    ORDER BY e.term, e.exam_date ASC
");

$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Add Exams</title>
<style>
:root{
  --sidebar-width: 240px;
  --primary: #2563eb;
  --dark: #0f172a;
  --bg: #f1f5f9;
  --card: #ffffff;
}

/* ===== Base ===== */
body{
  margin:0;
  font-family:"Segoe UI", Arial, sans-serif;
  display:flex;
  background: var(--bg);
  color:#1f2937;
}

/* ===== Sidebar ===== */
.sidebar{
  width: var(--sidebar-width);
  background: var(--dark);
  color:#fff;
  height:100vh;
  position:fixed;
  left:0;
  top:0;
  padding-top:20px;
  display:flex;
  flex-direction:column;
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
.sidebar a:hover{background:#1e293b;}
.sidebar a.logout{background:#7f1d1d;}
.sidebar a.logout:hover{background:#dc2626;}

/* ===== Header ===== */
.header{
  position: fixed;
  top:0;
  left: var(--sidebar-width);
  right:0;
  height:80px;
  background: var(--primary);
  color:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:22px;
  font-weight:600;
  z-index:10;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* ===== Main ===== */
.main{
  margin-left: var(--sidebar-width);
  padding: 100px 30px 30px 30px;
  width: calc(100% - var(--sidebar-width));
  display:flex;
  flex-direction:column;
  align-items:center;
}

/* ===== Container ===== */
.container{
  width: 700px;
  background: var(--card);
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
  margin-bottom: 40px;
}

/* ===== Form ===== */
form label{display:block; font-weight:500; margin-top:12px; margin-bottom:5px;}
form input, form select{
  width:100%;
  padding:10px;
  border-radius:8px;
  border:1px solid #ccc;
  font-size:14px;
}
form button{
  margin-top:20px;
  width:100%;
  padding:12px;
  border:none;
  border-radius:8px;
  background: var(--primary);
  color:#fff;
  font-size:16px;
  cursor:pointer;
  transition:0.3s;
}
form button:hover{background:#1d4ed8;}

/* ===== Messages ===== */
.msg{ text-align:center; margin-bottom:15px; font-weight:500;}
.error{ color:red; }
.success{ color:green; }

/* ===== Table ===== */
table{
  width:100%;
  border-collapse: collapse;
  margin-top:25px;
  border-radius:10px;
  overflow:hidden;
  background:#fff;
}
th, td{
  padding:12px;
  text-align:center;
  border-bottom:1px solid #e5e7eb;
}
th{
  background: var(--primary);
  color:#fff;
  font-weight:600;
}
tr:hover{background:#f8fafc;}
a.edit, a.delete{
  padding:5px 10px;
  border-radius:6px;
  color:#fff;
  text-decoration:none;
  font-size:14px;
}
a.edit{background:#10b981;}
a.edit:hover{background:#059669;}
a.delete{background:#ef4444;}
a.delete:hover{background:#dc2626;}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="../index.php">🏠 Home</a>
  <a href="../Manage_student/Managestudent.php">📚 Manage Students</a>
  <a href="../Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
  <a href="../classes/classes.php">🏫 Manage Classes</a>
  <a href="../subjects.php">📖 Manage Subjects</a>
  <a href="../Managebook.php">📚 Manage Books</a>
  <a href="../add_student.php">➕ Add Student</a>
  <a href="../add_teacher.php">➕ Add Teacher</a>
  <a href="add_exam.php">➕ Add Exam</a>
  <a href="../admin_approve_results.php">✅ Approve Results</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">➕ Add Term-wise Exam</div>

<div class="main">
  <div class="container">
    <?php if($msg) echo "<p class='msg success'>{$msg}</p>"; ?>
    <form method="POST">
      <label>Select Class(es)</label>
      <select name="class_id[]" multiple required>
        <?php if ($classes && $classes->num_rows > 0): ?>
          <?php while($c=$classes->fetch_assoc()): ?>
            <option value="<?= $c['class_id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
          <?php endwhile; ?>
        <?php else: ?>
          <option disabled>No Classes Available</option>
        <?php endif; ?>
      </select>

      <label>Select Subject(s)</label>
      <select name="subject_id[]" multiple required>
        <?php if ($subjects && $subjects->num_rows > 0): ?>
          <?php while($s=$subjects->fetch_assoc()): ?>
            <option value="<?= $s['subject_id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
          <?php endwhile; ?>
        <?php else: ?>
          <option disabled>No Subjects Available</option>
        <?php endif; ?>
      </select>

      <label>Exam Date</label>
      <input type="date" name="exam_date" required>

      <label>Maximum Marks</label>
      <input type="number" name="max_marks" min="1" required>

      <label>Term</label>
      <select name="term" required>
        <option value="Term 1">Term 1</option>
        <option value="Term 2">Term 2</option>
        <option value="Term 3">Term 3</option>
      </select>

      <button type="submit" name="add_exam">Add Exam</button>
    </form>

    <h2 style="margin-top:30px;">📅 Upcoming Exams</h2>
    <table>
      <tr>
        <th>Exam ID</th><th>Class</th><th>Subject</th><th>Exam Date</th><th>Max Marks</th><th>Term</th><th>Edit</th><th>Delete</th>
      </tr>
      <?php if ($exams_result && $exams_result->num_rows > 0): ?>
        <?php while($exam=$exams_result->fetch_assoc()): ?>
        <tr>
          <td><?= $exam['exam_id'] ?></td>
          <td><?= htmlspecialchars($exam['class_name']) ?></td>
          <td><?= htmlspecialchars($exam['subject_name']) ?></td>
          <td><?= $exam['exam_date'] ?></td>
          <td><?= $exam['max_marks'] ?></td>
          <td><?= $exam['term'] ?></td>
          <td><a href="edit_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="edit">Edit</a></td>
          <td><a href="delete_exam.php?exam_id=<?= $exam['exam_id'] ?>" class="delete" onclick="return confirm('Are you sure?');">Delete</a></td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8">No Exams Scheduled</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

</body>
</html>
