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
$classes = $conn->query("
    SELECT class_id, class_name
    FROM classes
    ORDER BY class_name
");

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
/* ===== Layout ===== */
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    display: flex;
}

/* ===== Sidebar ===== */
.sidebar {
    width: 220px;
    background: #111;
    color: #fff;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding-top: 20px;
}
.sidebar h2 {
    text-align: center;
    margin-bottom: 30px;
    font-size: 20px;
    color: #00bfff;
}
.sidebar a {
    display: block;
    padding: 12px 20px;
    margin: 8px 15px;
    background: #222;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: 0.3s;
}
.sidebar a:hover {
    background: #00bfff;
    color: #111;
}
.sidebar a.logout {
    background: #dc3545;
}

/* ===== Main ===== */
.main {
    margin-left: 220px;
    padding: 20px;
    flex: 1;
}

.header {
    background: #fff;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
    font-size: 22px;
    color: #333;
}

/* ===== Card ===== */
.card {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

/* ===== Form ===== */
select {
    width: 100%;
    padding: 10px;
    margin: 15px 0 25px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* ===== Table ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
}

th {
    background: #00bfff;
    color: #fff;
}

tr:hover {
    background: #f1f1f1;
}

.empty {
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
        <h1>📖 Class-wise Subjects</h1>
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
