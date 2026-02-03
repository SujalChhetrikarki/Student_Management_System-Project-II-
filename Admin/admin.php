<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login | SMS</title>

<style>
:root{
  --primary:#2563eb;
  --danger:#dc2626;
  --dark:#0f172a;
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
  background:linear-gradient(135deg,#0f172a,#1e293b);
}

/* Main */
main{
  flex:1;
  display:flex;
  justify-content:center;
  align-items:center;
  padding:40px 20px;
}

/* Admin Card */
.admin-box{
  background:var(--card);
  width:380px;
  padding:45px;
  border-radius:20px;
  box-shadow:0 30px 60px rgba(0,0,0,.45);
  animation:fadeUp .8s ease;
}

@keyframes fadeUp{
  from{opacity:0; transform:translateY(30px);}
  to{opacity:1; transform:translateY(0);}
}

.admin-box img{
  display:block;
  margin:0 auto 18px;
  width:90px;
  height:90px;
  border-radius:14px;
}

.admin-box h2{
  text-align:center;
  margin-bottom:8px;
}

.admin-box p{
  text-align:center;
  font-size:14px;
  color:var(--muted);
  margin-bottom:28px;
}

/* Form */
label{
  font-size:14px;
  color:#374151;
}

input{
  width:100%;
  padding:13px 14px;
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

/* Buttons */
button{
  width:100%;
  padding:14px;
  border:none;
  border-radius:12px;
  background:linear-gradient(135deg,var(--primary),#1e40af);
  color:#fff;
  font-size:16px;
  font-weight:500;
  cursor:pointer;
  transition:.3s;
}

button:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 25px rgba(37,99,235,.45);
}

.signup-btn{
  display:block;
  margin-top:14px;
  text-align:center;
  padding:12px;
  border-radius:12px;
  background:#16a34a;
  color:#fff;
  font-size:15px;
  text-decoration:none;
  transition:.3s;
}

.signup-btn:hover{
  background:#15803d;
}

.note{
  text-align:center;
  font-size:13px;
  color:#b91c1c;
  margin-top:14px;
}

/* Responsive */
@media(max-width:480px){
  .admin-box{padding:35px;}
}
</style>
</head>

<body>
<div id="header"></div>
<main>
  <div class="admin-box">

    <a href="../index.php">
      <img src="../Images/logo.jpg" alt="Logo">
    </a>

    <h2>Admin Login</h2>
    <p>Authorized access only</p>

    <form action="admin_login_process.php" method="post">
      <label>Admin Email</label>
      <input type="email" name="email" placeholder="admin@academy.com" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>

      <button type="submit">Login</button>
    </form>

    <a href="#" class="signup-btn" id="secureSignUp">Sign Up as Admin</a>
    <p class="note">⚠ Restricted to system administrators</p>

    <!-- Hidden admin access code form -->
    <form action="verify_admin_code.php" method="POST" id="codeForm" style="display:none;">
      <input type="hidden" name="code" id="hiddenCode">
    </form>

  </div>
</main>

<div id="footer"></div>
<script>
    // Load header and footer
    fetch("adminheader.php")
        .then(res => res.text())
        .then(data => document.getElementById("header").innerHTML = data);

    fetch("adminfooter.php")
        .then(res => res.text())
        .then(data => document.getElementById("footer").innerHTML = data);

    // Secure Admin Signup Code
    document.getElementById("secureSignUp").addEventListener("click", function (e) {
        e.preventDefault();
        const code = prompt("🔒 Enter Admin Access Code:");
        if (code) {
            document.getElementById("hiddenCode").value = code;
            document.getElementById("codeForm").submit();
        }
    });
</script>

</body>
</html>
