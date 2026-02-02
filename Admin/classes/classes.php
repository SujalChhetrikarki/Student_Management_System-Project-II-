<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// ✅ Fetch classes with assigned teacher, student count, subject count
$sql = "
    SELECT 
        c.class_id,
        c.class_name,
        t.name AS teacher_name,
        COUNT(DISTINCT s.student_id) AS total_students,
        COUNT(DISTINCT sub.subject_id) AS total_subjects
    FROM classes c
    LEFT JOIN class_teachers ct ON c.class_id = ct.class_id
    LEFT JOIN teachers t ON ct.teacher_id = t.teacher_id
    LEFT JOIN students s ON s.class_id = c.class_id
    LEFT JOIN subjects sub ON sub.class_id = c.class_id
    GROUP BY c.class_id, c.class_name, t.name
    ORDER BY c.class_id ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes</title>
    <link rel="stylesheet" href="classes.css">
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
  margin: 0;
  font-family: "Segoe UI", Arial, sans-serif;
  background: var(--bg);
  color: #1f2937;
}

/* ===== Sidebar ===== */
.sidebar{
  width: var(--sidebar-width);
  background: var(--dark);
  color: #fff;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  padding-top: 20px;
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

/* ===== Main Container ===== */
.container{
  margin-left: var(--sidebar-width);
  padding: 30px;
  max-width: calc(100% - var(--sidebar-width));
}

/* ===== Page Header ===== */
.page-header{
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.page-header h1{
  margin: 0;
  font-size: 24px;
}

.page-header a{
  background: var(--primary);
  color: #fff;
  padding: 10px 16px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 500;
}

.page-header a:hover{
  background: #1d4ed8;
}

/* ===== Card ===== */
.card{
  background: var(--card);
  padding: 20px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
}

/* ===== Table ===== */
table{
  width: 100%;
  border-collapse: collapse;
}

th, td{
  padding: 14px 16px;
  text-align: center;
}

th{
  background: var(--primary);
  color: #fff;
  font-weight: 600;
}

tr{
  border-bottom: 1px solid #e5e7eb;
}

tr:hover{
  background: #f8fafc;
}

/* ===== Buttons ===== */
.btn,
.btn-sm{
  text-decoration: none;
  padding: 8px 14px;
  border-radius: 8px;
  background: var(--primary);
  color: #fff;
  font-size: 14px;
  display: inline-block;
}

.btn:hover,
.btn-sm:hover{
  background: #1d4ed8;
}

.btn-sm.danger{
  background: #ef4444;
}

.btn-sm.danger:hover{
  background: #dc2626;
}

/* ===== Responsive ===== */
@media (max-width: 900px){
  .sidebar{ width: 200px; }
  .container{ margin-left: 200px; }
}

@media (max-width: 700px){
  .sidebar{
    position: relative;
    width: 100%;
    height: auto;
  }
  .container{
    margin-left: 0;
  }
}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="../index.php">🏠 Home</a>
  <a href="../Manage_student/Managestudent.php">📚 Manage Students</a>
  <a href="../Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
  <a href="classes.php">🏫 Manage Classes</a>
  <a href="../subjects.php">📖 Manage Subjects</a>
  <a href="../Managebook.php">📚 Manage Books</a>
  <a href="../add_student.php">➕ Add Student</a>
  <a href="../add_teacher.php">➕ Add Teacher</a>
  <a href="../Add_exam/add_exam.php">➕ Add Exam</a>
  <a href="../admin_approve_results.php">✅ Approve Results</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="container">
    <h1>📚 Manage Classes</h1>

    <a class="btn" href="add_class.php">➕ Add New Class</a>
<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Class Name</th>
                <th>Teacher</th>
                <th>Total Students</th>
                <th>Total Subjects</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['class_id']; ?></td>
                    <td><?= htmlspecialchars($row['class_name']); ?></td>
                    <td><?= $row['teacher_name'] ?? 'Unassigned'; ?></td>
                    <td><?= $row['total_students']; ?></td>
                    <td><?= $row['total_subjects']; ?></td>
                    <td>
                        <a class="btn-sm" href="edit_class.php?id=<?= $row['class_id']; ?>">✏ Edit</a>
                        <a class="btn-sm danger" href="delete_class.php?id=<?= $row['class_id']; ?>" onclick="return confirm('Delete this class?')">🗑 Delete</a>
                        <a class="btn-sm" href="view_students.php?id=<?= $row['class_id']; ?>">👨‍🎓 Students</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No classes found</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    
</div>
</body>
</html>
<?php $conn->close(); ?>
