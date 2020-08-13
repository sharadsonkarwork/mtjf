<!DOCTYPE html>

<html lang="en">
    <!-- BEGIN HEAD -->
    <head>
        <meta charset="utf-8" />
        <title>EngineerBabu | Login</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
       
        <link href="<?php echo base_url()?>template/assets/global/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/plugins/select2/css/select2-bootstrap.min.css" rel="stylesheet" type="text/css" />
       
        <link href="<?php echo base_url()?>template/assets/global/css/components-md.min.css" rel="stylesheet" id="style_components" type="text/css" />
        <link href="<?php echo base_url()?>template/assets/global/css/plugins-md.min.css" rel="stylesheet" type="text/css" />

        <link href="<?php echo base_url();?>template/assets/global/css/parsley.css" rel="stylesheet"> 
       
        <link href="<?php echo base_url()?>template/assets/pages/css/login.min.css" rel="stylesheet" type="text/css" />
       
        <link rel="shortcut icon" href="favicon.ico" /> </head>
    <!-- END HEAD -->

    <body class=" login">
        <!-- BEGIN LOGO -->
        <div class="logo">
            <a href="#">
                <img src="" alt="" /> </a>
        </div>
        
        <div class="content">
            <!-- BEGIN LOGIN FORM -->
            <form id="form11" class="login-form" action="<?php echo base_url().'login';?>" method="post" data-parsley-validate=''>
                <h3 class="form-title font-green">Sign In</h3>
                <div class="alert alert-danger display-hide">
                    <button class="close" data-close="alert"></button>
                    <span> Enter any username and password. </span>
                </div>
				<?php if($this->session->flashdata('msg')){?>
                	<div class="alert alert-danger">
                        <button class="close" data-close="alert"></button>
                        <span> <?php echo $this->session->flashdata('msg');?></span>
                    </div>
                <?php }?>
                	
                <div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9">Username</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="text" autocomplete="off" placeholder="Email"  name="username" data-parsley-type="email" required/> </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <input class="form-control form-control-solid placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="password" required/> </div>
                <div class="form-actions" align="center">
                    <input name="submit" type="submit" class="btn green uppercase" value="Login">
                </div>
                
                <div class="create-account">
                    <p>
                        
                    </p>
                </div>
            </form>
            <!-- END LOGIN FORM -->
            <!-- END REGISTRATION FORM -->
        </div>
        <div class="copyright"> 2016 © EngineerBabu. Admin Dashboard. </div>

        <script src="<?php echo base_url()?>template/assets/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/js/parsley.min.js"></script>

        <script src="<?php echo base_url()?>template/assets/global/plugins/js.cookie.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/jquery-validation/js/additional-methods.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/plugins/select2/js/select2.full.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/global/scripts/app.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url()?>template/assets/pages/scripts/login.min.js" type="text/javascript"></script>

<script type="text/javascript">
  $('#form11').parsley();  
</script>
        
    </body>

</html>