<?php  
session_start();  
include "db.php";  
$teacher_name = $_SESSION['teacher_name'];

if(!isset($_SESSION['teacher_email'])){  
    header("Location: teacher_login.php");  
    exit();  
}  

// TIME SLOTS  
$slots = [  
"10:30-11:30",
"11:30-12:30",
"12:30-01:30",
"02:00-03:00",
"03:00-04:00",
"04:00-05:00"
];  

$success_msg = "";

if(isset($_GET['success'])){
    $success_msg = "Session Created Successfully!";
}

// CREATE SESSION
if(isset($_POST['create_session'])){
    

    $subject = $_POST['subject'];
    $teacher_name = $_SESSION['teacher_name'];
    $department = $_POST['department'];
    $session_time = $_POST['session_time'];
    $teacher_latitude = $_POST['teacher_latitude'];
    $teacher_longitude = $_POST['teacher_longitude'];
    if(empty($teacher_latitude) || empty($teacher_longitude)){
        echo "<script>alert('Location not fetched');</script>";
        exit();
    }

    $otp = rand(1000,9999);
    $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    mysqli_query($conn,"INSERT INTO sessions(
        subject,
        teacher_name,
        department,
        session_time,
        otp,
        otp_expiry,
        teacher_latitude,
        teacher_longitude
        )
        VALUES(
        '$subject',
        '$teacher_name',
        '$department',
        '$session_time',
        '$otp',
        '$expiry',
        '$teacher_latitude',
        '$teacher_longitude'
        )") or die(mysqli_error($conn));
   
    header("Location: teacher_dashboard.php?success=1");
    exit();
}
$now = date("Y-m-d H:i:s");

$teacher_name = $_SESSION['teacher_name'];

$sessions = mysqli_query($conn,"
SELECT * FROM sessions 
WHERE otp_expiry > '$now'
AND teacher_name='$teacher_name'
ORDER BY id DESC
");

// ATTENDANCE FILTER
$name = $_GET['name'] ?? '';
$roll = $_GET['roll'] ?? '';
$subject = $_GET['subject'] ?? '';
$date = $_GET['date'] ?? '';

$teacher_name = $_SESSION['teacher_name'];

$attendance_query = "
SELECT a.*, s.subject, s.session_time 
FROM attendance a 
JOIN sessions s ON s.id=a.session_id
WHERE s.teacher_name='$teacher_name'
";

if($name != '') $attendance_query .= " AND a.student_name LIKE '%$name%'";
if($roll != '') $attendance_query .= " AND a.roll_number LIKE '%$roll%'";
if($subject != '') $attendance_query .= " AND s.subject LIKE '%$subject%'";
if($date != '') $attendance_query .= " AND DATE(a.time)='$date'";
$attendance_query .= " ORDER BY a.time DESC";

$attendance = mysqli_query($conn,$attendance_query);
$activeTab = $_GET['tab'] ?? 'home';
?>
<!DOCTYPE html>
<html>
<head>
<title>Teacher Dashboard</title>

<style>
body{margin:0;font-family:Arial;display:flex;background:#f4f6f8;}
.sidebar{width:220px;background:#34495e;color:white;height:100vh;position:fixed;}
.sidebar h2{text-align:center;padding:20px;}
.sidebar a{display:block;padding:15px;color:white;text-decoration:none;cursor:pointer;}
.sidebar a:hover{background:#2c3e50;}

.main{margin-left:220px;padding:20px;width:100%;}

.card{background:white;padding:20px;margin-bottom:20px;border-radius:10px;box-shadow:0 0 10px #ccc;}

button{padding:10px;background:#3498db;color:white;border:none;border-radius:5px;cursor:pointer;}
button:hover{background:#2980b9;}

input,select{width:90%;padding:10px;margin:10px;}
</style>

</head>
<body>

<div class="sidebar">
<h2>Teacher Panel</h2>
<a onclick="show('home')">Home</a>
<a onclick="show('create')">Create Session</a>
<a onclick="show('qr')">Generate QR</a>
<a onclick="show('view')">View Attendance</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">

<!-- HOME -->
<div id="home" class="card">
<h2>Welcome <?php echo $teacher_name; ?> </h2>
<?php if($success_msg != ""){ ?>
<p style="color:green;"><?php echo $success_msg; ?></p>
<?php } ?>
<p>
📚 Welcome to your Smart Attendance System.
</p>

</div>

<!-- CREATE SESSION -->
<div id="create" class="card" style="display:none;">
<h2>Create Session</h2>

<form method="POST" id="sessionForm">

<input type="text" name="subject" placeholder="Subject" required>

<select name="department">
<option value="CSE">CSE</option>
<option value="ETE">ETE</option>
<option value="ME">ME</option>
</select>
<select name="session_time">
<?php foreach($slots as $s){ echo "<option>$s</option>"; } ?>
</select>
<input type="hidden" name="teacher_latitude" id="teacher_latitude">
<input type="hidden" name="teacher_longitude" id="teacher_longitude">
<button type="button" id="createBtn">Create Session</button>


</form>
</div>

<!-- QR GENERATE -->
<div id="qr" class="card" style="display:none;">
<h2>QR Codes (Department Wise)</h2>

<?php 
if(mysqli_num_rows($sessions) > 0){

while($session = mysqli_fetch_assoc($sessions)){ 

$remaining = strtotime($session['otp_expiry']) - time();
if($remaining <= 0) continue;

$link = "http://localhost/qr-attendance/student_dashboard.php?session_id=".$session['id']."&dept=".$session['department'];
?>

<div style="border:1px solid #ccc; padding:15px; margin-bottom:15px;">

<p><b>Subject:</b> <?php echo $session['subject']; ?></p>
<p><b>Teacher:</b> <?php echo $session['teacher_name']; ?></p>
<p><b>Dept:</b> <?php echo $session['department']; ?></p>
<p><b>Time:</b> <?php echo $session['session_time']; ?></p>
<p><b>OTP:</b> <?php echo $session['otp']; ?></p>

<input type="text" value="<?php echo $link; ?>">

<br><br>

<img src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo urlencode($link); ?>&size=150x150">

<p style="color:red;font-weight:bold;">
Time Left: <span class="timer" data-time="<?php echo $remaining; ?>"></span>
</p>

</div>

<?php } 

}else{
    echo "<p style='color:red;'>No Active Sessions</p>";
}
?>

</div>

<!-- ATTENDANCE -->
<div id="view" class="card" style="display:none;">
<h2>Attendance</h2>
<form method="GET">

<input type="hidden" name="tab" value="view">

<input type="text" name="name" placeholder="Search Name"
value="<?php echo $_GET['name'] ?? ''; ?>">

<input type="text" name="roll" placeholder="Search Roll"
value="<?php echo $_GET['roll'] ?? ''; ?>">

<input type="text" name="subject" placeholder="Search Subject"
value="<?php echo $_GET['subject'] ?? ''; ?>">

<input type="date" name="date"
value="<?php echo $_GET['date'] ?? ''; ?>">


<button type="submit">Search</button>

</form>

<table border="1" width="100%">
<tr>
<th>Name</th>
<th>Roll</th>
<th>Dept</th>
<th>Subject</th>
<th>Time</th>
<th>Date</th>
</tr>

<?php while($row=mysqli_fetch_assoc($attendance)){ ?>

<tr>
<td><?php echo $row['student_name']; ?></td>
<td><?php echo $row['roll_number']; ?></td>
<td><?php echo $row['department']; ?></td>
<td><?php echo $row['subject']; ?></td>
<td><?php echo $row['session_time']; ?></td>
<td><?php echo date('Y-m-d', strtotime($row['time'])); ?></td>
</tr>

<?php } ?>

</table>
</div>

</div>

<script>

// NAVIGATION
function show(id){
document.getElementById('home').style.display='none';
document.getElementById('create').style.display='none';
document.getElementById('qr').style.display='none';
document.getElementById('view').style.display='none';

document.getElementById(id).style.display='block';
}

<?php if(in_array($activeTab,['home','create','qr','view'])){ ?>
show('<?php echo $activeTab; ?>');
<?php } else { ?>
show('home');
<?php } ?>


let timers = document.querySelectorAll(".timer");

timers.forEach(function(el){
let timeLeft = parseInt(el.getAttribute("data-time"));

let t = setInterval(function(){

let min = Math.floor(timeLeft/60);
let sec = timeLeft%60;

el.innerHTML = min+"m "+sec+"s";

timeLeft--;

if(timeLeft < 0){
clearInterval(t);
el.innerHTML = "Expired";
}

},1000);

});
document.getElementById("createBtn").addEventListener("click", function(){

if(navigator.geolocation){
    navigator.geolocation.getCurrentPosition(function(position){

        document.getElementById("teacher_latitude").value = position.coords.latitude;
        document.getElementById("teacher_longitude").value = position.coords.longitude;

        // hidden input add for POST trigger
        let input = document.createElement("input");
        input.type = "hidden";
        input.name = "create_session";
        input.value = "1";
        document.getElementById("sessionForm").appendChild(input);

        document.getElementById("sessionForm").submit();

    }, function(){
        alert("Location allow karo tabhi session create hoga");
    });
}else{
    alert("Browser location support nahi karta");
}

});
</script>

</body>
</html>