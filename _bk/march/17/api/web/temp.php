<?php
 $dba="jokamo"; 
 $host="localhost";
 $user="zokamo";
 $pass="r9tfVRe4AKBbDCc5";

$conn = mysqli_connect($host,$user,$pass) or die('Server Information is not Correct');
mysqli_select_db($conn, $dba) or die('Database Information is not correct');

$ids = base64_decode($_REQUEST['auth_key']);

if(isset($_POST['submit'])){

$id = $_POST['id'];
  $password = $_POST['password'];
     $confirmpas = $_POST['confirmpas'];

    if(strlen($password) >= 6)
    {
   		if($password == $confirmpas){

			$user = mysqli_query($conn,"SELECT * FROM user WHERE user_id = '$id'");
			
			if(mysqli_num_rows($user)>0)
			{ 
				$ress = mysqli_fetch_assoc($user);

				if($ress['verify_code'] != '')
				{	
					
						if($ress['password'] == sha1($password)){

							 	echo "<script>alert('Password Successfully Changed');</script>";
							 	//$message= '<img src="https://www.jingadala.com/uploads/logo_image/Jingadala_Popup_01.jpg" style="width:80%; height:100%; margin-left:12%;">';
                                    
								echo $message;exit;
						}else{
							$update = mysqli_query($conn,"UPDATE user SET password = '".sha1($password)."', verify_code = '' WHERE user_id = '$ids'");
							if($update){
								echo "<script>alert('Password Successfully Changed');</script>";
	                		    //$message= '<img src="https://www.jingadala.com/uploads/logo_image/Jingadala_Popup_01.jpg" style="width:80%; height:100%; margin-left:12%;">';
	                		    $message = "your password change successfully";
								echo $message;exit;
							}else{
								$msg = "Password has not been changed";
							}
						}
					
				}else
				{
					/*$sel_temp = mysqli_query($conn,"SELECT * FROM email_template_descriptions WHERE id = '4'");
	                if(mysqli_num_rows($sel_temp)>0)
	                {
						$key3 = mysqli_fetch_assoc($sel_temp);
	                    
	                    $content = $key3['content'];
	                }else
	                {
	                    $content = ''; 
	                }*/
	                //$message= '<img src="https://www.jingadala.com/uploads/logo_image/Jingadala_Popup_02.jpg" style="width:80%; height:100%; margin-left:12%;">';
          			//$message= base_url."uploads/logo_image/Jingadala_Popup_01.jpg";
                    /*$message=stripcslashes($content);
	                $message=str_replace("{date}",date("d"),$message);
	              	$message=str_replace("images/line-break-3.jpg",base_url.'api/v1/images/email_img/line-break-3.jpg',$message);
	              	$message=str_replace("images/line-break-2.jpg",base_url.'api/v1/images/email_img/line-break-2.jpg',$message);
	              	$message=str_replace("images/ribbon.jpg",base_url.'api/v1/images/ribbon.jpg',$message);
	              	$message=str_replace("img/great_lakes_logo.png",base_url.'template/assets/layouts/layout4/img/great_lakes_logo.png',$message);*/
	              	$message = "this link has been expired";

	                echo $message;exit;
				}
			}else{
				$msg = "User does not exists";
			}
		}else{
			//echo "<script>alert('Password does not match');</script>";
			$msg = 'Confirm password does not match';
		}
	}else
	{
		$msg = 'Minimum 6 character password required';
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
<style>
 body{
   font-size: 100%;
   background:steelblue; 
   font-family: 'Roboto', sans-serif;
}
.button {
background:steelblue;
}

</style>
</head>
<body>
<!--element start here-->
<div class="elelment" >
	<div class="element-main" >
	<h2 style="color: #333333; margin-top: -10px; margin-bottom: 25px; font-weight: normal; font-size: 48px; font-family: Georgia, 'Times New Roman', Times, serif"> <em style="  margin-left: 0%;">Jingadala</h2><br>

		<h1 style="font-size: 32px; font-family: Georgia, 'Times New Roman', Times, serif; color: #4e3227; margin-top: 0px; margin-bottom: 0px; font-weight: normal;">Change Password</h1>
		<br />
		<form method="post" onsubmit="return checklength()">
			<!-- <label>New Password</label> -->
			<input type="password" name='password' style="color:black" id="passs" placeholder="Enter New Password" required>
			<input type="hidden" name='id' value="<?php echo $ids;?>">

			<br />
			<!-- <label>Confirm Password</label> -->
			<input type="password" name='confirmpas' style="color:black" placeholder="Confirm New Password" required> 
			<div style="color:red"><?php echo $msg; ?> </div>
			<input type="submit" name='submit' value="Change Password">
		</form>
	</div>
</div>
<!-- <div class="copy-right">
			<p>@Copyright WaggingPal ,All rights reserved.  <a href="http://w3layouts.com/" target="_blank">  W3layouts </a></p>
</div> -->

<!--element end here-->
</body>

</html>