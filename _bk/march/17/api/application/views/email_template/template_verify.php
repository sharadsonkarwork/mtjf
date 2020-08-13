<html>
<head>
	<title>Email Template</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900" rel="stylesheet">
</head>
<body style="margin: 0;">
	
		<table width="600" style="font-family: 'Roboto', sans-serif;background:url(https://i.imgur.com/I5d1ltq.png) no-repeat center center;" align="center">
				<tr>
					<td style="    padding: 42px;">
						<table style="border-radius: 4px;" bgcolor="#fff">
							<tr>
								<td>
									<div align="center">
										<img src="https://i.imgur.com/aLmfY9m.png" alt="" style="margin-top: 18px; " >
										<hr style="width: 90%">
									</div>
								</td>
							</tr>
							<tr>
								<td style="padding: 15px;">
									<span style="color: #00176d; font-size: 25px; font-weight: bold;">Dear <?php if(!empty($name)){ echo $name; }else{ echo 'User'; }?>,</span>

								</td>
							</tr>
							<tr>
								<td style="padding: 15px;">
									<span style="font-size: 13px; font-weight: 600; color: #8a8a8a;">Your email <?php if(!empty($email)){ echo $email; }else{ echo ''; }?> must be confirmed before using 
									it to log in to our store</span>
								</td>
							</tr>
							<tr>
								<td style="padding: 15px;">
									<span style="font-size: 13px; font-weight: 600; color: #8a8a8a;">Use the following email when prompted to log in:</span>
									<br>
									<span style="font-size: 13px; font-weight: 600; color: #8a8a8a;">Email: <?php if(!empty($email)){ echo $email; }else{ echo ''; }?></span>
									<span></span>
								</td>
							</tr>
							<tr>
								<td style="padding: 15px;">
									<span style="font-size: 13px; font-weight: 600; color: #8a8a8a;">
										Click here to confirm your email and instantly log in (the link is valid only once):
									</span>
								</td>
							</tr>
							<tr>
								<td style="text-align: center; padding: 15px;">
									<?php if(!empty($url)){ echo $url; }else{ echo "<a href='#'>";}?>CONFIRM ACCOUNT</a>
								</td>
							</tr>
							<tr>
								<td style="padding: 15px;">
									<span style="font-size: 13px; font-weight: 600; color: #8a8a8a;">If you have any questions, please feel free to contact us at support@qalame.com or by phone at</span>
								</td>
							</tr>
							<!-- <tr>
								<td style=" text-align: center;font-size: 22px;font-weight: 700;" >
									<span style="color: #388e3c;">Follow us</span>
									<ul style="list-style-type: none;margin-top: 12px;margin-bottom: 42px; text-align: center;     margin-right: 44px;">
									
										<li style="display: inline-block;margin-right: 17px;">
											<a href="#" title="" style="display: inline-block;line-height: 0;"><img src="https://i.imgur.com/5VLdP2k.png" alt=""></a>
										</li>
										
										<li  style="display: inline-block;margin-right: 17px;">
											<a href="#" title="" style="display: inline-block;line-height: 0;"><img src="https://i.imgur.com/kOaWzqZ.png" alt=""></a>
										</li>
										
										<li  style="display: inline-block;margin-right:1px;">
											<a href="#" title="" style="display: inline-block;line-height: 0;"><img src="https://i.imgur.com/KgcbHme.png" alt=""></a>
										</li>
									</ul>
								</td>
							</tr> -->
						</table>
					</td>
				</tr>
		</table>


	</body>
</html>