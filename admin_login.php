<?php
session_start();
include "db.php";

if(isset($_POST['login'])){

$email = $_POST['email'];
$pass = $_POST['password'];

$check = mysqli_query($conn, "SELECT * FROM admin WHERE email='$email' AND password='$pass'");

if(mysqli_num_rows($check)>0){

$_SESSION['admin_email']=$email;

echo "<script>alert('Login Success'); window.location='admin_dashboard.php';</script>";

}else{
$error = "Wrong Email or Password";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>

<style>

body{
margin:0;
font-family:Arial;
background:linear-gradient(135deg,#232526,#0f0f0f);
height:100vh;
display:flex;
justify-content:center;
align-items:center;
}

.box{
background:white;
padding:30px;
width:320px;
border-radius:15px;
box-shadow:0 10px 25px rgba(0,0,0,0.2);
text-align:center;
}

h2{
margin-bottom:20px;
color:#333;
}

input{
width:90%;
padding:12px;
margin:10px 0;
border-radius:8px;
border:1px solid #ccc;
outline:none;
}

input:focus{
border-color:#ff7e5f;
}

button{
width:95%;
padding:12px;
background:#ff7e5f;
color:white;
border:none;
border-radius:8px;
cursor:pointer;
font-size:16px;
}

button:hover{
background:#e96a50;
}

.error{
color:red;
margin-top:10px;
}

</style>

</head>

<body>

<div class="box">

<h2>Admin Login</h2>

<form method="POST">

<input type="email" name="email" placeholder="Enter Email" required>

<input type="password" name="password" placeholder="Enter Password" required>

<button name="login">Login</button>

</form>

<?php if(isset($error)){ ?>
<p class="error"><?php echo $error; ?></p>
<?php } ?>

</div>

</body>
</html>