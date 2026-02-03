<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

include '../Database/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Fetch current password
    $stmt = $conn->prepare("SELECT password FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $_SESSION['student_id']);
    $stmt->execute();
    $stmt->bind_result($hashed);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hashed)) {
        $message = "❌ Current password is incorrect!";
    } elseif ($new !== $confirm) {
        $message = "⚠ New passwords do not match!";
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
        $update->bind_param("ss", $new_hash, $_SESSION['student_id']);
        if ($update->execute()) {
            $message = "✅ Password changed successfully!";
        } else {
            $message = "❌ Error updating password.";
        }
        $update->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fc;
            color: #333;
        }
        .sidebar {
            width: 240px;
            background: #00bfff;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            padding: 20px 15px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        .sidebar h2 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            padding: 12px 15px;
            margin-bottom: 10px;
            text-decoration: none;
            border-radius: 10px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
        }
        .sidebar a.logout {
            margin-top: auto;
            background: #dc3545;
        }
        .sidebar a.logout:hover {
            background: #c82333;
        }

        .main {
            margin-left: 240px;
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, #00bfff 0%, #0099cc 100%);
            color: #fff;
            padding: 20px 25px;
            border-radius: 12px;
            font-size: 22px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,191,255,0.3);
            font-weight: 600;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            max-width: 550px;
            margin: auto;
        }

        h2 {
            color: #00bfff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            padding-bottom: 15px;
            border-bottom: 3px solid #00bfff;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            font-size: 15px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        input:focus {
            outline: none;
            border-color: #00bfff;
            box-shadow: 0 0 0 3px rgba(0,191,255,0.1);
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #00bfff 0%, #0099cc 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,191,255,0.3);
            margin-top: 10px;
        }

        button:hover {
            background: linear-gradient(135deg, #0099cc 0%, #007aa3 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,191,255,0.4);
        }

        .msg {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .msg.error {
            color: #721c24;
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .success {
            color: #155724;
            background: #d4edda;
            border-left: 4px solid #28a745;
        }

        a.back-link {
            text-decoration: none;
            color: #00bfff;
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 6px;
            transition: background 0.3s;
            font-weight: 500;
        }
        a.back-link:hover {
            background: #f0f9ff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📚 Dashboard</h2>
        <a href="student_dashboard.php">🏠 Home</a>
        <a href="attendance.php">📅 Attendance</a>
        <a href="results.php">📊 Results</a>
        <a href="profile.php">👤 Profile</a>
        <a href="change_password.php">🔑 Change Password</a>
        <a href="logout.php" class="logout">🚪 Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main">
        <div class="header">🔑 Change Your Password</div>

        <div class="card">
            <?php if ($message): ?>
                <div class="msg <?php echo (strpos($message, '✅') !== false) ? 'success' : ''; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>

                <label>New Password:</label>
                <input type="password" name="new_password" required>

                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>

                <button type="submit">Update Password</button>
            </form>

            <a class="back-link" href="student_dashboard.php">⬅ Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
