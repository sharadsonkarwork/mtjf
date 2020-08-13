<?php
 $dba="trevy89_waggingpal_db"; 
 $host="localhost";
 $user="trevy89_wagging1";
 $pass="waggingpal@db";

$conn        =    mysqli_connect($host,$user,$pass) or die('Server Information is not Correct');
mysqli_select_db($conn, $dba) or die('Database Information is not correct');

$ids = base64_decode($_REQUEST['auth_key']);

if(isset($_POST['submit'])){

$id = $_POST['id'];
  $password = $_POST['password'];
     $confirmpas = $_POST['confirmpas'];

    if(strlen($password) >= 6)
    {
   		if($password == $confirmpas){

			$vendor = mysqli_query($conn,"SELECT * FROM vendor WHERE vendor_id = '$id'");
			if(mysqli_num_rows($vendor)>0)
			{ 
				$ress = mysqli_fetch_assoc($vendor);
				
				if($ress['password'] == md5($password)){
						 	echo "<script>alert('Password Successfully Changed');</script>";
				}else{
					$update = mysqli_query($conn,"UPDATE vendor SET password = '".md5($password)."' WHERE vendor_id = '$ids'");
					if($update){
							 echo "<script>alert('Password Successfully Changed');</script>";
					}else{
						echo "<script>alert('Password Not Changed');</script>";
					}
				}
			}else{
				echo "<script>alert('User does not exists');</script>";
			}
		}else{
			echo "<script>alert('Password does not match');</script>";
		}
	}else
	{
		echo "<script>alert('Minimum 6 character required');</script>";
	}	
}




?>
<!DOCTYPE HTML>
<html>
<head>
<title>Change Password</title>
<link href="css/style.css" rel="stylesheet" type="text/css" media="all"/>
<!-- Custom Theme files -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<meta name="keywords" content="Reset Password Form Responsive, Login form web template, Sign up Web Templates, Flat Web Templates, Login signup Responsive web template, Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<!--google fonts-->
<link href='//fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900' rel='stylesheet' type='text/css'>
</head>
<body>
<!--element start here-->
<div class="elelment">
	<div class="element-main">
	<h2 style="color: #333333; margin-top: -10px; margin-bottom: 25px; font-weight: normal; font-size: 48px; font-family: Georgia, 'Times New Roman', Times, serif"> <em style="  margin-left: 0%;">WaggingPal</h2><br>

		<h1 style="font-size: 32px; font-family: Georgia, 'Times New Roman', Times, serif; color: #4e3227; margin-top: 0px; margin-bottom: 0px; font-weight: normal;">Change Password</h1>
		<br />
		<form method="post" onsubmit="return checklength()">
			<!-- <label>New Password</label> -->
			<input type="password" name='password' style="color:black" id="passs" placeholder="Enter New Password" required>
			<input type="hidden" name='id' value="<?php echo $ids;?>">

			<br />
			<!-- <label>Confirm Password</label> -->
			<input type="password" name='confirmpas' style="color:black" placeholder="Confirm New Password" required> 
			<input type="submit" name='submit' value="Change Password">
		</form>
	</div>
</div>
<!-- <div class="copy-right">
			<p>@Copyright WaggingPal ,All rights reserved.  <a href="http://w3layouts.com/" target="_blank">  W3layouts </a></p>
</div> -->

<!--element end here-->
</body>
<script>
function checklength(val)
{

  var passs = document.getElementById('passs').value.length;
  
  if(passs < 6){
  alert("Minimum 6 character password required");
  return false;
  }
}
</script>
</html>