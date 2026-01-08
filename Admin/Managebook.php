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
$selected_class = 0;

if (isset($_GET['class_id']) && $_GET['class_id'] != '') {
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
<title>Class Subjects Panel</title>

<style>
* { box-sizing: border-box; font-family: Arial, sans-serif; }
body { background: #f4f6f9; padding: 40px; }

.container {
    max-width: 800px;
    background: #fff;
    margin: auto;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
}

h1 {
    margin-bottom: 20px;
    color: #333;
}

select {
    width: 100%;
    padding: 10px;
    margin-bottom: 25px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
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
    color: #777;
    padding: 20px;
}
</style>
</head>

<body>

<div class="container">
    <h1>📖 Class-wise Subjects</h1>

    <!-- CLASS SELECT -->
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

    <!-- SUBJECT LIST -->
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

</body>
</html>
