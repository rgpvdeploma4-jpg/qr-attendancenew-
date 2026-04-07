<?php
include "db.php";

if(isset($_POST['add'])){

$name = $_POST['name'];
$roll = $_POST['roll'];
$email = $_POST['email'];
$password = $_POST['password'];
$department = $_POST['department'];

$query = "INSERT INTO students (name, roll_number, email, password, department) 
VALUES ('$name','$roll','$email','$password','$department')";

if(mysqli_query($conn,$query)){
echo "<script>alert('Student Added Successfully');</script>";
}else{
echo "<script>alert('Error');</script>";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Student</title>

<style>
body{
font-family:Arial;
text-align:center;
background:#f4f4f4;
padding-top:50px;
}

.box{
background:white;
width:300px;
margin:auto;
padding:20px;
border-radius:10px;
box-shadow:0 0 10px #ccc;
}

input{
width:90%;
padding:10px;
margin:10px;
}

button{
width:90%;
padding:10px;
background:green;
color:white;
border:none;
}

</style>

</head>

<body>

<div class="box">

<h2>Add Student</h2>

<form method="POST">

<input type="text" name="name" placeholder="Name" required>
<input type="text" name="roll" placeholder="Roll Number" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<input type="department" name="department" placeholder="department" required>
<button name="add">Add Student</button>

</form>

</div>

</body>
</html>