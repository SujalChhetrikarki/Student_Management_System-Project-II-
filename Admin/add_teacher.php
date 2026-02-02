<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

include '../Database/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Teacher</title>
<style>
:root{
    --sidebar-width: 240px;
    --primary: #2563eb;
    --dark: #0f172a;
    --bg: #f1f5f9;
    --card: #ffffff;
}

/* ===== Base ===== */
*{box-sizing:border-box; margin:0; padding:0; font-family:"Segoe UI", Arial, sans-serif;}
body{display:flex; background:var(--bg); color:#1f2937;}

/* ===== Sidebar ===== */
.sidebar{
    width: var(--sidebar-width);
    background: var(--dark);
    color: #fff;
    height: 100vh;
    position: fixed;
    top:0;
    left:0;
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
.container h2{text-align:center; margin-bottom:20px; color:#1f2937;}

/* ===== Forms ===== */
form label{display:block; margin-top:12px; margin-bottom:5px; font-weight:500;}
form input, form select{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:14px;
}
form input[type="checkbox"]{width:auto; margin-right:8px;}
form button{
    margin-top:20px;
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:var(--primary);
    color:#fff;
    font-size:16px;
    cursor:pointer;
    transition:0.3s;
}
form button:hover{background:#1d4ed8;}

/* ===== Messages ===== */
.msg{ text-align:center; margin-bottom:15px; font-weight:500;}
.error{color:red;}
.success{color:green;}

/* ===== Class-Subject Boxes ===== */
.class-box{
    border:1px solid #e5e7eb;
    padding:15px;
    border-radius:12px;
    margin-bottom:12px;
    background:#f9fafb;
    transition:0.2s;
}
.class-box:hover{
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
}
.class-title{font-weight:600; margin-bottom:8px;}
.subjects-list label{display:block; margin-left:18px; margin-bottom:3px; font-weight:400;}
.note{font-size:12px; color:#555; margin-top:5px;}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="./index.php">🏠 Home</a>
    <a href="./Manage_student/Managestudent.php">📚 Manage Students</a>
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Manage Teachers</a>
    <a href="./classes/classes.php">🏫 Manage Classes</a>
    <a href="./subjects.php">📖 Manage Subjects</a>
    <a href="./Managebook.php">📚 Manage Books</a>
    <a href="add_student.php">➕ Add Student</a>
    <a href="./add_teacher.php">➕ Add Teacher</a>
    <a href="./Add_exam/add_exam.php">➕ Add Exam</a>
    <a href="./admin_approve_results.php">✅ Approve Results</a>
    <a href="./logout.php" class="logout">🚪 Logout</a>
</div>

<div class="header">➕ Add New Teacher</div>

<div class="main">
<div class="container">
    <h2>Register Teacher</h2>

    <?php if(isset($_SESSION['error'])): ?>
        <p class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <p class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form action="add_teacher_process.php" method="POST" id="addTeacherForm">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Full Name" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="Email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <label>Specialization</label>
        <input type="text" name="specialization" placeholder="e.g. Math, Science" required>

        <label>
            <input type="checkbox" id="is_class_teacher" name="is_class_teacher" value="1">
            Make this teacher a Class Teacher (only one class allowed)
        </label>

        <div id="class_teacher_select" style="display:none; margin-top:10px;">
            <label for="class_teacher_class">Select Class:</label>
            <select name="class_teacher_class" id="class_teacher_class">
                <option value="">-- Select Class --</option>
                <?php
                $classQuery = $conn->query("SELECT class_id, class_name FROM classes");
                while($row = $classQuery->fetch_assoc()){
                    echo "<option value='".htmlspecialchars($row['class_id'], ENT_QUOTES)."'>".htmlspecialchars($row['class_name'])."</option>";
                }
                ?>
            </select>
            <p class="note">If checked, teacher will be class teacher of selected class only.</p>
        </div>

        <label>Assign Subjects to Classes:</label>
        <div class="class-subject-container">
            <?php
            $classes = $conn->query("SELECT class_id, class_name FROM classes");
            $subjectsAll = [];
            $subjectQuery = $conn->query("SELECT subject_id, subject_name FROM subjects");
            while ($s = $subjectQuery->fetch_assoc()) $subjectsAll[] = $s;

            while($class = $classes->fetch_assoc()){
                $cid = (int)$class['class_id'];
                echo "<div class='class-box'>";
                echo "<div class='class-title'><label><input type='checkbox' class='teaching_class_checkbox' name='teaching_classes[]' value='{$cid}'> ".htmlspecialchars($class['class_name'])."</label></div>";
                echo "<div class='subjects-list'>";
                foreach($subjectsAll as $sub){
                    $sid = (int)$sub['subject_id'];
                    echo "<label><input type='checkbox' name='subjects_for_class[{$cid}][]' value='{$sid}'> ".htmlspecialchars($sub['subject_name'])."</label>";
                }
                echo "</div></div>";
            }
            ?>
        </div>

        <button type="submit">➕ Add Teacher</button>
    </form>
    <p class="note">Class Teacher role is limited to one class. Teacher can still teach selected subjects in multiple classes.</p>
</div>
</div>

<script>
const classTeacherCheckbox = document.getElementById('is_class_teacher');
const classTeacherSelectDiv = document.getElementById('class_teacher_select');
const classTeacherSelect = document.getElementById('class_teacher_class');

classTeacherCheckbox.addEventListener('change', function(){
    classTeacherSelectDiv.style.display = this.checked ? 'block' : 'none';
    if(!this.checked) classTeacherSelect.value = '';
});

document.getElementById('addTeacherForm').addEventListener('submit', function(e){
    const classBoxes = document.querySelectorAll('.class-box');
    let valid = true;
    classBoxes.forEach(box => {
        const classCheckbox = box.querySelector('.teaching_class_checkbox');
        const subjectChecks = box.querySelectorAll('input[type="checkbox"][name^="subjects_for_class"]');
        let anySubject = false;
        subjectChecks.forEach(s => { if(s.checked) anySubject=true; });
        if(anySubject && !classCheckbox.checked){
            valid=false;
            classCheckbox.focus();
        }
    });
    if(!valid){
        e.preventDefault();
        alert("Select the class checkbox if assigning subjects to that class.");
    }
});
</script>
</body>
</html>
