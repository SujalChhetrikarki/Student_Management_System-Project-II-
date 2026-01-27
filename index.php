<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Management System</title>
  
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      background: #6dd5ed;
      color: #333;
    }
    header {
      background: #ffffff;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 40px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    nav a {
      margin-left: 20px;
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }

    nav a:hover {
      color: #007bff;
    }

    /* Login Section */
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 80px 20px 40px;
    }

    .login-card {
      background: #ffffff;
      padding: 40px;
      border-radius: 14px;
      width: 360px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.12);
      text-align: center;
    }

    .login-card img {
      width: 80px;
      height: 80px;
      border-radius: 10px;
      margin-bottom: 15px;
    }

    .login-card h2 {
      margin-bottom: 25px;
    }

    select {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 25px;
      font-size: 15px;
    }

    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background: #0056b3;
    }

    /* About Section */
    .about-section {
      max-width: 900px;
      margin: 40px auto 80px;
      background: #ffffff;
      padding: 40px;
      border-radius: 14px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      text-align: center;
    }

    .about-section h2 {
      margin-bottom: 20px;
      font-size: 26px;
    }

    .about-section p {
      font-size: 16px;
      line-height: 1.7;
      color: #555;
      max-width: 750px;
      margin: 0 auto 15px;
    }

    footer {
      text-align: center;
      padding: 15px;
      color: #666;
      font-size: 14px;
      background: #ffffff;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
  </style>
</head>

<body>

<header style="
    background:#ffffff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px 50px;
    box-shadow:0 4px 12px rgba(0,0,0,0.08);
">
    <!-- Logo -->
    <div style="display:flex; align-items:center; gap:12px;">
        <img src="Images/logo.jpg" 
             alt="Logo"
             style="
                width:48px;
                height:48px;
                object-fit:cover;
                border-radius:10px;
             ">
        <div>
            <div style="font-size:18px; font-weight:600; color:#333;">
                Student Management
            </div>
            <div style="font-size:13px; color:#777;">
                Diversity Academy
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav>
        <a href="index.php" style="margin-left:20px; text-decoration:none; color:#333;">Home</a>
        <a href="Students/student.php" style="margin-left:20px; text-decoration:none; color:#333;">Student</a>
        <a href="Teachers/teacher.php" style="margin-left:20px; text-decoration:none; color:#333;">Teacher</a>
        <a href="Admin/admin.php" style="margin-left:20px; text-decoration:none; color:#333;">Admin</a>
    </nav>
</header>


<!-- Login -->
<div class="login-container">
  <div class="login-card">
    <img src="Images/logo.jpg" alt="School Logo">
    <h2>Login Portal</h2>

    <select id="role">
      <option value="student" selected>Student</option>
      <option value="teacher">Teacher</option>
    </select>

    <button onclick="redirectLogin()">Login</button>
  </div>
</div>

<!-- About -->
<section class="about-section">
  <h2>About Diversity Academy</h2>

  <p>
    Diversity Academy is committed to nurturing students from all backgrounds by promoting
    creativity, critical thinking, and collaboration in a supportive academic environment.
  </p>

  <p>
    With a strong focus on digitalization, we integrate modern technology into education,
    empowering students with tools for research, communication, and skill development.
  </p>

  <p>
    Our mission is to prepare confident, responsible learners who are ready to succeed
    in an evolving digital world.
  </p>
</section>

<footer>
  © 2026 Diversity Academy | Student Management System
</footer>

<script>
  function redirectLogin() {
    const role = document.getElementById("role").value;

    if (role === "student") {
      window.location.href = "./Students/student.php";
    } else if (role === "admin") {
      window.location.href = "./Admin/admin.php";
    } else if (role === "teacher") {
      window.location.href = "./Teachers/teacher.php";
    }
  }
</script>

</body>
</html>
