<?php
session_start();
include "db.php";

if(isset($_POST['login'])){

$email = $_POST['email'];
$pass = $_POST['password'];

$check = mysqli_query($conn, "SELECT * FROM teachers WHERE email='$email' AND password='$pass'");
$row = mysqli_fetch_assoc($check);

if(mysqli_num_rows($check)>0){

    $teacher = mysqli_fetch_assoc($check); 
    
    $_SESSION['teacher_email'] = $email;
    $_SESSION['teacher_name'] = $row['name']; 
    
    echo "<script>alert('Login Success'); window.location='teacher_dashboard.php';</script>";
}else{
$error = "Wrong Email or Password";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Teacher Login</title>

<style>

body{
margin:0;
font-family:Arial;
background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
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
border-color:#667eea;
}

button{
width:95%;
padding:12px;
background:#667eea;
color:white;
border:none;
border-radius:8px;
cursor:pointer;
font-size:16px;
}

button:hover{
background:#5a67d8;
}

.error{
color:red;
margin-top:10px;
}

</style>

</head>

<body>

<div class="box">

<h2>Teacher Login</h2>

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