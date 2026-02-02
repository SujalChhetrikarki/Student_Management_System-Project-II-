<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';

// ✅ Total students and teachers
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$total_teachers = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'] ?? 0;

// ✅ Pass/Fail trend by exam
$sql_exams = "SELECT exam_id, exam_date FROM exams ORDER BY exam_date ASC";
$result_exams = $conn->query($sql_exams);

$exam_dates = [];
$pass_counts = [];
$fail_counts = [];

while ($exam = $result_exams->fetch_assoc()) {
    $exam_dates[] = $exam['exam_date'];

    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN average_marks >= 40 THEN 1 ELSE 0 END) AS pass_count,
            SUM(CASE WHEN average_marks < 40 THEN 1 ELSE 0 END) AS fail_count
        FROM results 
        WHERE exam_id=?
    ");
    $stmt->bind_param("i", $exam['exam_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    $pass_counts[] = $res['pass_count'] ?? 0;
    $fail_counts[] = $res['fail_count'] ?? 0;
}

// ✅ Upcoming birthdays (next 7 days only + class name)
$sql_birthdays = "
    SELECT s.name, s.date_of_birth, c.class_name
    FROM students s
    JOIN classes c ON s.class_id = c.class_id
    WHERE DATE_FORMAT(s.date_of_birth, '%m-%d') 
          BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') 
          AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 7 DAY), '%m-%d')
    ORDER BY DATE_FORMAT(s.date_of_birth, '%m-%d') ASC
";
$birthdays = $conn->query($sql_birthdays);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
}

*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:"Segoe UI", Arial, sans-serif;
}

body{
  background:var(--bg);
  display:flex;
}

/* ===== Sidebar ===== */
.sidebar{
  width:240px;
  background:var(--sidebar);
  color:#fff;
  min-height:100vh;
  padding:20px 15px;
  position:fixed;
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
  padding:12px 15px;
  margin-bottom:10px;
  text-decoration:none;
  color:#e5e7eb;
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
  padding:18px 25px;
  border-radius:14px;
  box-shadow:0 10px 25px rgba(0,0,0,.05);
  margin-bottom:25px;
}

.header h1{
  font-size:22px;
  color:var(--text);
}

/* ===== Cards ===== */
.cards{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
  gap:20px;
  margin-bottom:30px;
}

.card{
  background:var(--card);
  padding:25px;
  border-radius:18px;
  box-shadow:0 15px 30px rgba(0,0,0,.06);
  transition:.3s;
}

.card:hover{
  transform:translateY(-5px);
}

.card h3{
  font-size:16px;
  color:var(--muted);
  margin-bottom:10px;
}

.card p{
  font-size:28px;
  font-weight:600;
  color:var(--text);
}

/* Notice Card */
.card a{
  display:inline-block;
  background:var(--primary);
  color:#fff;
  padding:8px 14px;
  border-radius:8px;
  font-size:14px;
  text-decoration:none;
  margin-top:10px;
}

.card a:hover{
  background:var(--secondary);
}

/* ===== Birthday Section ===== */
.birthday-card ul{
  list-style:none;
  margin-top:15px;
}

.birthday-item{
  background:#f8fafc;
  padding:12px 15px;
  border-radius:12px;
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:10px;
  transition:.3s;
}

.birthday-item:hover{
  background:#e0f2fe;
}

.birthday-info{
  font-weight:600;
  color:var(--text);
}

.bday-class{
  font-size:13px;
  color:var(--muted);
}

.birthday-date{
  font-size:13px;
}

.today-badge{
  background:#dc2626;
  color:#fff;
  padding:4px 8px;
  border-radius:8px;
}

.upcoming-badge{
  background:#2563eb;
  color:#fff;
  padding:4px 8px;
  border-radius:8px;
}

/* ===== Chart ===== */
.chart-container{
  background:var(--card);
  padding:25px;
  border-radius:18px;
  box-shadow:0 15px 30px rgba(0,0,0,.06);
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

<div class="main">
  <div class="header">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']); ?> 👋</h1>
  </div>

  <div class="cards">
    <div class="card">
      <h3>Total Students</h3>
      <p><?= $total_students ?></p>
    </div>
    <div class="card">
      <h3>Total Teachers</h3>
      <p><?= $total_teachers ?></p>
    </div>
    <div class="card">
  <h3>📢 Manage Notices</h3>
  <p><a href="manage_notices.php" style="
      background:#00bfff; color:white; text-decoration:none; 
      padding:8px 14px; border-radius:6px; display:inline-block;">
      ➕ Add / Manage Notices
  </a></p>
</div>


    <div class="card birthday-card">
      <h3>🎂 Birthdays This Week</h3>
      <?php if ($birthdays->num_rows > 0): ?>
          <ul class="birthday-list">
              <?php while ($b = $birthdays->fetch_assoc()): 
                  $dob = strtotime($b['date_of_birth']);
                  $dobThisYear = date("Y") . "-" . date("m-d", $dob);

                  // Handle passed dates
                  $nextBirthday = (strtotime($dobThisYear) < strtotime(date("Y-m-d"))) 
                                  ? strtotime("+1 year", strtotime($dobThisYear)) 
                                  : strtotime($dobThisYear);

                  $daysLeft = (int)(($nextBirthday - time()) / (60 * 60 * 24));
                  $isToday = ($daysLeft === 0);
              ?>
                  <li class="birthday-item <?= $isToday ? 'today' : '' ?>">
                      <div class="birthday-info">
                          <?= htmlspecialchars($b['name']) ?> 
                          <span class="bday-class">(<?= htmlspecialchars($b['class_name']) ?>)</span>
                      </div>
                      <div class="birthday-date">
                          <span class="date"><?= date("M d", $dob) ?></span>
                          <span class="<?= $isToday ? 'today-badge' : 'upcoming-badge' ?>">
                              <?= $isToday ? '🎉 Today!' : "in $daysLeft days" ?>
                          </span>
                      </div>
                  </li>
              <?php endwhile; ?>
          </ul>
      <?php else: ?>
          <p>No birthdays this week 🎉</p>
      <?php endif; ?>
    </div>
  </div>
  

  <div class="chart-container">
    <h3>📊 Students Pass vs Fail Trend</h3>
    <canvas id="passFailLineChart" height="150"></canvas>
  </div>
</div>

<script>
const ctx = document.getElementById('passFailLineChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($exam_dates) ?>,
        datasets: [
            {
                label: 'Pass',
                data: <?= json_encode($pass_counts) ?>,
                borderColor: '#4CAF50',
                backgroundColor: '#4CAF50',
                fill: false,
                tension: 0.2
            },
            {
                label: 'Fail',
                data: <?= json_encode($fail_counts) ?>,
                borderColor: '#FF4444',
                backgroundColor: '#FF4444',
                fill: false,
                tension: 0.2
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            title: { display: true, text: 'Pass/Fail Trend by Exam' }
        },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
</body>
</html>

