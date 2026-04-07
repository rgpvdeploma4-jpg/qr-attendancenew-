<?php
include "db.php";

if(isset($_POST['add'])){

$email = $_POST['email'];
$password = $_POST['password'];

$query = "INSERT INTO admin (email, password) 
VALUES ('$email','$password')";

if(mysqli_query($conn,$query)){
echo "<script>alert('Admin Added Successfully');</script>";
}else{
echo "<script>alert('Error adding admin');</script>";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Admin</title>

<style>
body{
font-family:Arial;
background:linear-gradient(135deg,#ff9966,#ff5e62);
height:100vh;
display:flex;
justify-content:center;
align-items:center;
}

.box{
background:white;
padding:25px;
width:320px;
border-radius:12px;
box-shadow:0 10px 25px rgba(0,0,0,0.2);
text-align:center;
}

h2{
margin-bottom:15px;
}

input{
width:90%;
padding:10px;
margin:10px;
border-radius:6px;
border:1px solid #ccc;
}

button{
width:90%;
padding:10px;
background:#ff5e62;
color:white;
border:none;
border-radius:6px;
cursor:pointer;
}

button:hover{
background:#e14c50;
}
</style>

</head>

<body>

<div class="box">

<h2>Add Admin</h2>

<form method="POST">

<input type="email" name="email" placeholder="Admin Email" required>
<input type="password" name="password" placeholder="Password" required>

<button name="add">Add Admin</button>

</form>

</div>

</body>
</html>