<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Teacher Login | SMS</title>

<style>
:root{
  --primary:#2563eb;
  --secondary:#1e40af;
  --bg:#f1f5f9;
  --card:#ffffff;
  --text:#1f2937;
  --muted:#6b7280;
}

/* Reset */
*{
  margin:0;
  padding:0;
  box-sizing:border-box;
  font-family:"Segoe UI", Arial, sans-serif;
}

body{
  min-height:100vh;
  display:flex;
  flex-direction:column;
  background:linear-gradient(135deg,#6dd5ed,#2193b0);
}

/* Header */
header{
  background:#fff;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 50px;
  box-shadow:0 6px 20px rgba(0,0,0,.08);
}

.logo{
  display:flex;
  align-items:center;
  gap:12px;
}

.logo img{
  width:48px;
  height:48px;
  border-radius:10px;
}

.logo h1{
  font-size:18px;
  font-weight:600;
}

.logo span{
  font-size:13px;
  color:var(--muted);
}

nav a{
  margin-left:22px;
  text-decoration:none;
  font-weight:500;
  color:var(--text);
  position:relative;
}

nav a::after{
  content:"";
  position:absolute;
  left:0;
  bottom:-6px;
  width:0;
  height:2px;
  background:var(--primary);
  transition:.3s;
}

nav a:hover::after{
  width:100%;
}

/* Main */
main{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:40px 20px;
}

/* Login Card */
.login-box{
  background:var(--card);
  width:360px;
  padding:40px;
  border-radius:18px;
  box-shadow:0 20px 40px rgba(0,0,0,.18);
  animation:fadeUp .8s ease;
}

@keyframes fadeUp{
  from{opacity:0; transform:translateY(25px);}
  to{opacity:1; transform:translateY(0);}
}

.login-box h2{
  text-align:center;
  margin-bottom:10px;
}

.login-box p{
  text-align:center;
  font-size:14px;
  color:var(--muted);
  margin-bottom:25px;
}

/* Form */
label{
  font-size:14px;
  color:#374151;
}

input{
  width:100%;
  padding:12px 14px;
  margin-top:6px;
  margin-bottom:18px;
  border-radius:10px;
  border:1px solid #d1d5db;
  font-size:14px;
}

input:focus{
  outline:none;
  border-color:var(--primary);
  box-shadow:0 0 0 3px rgba(37,99,235,.15);
}

button{
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg,var(--primary),var(--secondary));
  color:#fff;
  font-size:16px;
  font-weight:500;
  cursor:pointer;
  transition:.3s;
}

button:hover{
  transform:translateY(-2px);
  box-shadow:0 10px 20px rgba(37,99,235,.4);
}

/* Footer */
footer{
  background:#fff;
  text-align:center;
  padding:16px;
  box-shadow:0 -4px 15px rgba(0,0,0,.05);
}

footer div:first-child{
  font-size:14px;
  color:#555;
}

footer div:last-child{
  font-size:13px;
  color:#888;
}

/* Responsive */
@media(max-width:768px){
  nav{display:none;}
}
</style>
</head>

<body>

<header>
  <div class="logo">
    <img src="../Images/logo.jpg" alt="Logo">
    <div>
      <h1>Student Management</h1>
      <span>Diversity Academy</span>
    </div>
  </div>

  <nav>
    <a href="../index.php">Home</a>
    <a href="../Students/student.php">Student</a>
    <a href="../Teachers/teacher.php">Teacher</a>
    <a href="../Admin/admin.php">Admin</a>
  </nav>
</header>

<main>
  <div class="login-box">
    <h2>Teacher Login</h2>
    <p>Sign in to manage classes and students</p>

    <form action="teacher_login.php" method="post">
      <label>Email</label>
      <input type="email" name="email" placeholder="teacher@email.com" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>

      <button type="submit">Login</button>
    </form>
  </div>
</main>

<footer>
  <div>© 2026 Diversity Academy</div>
  <div>Student Management System</div>
</footer>

</body>
</html>
