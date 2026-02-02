<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// Fetch all teachers
$sql = "SELECT * FROM teachers ORDER BY name ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching teachers: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
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

/* ===== Header ===== */
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
  margin-top: 10px;
}

th, td{
  padding: 14px 16px;
  text-align: left;
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
.action-btn{
  padding: 7px 14px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 14px;
  margin-right: 6px;
  display: inline-block;
}

.edit-btn{
  background: #facc15;
  color: #111;
}

.edit-btn:hover{
  background: #eab308;
}

.delete-btn{
  background: #ef4444;
  color: #fff;
}

.delete-btn:hover{
  background: #dc2626;
}

/* ===== Responsive ===== */
@media (max-width: 900px){
  .sidebar{
    width: 200px;
  }
  .container{
    margin-left: 200px;
  }
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
        <a href="./Teachersshow.php">👨‍🏫 Manage Teachers</a>
        <a href="../Classes/classes.php">🏫 Manage Classes</a>
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
        <h1>👨‍🏫 Manage Teachers</h1>
        <a href="../add_teacher.php" class="btn">➕ Add New Teacher</a>

        <table>
            <tr>
                <th>Teacher ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>Class Teacher?</th>
                <th>Assigned Classes</th>
                <th>Assigned Subjects</th>
                <th>Actions</th>
            </tr>

            <?php while ($teacher = $result->fetch_assoc()): ?>
                <?php
                $tid = $teacher['teacher_id'];
                $is_class_teacher = $teacher['is_class_teacher'] ? "✅" : "❌";

                // Fetch assigned classes & subjects from class_subject_teachers
                $sql_class_subjects = "SELECT c.class_name, s.subject_name
                                       FROM class_subject_teachers cst
                                       JOIN classes c ON cst.class_id = c.class_id
                                       JOIN subjects s ON cst.subject_id = s.subject_id
                                       WHERE cst.teacher_id = ?";
                $stmt_cs = $conn->prepare($sql_class_subjects);
                $stmt_cs->bind_param("s", $tid);
                $stmt_cs->execute();
                $res_cs = $stmt_cs->get_result();

                $classes_arr = [];
                $subjects_arr = [];
                while ($row = $res_cs->fetch_assoc()) {
                    if (!in_array($row['class_name'], $classes_arr)) $classes_arr[] = $row['class_name'];
                    if (!in_array($row['subject_name'], $subjects_arr)) $subjects_arr[] = $row['subject_name'];
                }

                $classes_str = !empty($classes_arr) ? implode(", ", $classes_arr) : "-";
                $subjects_str = !empty($subjects_arr) ? implode(", ", $subjects_arr) : "-";
                ?>

                <tr>
                    <td><?= htmlspecialchars($teacher['teacher_id']) ?></td>
                    <td><?= htmlspecialchars($teacher['name']) ?></td>
                    <td><?= htmlspecialchars($teacher['email']) ?></td>
                    <td><?= htmlspecialchars($teacher['specialization']) ?></td>
                    <td><?= $is_class_teacher ?></td>
                    <td><?= htmlspecialchars($classes_str) ?></td>
                    <td><?= htmlspecialchars($subjects_str) ?></td>
                    <td>
                        <a href="edit_teacher.php?teacher_id=<?= urlencode($tid) ?>" class="action-btn edit-btn">✏ Edit</a>
                        <a href="delete_teacher.php?teacher_id=<?= urlencode($tid) ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">🗑 Delete</a>
                    </td>
                </tr>

            <?php endwhile; ?>
        </table>
    </div>

</body>
</html>
