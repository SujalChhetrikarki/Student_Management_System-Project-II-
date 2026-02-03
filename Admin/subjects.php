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
* { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
body { display: flex; min-height: 100vh; background: #f4f6f9; }
.sidebar {
    width: 220px; background: #111; color: #fff;
    display: flex; flex-direction: column;
    padding-top: 20px; position: fixed; top: 0; left: 0; bottom: 0;
}
.sidebar h2 { text-align: center; color: #00bfff; margin-bottom: 30px; }
.sidebar a {
    display: block; padding: 12px 20px; margin: 5px 15px;
    background: #222; color: #fff; text-decoration: none;
    border-radius: 6px;
}
.sidebar a:hover { background: #00bfff; color: #111; }
.sidebar a.logout { background: #dc3545; }

.header {
    position: fixed; top: 0; left: 220px; right: 0;
    height: 80px; background: #00bfff; color: #fff;
    display: flex; align-items: center; justify-content: center;
}

.main {
    margin-left: 220px; width: calc(100% - 220px);
    padding-top: 120px; display: flex; justify-content: center;
}

.container {
    width: 700px; background: #fff; padding: 30px;
    border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 10px; border: 1px solid #ddd; }
th { background: #00bfff; color: #fff; }
a.delete { background: #dc3545; color: #fff; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
.success { color: green; margin-bottom: 10px; }
.error { color: red; margin-bottom: 10px; }
button { padding: 12px; width: 100%; background: #00bfff; color: #fff; border: none; border-radius: 6px; }
select, input { width: 100%; padding: 10px; margin-top: 5px; }
</style>
</head>

<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="index.php">🏠 Home</a> 
    <a href="../Admin/Manage_student/Managestudent.php">📚 Manage Students</a> 
    <a href="../Admin/Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a> 
    <a href="../Admin/Classes/classes.php">🏫 Manage Classes</a> 
    <a href="subjects.php">📖 Manage Subjects</a> 
    <a href="./Managebook.php">📚 Manage Books</a>
    <a href="../Admin/add_student.php">➕ Add Student</a> 
    <a href="../Admin/add_teacher.php">➕ Add Teacher</a> 
    <a href="../Admin/Add_exam/add_exam.php">➕ Add Exam</a> 
    <a href="../Admin/admin_approve_results.php">✅ Approve Results</a> 
    <a href="../Admin/logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">
    <h1>Subject Management</h1>
</div>

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
