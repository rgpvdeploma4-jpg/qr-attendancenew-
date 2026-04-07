<?php
session_start();
include "db.php";

// PHPMailer include
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// SEND OTP FUNCTION 
function sendOTP($email,$otp){

$mail = new PHPMailer(true);

try{
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'rgpvdiploma113@gmail.com'; 
$mail->Password = 'oaoufuazitlgwpuo';     
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = 'Student Login Verification - OTP';

$mail->Body = "
Hello Student,<br><br>

Your login OTP is: <b>$otp</b><br><br>

This OTP is valid for 5 minutes.<br><br>

If you did not request this, please ignore this email.<br><br>

Thanks,<br>
Attendance System
";

$mail->send();

}catch(Exception $e){
echo "<p style='color:red;'>OTP send failed</p>";
}
}

// SEND OTP 
if(isset($_POST['send_otp'])){

$email = mysqli_real_escape_string($conn, $_POST['email']);
$_SESSION['otp_email'] = $email;

$check = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");

if(mysqli_num_rows($check) > 0){

$otp = rand(100000,999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

mysqli_query($conn, "UPDATE students SET otp='$otp', otp_expiry='$expiry' WHERE email='$email'");

sendOTP($email,$otp);

$msg = "OTP sent to your email";

}else{
$error = "Email not registered";
}
}

//  VERIFY OTP 
if(isset($_POST['verify_otp'])){

$email = $_SESSION['otp_email'];
$otp = $_POST['otp'];
$now = date("Y-m-d H:i:s");

$check = mysqli_query($conn, "SELECT * FROM students WHERE email='$email' AND otp='$otp' AND otp_expiry > '$now'");

if(mysqli_num_rows($check) > 0){

$_SESSION['student_email'] = $email;
unset($_SESSION['otp_email']);

echo "<script>alert('Login Successful'); window.location='student_dashboard.php';</script>";

}else{
$error = "Wrong or expired OTP";
}
}

// PASSWORD LOGIN
if(isset($_POST['login_pass'])){

$email = mysqli_real_escape_string($conn, $_POST['email']);
$pass = $_POST['password'];

$check = mysqli_query($conn, "SELECT * FROM students WHERE email='$email' AND password='$pass'");

if(mysqli_num_rows($check) > 0){

$_SESSION['student_email'] = $email;

echo "<script>alert('Login Successful'); window.location='student_dashboard.php';</script>";

}else{
$error = "Wrong Password";
}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Login</title>

<style>
body{
font-family: 'Segoe UI', sans-serif;
margin:0;
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background: linear-gradient(135deg, #141e30, #243b55);
}

.box{
background:white;
width:340px;
padding:25px;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,0.2);
text-align:center;
animation: fadeIn 0.6s ease-in-out;
}

h2{
margin-bottom:15px;
color:#333;
}

input{
width:90%;
padding:12px;
margin:10px 0;
border-radius:8px;
border:1px solid #ccc;
font-size:14px;
outline:none;
transition:0.3s;
}

input:focus{
border-color:#667eea;
box-shadow:0 0 5px rgba(102,126,234,0.5);
}

button{
padding:12px;
background:#667eea;
color:white;
border:none;
cursor:pointer;
width:95%;
border-radius:8px;
font-weight:bold;
transition:0.3s;
}

button:hover{
background:#5a67d8;
}

.link{
color:#667eea;
cursor:pointer;
margin-top:10px;
display:block;
font-size:14px;
}

p{
font-size:14px;
}

@keyframes fadeIn{
from{opacity:0; transform:translateY(20px);}
to{opacity:1; transform:translateY(0);}
}
</style>

</head>

<body>

<div class="box">

<h2>Student Login</h2>

<?php
if(isset($msg)) echo "<p style='color:green;'>$msg</p>";
if(isset($error)) echo "<p style='color:red;'>$error</p>";
?>

<!-- EMAIL -->
<form method="POST">
<input type="email" name="email" placeholder="Enter Email"
value="<?php echo isset($_SESSION['otp_email']) ? $_SESSION['otp_email'] : ''; ?>" required>

<button name="send_otp">Send OTP</button>
</form>

<!-- OTP -->
<?php if(isset($_SESSION['otp_email'])){ ?>
<form method="POST">
<input type="text" name="otp" placeholder="Enter OTP" required>
<button name="verify_otp">Login</button>
</form>
<?php } ?>

<p class="link" onclick="showPassword()">Try another way</p>

<!-- PASSWORD LOGIN -->
<div id="passSection" style="display:none;">
<form method="POST">
<input type="email" name="email" placeholder="Enter Email" required>
<input type="password" name="password" placeholder="Enter Password" required>
<button name="login_pass">Login</button>
</form>
</div>

</div>

<script>
function showPassword(){
document.getElementById("passSection").style.display="block";
}
</script>

</body>
</html>