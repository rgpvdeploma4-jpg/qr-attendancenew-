<?php
include "db.php";

if(isset($_POST['add'])){

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$department = $_POST['department'];

$query = "INSERT INTO teachers (name, email, password, department) 
VALUES ('$name','$email','$password','$department')";

if(mysqli_query($conn,$query)){
echo "<script>alert('Teacher Added Successfully');</script>";
}else{
echo "<script>alert('Error adding teacher');</script>";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Teacher</title>

<style>
body{
font-family:Arial;
background:linear-gradient(135deg,#43cea2,#185a9d);
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
background:#185a9d;
color:white;
border:none;
border-radius:6px;
cursor:pointer;
}

button:hover{
background:#144b82;
}
</style>

</head>

<body>

<div class="box">

<h2>Add Teacher</h2>

<form method="POST">

<input type="text" name="name" placeholder="Teacher Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<input type="department" name="department" placeholder="department" required>
<button name="add">Add Teacher</button>

</form>

</div>

</body>
</html>