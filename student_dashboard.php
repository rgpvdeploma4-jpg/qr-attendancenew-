<?php
session_start();
include "db.php";
$activeTab = $_GET['tab'] ?? 'home'; // Already top me add karo

$session_id = $_GET['session_id'] ?? '';
// LOGIN CHECK
if(!isset($_SESSION['student_email'])){
    header("Location: student_login.php");
    exit();
}

$email = $_SESSION['student_email'];

// FETCH STUDENT
$student_query = mysqli_query($conn,"SELECT * FROM students WHERE email='$email'");
$student = mysqli_fetch_assoc($student_query);
if(!$student){
    session_destroy();
    header("Location: student_login.php");
    exit();
}

// MARK ATTENDANCE POST HANDLER
if(isset($_POST['mark'])){

    $otp = $_POST['otp'];
    $session_id = $_POST['session_id'];
    $now = date("Y-m-d H:i:s");

    $check = mysqli_query($conn,"
        SELECT * FROM sessions 
        WHERE id='$session_id' 
        AND otp='$otp' 
        AND otp_expiry > '$now'
    ");

    if(mysqli_num_rows($check) > 0){
     
$session_data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM sessions WHERE id='$session_id'"));


if($session_data['department'] != $student['department']){
    echo "<script>alert('Wrong Department QR!');</script>";
    exit();
}

        // Already marked check
        $already = mysqli_query($conn,"
            SELECT * FROM attendance 
            WHERE student_id='".$student['id']."' 
            AND session_id='$session_id'
        ");

        if(mysqli_num_rows($already)==0){

            mysqli_query($conn,"
                INSERT INTO attendance(student_id,session_id,student_name,roll_number,department,time)
                VALUES('".$student['id']."','$session_id','".$student['name']."','".$student['roll_number']."','".$student['department']."',NOW())
            ");

            echo "<script>alert('Attendance Marked Successfully');</script>";

        }else{
            echo "<script>alert('Already Marked');</script>";
        }

    } else {
        echo "<script>alert('Invalid or Expired OTP');</script>";
    }
}
$active_sessions = mysqli_query($conn, "
SELECT * FROM sessions
WHERE department='".$student['department']."'
AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) <= 15
ORDER BY id DESC
");
if(isset($_POST['location_mark'])){

    $session_id = $_POST['session_id'];

    $student_lat = $_POST['student_lat'];
    $student_long = $_POST['student_long'];

    $session_query = mysqli_query($conn,"SELECT * FROM sessions WHERE id='$session_id'");
    $session = mysqli_fetch_assoc($session_query);
    if($session['department'] != $student['department']){
        echo "<script>alert('Wrong Department');</script>";
        exit();
    }

    $teacher_lat = $session['teacher_latitude'];
    $teacher_long = $session['teacher_longitude'];

    function distanceMeter($lat1, $lon1, $lat2, $lon2){
        $earth = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earth * $c;
    }

    $distance = distanceMeter($student_lat, $student_long, $teacher_lat, $teacher_long);

    if($distance <= 30){

        $already = mysqli_query($conn,"
        SELECT * FROM attendance
        WHERE student_id='".$student['id']."'
        AND session_id='$session_id'
        ");

        if(mysqli_num_rows($already)==0){

            mysqli_query($conn,"
            INSERT INTO attendance(student_id,session_id,student_name,roll_number,department,time)
            VALUES(
            '".$student['id']."',
            '$session_id',
            '".$student['name']."',
            '".$student['roll_number']."',
            '".$student['department']."',
            NOW()
            )
            ");

            echo "<script>alert('Attendance Marked Successfully');</script>";

        }else{
            echo "<script>alert('Attendance Already Marked');</script>";
        }

    }else{
        echo "<script>alert('You are outside the allowed classroom area');</script>";
    }
}
// FETCH ATTENDANCE
$subject = $_GET['subject'] ?? '';
$date = $_GET['date'] ?? '';
$attendance_query_str = "
SELECT a.*, s.subject, s.session_time, s.department, a.student_name, a.roll_number
FROM attendance a
JOIN sessions s ON a.session_id=s.id
WHERE a.student_id='".$student['id']."'
";

if($subject != '') $attendance_query_str .= " AND s.subject LIKE '%$subject%'";
if($date != '') $attendance_query_str .= " AND DATE(a.time)='$date'";

$attendance_query_str .= " ORDER BY a.time DESC";  
$attendance_query = mysqli_query($conn, $attendance_query_str);
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.7/html5-qrcode.min.js"></script>

<style>
body{margin:0;font-family:Arial;display:flex;background:#f4f6f8;}
.sidebar{width:220px;background:#2c3e50;color:white;height:100vh;position:fixed;top:0;left:0;}
.sidebar h2{text-align:center;padding:20px;margin:0;}
.sidebar a{display:block;padding:15px;color:white;text-decoration:none;cursor:pointer;}
.sidebar a:hover{background:#1abc9c;}
.main{margin-left:220px;padding:20px;width:100%;}
.card{background:white;padding:20px;border-radius:10px;box-shadow:0 0 10px #ccc;margin-bottom:20px;}
button{padding:10px;background:#3498db;color:white;border:none;border-radius:5px;cursor:pointer;}
button:hover{background:#2980b9;}
#reader{margin-top:20px;width:100%;max-width:400px;}
input{padding:10px;width:90%;margin-top:10px;border-radius:5px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;border:1px solid #ddd;text-align:center;}
th{background:#2c3e50;color:white;}
</style>
</head>
<body>

<div class="sidebar">
<h2>Student Panel</h2>
<a onclick="showSection('home')">Home</a>
<a onclick="showSection('profile')">Profile</a>
<a onclick="showSection('mark')">Mark Attendance</a>
<a onclick="showSection('view')">View Attendance</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">

<!-- HOME -->
<div id="home" class="card">
<h2>Welcome <?php echo $student['name']; ?></h2>
<p>📚 Welcome to your Smart Attendance System.</p>
</div>

<!-- PROFILE -->
<div id="profile" class="card" style="display:none;">
<h2>Profile</h2>
<p><b>Name:</b> <?php echo $student['name']; ?></p>
<p><b>Roll:</b> <?php echo $student['roll_number']; ?></p>
<p><b>Department:</b> <?php echo $student['department']; ?></p>
</div>

<!-- MARK ATTENDANCE -->
<div id="mark" class="card" style="display:none;">
<h2>Mark Attendance</h2>

<h3>Active Sessions</h3>

<?php
if(mysqli_num_rows($active_sessions) > 0){
    while($s = mysqli_fetch_assoc($active_sessions)){
?>

<div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;border-radius:10px;">
    <p><b>Subject:</b> <?php echo $s['subject']; ?></p>
    <p><b>Department:</b> <?php echo $s['department']; ?></p>
    <p><b>Teacher:</b> <?php echo $s['teacher_name']; ?></p>
    <p><b>Time Slot:</b> <?php echo $s['session_time']; ?></p>

    <button type="button"
    onclick="markByLocation(
    '<?php echo $s['id']; ?>',
    '<?php echo $s['teacher_latitude']; ?>',
    '<?php echo $s['teacher_longitude']; ?>'
    )">
    Mark Attendance
    </button>
</div>

<?php
    }
}else{
    echo "<p style='color:red;'>No Active Session</p>";
}
?>

<hr><br>

<h3>QR + OTP Method</h3>
<button onclick="startQR()">Scan QR & Mark Attendance</button>
<div id="reader"></div>

<form method="POST" id="otpBox" style="display:none;">
<input type="hidden" name="session_id" id="session_id">

<p><b>Name:</b> <?php echo $student['name']; ?></p>
<p><b>Roll:</b> <?php echo $student['roll_number']; ?></p>
<p><b>Department:</b> <?php echo $student['department']; ?></p>

<input type="text" name="otp" placeholder="Enter OTP" required>
<button name="mark">Submit Attendance</button>
</form>

<form method="POST" id="locationAttendanceForm" style="display:none;">
    <input type="hidden" name="location_mark" value="1">
    <input type="hidden" name="session_id" id="location_session_id">
    <input type="hidden" name="student_lat" id="student_lat">
<input type="hidden" name="student_long" id="student_long">
</form>

</div>

<!-- VIEW ATTENDANCE -->
<div id="view" class="card" style="display:none;">
<h2>Attendance Records</h2>
<form method="GET">
<input type="hidden" name="tab" value="view"> 
<input type="text" name="subject" placeholder="Search Subject" value="<?php echo $_GET['subject'] ?? ''; ?>">
<input type="date" name="date" value="<?php echo $_GET['date'] ?? ''; ?>">
<button type="submit">Search</button>
</form>

<table>
<tr>
<th>Name</th>
<th>Roll Number</th>
<th>Subject</th>
<th>Department</th>
<th>Time</th>
<th>Date</th>
</tr>

<?php while($row=mysqli_fetch_assoc($attendance_query)){ ?>
<tr>
<td><?php echo $row['student_name']; ?></td>
<td><?php echo $row['roll_number']; ?></td>
<td><?php echo $row['subject']; ?></td>
<td><?php echo $row['department']; ?></td>
<td><?php echo $row['session_time']; ?></td>
<td><?php echo date("Y-m-d", strtotime($row['time'])); ?></td>
</tr>
<?php } ?>
</table>
</div>

<script>
function showSection(id){
    document.getElementById('home').style.display='none';
    document.getElementById('profile').style.display='none';
    document.getElementById('mark').style.display='none';
    document.getElementById('view').style.display='none';
    document.getElementById(id).style.display='block';
}

<?php if(in_array($activeTab, ['home','profile','mark','view'])){ ?>
showSection('<?php echo $activeTab; ?>');
<?php } else { ?>
showSection('home');
<?php } ?>

function startQR(){
let html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
{ facingMode: "environment" },
{ fps:10, qrbox:250 },
(qrCodeMessage)=>{
alert("QR Scanned: "+qrCodeMessage);
html5QrCode.stop();
document.getElementById('otpBox').style.display='block';
let url = new URL(qrCodeMessage);
let sessionId = url.searchParams.get("session_id");

document.getElementById('session_id').value = sessionId;
},
(error)=>{}
).catch(err=>{
alert("Camera not found or permission denied. Use a device with camera.");
});
}
function markByLocation(sessionId, teacherLat, teacherLong){

if(!navigator.geolocation){
    alert("Location support nahi hai");
    return;
}

navigator.geolocation.getCurrentPosition(function(position){

    document.getElementById("location_session_id").value = sessionId;
    document.getElementById("student_lat").value = position.coords.latitude;
    document.getElementById("student_long").value = position.coords.longitude;

    document.getElementById("locationAttendanceForm").submit();

}, function(){
    alert("Location allow karo");
});
}
</script>

</body>
</html>