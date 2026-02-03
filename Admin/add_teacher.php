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
    --primary: #2563eb;
    --bg: #f1f5f9;
    --card: #ffffff;
    --text: #1f2937;
    --nav-bg: #ffffff;
    --nav-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* ===== Base ===== */
*{box-sizing:border-box; margin:0; padding:0; font-family:"Segoe UI", Arial, sans-serif;}
body{background:var(--bg); color:var(--text); padding-top:70px;}

/* ===== Modern Top Navigation ===== */
.top-nav{
    position:fixed;
    top:0;
    left:0;
    right:0;
    background:var(--nav-bg);
    box-shadow:var(--nav-shadow);
    z-index:1000;
    padding:0 30px;
    height:70px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.nav-brand{
    font-size:22px;
    font-weight:700;
    color:var(--primary);
    text-decoration:none;
}
.nav-menu{
    display:flex;
    gap:5px;
    align-items:center;
}
.nav-menu a{
    padding:10px 18px;
    text-decoration:none;
    color:var(--text);
    border-radius:8px;
    transition:all 0.3s;
    font-size:14px;
    font-weight:500;
}
.nav-menu a:hover{
    background:var(--bg);
    color:var(--primary);
}
.nav-menu a.logout{
    background:#dc2626;
    color:#fff;
    margin-left:10px;
}
.nav-menu a.logout:hover{
    background:#b91c1c;
}

/* ===== Main ===== */
.main{
    padding:30px;
    max-width:1400px;
    margin:0 auto;
    width:100%;
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

<!-- Modern Top Navigation -->
<nav class="top-nav">
  <a href="index.php" class="nav-brand">🎓 Admin Panel</a>
  <div class="nav-menu">
    <a href="index.php">🏠 Home</a>
    <a href="./Manage_student/Managestudent.php">📚 Students</a>
    <a href="./Manage_Teachers/Teachersshow.php">👨‍🏫 Teachers</a>
    <a href="./classes/classes.php">🏫 Classes</a>
    <a href="subjects.php">📖 Subjects</a>
    <a href="Managebook.php">📚 Books</a>
    <a href="add_student.php">➕ Add Student</a>
    <a href="./add_teacher.php">➕ Add Teacher</a>
    <a href="./Add_exam/add_exam.php">➕ Exam</a>
    <a href="./admin_approve_results.php">✅ Results</a>
    <a href="./logout.php" class="logout">🚪 Logout</a>
  </div>
</nav>

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
