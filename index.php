<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Attendance System</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}

body {
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    background: #fff;
    padding: 50px 30px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    text-align: center;
    width: 350px;
    position: relative;
}

.container img.logo {
    width: 100px;
    margin-bottom: 20px;
}

.container h1 {
    margin-bottom: 30px;
    color: #333;
    font-size: 24px;
}

.button {
    display: block;
    width: 100%;
    padding: 15px;
    margin: 15px 0;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.4s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.teacher-btn {
    background: #3498db;
    color: #fff;
}

.teacher-btn:hover {
    background: #2980b9;
    transform: translateY(-3px);
}

.student-btn {
    background: #2ecc71;
    color: #fff;
}

.student-btn:hover {
    background: #27ae60;
    transform: translateY(-3px);
}

.admin-btn {
    background: #e67e22;
    color: #fff;
}

.admin-btn:hover {
    background: #d35400;
    transform: translateY(-3px);
}

.footer {
    margin-top: 25px;
    font-size: 12px;
    color: #888;
}
</style>
</head>
<body>
<div class="container">
    <img src="YOUR_LOGO.png" alt="Project Logo" class="logo">
    <h1>QR Attendance System</h1>
    
    <button class="button teacher-btn" onclick="window.location.href='teacher_login.php'">Teacher Login</button>
    <button class="button student-btn" onclick="window.location.href='student_login.php'">Student Login</button>
    <button class="button admin-btn" onclick="window.location.href='admin_login.php'">Admin Login</button>
    
    <div class="footer">Powered by Govt. Polytechnic College Dewas</div>
</div>
</body>
</html>