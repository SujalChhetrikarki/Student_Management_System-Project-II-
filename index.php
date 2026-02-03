<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Management System</title>

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
  background:linear-gradient(135deg,#6dd5ed,#2193b0);
  color:var(--text);
  min-height:100vh;
  display:flex;
  flex-direction:column;
}

/* Header */
header{
  background:#fff;
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:14px 60px;
  box-shadow:0 6px 20px rgba(0,0,0,0.08);
  position:sticky;
  top:0;
  z-index:10;
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
  margin-left:24px;
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

/* Login */
.login-container{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:60px 20px;
}

.login-card{
  background:var(--card);
  width:380px;
  padding:40px;
  border-radius:18px;
  box-shadow:0 20px 40px rgba(0,0,0,0.15);
  text-align:center;
  animation:fadeUp .8s ease;
}

@keyframes fadeUp{
  from{opacity:0; transform:translateY(20px);}
  to{opacity:1; transform:translateY(0);}
}

.login-card img{
  width:90px;
  margin-bottom:15px;
  border-radius:12px;
}

.login-card h2{
  margin-bottom:8px;
}

.login-card p{
  font-size:14px;
  color:var(--muted);
  margin-bottom:25px;
}

select{
  width:100%;
  padding:14px;
  border-radius:10px;
  border:1px solid #d1d5db;
  margin-bottom:25px;
  font-size:15px;
}

button{
  width:100%;
  padding:14px;
  border:none;
  border-radius:10px;
  background:linear-gradient(135deg,var(--primary),var(--secondary));
  color:#fff;
  font-size:16px;
  font-weight:500;
  cursor:pointer;
  transition:.3s;
}

button:hover{
  transform:translateY(-2px);
  box-shadow:0 10px 20px rgba(37,99,235,0.4);
}

/* About */
.about-section{
  max-width:900px;
  margin:0 auto 80px;
  background:#fff;
  padding:50px;
  border-radius:18px;
  box-shadow:0 10px 30px rgba(0,0,0,0.08);
  text-align:center;
}

.about-section h2{
  font-size:28px;
  margin-bottom:20px;
}

.about-section p{
  font-size:16px;
  line-height:1.7;
  color:#555;
  margin-bottom:15px;
}

/* Footer */
footer{
  background:#fff;
  text-align:center;
  padding:15px;
  font-size:14px;
  color:#6b7280;
  box-shadow:0 -4px 15px rgba(0,0,0,0.05);
}

/* Responsive */
@media(max-width:768px){
  header{
    padding:14px 25px;
  }
  nav{
    display:none;
  }
}
</style>
</head>

<body>

<header>
  <div class="logo">
    <img src="Images/logo.jpg" alt="Logo">
    <div>
      <h1>Student Management</h1>
      <span>Diversity Academy</span>
    </div>
  </div>

  <nav>
    <a href="index.php">Home</a>
    <a href="Students/student.php">Student</a>
    <a href="Teachers/teacher.php">Teacher</a>
    <a href="Admin/admin.php">Admin</a>
  </nav>
</header>

<div class="login-container">
  <div class="login-card">
    <img src="Images/logo.jpg">
    <h2>Login Portal</h2>
    <p>Select your role to continue</p>

    <select id="role">
      <option value="student">Student</option>
      <option value="teacher">Teacher</option>
      <option value="admin">Admin</option>
    </select>

    <button onclick="redirectLogin()">Continue</button>
  </div>
</div>

<section class="about-section">
  <h2>About Diversity Academy</h2>
  <p>Diversity Academy nurtures students from all backgrounds by promoting creativity,
     critical thinking, and collaboration.</p>
  <p>We integrate modern technology to empower learners with digital skills and confidence.</p>
  <p>Our mission is to prepare responsible learners for an evolving digital world.</p>
</section>

<footer>
  © 2026 Diversity Academy | Student Management System
</footer>

<script>
function redirectLogin(){
  const role=document.getElementById("role").value;
  if(role==="student") location.href="Students/student.php";
  if(role==="teacher") location.href="Teachers/teacher.php";
  if(role==="admin") location.href="Admin/admin.php";
}
</script>

</body>
</html>
