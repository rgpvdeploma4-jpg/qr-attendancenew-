<?php
$conn = mysqli_connect("localhost", "root", "", "qr-attendance");

if(!$conn){
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
