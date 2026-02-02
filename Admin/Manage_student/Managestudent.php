<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit;
}

include '../../Database/db_connect.php';

// ✅ Get search input
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// ✅ Fetch students with performance (filtered by search if provided)
$sql = "
    SELECT s.student_id, s.name, s.email, s.class_id, c.class_name,
           IFNULL(ROUND(AVG(r.average_marks),2), 0) as avg_marks
    FROM students s
    JOIN classes c ON s.class_id = c.class_id
    LEFT JOIN results r ON s.student_id = r.student_id
    WHERE s.name LIKE '%$search%'
    GROUP BY s.student_id, s.name, s.email, s.class_id, c.class_name
    ORDER BY avg_marks DESC
";

$students = $conn->query($sql);
if (!$students) {
    die("❌ SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link rel="stylesheet" href="managestudent.css">
<style>
:root{
  --primary:#2563eb;
  --secondary:#1e40af;
  --bg:#f1f5f9;
  --card:#ffffff;
  --sidebar:#0f172a;
  --sidebar-hover:#1e293b;
  --text:#1f2937;
  --muted:#6b7280;
  --danger:#dc2626;
  --warning:#f59e0b;
  --success:#16a34a;
}

*{
  box-sizing:border-box;
  font-family:"Segoe UI", system-ui, Arial, sans-serif;
}

body{
  margin:0;
  background:var(--bg);
  display:flex;
}

/* ===== Sidebar ===== */
.sidebar{
  width:240px;
  background:var(--sidebar);
  color:#fff;
  height:100vh;
  position:fixed;
  padding:20px 15px;
}

.sidebar h2{
  text-align:center;
  margin-bottom:30px;
  color:#60a5fa;
  font-size:20px;
}

.sidebar a{
  display:flex;
  align-items:center;
  gap:10px;
  padding:12px 16px;
  margin:8px 10px;
  background:transparent;
  color:#e5e7eb;
  text-decoration:none;
  border-radius:10px;
  transition:.3s;
}

.sidebar a:hover{
  background:var(--sidebar-hover);
  color:#fff;
}

.sidebar a.logout{
  background:#7f1d1d;
}

.sidebar a.logout:hover{
  background:var(--danger);
}

/* ===== Main ===== */
.main{
  margin-left:240px;
  padding:25px;
  width:100%;
}

/* ===== Header ===== */
.header{
  background:var(--card);
  padding:20px 25px;
  border-radius:14px;
  box-shadow:0 10px 25px rgba(0,0,0,.06);
  margin-bottom:25px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  flex-wrap:wrap;
}

.header h1{
  font-size:22px;
  color:var(--text);
  margin:0 0 10px 0;
}

/* ===== Search ===== */
.search-box{
  display:flex;
  gap:10px;
}

.search-box input{
  padding:10px 14px;
  border-radius:10px;
  border:1px solid #cbd5f5;
  min-width:220px;
}

.search-box button{
  background:var(--primary);
  color:#fff;
  border:none;
  padding:10px 16px;
  border-radius:10px;
  cursor:pointer;
}

.search-box button:hover{
  background:var(--secondary);
}

.search-box a{
  background:#64748b;
  color:#fff;
  padding:10px 16px;
  border-radius:10px;
  text-decoration:none;
}

/* ===== Table ===== */
table{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  background:var(--card);
  border-radius:14px;
  overflow:hidden;
  box-shadow:0 15px 30px rgba(0,0,0,.06);
}

th,td{
  padding:14px 16px;
  text-align:center;
}

th{
  background:var(--primary);
  color:#fff;
  font-weight:600;
}

tr:nth-child(even){
  background:#f8fafc;
}

tr:hover{
  background:#e0f2fe;
}

/* ===== Buttons ===== */
.btn{
  padding:6px 12px;
  border-radius:8px;
  text-decoration:none;
  font-size:14px;
  display:inline-block;
}

.btn.edit{
  background:var(--warning);
  color:#111;
}

.btn.edit:hover{
  background:#eab308;
}

.btn.delete{
  background:var(--danger);
  color:#fff;
}

.btn.delete:hover{
  background:#b91c1c;
}

/* ===== Performance Badge ===== */
td:nth-child(5){
  font-weight:600;
}

/* ===== Responsive ===== */
@media(max-width:900px){
  .sidebar{width:200px;}
  .main{margin-left:200px;}
}

@media(max-width:700px){
  .sidebar{display:none;}
  .main{margin-left:0;}
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
        <a href="../classes/classes.php">🏫 Manage Classes</a>
        <a href="../subjects.php">📖 Manage Subjects</a>
        <a href="../Managebook.php">📚 Manage Books</a>
        <a href="../add_student.php">➕ Add Student</a>
        <a href="../add_teacher.php">➕ Add Teacher</a>
        <a href="../Add_exam/add_exam.php">➕ Add Exam</a>
        <a href="../admin_approve_results.php">✅ Approve Results</a>
        <a href="../logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="header">
            <h1>👨‍🎓 Manage Students</h1>

            <!-- Search Form -->
            <form method="get" action="" class="search-box">
                <input type="text" name="search" placeholder="🔍 Search by Name" value="<?= htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
                <a href="Managestudent.php">Reset</a>
            </form>
        </div>

        <!-- Students Table -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Class</th>
                    <th>Performance (Avg Marks)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows > 0): ?>
                    <?php while ($row = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['student_id']; ?></td>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['class_name']); ?></td>
                            <td>
                                <?php
                                $performance = $row['avg_marks'];
                                if ($performance >= 75) echo "🌟 Excellent ($performance%)";
                                elseif ($performance >= 50) echo "👍 Good ($performance%)";
                                elseif ($performance > 0) echo "⚠ Needs Improvement ($performance%)";
                                else echo "❌ No Results";
                                ?>
                            </td>
                            <td>
                                <a href="edit_student.php?student_id=<?= $row['student_id']; ?>" class="btn edit">✏ Edit</a>
                               <a href="delete_student.php?student_id=<?= urlencode($row['student_id']); ?>"
   class="btn delete"
   onclick="return confirm('Are you sure you want to delete this student?');">
   🗑 Delete
</a>

                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No matching students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
