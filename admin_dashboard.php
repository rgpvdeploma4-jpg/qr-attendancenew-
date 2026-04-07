<?php
session_start();
include "db.php";

// Admin login check
if(!isset($_SESSION['admin_email'])){
    header("Location: admin_login.php");
    exit();
}

// ADD STUDENT
if(isset($_POST['add_student'])){
    $name = $_POST['name'] ?? '';
    $roll = $_POST['roll_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $department = $_POST['department'] ?? '';

    if($name && $roll && $email && $department){
        mysqli_query($conn,"INSERT INTO students(name,roll_number,email,department) VALUES('$name','$roll','$email','$department')");
        $msg_student = "Student Added Successfully!";
    }
}

// ADD TEACHER

if(isset($_POST['add_teacher'])){
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $department = isset($_POST['department']) ? trim($_POST['department']) : '';

    if($name != "" && $email != "" && $department != ""){
        mysqli_query($conn,"INSERT INTO teachers(name,email,department) VALUES('$name','$email','$department')");
        $msg_teacher = "Teacher Added Successfully!";
    } else {
        $msg_teacher = "Please fill all fields!";
    }
}

// DELETE STUDENT
if(isset($_GET['delete_student'])){
    $id = intval($_GET['delete_student']);
    mysqli_query($conn,"DELETE FROM students WHERE id='$id'");
    header("Location: admin_dashboard.php?tab=students");
    exit();
}

// DELETE TEACHER
if(isset($_GET['delete_teacher'])){
    $id = intval($_GET['delete_teacher']);
    mysqli_query($conn,"DELETE FROM teachers WHERE id='$id'");
    header("Location: admin_dashboard.php?tab=teachers");
    exit();
}

// Fetch all students
$students = mysqli_query($conn,"SELECT * FROM students ORDER BY id DESC");
$teachers = mysqli_query($conn,"SELECT * FROM teachers ORDER BY id DESC");
$teacher_list = mysqli_query($conn,"SELECT DISTINCT teacher_name, department FROM sessions");
$dept = $_GET['dept'] ?? '';
$teacher = $_GET['teacher'] ?? '';
$name = $_GET['name'] ?? '';
$roll = $_GET['roll'] ?? '';
$subject = $_GET['subject'] ?? '';
$date = $_GET['date'] ?? '';

$attendance_query = "
SELECT a.*, s.subject, s.session_time, s.department, s.teacher_name
FROM attendance a 
JOIN sessions s ON a.session_id=s.id
WHERE 1
";
if($dept != '') $attendance_query .= " AND a.department='$dept'";
if($teacher != '') $attendance_query .= " AND s.teacher_name='$teacher'";
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
<title>Admin Dashboard</title>
<style>
body{
    margin:0;
    font-family:Arial;
    display:flex;
    background:#f4f6f8;
}

/* SIDEBAR */
.sidebar{
    width:220px;
    background:#2c3e50;
    color:white;
    height:100vh;
    position:fixed;
    top:0;
    left:0;
}
.sidebar h2{
    text-align:center;
    padding:20px;
    margin:0;
}
.sidebar a{
    display:block;
    padding:15px;
    color:white;
    text-decoration:none;
    cursor:pointer;
}
.sidebar a:hover{
    background:#1abc9c;
}

/* MAIN */
.main{
    margin-left:220px;
    padding:20px;
    width:100%;
}

/* CARD */
.card{
    background:white;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px #ccc;
    margin-bottom:20px;
}

/* BUTTON */
button, .delete-btn{
    padding:10px;
    background:#3498db;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    margin-top:5px;
}
button:hover, .delete-btn:hover{
    background:#2980b9;
}
.delete-btn{
    background:#e74c3c;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th,td{
    padding:10px;
    border:1px solid #ddd;
    text-align:center;
}

th{
    background:#2c3e50;
    color:white;
}
</style>
</head>
<body>

<div class="sidebar">
<h2>Admin Panel</h2>
<a onclick="showSection('home')">Home</a>
<a onclick="showSection('students')">Manage Students</a>
<a onclick="showSection('teachers')">Manage Teachers</a>
<a onclick="showSection('attendance')">View Attendance</a>
<a href="logout.php">Logout</a>
</div>

<div class="main">

<!-- HOME -->
<div id="home" class="card">
<h2>Welcome Admin</h2>
<p>📚 Welcome to your Smart Attendance System.</p>
</div>

<!-- STUDENTS -->
<div id="students" class="card content-section" style="display:none;">
<h2>Manage Students</h2>

<!-- Add Student Button -->
<form method="POST" action="add_student.php">
    <button type="submit" name="add_student">Add Student</button>
</form>

<!-- List of Students -->
<h3>Existing Students</h3>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Roll Number</th>
<th>Department</th>
<th>Email</th>
<th>Action</th>
</tr>

<?php
$i =  1;
while($stu = mysqli_fetch_assoc($students)){
    echo "<tr>
            <td>".$i."</td>
            <td>".$stu['name']."</td>
            <td>".$stu['roll_number']."</td>
            <td>".$stu['department']."</td>
            <td>".$stu['email']."</td>
            <td>
                <a href='admin_dashboard.php?delete_student=".$stu['id']."' onclick=\"return confirm('Are you sure?')\">Delete</a>
            </td>
          </tr>";
        $i++;
}
?>
</table>
</div>

<!-- TEACHERS -->
<div id="teachers" class="card content-section" style="display:none;">
<h2>Manage Teachers</h2>

<!-- Add Teacher Button -->
<form method="POST" action="add_teacher.php">
    <button type="submit" name="add_teacher">Add Teacher</button>
</form>

<!-- List of Teachers -->
<h3>Existing Teachers</h3>
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Department</th>
<th>Email</th>
<th>Action</th>
</tr>

<?php
$j = 1;
while($tch = mysqli_fetch_assoc($teachers)){
    echo "<tr>
            <td>".$j."</td>
            <td>".$tch['name']."</td>
            <td>".$tch['department']."</td>
            <td>".$tch['email']."</td>
            <td>
                <a href='admin_dashboard.php?delete_teacher=".$tch['id']."' onclick=\"return confirm('Are you sure?')\">Delete</a>
            </td>
          </tr>";
          $j++;
}
?>
</table>
</div>
<!-- ATTENDANCE -->
<div id="attendance" class="card" style="display:none;">
<h2>Attendance Records</h2>

<form method="GET">

<input type="hidden" name="tab" value="attendance">

<select name="dept">
<option value="">All Dept</option>
<option value="CSE" <?php if(($_GET['dept'] ?? '')=='CSE') echo 'selected'; ?>>CSE</option>
<option value="ETE" <?php if(($_GET['dept'] ?? '')=='ETE') echo 'selected'; ?>>ETE</option>
<option value="ME" <?php if(($_GET['dept'] ?? '')=='ME') echo 'selected'; ?>>ME</option>
</select>
<select name="teacher">
<option value="">All Teachers</option>

<?php
mysqli_data_seek($teacher_list, 0);
while($t = mysqli_fetch_assoc($teacher_list)){
    if($dept == '' || $dept == $t['department']){
?>
<option value="<?php echo $t['teacher_name']; ?>"
<?php if(($_GET['teacher'] ?? '') == $t['teacher_name']) echo 'selected'; ?>>
<?php echo $t['teacher_name']; ?>
</option>

<?php } } ?>
</select>
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

<?php
$total_classes = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM sessions"));
$total_present = mysqli_num_rows($attendance);

$percentage = 0;
if($total_classes > 0){
    $percentage = ($total_present / $total_classes) * 100;
}
?>

<p><b>Total Attendance:</b> <?php echo $total_present; ?></p>
<p><b>Attendance %:</b> <?php echo round($percentage,2); ?>%</p>
<?php if(mysqli_num_rows($attendance) == 0){ ?>
<p style="color:red;">No Attendance Found</p>
<?php } ?>
<table>
<tr>
<th>Student Name</th>
<th>Roll Number</th>
<th>Department</th>
<th>Teacher</th>
<th>Subject</th>
<th>Session</th>
<th>Marked At</th>
<th>Date</th>
</tr>

<?php while($att=mysqli_fetch_assoc($attendance)){ ?>

<tr>
<td><?php echo $att['student_name']; ?></td>
<td><?php echo $att['roll_number']; ?></td>
<td><?php echo $att['department']; ?></td>
<td><?php echo $att['teacher_name']; ?></td>
<td><?php echo $att['subject']; ?></td>

<!-- Session Time -->
<td><?php echo $att['session_time']; ?></td>

<!-- Marked At -->
<td><?php echo date("d-m-Y H:i:s", strtotime($att['time'])); ?></td>

<!-- Only Date -->
<td><?php echo date("d-m-Y", strtotime($att['time'])); ?></td>

</tr>

<?php } ?>
</table>
</div>

</div>

<script>
function showSection(id){
    document.getElementById('home').style.display='none';
    document.getElementById('students').style.display='none';
    document.getElementById('teachers').style.display='none';
    document.getElementById('attendance').style.display='none';
    document.getElementById(id).style.display='block';
}

// Open tab if coming after delete/add, default to home
<?php if(in_array($activeTab,['students','teachers','attendance'])){ ?>
showSection('<?php echo $activeTab; ?>');
<?php } else { ?>
showSection('home');
<?php } ?>
</script>

</body>
</html>