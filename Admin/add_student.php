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
$classes_result = $conn->query("SELECT class_id, class_name FROM classes ORDER BY class_name");
$classes = [];
if ($classes_result && $classes_result->num_rows > 0) {
    while($row = $classes_result->fetch_assoc()) {
        $classes[] = $row;
    }
}

/* =========================
   FETCH STUDENTS
========================= */
$students_result = $conn->query("
    SELECT s.student_id, s.name, s.email, s.gender, s.date_of_birth, c.class_name
    FROM students s
    JOIN classes c ON s.class_id = c.class_id
    ORDER BY c.class_name, s.name
");
$students = [];
if ($students_result && $students_result->num_rows > 0) {
    while($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Students</title>
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
  display: flex;
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

/* ===== Header ===== */
.header{
  position: fixed;
  top: 0;
  left: var(--sidebar-width);
  right: 0;
  height: 80px;
  background: var(--primary);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: 600;
  z-index: 10;
}

/* ===== Main ===== */
.main{
  margin-left: var(--sidebar-width);
  padding: 100px 30px 30px 30px;
  width: calc(100% - var(--sidebar-width));
  display: flex;
  flex-direction: column;
  align-items: center;
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
  margin-top: 20px;
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

/* ===== Alerts ===== */
.success{ color: green; margin-bottom: 10px; font-weight: 500;}
.error{ color: red; margin-bottom: 10px; font-weight: 500; }

/* ===== Student Grid ===== */
.student-grid{
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 20px;
}

.student-card{
  background: #f8f9fa;
  border-radius: 10px;
  padding: 15px;
  border-left: 5px solid var(--primary);
  transition: 0.3s;
}

.student-card:hover{
  transform: translateY(-5px);
  box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}

.student-card h4{
  margin: 0 0 8px;
  color: var(--primary);
}

.student-card p{
  margin: 4px 0;
  font-size: 14px;
  color: #333;
}

.empty{
  text-align: center;
  padding: 20px;
  color: #777;
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">🏠 Home</a> 
    <a href="./Manage_student/Managestudent.php">📚 Manage Students</a> 
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a> 
    <a href="./classes/classes.php">🏫 Manage Classes</a> 
    <a href="./subjects.php">📖 Manage Subjects</a> 
    <a href="./Managebook.php">📚 Manage Books</a>
    <a href="./add_student.php">➕ Add Student</a> 
    <a href="./add_teacher.php">➕ Add Teacher</a> 
    <a href="./Add_exam/add_exam.php">➕ Add Exam</a> 
    <a href="./admin_approve_results.php">✅ Approve Results</a> 
    <a href="./logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">📚 Manage Students</div>

<div class="main">

<!-- Add Student Form -->
<div class="container">
<h2>Register New Student</h2>

<?php if(isset($_SESSION['success'])): ?>
<p class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<p class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

<form action="add_student_process.php" method="POST">
    <label>Full Name</label>
    <input type="text" name="name" placeholder="Full Name" required>

    <label>Email</label>
    <input type="email" name="email" placeholder="Email" required>

    <label>Password</label>
    <input type="password" name="password" placeholder="Password" required>

    <label>Class</label>
    <select name="class_id" required>
        <option value="">Select Class</option>
        <?php foreach($classes as $c): ?>
            <option value="<?= $c['class_id']; ?>"><?= htmlspecialchars($c['class_name']); ?></option>
        <?php endforeach; ?>
    </select>

    <label>Date of Birth</label>
    <input type="date" name="date_of_birth" required>

    <label>Gender</label>
    <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
    </select>

    <button type="submit">Add Student</button>
</form>
</div>

<!-- Student Viewer -->
<div class="container">
<h3>View Students</h3>

<div style="display:flex;gap:10px;justify-content:center;margin-bottom:20px;">
    <select id="classFilter">
        <option value="">-- Select Class --</option>
        <?php foreach($classes as $class): ?>
            <option value="<?= htmlspecialchars($class['class_name']); ?>">
                <?= htmlspecialchars($class['class_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button onclick="showStudents()">Show Students</button>
</div>

<div id="studentContainer" style="display:<?= count($students) > 0 ? 'grid' : 'none' ?>;" class="student-grid">
    <?php if(count($students) > 0): ?>
        <?php foreach($students as $student): ?>
            <div class="student-card" data-class="<?= htmlspecialchars($student['class_name']); ?>">
                <h4><?= htmlspecialchars($student['name']); ?></h4>
                <p><b>ID:</b> <?= $student['student_id']; ?></p>
                <p><b>Class:</b> <?= htmlspecialchars($student['class_name']); ?></p>
                <p><b>Email:</b> <?= htmlspecialchars($student['email']); ?></p>
                <p><b>Gender:</b> <?= $student['gender']; ?></p>
                <p><b>DOB:</b> <?= $student['date_of_birth']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty">No students found</p>
    <?php endif; ?>
</div>
</div>

<script>
function showStudents(){
    const selectedClass = document.getElementById("classFilter").value;
    const cards = document.querySelectorAll(".student-card");

    cards.forEach(card => {
        card.style.display = (selectedClass === "" || card.dataset.class === selectedClass) ? "block" : "none";
    });
}
</script>
</body>
</html>
