<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #6dd5ed;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px #aaa;
            width: 300px;
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-box button {
            width: 100%;
            padding: 10px;
            background: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #218838;
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
        <img src="../Images/logo.jpg" 
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
        <a href="../index.php" style="margin-left:20px; text-decoration:none; color:#333;">Home</a>
        <a href="../Students/student.php" style="margin-left:20px; text-decoration:none; color:#333;">Student</a>
        <a href="../Teachers/teacher.php" style="margin-left:20px; text-decoration:none; color:#333;">Teacher</a>
        <a href="../Admin/admin.php" style="margin-left:20px; text-decoration:none; color:#333;">Admin</a>
    </nav>
</header>


<main>
    <div class="login-box">
        <h2>Student Login</h2>

        <form action="login_process.php" method="post">
            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password :</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</main>

<footer style="
    background:#ffffff;
    padding:20px 10px;
    text-align:center;
    box-shadow:0 -2px 10px rgba(0,0,0,0.05);
">
    <div style="font-size:14px; color:#555;">
        © 2026 Diversity Academy
    </div>
    <div style="font-size:13px; color:#888; margin-top:4px;">
        Student Management System
    </div>
</footer>


<script>
    fetch("../Header.php")
        .then(res => res.text())
        .then(data => document.getElementById("header").innerHTML = data);

    fetch("../Footer.php")
        .then(res => res.text())
        .then(data => document.getElementById("footer").innerHTML = data);
</script>

</body>
</html>
