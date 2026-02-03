<?php
session_start();
include '../Database/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $result_ids = $_POST['result_id'] ?? [];
    foreach ($result_ids as $rid) {
        $stmt = $conn->prepare("UPDATE results SET status='Approved' WHERE result_id=?");
        $stmt->bind_param("i", $rid);
        $stmt->execute();
    }
    header("Location: admin_approve_results.php?msg=" . urlencode("✅ Selected results approved!"));
    exit;
}

$sql = "SELECT r.result_id, r.marks_obtained, r.average_marks, e.exam_date, e.term,
               s.subject_name, c.class_name, st.name as student_name
        FROM results r
        JOIN exams e ON r.exam_id=e.exam_id
        JOIN subjects s ON e.subject_id=s.subject_id
        JOIN classes c ON e.class_id=c.class_id
        JOIN students st ON r.student_id=st.student_id
        WHERE r.status='Pending'
        ORDER BY c.class_name, e.term, e.exam_date ASC";

$results = $conn->query($sql);
$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Approve Results</title>
<style>
:root {
    --sidebar-width: 240px;
    --primary: #2563eb;
    --dark: #0f172a;
    --bg: #f1f5f9;
    --card: #fff;
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
  width: 100%;
  max-width: 1000px;
  background: var(--card);
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
}

/* ===== Table ===== */
table{
  width:100%;
  border-collapse: collapse;
  margin-top:20px;
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
input[type="checkbox"]{width:auto;}

/* ===== Button ===== */
.btn{
  margin-top:20px;
  padding:10px 18px;
  border:none;
  border-radius:8px;
  background: var(--primary);
  color:#fff;
  font-weight:500;
  cursor:pointer;
  transition:0.3s;
}
.btn:hover{background:#1d4ed8;}

/* ===== Messages ===== */
.msg{ text-align:center; margin-bottom:15px; font-weight:500; color:#16a34a; }

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
  <a href="logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">✅ Approve Pending Results</div>

<div class="main">
  <div class="container">
    <?php if($msg) echo "<p class='msg'>{$msg}</p>"; ?>

    <form method="POST">
      <table>
        <tr>
          <th>Select</th>
          <th>Student</th>
          <th>Class</th>
          <th>Subject</th>
          <th>Term</th>
          <th>Exam Date</th>
          <th>Marks</th>
          <th>Average</th>
        </tr>

        <?php if($results->num_rows==0): ?>
        <tr><td colspan="8">No pending results.</td></tr>
        <?php else: ?>
        <?php while($r=$results->fetch_assoc()): ?>
        <tr>
          <td><input type="checkbox" name="result_id[]" value="<?= $r['result_id'] ?>"></td>
          <td><?= htmlspecialchars($r['student_name']) ?></td>
          <td><?= htmlspecialchars($r['class_name']) ?></td>
          <td><?= htmlspecialchars($r['subject_name']) ?></td>
          <td><?= htmlspecialchars($r['term']) ?></td>
          <td><?= htmlspecialchars($r['exam_date']) ?></td>
          <td><?= htmlspecialchars($r['marks_obtained']) ?></td>
          <td><?= number_format($r['average_marks'],2) ?></td>
        </tr>
        <?php endwhile; ?>
        <?php endif; ?>
      </table>

      <?php if($results->num_rows>0): ?>
      <button type="submit" name="approve" class="btn">Approve Selected Results</button>
      <?php endif; ?>
    </form>
  </div>
</div>

</body>
</html>
