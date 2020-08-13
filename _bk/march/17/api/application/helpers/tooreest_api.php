<?php
require '.././libs/Slim/Slim.php';
require_once 'dbHelper.php';
require_once 'auth.php';
require_once 'gcm.php';
require 'PHPmailer/Send_Mail.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app = \Slim\Slim::getInstance();
$db = new dbHelper();

/*date_default_timezone_set("Asia/Kolkata");*/
$base_url = "https://tooreest.com/tooreest/";
$dateTime = date("Y-m-d H:i:s", time()); 
$militime=round(microtime(true) * 1000);
define('base_url', $base_url);
define('militime', $militime);
define('dateTime', $dateTime);
define('GOOGLE_API_KEY', 'AAAAV9ZCC1M:APA91bE2OUfFL0ePswBqBreUwfi04JLQI5saYhgjgdliiBs8vySRT0zSeP9i2NqNymGZbzHtUjFN6_q4UzVh611qo86Ibv3jvdmmeXQ-VPkvFHbqrD3VlZAUjCy8xbpU_QKO4Qke8aNy');
//define('user_image_url',$user_image_url);



$app->post('/Registration',function() use ($app){
  $json1 = file_get_contents('php://input');
  if(!empty($json1))
  {
    $data = json_decode($json1);
    $language = $data->language;
    $country = $data->country;
    $city = $data->city;
    $email= $data->email;
    $full_name= $data->full_name;
    $address= $data->address;
    $mobile = $data->mobile;
    $password = $data->password;
    //$sub_cat = $data->sub_cat;
    $user_type = $data->user_type;
    $company_name = $data->company_name;
    //$guide_type = $data->guide_type;
    $postal_code = $data->postal_code;
    $lat = $data->lat;
    $lng = $data->lng;
    //$refer_code = $data->refer_code;

    $refer_code1 = randomuniqueCode();
    if($user_type == 1) {
      $refer_code = $data->refer_code;
    }else{
      $refer_code ='';
    }
    global $db;
    if(!empty($email) && !empty($mobile) && !empty($password))
    {      //$code = '1234'; //substr(randomuniqueCode(),0,6);
        $Otp = substr(randomTxn(),0,4);
        $msg = 'OTP for tooreest app: '.$Otp;
        $condition = array('mobile_no'=>$mobile);
        $condition2 = array('email'=>$email,'type'=>$user_type);
        $query_login = $db->select("users","*",$condition2);
        if($query_login["status"] == "success")
        {
            if($query_login['data'][0]['mobile_no']==$mobile)
            {
                if($query_login['data'][0]['mobile_status']==1)
                {
                    if($query_login['data'][0]['email_status']==1)
                    {
                        $query_login['status'] ="failed";
                        $query_login['message'] ="Email address and mobile number already registered.";
                        unset($query_login['data']);
                        echoResponse(200,$query_login);
                    }else
                    {
                        $query_login['status'] ="failed";
                        $query_login['message'] ="Email address already registered! please login.";
                        unset($query_login['data']);
                        echoResponse(200,$query_login);
                    }   
                }else
                {
                   //otp send function here

                    $update = $db->update("users",array('otp_code'=>hash('sha256', $Otp)),array('user_id'=>$query_login['data'][0]['user_id']),array());
                    sms_send($mobile,$msg);
                    $query_login['status'] ="success";
                    $query_login['message'] ="Successfully registered.";
                    $query_login['data'] = $query_login['data'][0]['user_id'];
                    echoResponse(200,$query_login);
                }
            }else
            {
                $mobile_check = $db->customQuery("SELECT * FROM `users` WHERE `mobile_no` = '$mobile' AND `mobile_status` = 1 AND `email` != '$email' AND `type` ='$user_type'");
                //$mobile_check = $db->select("users","user_id",array('mobile_no'=>$mobile,'mobile_status'=>1));
                if($mobile_check['status']=='success')
                {
                    $query_login['status'] ="failed";
                    $query_login['message'] ="Mobile number already registered.";
                    unset($query_login['data']);
                    echoResponse(200,$query_login);
                }else
                {
                    //$blank_mobile = $db->customQuery("UPDATE `users` SET `mobile_no` = '' WHERE `mobile_no` = '$mobile' AND `type` ='$user_type'");
                    $update_num = $db->update("users",array('mobile_no'=>$mobile,'otp_code'=>hash('sha256', $Otp)),array('user_id'=>$query_login['data'][0]['user_id']),array());
                    sms_send($mobile,$msg);
                    $query_login['status'] ="success";
                    $query_login['message'] ="Successfully registered.";
                    $query_login['data'] = $query_login['data'][0]['user_id'];
                    echoResponse(200,$query_login);
                  
                }  
            }  
        }
        else
        {
            $query_login = $db->select("users","user_id",array('mobile_no'=>$mobile,'mobile_status'=>1,'type'=>$user_type));
            if($query_login['status']=="success")
            {  
                $query_login['status'] ="failed";
                $query_login['message'] ="Mobile number already exists! please try another.";
                unset($query_login['data']);
                echoResponse(200,$query_login);
            }else
            {
               /*echo "hihih";
               exit;*/     /*if($user_type==2)
                    {
                        $s_category = json_encode($sub_cat);
                        for ($i=0; $i <count($sub_cat) ; $i++) { 
                            $s_categorys[] = $sub_cat[$i]->subcategory_id;
                        } 
                        $s_category1 = implode(',', $s_categorys);          
                    }else
                    {
                      $s_category1 = "";
                      $s_category = "";
                    }*/
                    if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name']))
                    {
                      $image= $_FILES['image']['tmp_name'];
                      $image_name= $_FILES['image']['name'];
                      $image_name = militime.$image_name;
                      move_uploaded_file($image,"../../uploads/user_image/".$image_name);
                      $u_image1 = base_url."uploads/user_image/".$image_name;
                   }
                   else
                   {
                   
                      $image_name ='';
                   }
                  //$blank_mobile = $db->customQuery("UPDATE `users` SET `mobile_no` = '' WHERE `mobile_no` = '$mobile' AND `type` ='$user_type'");
                  
                  $user_data = array(
                        'language' =>$language,
                        'country' =>$country,
                        'city' =>$city, 
                        'company_name'=>$company_name,
                        'full_name'=>$full_name,
                        'email'=>$email,
                        'mobile_no'=>$mobile,
                        'address' =>$address, 
                        'postal_code' =>$postal_code,
                        ///'category' => $s_category,
                        //'comma_id'=>$s_category1,
                        'type'=>$user_type,
                        'image'=>$image_name,
                        'lat'=>$lat,
                        'lng'=>$lng,
                        'avail_type'=>1,
                        'user_refer_code'=>$refer_code,

                        'refer_code' =>$refer_code1,
                        //'guide_type'=>$guide_type,
                        'password'=>sha1($password),
                        'otp_code'=>hash('sha256', $Otp),
                        'create_at'=>militime,
                        'update_at'=>militime
                      );
                 
                       if($refer_code !='')
                       {
                         $r_code = $db->select("users","refer_code,count,user_id,device_token",array('refer_code'=>$refer_code));
                         if($r_code['status']=="success"){
                             
                             $rows1 = $db->insert("users",$user_data,array());
                             if($rows1['status']=="success")
                             {                                                                   
                                sms_send($mobile,$msg);
                                $rows1['status'] ="success";
                                $rows1['message'] ="Successfully registered.";
                                  //$rows1['data'];
                                echoResponse(200,$rows1);
                             }else
                             {
                                 $rows1['status'] ="failed";
                                 $rows1['message'] ="something went wrong! Please try again later.";
                                 unset($rows1['data']);
                                 echoResponse(200,$rows1);
                             }
                         }
                         else
                         {
                             $r_code['status'] ="failed";
                             $r_code['message'] ="referral code is invalid";
                             unset($r_code['data']);
                             echoResponse(200,$r_code);
                         } 
                       }
                       else
                       {
                          $rows2 = $db->insert("users",$user_data,array());
                          if($rows2['status']=="success")
                          {
                              sms_send($mobile,$msg);
                              $rows2['status'] ="success";
                              $rows2['message'] ="Successfully registered.";
                              //$rows1['data'];
                              echoResponse(200,$rows2);
                          }else
                          {
                             $rows2['status'] ="failed";
                             $rows2['message'] ="something went wrong! Please try again later.";
                             unset($rows2['data']);
                             echoResponse(200,$rows2);
                          }
                       }
            }
        }
    }
    else
    {
        $json1['status'] ="failed";
        $json1['message'] ="Request parameter not valid";
        echoResponse(200,$json1);
    }
  }else
  {
      $json1['status'] ="failed";
      $json1['message'] ="No Request parameter";
      echoResponse(200,$json1);
  }

});


$app->post('/Mobile_verification', function() use ($app){
  $json1 = file_get_contents('php://input');
  if(!empty($json1))
  {
      $data = json_decode($json1);
      if($data->user_id != '' && $data->user_id != 0 && $data->verify_code != '' && strlen($data->verify_code) == 4)
      {
        global $db;
        
        $code = substr(randomuniqueCode(),0,6);
        $rows = $db->select("users","*",array('user_id'=>$data->user_id));
        if($rows["status"]=="success")
        {
        	/*print_r($rows);
        	exit;
*/          //echo hash('sha256', $data->verify_code);exit;
         /*$a = $rows['data'][0]['otp_code'];
         print_r($a);
         exit;*/
            if($rows['data'][0]['otp_code'] == hash('sha256', $data->verify_code))
            {
                $update = $db->update("users",array('otp_code'=>'','mobile_status'=>1,'Update_at'=>militime),array('user_id'=>$data->user_id),array());
                /*print_r($update);
                exit;*/
              
                if($update['status']=="success")
                {
                    /*$subject = "Tooreest App: Verification Code";
                    $email_from ='no-reply@tooreest.com';
                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1'. "\r\n";
                    $headers .= 'From: '.$email_from. '\r\n';
                    */
                    if($rows['data'][0]['email_status']!=1)
                    {

                      $sel_temp = $db->select("email_template","*",array('id'=>'2'),array());
                       /*print_r($sel_temp);
                       exit;*/
                      if($sel_temp['status'] =="success")
                      {
                          foreach($sel_temp['data'] as $key3)
                          {
                              $content = $key3['template'];
                          }
                      }else
                      {
                          $content = ''; 
                      }
                      
                      $subject = "Tooreest App: Verification Code";
                      $email_from ='info@tooreest.com';
                      //$headers  = 'MIME-Version: 1.0' . "\r\n";
                      //$headers .= 'Content-type: text/html; charset=iso-8859-1'. "\r\n";
                      //$headers .= 'From: '.$email_from. '\r\n';
                      $cc = '';
                      $message=stripcslashes($content);
                      $message=str_replace("{date}",date("d"),$message);
                      $message=str_replace("images/line-break-3.jpg",base_url.'api/v1/images/email_img/line-break-3.jpg',$message);
                      $message=str_replace("images/line-break-2.jpg",base_url.'api/v1/images/email_img/line-break-2.jpg',$message);
                      $message=str_replace("images/ribbon.jpg",base_url.'api/v1/images/ribbon.jpg',$message);
                      $message=str_replace("jokaamo_logo",base_url.'uploads/logo_black.png',$message);
                      $message=str_replace("{email}",$rows["data"][0]['email'],$message);
                      $message=str_replace("{link}","<a href=".base_url."api/v1/tooreest_api.php/VerifyEmail?secretid=".base64_encode($data->user_id)."&secret_key=".hash('sha256',$code).">CLICK HERE</a>",$message);
                     
                      //$message="For Email verification: "."<a href=".base_url."api/v1/tooreest_api.php/VerifyEmail?secretid=".base64_encode($data->user_id)."&secret_key=".hash('sha256',$code).">CLICK HERE</a>";
                      $update1 = $db->customQuery("UPDATE `users` SET `update_at`= '".militime."',`verify_code` = '".hash('sha256', $code)."' WHERE `user_id` = '".$data->user_id."'");
                      if($update1['status']=="success")
                      {   
                      	$senddd = Send_Mail($email_from,$rows['data'][0]['email'],$cc,$subject,$message);
                          $update['status']="success";
                          $update["message"] = "Mobile Number Successfully Verified";
                          unset($update['data']);
                          echoResponse(200, $update);
                      }else
                      {
                          $update['status'] = "failed";
                          $update['message'] ="failed";
                          unset($update['data']);
                          echoResponse(200,$update);
                      }
                    }
                }
            }else
            {
               $rows['status']="failed";
               $rows["message"] = "Otp not matched.";
               unset($rows['data']);
               echoResponse(200, $rows);
            }     
        }else
        {
           $rows["status"] = 'failed';
           $rows["message"]= "Invalid Request";
           unset($rows["data"]);
           echoResponse(200, $rows);
        }
      }else
      {
         $check_otp["status"] = 'failed';
         $check_otp['message']= "Invalid Request parameter";
         echoResponse(200,$check_otp);
      }
  }else
  {
     $check_otp["status"] = 'failed';
     $check_otp['message'] ="No Request parameter";
     echoResponse(200,$check_otp);
  }
});

$app->get('/VerifyEmail', function() use ($app){
    $user_id = base64_decode($app->request()->params('secretid'));
    $code = $app->request()->params('secret_key');

    $condition = array('user_id'=>$user_id);
    global $db;
    $content = '';
    $rows = $db->select("users","*",$condition);
    
    if($rows["status"]=="success")
    {
        if($code != '' && $code != 'null')
        {
          $verify_code = $rows['data'][0]['verify_code'];
          if($verify_code == '' && $rows['data'][0]['email_status']==1)
          {
            $message = "<img src=".base_url."uploads/Popup_01.jpg style='width:80%; height:90%; margin-left: 12%;'>";
              echo $message;exit;
          }elseif($code == $verify_code)
          {
              $rows3 = $db->update("users",array("email_status"=>1,'verify_code'=>''),$condition,array());
              $message = "<img src=".base_url."uploads/Popup.jpg style='width:80%; height:90%; margin-left: 12%;'>";
              echo $message;exit;
          }else
          {
              $message = "<img src=".base_url."uploads/Popup_01.jpg style='width:80%; height:90%; margin-left: 12%;'>";  
              echo $message;exit;
          } 
      }else
      {
          $message = "<img src=".base_url."uploads/Popup_01.jpg style='width:80%; height:90%; margin-left: 12%;'>";
          echo $message;exit; 
      }
    }else
    {
      $message = "<img src=".base_url."uploads/Popup_01.jpg style='width:80%; height:90%; margin-left: 12%;'>";
      echo $message;exit;
    }
});

/////Login here//////

$app->post('/Login',function() use ($app){
  $json1 = file_get_contents('php://input');
  if(!empty($json1))
  {
      $data = json_decode($json1);
      $email = $data->email;
      $password = $data->password;
      $user_type = $data->user_type;
      $device_token = $data->device_token;
      $device_id = $data->device_id;
      $device_type =$data->device_type;
      global $db;
    if(!empty($email) && !empty($password))
    { 
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        $token = $token.militime;
        $images = '';
        $query_login = $db->select("users","*",array("email"=>$email,"type"=>$user_type));
        if($query_login["status"] == "success")
        { 
            if($query_login['data'][0]['admin_status'] == 0)
            {
                if($query_login['data'][0]['password'] == hash('sha1',$password))
                {
                  if($query_login['data'][0]['email_status']==1 && $query_login['data'][0]['mobile_status']==1)
                  {
                      $update = $db->update("users",array('token'=>$token, 'device_type'=>$device_type, 'device_token'=>$device_token,'device_id'=>$device_id,'update_at'=>militime),array("user_id"=>$query_login['data'][0]['user_id']),array());
                      if($update['status'] == "success")
                      {
                        //$ex = explode(',', $query_login['data'][0]['sub_cat']);
                          if(!empty($query_login['data'][0]['image']))
                          {
                              $images = base_url.'uploads/user_image/'.$query_login['data'][0]['image'];
                          }
                          
                          $idproof = '';  $bankproof = ''; $addproof=''; $licenseproof = '';
                          $selectproof = $db->select("provider_document","*",array('provider_id'=>$query_login['data'][0]['user_id']));
                          if($selectproof['status']=="success")
                          {
                              if(!empty($selectproof['data'][0]['id_img'])){ $idproof = base_url.'uploads/document/'.$selectproof['data'][0]['id_img']; }
                              if(!empty($selectproof['data'][0]['bank_img'])){ $bankproof = base_url.'uploads/document/'.$selectproof['data'][0]['bank_img']; }
                              if(!empty($selectproof['data'][0]['address_img'])){ $addproof = base_url.'uploads/document/'.$selectproof['data'][0]['address_img']; }
                              if(!empty($selectproof['data'][0]['license_img'])){ $licenseproof = base_url.'uploads/document/'.$selectproof['data'][0]['license_img']; }
                              $bankname = $selectproof['data'][0]['bank_name'];
                              $username = $selectproof['data'][0]['user_name'];
                              $acnum = $selectproof['data'][0]['account_num'];
                              $ifscocde = $selectproof['data'][0]['bic_code'];
                              $licennum = $selectproof['data'][0]['license_num'];
                              $bank_code = $selectproof['data'][0]['bank_code'];
                              $paypal_email_id = $selectproof['data'][0]['paypal_email_id'];
                              $has_paypal_account = $selectproof['data'][0]['has_paypal_account'];
                          }else
                          {
                             $bankname = ''; $username = ''; $acnum = ''; $ifscocde = ''; $licennum = ''; $bank_code = ''; $paypal_email_id ='' ; $has_paypal_account ='0';
                          }
                          $availability_time = array();
                          if($query_login['data'][0]['type']==2 && !empty($query_login['data'][0]['availability']))
                          {
                             $availability_time = json_decode($query_login['data'][0]['availability']);
                          }
                          $u_id = $query_login['data'][0]['user_id'];
                          //$rcount = $db->customQueryselect("SELECT COUNT('count') AS r_count FROM users WHERE user_id = $u_id");
                          $refer_count =$query_login['data'][0]['count'];

                        $arr = array(
                           'full_name'=>$query_login['data'][0]['full_name'],  
                           'company_name'=>$query_login['data'][0]['company_name'],
                           'email'=>$query_login['data'][0]['email'],  
                           'mobile_no'=>$query_login['data'][0]['mobile_no'],  
                           'address'=>$query_login['data'][0]['address'],  
                           'postal_code'=>$query_login['data'][0]['postal_code'],  
                           'image'=>$images,
                           'language'=>$query_login['data'][0]['language'],  
                           'country'=>$query_login['data'][0]['country'],  
                           'city'=>$query_login['data'][0]['city'],  
                           'sub_cat' =>$query_login['data'][0]['category'],
                           'user_type'=>$query_login['data'][0]['type'],
                           'guide_type'=>$query_login['data'][0]['guide_type'],
                           'about_me'=>$query_login['data'][0]['about_me'],
                           'facebook'=>$query_login['data'][0]['facebook'],
                           'google_plus'=>$query_login['data'][0]['google_plus'],
                           'twitter'=>$query_login['data'][0]['twitter'],
                           'linkedin'=>$query_login['data'][0]['linkedin'],
                           'lat'=>$query_login['data'][0]['lat'],
                           'lng'=>$query_login['data'][0]['lng'],
                           'refer_code' =>$query_login['data'][0]['refer_code'],
                           'refer_count' =>$refer_count,
                           'availability'=>$availability_time,
                           'flag'=>$query_login['data'][0]['avail_type'],
                           'is_id_verify'=>$query_login['data'][0]['is_id_verify'],
                           'id_img' => $idproof,
                           'bank_img' => $bankproof,
                           'address_img' => $addproof,
                           'license_img' => $licenseproof,
                           'bank_name' => $bankname,
                           'user_name' => $username,
                           'account_num' => $acnum,
                           'bic_code' => $ifscocde,
                           'bank_code'=>$bank_code,
                           'paypal_email_id'=>$paypal_email_id,
                           'has_paypal_account'=>$has_paypal_account,
                           'license_num' => $licennum,
                           'token'=>$token
                           );

                           if($query_login['data'][0]['is_first_login'] == 1){

                                if($query_login['data'][0]['user_refer_code'] !='')
                                {
                                      $rr_code= $query_login['data'][0]['user_refer_code'];

                                      $new_code = $db->select("users","user_id,count,full_name,device_token,device_type",array('refer_code'=>$rr_code));
                                     
                                      $name =$query_login['data'][0]['full_name'];
                                      $a = $new_code['data'][0]['count']+1;
                                      
                                      $b = $query_login['data'][0]['count']+1;
                                      
                                     
                                      $update11 = $db->update("users",array('count'=>$a),array('user_id'=>$new_code['data'][0]['user_id']),array());
                                      $update12 = $db->update("users",array('count'=>$b),array('user_id'=>$query_login['data'][0]['user_id']),array());

                                        $msg ="".$name." has registered in Tooreest from your referral code ";

                                        $message = array("message" =>$msg,"image" =>'',"title" =>'Successful referral',"type"=>'Referral',"refer_count"=>$a,"timestamp" =>militime);

                                        $notification_array = array('sender_id'=>$query_login['data'][0]['user_id'],
                                                                    'reciver_id'=>$new_code['data'][0]['user_id'],
                                                                    'message'=>$msg,
                                                                    'type'=>'Referral',
                                                                    'title'=>'Successful referral',
                                                                    'Create_at'=>militime
                                                                    );
                                        $notification_id = $db->insert("notification_tb",$notification_array, array());
                                        if($new_code['data'][0]['device_type']=='Android')
                                        {
                                            AndroidNotification($new_code['data'][0]['device_token'],$message);
                                        }else
                                        {
                                            iOSPushNotification($new_code['data'][0]['device_token'],$msg,'Successful referral','Referral',$a,1);
                                        }
                                }

                             $update1 = $db->update("users",array('is_first_login'=>0),array('user_id'=>$query_login['data'][0]['user_id']),array());
                           }
                                            
                           $query_login['status'] ="success";
                           $query_login['message'] ="Successfully Login";
                           $query_login['data'] = $arr;
                           echoResponse(200,$query_login);
                      }
                      else
                      {
                       $query_login['status'] ="failed";
                       $query_login['message'] ="something went wrong";
                       unset($query_login['data']);
                       echoResponse(200,$query_login);
                      }
                  }elseif($query_login['data'][0]['mobile_status']==0)
                  {
                      $query_login['status'] ="failed";
                      $query_login['message'] ="Account does not exists.";
                      unset($query_login['data']);
                      echoResponse(200,$query_login);
                  }elseif($query_login['data'][0]['email_status']==0)
                  {
                      $sel_temp = $db->select("email_template","*",array('id'=>'2'),array());
                           /*print_r($sel_temp);
                           exit;*/
                      if($sel_temp['status'] =="success")
                      {
                          foreach($sel_temp['data'] as $key3)
                          {
                              $content = $key3['template'];
                          }
                      }else
                      {
                          $content = ''; 
                      }
                      
                      $subject = "Tooreest App: Verification Code";
                      $email_from ='info@tooreest.com';
                      //$headers  = 'MIME-Version: 1.0' . "\r\n";
                      //$headers .= 'Content-type: text/html; charset=iso-8859-1'. "\r\n";
                      //$headers .= 'From: '.$email_from. '\r\n';
                      $code = substr(randomuniqueCode(),0,6);
                      
                      $cc = '';
                      $message=stripcslashes($content);
                      $message=str_replace("{date}",date("d"),$message);
                      $message=str_replace("images/line-break-3.jpg",base_url.'api/v1/images/email_img/line-break-3.jpg',$message);
                      $message=str_replace("images/line-break-2.jpg",base_url.'api/v1/images/email_img/line-break-2.jpg',$message);
                      $message=str_replace("images/ribbon.jpg",base_url.'api/v1/images/ribbon.jpg',$message);
                      $message=str_replace("jokaamo_logo",base_url.'uploads/logo_black.png',$message);
                      $message=str_replace("{email}",$query_login["data"][0]['email'],$message);
                      $message=str_replace("{link}","<a href=".base_url."api/v1/tooreest_api.php/VerifyEmail?secretid=".base64_encode($query_login['data'][0]['user_id'])."&secret_key=".hash('sha256',$code).">CLICK HERE</a>",$message);
                     
                      //$message="For Email verification: "."<a href=".base_url."api/v1/tooreest_api.php/VerifyEmail?secretid=".base64_encode($data->user_id)."&secret_key=".hash('sha256',$code).">CLICK HERE</a>";
                      $update1 = $db->customQuery("UPDATE `users` SET `update_at`= '".militime."',`verify_code` = '".hash('sha256', $code)."' WHERE `user_id` = '".$query_login['data'][0]['user_id']."'");  
                      $query_login['status'] ="failed";
                      $query_login['message'] ="Verification Mail has been send to you registered email address, please verify your email.";
                      unset($query_login['data']);
                      echoResponse(200,$query_login);
                  }
              }else
              {
                $query_login['status'] = "failed";
                $query_login['message'] = "Invalid login credentials.";
                unset($query_login['data']);
                echoResponse(200,$query_login);
              }     
            }else
            {
                $query_login['status'] = "failed";
                $query_login['message'] = "Your Tooreest account has been temporarily suspended as a security precaution.";
                unset($query_login['data']);
                echoResponse(200,$query_login);
            }
        }
        else
        { 
          $query_login['status'] = "failed";
          if($user_type==1){
          $query_login['message'] ="This email is not registered with customer app";
        }else{
          $query_login['message'] ="This email is not registered with provider app";
        }
          unset($query_login['data']);
          echoResponse(200,$query_login);
        }
    }else
    {
      $insert_user['message'] ="Invalid Request parameter";
      echoResponse(200,$insert_user);
    }
  }else
  {
    $insert_user['message'] ="No Request parameter";
    echoResponse(200,$insert_user);
  }
});

///Resend email verification code\\\
$app->post('/Resend_verification_link', function() use ($app)
{
    $email = $app->request()->params('email');
    $code = substr(randomuniqueCode(),0,6);
    global $db;
    $query_login = $db->select("users","*",array('email'=>$email));
    if($query_login['status']=="success")
    {   
          $sel_temp = $db->select("email_template","*",array('id'=>'2'),array());
                   
                    if($sel_temp['status'] =="success")
                    {
                        foreach($sel_temp['data'] as $key3)
                        {
                            $content = $key3['template'];
                        }
                    }else
                    {
                        $content = ''; 
                    }
                    
                    $subject = "Tooreest App: Verification Code";
                    $email_from ='info@tooreest.com';
                    $cc = '';
                    $message=stripcslashes($content);
                    $message=str_replace("{date}",date("d"),$message);
                    $message=str_replace("images/line-break-3.jpg",base_url.'api/v1/images/email_img/line-break-3.jpg',$message);
                    $message=str_replace("images/line-break-2.jpg",base_url.'api/v1/images/email_img/line-break-2.jpg',$message);
                    $message=str_replace("images/ribbon.jpg",base_url.'api/v1/images/ribbon.jpg',$message);
                    $message=str_replace("jokaamo_logo",base_url.'uploads/logo_black.png',$message);
                    $message=str_replace("{email}",$query_login["data"][0]['email'],$message);
                    $message=str_replace("{link}","<a href=".base_url."api/v1/tooreest_api.php/VerifyEmail?secretid=".base64_encode($query_login['data'][0]['user_id'])."&secret_key=".hash('sha256',$code).">CLICK HERE</a>",$message);
        
            
         $update1 = $db->update("users",array('verify_code'=>hash('sha256', $code)),array('user_id'=>$query_login['data'][0]['user_id']),array());
         if($update1['status']=='success')
         {
            $senddd = Send_Mail($email_from,$query_login['data'][0]['email'],$cc,$subject,$message); 
            $query_login['status']="success";
            $query_login["message"] = "Email Verification Link has been successfully sent";
            unset($query_login['data']);
            echoResponse(200, $query_login);
         }else
         {
            $query_login['status'] = "failed";
            $query_login['message'] ="Something went wrong! Please try again later";
            unset($query_login['data']);
            echoResponse(200,$query_login);
         } 
    }else
    {
      $query_login['status'] = "failed";
      $query_login['message'] = "Email Address does not exists";
      unset($query_login['data']);
      echoResponse(200,$query_login);
    }
});
/////Forgot password here//////

$app->post('/Forget_Password',function() use ($app){
  $json1 = file_get_contents('php://input');
  if(!empty($json1))
  {
    $data = json_decode($json1);
    $email = $data->email;
    $verify_code = randomuniqueCode();
    global $db;
    if(!empty($email))
    {
        $condition2 = array('email'=>$email);
        $query_login = $db->select("users","*",$condition2);
        if($query_login["status"] == "success")
        {
          if($query_login['data'][0]['admin_status'] == 0) 
          { 
              if($query_login['data'][0]['mobile_status'] == 0)
              {
                  $query_login["status"] = 'failed';
                  $query_login["message"]= "Please verify your mobile number.";
                  unset($query_login["data"]);
                  echoResponse(200, $query_login);
              }elseif($query_login['data'][0]['email_status'] == 0)
              {
                  $query_login["status"] = 'failed';
                  $query_login["message"]= "Please verify your email address.";
                  unset($query_login["data"]);
                  echoResponse(200, $query_login);
              }else
              {
                  $user_id = $query_login['data'][0]['user_id'];
                  $update = $db->update("users",array('verify_code'=>sha1($verify_code),'update_at'=>militime),array('user_id'=>$user_id),array());
                  if($update['status'] =="success")
                  {  
                    $sel_temp = $db->select("email_template","*",array('id'=>'3'),array());
                    if($sel_temp['status'] =="success")
                    {
                    foreach($sel_temp['data'] as $key3)
                    {
                        $content = $key3['template'];
                    }
                    }else
                    {
                        $content = ''; 
                    }
                     $subject = "Tooreest App: Forget_Password";
                     $email_from ='no-reply@tooreest.com';
                     $cc = '';
                      $message=stripcslashes($content);
                     $message=str_replace("{date}",date("d"),$message);
                     $message=str_replace("images/line-break-3.jpg",base_url.'api/v1/images/email_img/line-break-3.jpg',$message);
                     $message=str_replace("images/line-break-2.jpg",base_url.'api/v1/images/email_img/line-break-2.jpg',$message);
                     $message=str_replace("images/ribbon.jpg",base_url.'api/v1/images/ribbon.jpg',$message);
                     $message=str_replace("jokaamo_logo",base_url.'uploads/logo_black.png',$message);
                     $message=str_replace("{email}",$query_login["data"][0]['email'],$message);
                     $message=str_replace("{link}","<a href=".base_url."web/temp.php?auth_key=".base64_encode($user_id)."&id=".sha1($verify_code).">CLICK HERE</a>",$message);
                     
                     $headers  = 'MIME-Version: 1.0' . "\r\n";
                     $headers .= 'Content-type: text/html; charset=iso-8859-1'. "\r\n";
                     $headers .= 'From: '.$email_from. '\r\n';
                     //$message="For Reset Password: "."<a href=".base_url."web/temp.php?auth_key=".base64_encode($user_id)."&id=".sha1($verify_code).">CLICK HERE</a>";
                     @mail($query_login["data"][0]['email'], $subject, $message,$headers);
                     $query_login["status"] = 'success';
                     $query_login["message"]= "Verifaction Code has been sent to your email address";
                     unset($query_login["data"]);
                     echoResponse(200, $query_login);    
                  }
                  else
                  {
                     $query_login["status"] = 'failed';
                     $query_login["message"]= "Something went wrog! please try again later";
                     unset($query_login["data"]);
                     echoResponse(200, $query_login);
                  }
              }
          }
          else
          {
            $query_login['status'] = "failed";
            $query_login['message'] = "Your Tooreest account has been temporarily suspended as a security precaution.";
            unset($query_login['data']);
            echoResponse(200,$query_login);
          }
        }
        else
        {
          $query_login["status"] = 'failed';
           $query_login["message"]= "This email does not exists";
           unset($query_login["data"]);
           echoResponse(200, $query_login);
        }
    }   
    else
    {
        $json2['message'] ="Request parameter not valid";
        echoResponse(200,$json2);
    }
  }
  else
  {
    $json1['message'] ="No Request parameter";
    echoResponse(200,$json1);
  }

});

////Upload document/////
$app->post('/Upload_Doc_Document',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $provider_id = $check['data'][0]['user_id'];
        $bank_name = $app->request()->params('bank_name');
        $user_name = $app->request()->params('user_name');
        $account_num = $app->request()->params('account_num');
        $bic_code = $app->request()->params('bic_code');
        $license_num = $app->request()->params('license_num');
        $bank_code = $app->request()->params('bank_code');
        $paypal_email_id = $app->request()->params('paypal_email_id');
        $has_paypal_account = $app->request()->params('has_paypal_account');
        global $db;
        $idurl = ''; $bidurl= '';
        
        if(isset($_FILES['address_img']['name']) && !empty($_FILES['address_img']['name']))
        {
            $image= $_FILES['address_img']['tmp_name'];
            $image_name= $_FILES['address_img']['name'];
            $address_img = militime.$image_name;
            move_uploaded_file($image,"../../uploads/document/".$address_img); 
        }else
        {
            $address_img = '';
        }
        if(isset($_FILES['id_img']['name']) && !empty($_FILES['id_img']['name']))
        {
            $image= $_FILES['id_img']['tmp_name'];
            $imagename= $_FILES['id_img']['name'];
            $id_image = militime.$imagename;
            move_uploaded_file($image,"../../uploads/document/".$id_image); 
        }else
        {
            $id_image = '';
        }
        if(isset($_FILES['bank_img']['name']) && !empty($_FILES['bank_img']['name']))
        {
            $image= $_FILES['bank_img']['tmp_name'];
            $imagename1= $_FILES['bank_img']['name'];
            $bank_image = militime.$imagename1;
            move_uploaded_file($image,"../../uploads/document/".$bank_image); 
        }else
        {
            $bank_image = '';
        }
        if(isset($_FILES['license_img']['name']) && !empty($_FILES['license_img']['name']))
        {
            $image= $_FILES['license_img']['tmp_name'];
            $imagename1= $_FILES['license_img']['name'];
            $license_image = militime.$imagename1;
            move_uploaded_file($image,"../../uploads/document/".$license_image); 
        }else
        {
            $license_image = '';
        }
              $data = array(
                       'provider_id' => $provider_id,
                       'address_img'=>$address_img,
                       'id_img'=>$id_image,
                       'bank_img'=>$bank_image,
                       'license_img'=>$license_image,
                       'bank_name'=>$bank_name,
                       'user_name'=>$user_name,
                       'account_num'=>$account_num,
                       'bic_code'=>$bic_code,
                       'bank_code'=>$bank_code,
                       'paypal_email_id'=>$paypal_email_id,
                       'has_paypal_account'=>$has_paypal_account,
                       'license_num'=>$license_num,
                       'create_at'=>date('Y-m-d H:i:s'),
                       'update_at'=>date('Y-m-d H:i:s')
                       );

              $select = $db->select("provider_document","*",array('provider_id'=>$provider_id));
              if($select['status']=='success')
              { 
                  if($address_img==''){ $address_img = $select['data'][0]['address_img']; }
                  if($id_image==''){ $id_image = $select['data'][0]['id_img']; }
                  if($bank_image==''){ $bank_image = $select['data'][0]['bank_img']; }
                  if($license_num ==''){ $license_image = '';}elseif($license_image==''){ $license_image = $select['data'][0]['license_img']; }

                  
                  unset($data['create_at']);
                  $data['address_img'] = $address_img;
                  $data['id_img'] = $id_image;
                  $data['bank_img'] = $bank_image;
                  $data['license_img'] = $license_image;
                  $data['update_at'] = militime;
                  $rows = $db->update("provider_document",$data,array('provider_id'=>$provider_id),array());
              }else
              {
                 $rows = $db->insert("provider_document",$data,array());
              }
              if($rows['status']=='success')
              {
                  if($address_img!=''){ $address_img = base_url.'uploads/document/'.$address_img; }
                  if($id_image!=''){ $id_image = base_url.'uploads/document/'.$id_image; }
                  if($bank_image!=''){ $bank_image = base_url.'uploads/document/'.$bank_image; }
                  if($license_image!=''){ $license_image = base_url.'uploads/document/'.$license_image; }
               
                  $rows['message'] = "Successfully Uploaded";
                  $rows['data'] = array('address_img'=>$address_img,'id_img'=>$id_image,'bank_img'=>$bank_image,'license_img'=>$license_image,'bank_name'=>$bank_name,
                       'user_name'=>$user_name,'account_num'=>$account_num,'bic_code'=>$bic_code,'license_num'=>$license_num);
                  echoResponse(200,$rows);
              }
              else
              {
                  $rows['status'] = "failed";
                  $rows['message'] = "something went wrong";
                  echoResponse(200,$rows);
              }
     }          
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Tooreest account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);
     }   
  }   
  else
  {
    $msg['message'] = "Invalid Token";
    echoResponse(200,$msg);
  }
}
else
{
  $msg['message'] = "Unauthorised access";
  echoResponse(200,$msg);
}
});

//about me update //

$app->post('/About_me',function() use ($app){
    $headers = apache_request_headers();
    if(!empty($headers['secret_key']))
    {
        $check = token_auth($headers['secret_key']);
        if($check['status']=="true")
        {
            $json1 = file_get_contents('php://input');
            if(!empty($json1))
            {
                $data = json_decode($json1);
                $about_me = $data->about_me;
                global $db;
                if(!empty($about_me))
                {
                    $user_id = $check['data'][0]['user_id'];
                    $condition = array('user_id'=>$user_id );
                    $data = array('about_me'=>$about_me,
                                   'update_at'=>militime
                                  );
                    $rowe = $db->update("users",$data,$condition,array());
                    if($rowe['status']=='success')
                    {
                        $rowes["status"] = "success"; 
                        $rowes["message"] = "Successfully Updated";
                        echoResponse(200, $rowes);
                    }
                    else
                    {
                        $rowes["status"] = "failed"; 
                        $rowes["message"] = "something went wrong! please try again later";
                        echoResponse(200, $rowes);
                    }
                }
                else
                {
                    $msg["status"] = "failed"; 
                    $msg['message'] = "Invalid parameter";
                    echoResponse(200,$msg);
                }
            }
            else
            {
                $msg["status"] = "failed"; 
                $msg['message'] = "No Request parameter";
                echoResponse(200,$msg);
            }
        }else
        {
            $check['status'] = "false";
            $check['message'] = "Invalid Token";
            unset($check['data']);
            echoResponse(200,$check);
        }
    }  
    else
    {
      $check['status'] = "false";
      $check['message'] = "Unauthorised access";
      unset($check['data']);
      echoResponse(200,$check);
    } 
});

/////subcategory here//////

$app->post('/Subcategory_list',function() use ($app){
  $json1 = file_get_contents('php://input');
  if(!empty($json1))
  {
    $data = json_decode($json1);
    $category_id = $data->cat_id;
    $orderby = "ORDER BY sub_id ASC";
     global $db;
     if(!empty($category_id))
     {
        $condition=array('category_id'=>$category_id,'admin_status' =>'1');
        $row = $db->select2("subcategory","*", $condition,$orderby);
        if($row['status']=='success')
        {
              foreach ($row['data'] as $key) {
                $new= $key['icon'];
                $arr[] = array(
                        'subcategory_id'=>$key['sub_id'],
                        'subcategory_name'=>$key['sub_name'],
                        'category_id'=>$key['category_id'],
                        'icon'=>base_url.'uploads/icon/'.$new
                       );
              }
            $row['status'] = "success";
            $row['message'] = "Successfully";
            $row['data'] = $arr;
            echoResponse(200,$row);
        }else
        {
            $row['status'] = "failed" ;
            $row['message'] = "Subcategory not found";
            unset($row['data']);
            echoResponse(200,$row);
        }
     }else
     {
        $row['status'] = "failed" ;
        $row['message'] ="Invalid Request parameter";
        unset($row['data']);
        echoResponse(200,$row);
     }
  }
  else
  {
    $row['status'] = "failed" ;
    $row['message'] ="No Request parameter";
    unset($row['data']);
    echoResponse(200,$row);
  }
});

//profile update //
$app->post('/Update_profile',function() use ($app){

  $headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {    
        $company_name = $app->request()->params('company_name');
        $full_name= $app->request()->params('full_name');
        $address= $app->request()->params('address'); 
        $postal_code = $app->request()->params('postal_code');
        $language = $app->request()->params('language');
        $country = $app->request()->params('country');
        $city = $app->request()->params('city');
        $facebook = $app->request()->params('facebook');
        $google_plus = $app->request()->params('google_plus');
        $twitter = $app->request()->params('twitter');
        $linkedin = $app->request()->params('linkedin');
        $about_me = $app->request()->params('about_me');
        $guide_type = $app->request()->params('guide_type');
        $lat = $app->request()->params('lat');
        $lng = $app->request()->params('lng');
        global $db;

          if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name']))
          {
              $image= $_FILES['image']['tmp_name'];
              $image_name= $_FILES['image']['name'];
              $image_name = militime.$image_name;
              move_uploaded_file($image,"../../uploads/user_image/".$image_name);
              $u_image1 = base_url."uploads/user_image/".$image_name;
          }
          else
          {
              $image_name = $check['data'][0]['image'];
              if(empty($image_name))
              {
                   $u_image1 = $image_name;
              }    
              else
              {
                  $u_image1 = base_url."uploads/user_image/".$image_name;
              }
          }
               $data = array(
                   'facebook' => $facebook,
                   'google_plus' => $google_plus,
                   'twitter' => $twitter,
                   'linkedin' => $linkedin, 
                   'company_name' => $company_name,
                   'full_name' => $full_name,
                   'address' => $address,
                   'postal_code' => $postal_code,
                   'country' => $country,
                   'language' => $language, 
                   'city' => $city,
                   'guide_type'=>$guide_type,
                   'image'=>$image_name,
                   'about_me'=>$about_me,
                   'lat'=>$lat,
                   'lng'=>$lng,
                   'update_at'=>militime
                   );
              $user_id = $check['data'][0]['user_id'];
              $condition = array('user_id'=>$user_id );
              $rowss = $db->update("users",$data,$condition,array());
   
          if($rowss['status']=='success')
          {
              $data['image'] =$u_image1;  
              $data['flag'] =$check['data'][0]['avail_type'];
              $rowss["status"] = "success"; 
              $rowss["message"] = "Successfully updated";
              $rowss["data"] = $data;
              echoResponse(200, $rowss);
          }
          else{
              $rowss["status"] = "failed";
              $rowss["message"] = "updation failed";
              echoResponse(200, $rowss);
          }
    }
    else
    {
        $check['status'] = "false";
        $check['message'] = "Invalid Token";
        unset($check['data']);
        echoResponse(200,$check);
    }
  }
  else
  {
      $check['status'] = "false";
      $check['message'] = "Unauthorised access";
      unset($check['data']);
      echoResponse(200,$check);
  }
});
///get services\\\\\
$app->post('/Get_services',function() use ($app){ 
    $json = file_get_contents('php://input');    
    $headers = apache_request_headers();
    if($headers['secret_key'] =='abcd')
    {
      if(!empty($json))
        {
            $data = json_decode($json); 
            if(!empty($data))
            {          
                $provider_id = $data->provider_id;
            }else
            {
                $provider_id = 0;
            }
        }else
        {
            $provider_id = 0;
        }
    }
    else
    {                
        $check = token_auth($headers['secret_key']);
        if($check['status']=="true")
        { 
            if(!empty($json))
            { 
                $data = json_decode($json); 
                if(!empty($data))
                {
                      if($provider_id = $check['data'][0]['type'] == 1) 
                      {
                        $provider_id = $data->provider_id;
                      }
                      else{
                      $provider_id = $check['data'][0]['user_id'];
                      }
                }else
                {
                  $provider_id = 0;
                }      
            }else
            {
                $provider_id = 0;
            }    
        }else
        {
            $provider_id = 0;
        }
    }
    if($provider_id!=0 && $provider_id !='')
    {   global $db;
        $arr = array();
        $select = $db->select("provider_services","*",array('provider_id'=>$provider_id,'status'=>1));
        if($select['status']=="success")
        {
            foreach ($select['data'] as $key) {
                $subcate = $db->customQueryselect("SELECT sub_name FROM subcategory WHERE sub_id = ".$key['sub_category_id']."");
                if($subcate['status']=="success")
                {
                    $subcat_name = $subcate['data'][0]['sub_name'];
                }
                $avesel= $db->customQueryselect("SELECT AVG(rating) as averating FROM rating WHERE provider_id ='".$provider_id."' AND sub_cat_id ='".$key['sub_category_id']."'");
                if($avesel['data'][0]['averating']!=null)
                { 
                  $averat = $avesel['data'][0]['averating'];
                }else
                {
                  $averat = 0;
                }
                $counts = 0;
                if(!empty($key['image']))
                {
                  $image = base_url.'uploads/subcategory_image/'.$key['image'];
                }else
                {
                  $image = '';
                }
                // if($headers['secret_key'] =='abcd')
                // {
                //   $tamot = (int) ceil((30 * $key['service_charge']) / 100) + $key['service_charge']; 
                // }else
                // {
                //   if($check['data'][0]['type'] == 1)
                //   {
                //     $tamot = (int) ceil((30 * $key['service_charge']) / 100) + $key['service_charge']; 
                //   }else
                //   {
                      $tamot = $key['service_charge'];
                    //}
                  //}
                /*$bcount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id = $provider_id AND booking_status ='completed'");
                $counts= $bcount['data'][0]['booking_count'];*/
                $arr[] = array(
                    'service_id'=>$key['service_id'],
                    'cat_id'=>$key['category_id'],
                    'sub_cat_id'=>$key['sub_category_id'],
                    'sub_cat_name'=>$subcat_name,
                    'ave_rating'=>$averat,
                    'projectdone'=>$counts,
                    'price'=>$tamot,
                    'description'=>$key['description'],
                    'service_type'=>$key['service_type'],
                    'image'=>$image
                  );
            }
            $select['status']="success";
            $select['message']="Service List";
            $select['data'] = $arr;
            echoResponse(200,$select);
        }else
        {
            $select['status']="failed";
            $select['message']="No service found";
            unset($select['data']);
            echoResponse(200,$select);
        }
    }else
    {
        $check['status'] = "false";
        $check['message'] = "Invalid Token";
        unset($check['data']);
        echoResponse(200,$check);
    }
});    
///add , edit provider services\\
$app->post('/Provider_services_add_update',function() use ($app){
  $headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
      $check = token_auth($headers['secret_key']);
      if($check['status']=="true")
      {
          global $db;
          $service_id = $app->request()->params('service_id'); 
          $sub_cate_id = $app->request()->params('sub_cate_id'); 
          $category_id = $app->request()->params('category_id'); 
          $description = $app->request()->params('description'); 
          $service_type = $app->request()->params('service_type'); 
          $service_charge = $app->request()->params('service_charge');
          if(!empty($category_id))
          {
              $user_id = $check['data'][0]['user_id'];
              $data = array(
                         'category_id'=>$category_id,
                         'sub_category_id'=>$sub_cate_id,
                         'service_charge'=>$service_charge,
                         'description'=>$description,
                         'service_type'=>$service_type
                        );
              if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name']))
              { 
                  $image= $_FILES['image']['tmp_name'];
                  $image_name= $_FILES['image']['name'];
                  $image_name = militime.$image_name;
                  move_uploaded_file($image,"../../uploads/subcategory_image/".$image_name);
                 /// $u_image1 = base_url."uploads/gallary/".$image_name;
                  $u_image1 = $image_name;
              }else
              {
                $u_image1 = '';
              }
             $select = $db->select("provider_services","service_id,image",array("service_id"=>$service_id)); 
             if($select['status']=='success')
             {
                  if($u_image1==''){
                    $u_image1 = $select['data'][0]['image'];
                  }
                  $data['update_at'] = date('Y-m-d H:i:s');
                  $data['image'] = $u_image1;
                  $services_query = $db->update("provider_services",$data,array('service_id'=>$service_id,'provider_id'=>$user_id),array());
             }
             else
             {
                $data['create_at'] = date('Y-m-d H:i:s');
                $data['update_at'] = date('Y-m-d H:i:s');
                $data['provider_id'] = $user_id;
                $data['image'] = $u_image1;
                $services_query = $db->insert("provider_services",$data,array());
             }
              if($services_query['status']=='success')
              {
                  $select["status"] = "success"; 
                  $select["message"] = "Services susessfully Added";
                  unset($select["data"]);
                  echoResponse(200, $select);
              }else
              {
                  $select["status"] = "failed"; 
                  $select["message"] = "Something went wrong! please try again later";
                  unset($select["data"]);
                  echoResponse(200, $select);
              }     
          }
          else
          {
             $empty_json["status"] = "failed"; 
             $empty_json['message'] ="No Request parameter";
             echoResponse(200,$empty_json);
          }
      }
      else
      {
            $check['status'] = "false";
            $check['message'] = "Invalid Token";
            unset($check['data']);
            echoResponse(200,$check);
      }
  }  
  else
  {
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
  } 
});

//upload documnet
$app->post('/Upload_Document',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $title = $app->request()->params('title');
        $provider_id = $check['data'][0]['user_id'];
        global $db;
          if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name']))
          {
              $image= $_FILES['image']['tmp_name'];
              $image_name= $_FILES['image']['name'];
              $image_name = militime.$image_name;
              move_uploaded_file($image,"../../uploads/gallary/".$image_name);
              $u_image1 = base_url."uploads/gallary/".$image_name;

              $data = array(
                   'title' => $title,
                   'provider_id' => $provider_id,
                   'document_file'=>$image_name,
                   'start_date'=>date('Y-m-d H:i:s'),
                   'create_at'=>militime
                   );
              $rows =$db->insert("gallary",$data,array());
              if($rows['status'] =='success')
              {
                  $rows['status'] = "success";
                  $rows['message'] = "upload successfully";
                  echoResponse(200,$rows);
              }
              else
              {
                  $rows['status'] = "failed";
                  $rows['message'] = "something went wrong";
                  echoResponse(200,$rows);
              }
          }
          else
          {
              $check['status'] = "failed";
              $check['message'] = "All parameter required";
              unset($check['data']);
              echoResponse(200,$check);   
          }

      }        
      else
      {
          $check['status'] = "failed";
          $check['message'] = "Your Tooreest account has been temporarily suspended as a security precaution.";
          unset($check['data']);
          echoResponse(200,$check);
      }
  }   
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Upload_Document_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
           $data = json_decode($json1);
           $provider_id = $data->provider_id;
           //$orderby = "ORDER BY provider_id DESC";
           global $db;
           $start_date = $data->create_at; 
            $start ='';
            if($start_date != 0 && $start_date != '')
            {
               $start = "AND create_at < '$start_date'";
            }
           if(!empty($provider_id) && $provider_id ==0)
           {
                $provider_id=$check['data'][0]['user_id']; 
           }
                //$condition=array('provider_id'=>$provider_id);
                //$rows = $db->select2("upload_document","*", $condition,$orderby);
                //print_r($rows);exit;
                $rows = $db->customQueryselect("SELECT * FROM gallary WHERE start_date BETWEEN DATE_SUB(NOW(), INTERVAL 15 DAY) AND NOW() AND provider_id = $provider_id  ".$start." ORDER BY gallary_id DESC LIMIT 10");
                if($rows['status']=='success')
                {
                    foreach ($rows['data'] as $key) {
                        $arr[] = array(
                                'document_id'=>$key['gallary_id'],
                                'title'=>$key['title'],
                                'image'=>base_url."uploads/gallary/".$key['document_file'],
                                'request_dt'=>$key['start_date'],                             
                                'create_at'=>$key['create_at']                                
                               );
                            }
                    $rows['status'] = "success";
                    $rows['message'] = "Data selected successfully";
                    $rows['data'] = $arr;
                    echoResponse(200,$rows);
                }else
                {
                    $rows['status'] = "failed" ;
                    $rows['message'] = "No request found";
                    unset($rows['data']);
                    echoResponse(200,$rows);
                }
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
    $msg['message'] = "Invalid Token";
    echoResponse(200,$msg);
  }
}
else
{
  $msg['message'] = "Unauthorised access";
  echoResponse(200,$msg);
}

});

$app->post('/Manage_availability',function() use ($app)
{
    $headers = apache_request_headers();
    if(!empty($headers['secret_key']))
    {
        global $db;
        $check = token_auth($headers['secret_key']);
        if($check['status']=="true")
        { 
            $Json = file_get_contents('php://input'); 
            $json_array = json_decode($Json);
            $flag = $json_array->flag;  //1 = for all, 0=for select
            $timing = '';
            if(!empty($Json))
            {
                if($flag==1)
                {
                  $timing = $db->update("users",array('avail_type'=>$flag),array('user_id'=>$check['data'][0]['user_id']),array());
                }else
                {
                  $timing = $db->update("users",array('availability'=>$Json,'avail_type'=>$flag),array('user_id'=>$check['data'][0]['user_id']),array());
                }
                if($timing)
                {
                    $final_output['status'] = 'success';
                    $final_output['message'] = 'Successfully';
                }else
                {
                    $final_output['status'] = 'failed';
                    $final_output['message'] = 'Something went wrong! Please try again later.';
                }
                echoResponse(200,$final_output);
            }else
            {
                $final_output['message'] = 'Request parameter not valid'; 
               echoResponse(200,$final_output);
            }
        }  
        else
        {
          $msg['message'] = "Invalid Token";
          echoResponse(200,$msg);
        }
    }
    else
    {
        $msg['message'] = "Unauthorised access";
        echoResponse(200,$msg);
    }      

});

$app->post('/Delete_services',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          $data = json_decode($json1);
          $service_id = $data->service_id;
          $id = $check['data'][0]['user_id'];
          global $db;
            if(!empty($service_id))
            {
               $rows = $db->update("provider_services",array('status'=>0),array('service_id'=>$service_id,'provider_id'=>$id),array());
               if($rows['status']=='success')
               {
                  $rows['status'] = "success";
                  $rows['message'] = "Delete Successfully";
                  echoResponse(200,$rows);
               }else{
                  $rows['status'] = "failed";
                  $rows['message'] = "Something went wrong! please try again later.";
                  echoResponse(200,$rows);
               }
            }
            else
            {
                $json2['status'] ="failed";
                $json2['message'] ="No Request parameter";
                echoResponse(200,$json2);
            }
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

//provider list by subcate id and filter
// $app->post('/Get_providerby_catid_filter',function() use ($app){ 
//     $json1 = file_get_contents('php://input');          
//     if(!empty($json1))
//     {   
//         $data = json_decode($json1);
//         $sub_category_id = $data->sub_category_id;           
//         $lat = $data->lat;           
//         $lng = $data->lng;           
//         $type = $data->type;    // get data by filter or only by category       
//         $guide_type = $data->guide_type;    // physical, virtual, both       
//         $min_range = $data->min_range;           
//         $max_range = $data->max_range;           
//         $distance = $data->distance;           
//         $start_date = $data->create_at;
//         //$customer_id  = $check['data'][0]['user_id'];
//         global $db;      
//         $start =''; $servicetype = ''; $pricerange = '';
//         if($start_date != 0 && $start_date != '')
//         {
//            $start = "AND users.create_at <'$start_date'";
//         }
//         if($guide_type!='' && $guide_type!=3)
//         {
//            $servicetype = "AND provider_services.service_type = '$guide_type'";
//         }elseif($guide_type==3)
//         {
//            $servicetype = "AND (provider_services.service_type = 1 OR provider_services.service_type = 2)";
//         }
//         if($min_range!='')
//         {
//            $pricerange = "AND provider_services.service_charge BETWEEN '$min_range' AND '$max_range'";
//         }
        
//         if($type=='1')
//         {
//             $select = $db->customQueryselect("SELECT users.company_name,users.availability,users.full_name,users.mobile_no,users.email,users.address,users.postal_code,users.language,users.country,users.city,users.about_me,users.image,users.facebook,users.google_plus,users.twitter,users.linkedin,users.create_at,users.avail_type,users.admin_status,(( 3959 * acos( cos( radians('$lat') ) * cos( radians(`lat`) ) 
//                                 * cos( radians(`lng`) - radians('$lng')) + sin(radians('$lat')) 
//                                 * sin( radians(`lat`))))) AS distance,provider_services.status,provider_services.service_id,provider_services.provider_id,provider_services.sub_category_id,provider_services.service_charge,provider_services.service_type,provider_services.description,provider_services.image as subcateimage FROM users INNER JOIN provider_services ON users.user_id=provider_services.provider_id HAVING distance <= '$distance' AND provider_services.sub_category_id = '$sub_category_id' ".$servicetype." ".$pricerange." ".$start." AND provider_services.status = 1 order by distance ASC");  
//         }else
//         {
//             $select = $db->customQueryselect("SELECT users.company_name,users.availability,users.full_name,users.mobile_no,users.email,users.address,users.postal_code,users.language,users.country,users.city,users.about_me,users.image,users.facebook,users.google_plus,users.twitter,users.linkedin,users.avail_type,users.create_at,(( 3959 * acos( cos( radians('$lat') ) * cos( radians(`lat`) ) 
//                                 * cos( radians(`lng`) - radians('$lng')) + sin(radians('$lat')) 
//                                 * sin( radians(`lat`))))) AS distance,provider_services.status,provider_services.service_id,provider_services.provider_id,provider_services.sub_category_id,provider_services.service_charge,provider_services.service_type,provider_services.description,provider_services.image as subcateimage FROM users INNER JOIN provider_services ON users.user_id=provider_services.provider_id  HAVING distance <= '$distance' AND provider_services.sub_category_id = '$sub_category_id' ".$start." AND provider_services.status = 1 order by distance ASC");  
//         }
//         //print_r($select);exit;
//         if($select['status']=="success")
//         {
//             foreach ($select['data'] as $key){

//                /*if($key['admin_status']==1)
//                {  
//                   $userstatus = 'Verified';
//                }else
//                {
//                   $userstatus = 'Unverified';
//                }*/
//                 $avesel= $db->customQueryselect("SELECT AVG(rating) as averating FROM rating WHERE provider_id ='".$key['provider_id']."' AND sub_cat_id = '$sub_category_id'");
//                 if(!empty($avesel['data'][0]['averating']))
//                 { 
//                   $averat = $avesel['data'][0]['averating'];
//                 }else
//                 {
//                   $averat = 0;
//                 }
//                 $idproof = $db->select("provider_document","license_num",array('provider_id'=>$key['provider_id']));
//                 if($idproof['status']=='success')
//                 {
//                     $licence_num = $idproof['data'][0]['license_num'];
//                 }else
//                 {
//                     $licence_num = 0;
//                 }
                
//                 /*$user_like = $db->select("favourite","*",array('user_id'=>$customer_id,'provider_id'=>$key['user_id']));
            
//                 if($user_like['status']=='success'){
//                    $like_status1 = 1;
//                 }
//                 else{
//                    $like_status1 = 0; 
//                 }*/

               
//                 /*$bcount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id ='".$key['user_id']."' AND booking_status ='completed'");
//                 $counts= $bcount['data'][0]['booking_count'];
//                 */
//                 if($key['image'])
//                 {
//                   $images = base_url.'uploads/user_image/'.$key['image'];
//                 }else
//                 {
//                   $images = '';
//                 }

//                 if($key['subcateimage'])
//                 {
//                   $subimages = base_url.'uploads/subcategory_image/'.$key['subcateimage'];
//                 }else
//                 {
//                   $subimages = '';
//                 }
//                 $new = json_decode($key['availability']);
//                 $key['availability'] = $new;
//                 $key['image'] = $images;
//                 $key['licence_num'] = $licence_num;
//                 $key['service_image'] = $subimages;
//                 $key['ave_rating'] = $averat;
//                 $key['flag'] = $key['avail_type'];
//                 $arr[] = $key;
//             }
            
//         }
//         if(!empty($arr))
//         {
//             $selct["status"] = "success"; 
//             $selct["message"] = "Successfully";
//             $selct["data"] = $arr;
//             echoResponse(200, $selct);
//         }else
//         {
//             $selct["status"] = "failed"; 
//             $selct["message"] = "We are sorry, guide under this category is not available at the moment.You may continue to check other categories";
//             unset($selct["data"]);
//             echoResponse(200, $selct);
//         }
//     }else
//     {
//       $check['status']='failed';
//       $check['message']='No request parameter';
//       unset($check['data']);
//       echoResponse(200,$check);
//     }      
// });

$app->post('/Get_providerby_catid_filter',function() use ($app){ 
    $json1 = file_get_contents('php://input');          
    if(!empty($json1))
    {   
        $data = json_decode($json1);
        $sub_category_id = $data->sub_category_id;           
        $lat = $data->lat;           
        $lng = $data->lng;           
        $type = $data->type;    // get data by filter or only by category       
        $guide_type = $data->guide_type;    // physical, virtual, both       
        $min_range = $data->min_range;           
        $max_range = $data->max_range;           
        $distance = $data->distance;           
        $start_date = $data->create_at;
        $days = $data->day;
        $page_no = $data->page;
        //$customer_id  = $check['data'][0]['user_id'];
        global $db;      
        $start =''; $servicetype = ''; $pricerange = ''; $subcate = '';
        if($start_date != 0 && $start_date != '')
        {
           $start = "AND provider_services.create_at < '$start_date'";
        }
        if($sub_category_id != 0 && $sub_category_id != '')
        {
           $subcate = "AND provider_services.sub_category_id = '$sub_category_id'";
        }
        if($guide_type!='' && $guide_type!=3)
        {
           $servicetype = "AND provider_services.service_type = '$guide_type'";
        }elseif($guide_type==3)
        {
           $servicetype = "AND (provider_services.service_type = 1 OR provider_services.service_type = 2)";
        }
        if($min_range!='' && $min_range!=0)
        {
           $pricerange = "AND provider_services.service_charge BETWEEN '$min_range' AND '$max_range'";
        }
          
        $per_set = 10;
        $from  = ($page_no-1)*$per_set;
        $orderby = "limit $from,$per_set";
        $arr = array();
        if($lat == 0 && $lng == 0)
        {
            $select = $db->customQueryselect("SELECT users.company_name,users.availability,users.avail_type,users.full_name,users.mobile_no,users.email,users.address,users.postal_code,users.language,users.country,users.city,users.about_me,users.image,users.facebook,users.google_plus,users.twitter,users.linkedin,provider_services.create_at,users.avail_type,users.admin_status,users.mobile_status,users.email_status,provider_services.status,provider_services.service_id,provider_services.provider_id,provider_services.sub_category_id,provider_services.service_charge,provider_services.service_type,provider_services.description,provider_services.image as subcateimage FROM users INNER JOIN provider_services ON users.user_id=provider_services.provider_id WHERE provider_services.status = 1 AND users.admin_status = 0 AND users.mobile_status = 1 AND users.email_status = 1 ".$subcate." ".$servicetype." ".$pricerange." order by provider_services.create_at DESC ".$orderby."");
        }else
        {
          if($type=='1')
          {
              $select = $db->customQueryselect("SELECT users.company_name,users.availability,users.avail_type,users.full_name,users.mobile_no,users.email,users.address,users.postal_code,users.language,users.country,users.city,users.about_me,users.image,users.facebook,users.google_plus,users.twitter,users.linkedin,provider_services.create_at,users.avail_type,users.admin_status,users.mobile_status,users.email_status,(( 3959 * acos( cos( radians('$lat') ) * cos( radians(`lat`) ) 
                                  * cos( radians(`lng`) - radians('$lng')) + sin(radians('$lat')) 
                                  * sin( radians(`lat`))))) AS distance,provider_services.status,provider_services.service_id,provider_services.provider_id,provider_services.sub_category_id,provider_services.service_charge,provider_services.service_type,provider_services.description,provider_services.image as subcateimage FROM users INNER JOIN provider_services ON users.user_id=provider_services.provider_id HAVING distance <= '$distance' ".$subcate." ".$servicetype." ".$pricerange." AND provider_services.status = 1 AND users.admin_status = 0 AND users.mobile_status = 1 AND users.email_status = 1 order by distance ASC ".$orderby."");  
          }else
          {
              $select = $db->customQueryselect("SELECT users.company_name,users.availability,users.avail_type,users.full_name,users.mobile_no,users.email,users.address,users.postal_code,users.language,users.country,users.city,users.about_me,users.image,users.facebook,users.google_plus,users.twitter,users.linkedin,users.avail_type,users.admin_status,users.mobile_status,users.email_status,provider_services.create_at,(( 3959 * acos( cos( radians('$lat') ) * cos( radians(`lat`) ) 
                                  * cos( radians(`lng`) - radians('$lng')) + sin(radians('$lat')) 
                                  * sin( radians(`lat`))))) AS distance,provider_services.status,provider_services.service_id,provider_services.provider_id,provider_services.sub_category_id,provider_services.service_charge,provider_services.service_type,provider_services.description,provider_services.image as subcateimage FROM users INNER JOIN provider_services ON users.user_id=provider_services.provider_id  HAVING distance <= '$distance' ".$subcate."  AND provider_services.status = 1 AND users.admin_status = 0 AND users.mobile_status = 1 AND users.email_status = 1 order by distance ASC ".$orderby."");  
          }
        }
        
        if($select['status']=="success")
        {
            foreach ($select['data'] as $key){

               /*if($key['admin_status']==1)
               {  
                  $userstatus = 'Verified';
               }else
               {
                  $userstatus = 'Unverified';
               }*/
               $avail = false;
               if($key['avail_type']==1)
               {
                  $avail = true;
                  $new = (object)array();
               }elseif($key['availability']!='')
               {  
                  $new = json_decode($key['availability']);
                  for ($i=0; $i < count($new->timings); $i++) { 
                      $cc = $new->timings[$i]->day;
                      if($cc == $days)
                      {
                        $avail = true;
                        break;
                      }
                  }
               } 

               if($avail == true)
               {
                  if(!empty($key['availability']))
                  {
                    $new = json_decode($key['availability']);
                  }
                  if(!isset($key['distance']))
                  {
                      $dis = "0.0"; 
                  }else
                  {
                      $dis = $key['distance'];
                  }
                  $avesel= $db->customQueryselect("SELECT AVG(rating) as averating FROM rating WHERE provider_id ='".$key['provider_id']."' AND sub_cat_id = '$sub_category_id'");
                  if(!empty($avesel['data'][0]['averating']))
                  { 
                    $averat = $avesel['data'][0]['averating'];
                  }else
                  {
                    $averat = 0;
                  }
                  $idproof = $db->select("provider_document","license_num",array('provider_id'=>$key['provider_id']));
                  if($idproof['status']=='success')
                  {
                      $licence_num = $idproof['data'][0]['license_num'];
                  }else
                  {
                      $licence_num = 0;
                  }
                  
                  $subcate = $db->select("subcategory","sub_name",array('sub_id'=>$key['sub_category_id']));
                  if($subcate['status']=='success')
                  {
                      $subname = $subcate['data'][0]['sub_name'];
                  }else
                  {
                      $subname = '';
                  }
                  /*$user_like = $db->select("favourite","*",array('user_id'=>$customer_id,'provider_id'=>$key['user_id']));
              
                  if($user_like['status']=='success'){
                     $like_status1 = 1;
                  }
                  else{
                     $like_status1 = 0; 
                  }*/

                 
                  /*$bcount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id ='".$key['user_id']."' AND booking_status ='completed'");
                  $counts= $bcount['data'][0]['booking_count'];
                  */
                  if($key['image'])
                  {
                    $images = base_url.'uploads/user_image/'.$key['image'];
                  }else
                  {
                    $images = '';
                  }

                  if($key['subcateimage'])
                  {
                    $subimages = base_url.'uploads/subcategory_image/'.$key['subcateimage'];
                  }else
                  {
                    $subimages = '';
                  }
                  $key['distance'] = $dis;
                  $key['availability'] = $new;
                  $key['image'] = $images;
                  $key['licence_num'] = $licence_num;
                  $key['service_image'] = $subimages;
                  $key['ave_rating'] = $averat;
                  $key['flag'] = $key['avail_type'];
                  $key['sub_category'] = $subname;
                  $arr[] = $key;

               }
            }
            /*if(!empty($arr))
            {*/
                $selct["status"] = "success"; 
                $selct["message"] = "Successfully";
                $selct["data"] = $arr;
                echoResponse(200, $selct);
            /*}else
            {
                $selct["status"] = "failed"; 
                $selct["message"] = "We are sorry, guide under this category is not available at the moment.You may continue to check other categories";
                unset($selct["data"]);
                echoResponse(200, $selct);
            }*/
        }else
        {
          $selct["status"] = "failed"; 
          $selct["message"] = "We are sorry, guide under this category is not available at the moment.You may continue to check other categories";
          unset($selct["data"]);
          echoResponse(200, $selct);
        }
    }else
    {
      $check['status']='failed';
      $check['message']='No request parameter';
      unset($check['data']);
      echoResponse(200,$check);
    }      
});
/*$app->post('/Manage_availability',function() use ($app) //multiple entry
{
    $headers = apache_request_headers();
    if(!empty($headers['secret_key']))
    {
        global $db;
        $check = token_auth($headers['secret_key']);
        if($check['status']=="true")
        { 
            $Json = file_get_contents('php://input'); 
            $json_array = json_decode($Json);
            $timing = '';
            if(!empty($json_array))
            {
                if(!empty($json_array->timings))
                {
                    for ($i=0; $i < count($json_array->timings); $i++) { 
                      
                      for ($j=0; $j < count($json_array->timings[$i]->timearr); $j++) { 
        
                          $timing = $db->update("users",array('provider_id'=>$check['data'][0]['user_id'],'visit_time_from'=>$json_array->timings[$i]->timearr[$j]->from,'visit_time_to'=>$json_array->timings[$i]->timearr[$j]->to,'visit_day'=>$json_array->timings[$i]->day,'create_at'=>date('Y-m-d H:i:s'),'update_at'=>date('Y-m-d H:i:s')),array());
                          }
                      }
                    if($timing)
                    {
                        $final_output['status'] = 'success';
                        $final_output['message'] = 'Successfully';
                    }else
                    {
                        $final_output['status'] = 'failed';
                        $final_output['message'] = 'Something went wrong! Please try again later.';
                    }
                    echoResponse(200,$final_output);
                }else
                {
                    $final_output['message'] = 'No Request parameter found'; 
                    echoResponse(200,$final_output);
                }
            }else
            {
                $final_output['message'] = 'Request parameter not valid'; 
               echoResponse(200,$final_output);
            }
        }  
        else
        {
          $msg['message'] = "Invalid Token";
          echoResponse(200,$msg);
        }
    }
    else
    {
        $msg['message'] = "Unauthorised access";
        echoResponse(200,$msg);
    }      

});*/
//////*********OLD API*****\\\\\\\

$app->post('/Add_offer',function() use ($app)
{
    $headers = apache_request_headers();
    if(!empty($headers['secret_key']))
    {
        $check = token_auth($headers['secret_key']);
        if($check['status']=="true")
        {
            $json = file_get_contents('php://input');
            if(!empty($json))
            {
                $provider_id = $check['data'][0]['user_id'];
                $data = json_decode($json);
                if(!empty($data))
                {
                    global $db;
                    $title = $data->title;
                    $description = $data->description;
                    $amount = $data->amount;

                    if($check['data'][0]['type']==2)
                    {
                        $inseoff = $db->insert("provider_offers",array('provider_id'=>$provider_id,'title'=>$title,'description'=>$description,'amount'=>$amount,'start_date'=>$data->start_date,'end_date'=>$data->end_date,'create_at'=>militime,'update_at'=>militime),array());
                       
                        if($inseoff['status']=="success")
                        {
                            $inseoff['status']="success";
                            $inseoff['message']="Offer Successfully Added";
                            unset($inseoff['data']);
                        }else
                        {
                            $inseoff['status']="failed";
                            $inseoff['message']="Something went wrong! Please try again later";
                            unset($inseoff['data']);
                        }
                        echoResponse(200,$inseoff);
                    }else
                    {
                        $check['status'] = "failed";
                        $check['message'] = "You are not applicable for added offer";
                        unset($check['data']);
                        echoResponse(200,$check);
                    }
                }else
                {
                   $check['status'] = "failed";
                   $check['message'] = "No Request parameter";
                   unset($check['data']);
                   echoResponse(200,$check);
                }
            }else
            {
                $check['status'] = "failed";
                $check['message'] = "No Request parameter";
                unset($check['data']);
                echoResponse(200,$check);
            }
        }else
        {
            $check['status'] = "false";
            $check['message'] = "Invalid Token";
            unset($check['data']);
            echoResponse(200,$check);
        }
    }else
    {
      $check['status'] = "false";
      $check['message'] = "Unauthorised access";
      unset($check['data']);
      echoResponse(200,$check);
    }
});



$app->post('/Change_Password',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
     {
        if($check['data'][0]['admin_status'] == 0) 
        {    
            $json1 = file_get_contents('php://input');
            if(!empty($json1))
            {
               $data = json_decode($json1);
               $current_password = $data->c_password;
               $new_password = $data->n_password;
               $confirm_password = $data->con_password;
               global $db;

               if(!empty($current_password) && !empty($new_password) && !empty($confirm_password))
               {
                   $user_id = $check['data'][0]['user_id'];
                   $condition = array('user_id'=>$user_id);
                   $password = $check['data'][0]['password'];
                   $cc_password = sha1($current_password);
                   if($cc_password == $password)
                   {
                      $new_password = sha1($new_password);
                      $con_password = sha1($confirm_password);
                   
                      if ($new_password == $con_password) 
                      {
                          $data =array(
                                       'password' => $new_password,
                                      );

                          $rows = $db->update("users",$data,$condition,array());
                          /*print_r($rows);
                          exit;*/
                          if($rows['status'] =='success')
                          {
                              $check['status'] = 'success';
                              $check['message'] ="your password has been susessfully changed";
                              unset($check["data"]);
                              echoResponse(200,$check);
                          }
                          else
                          {
                              $check["status"] = "failed";
                              $check['message'] ="something went wrong";
                              unset($check["data"]);
                              echoResponse(200,$check);
                          }
                      }
                      else
                      {   
                          $check["status"] = "failed";
                          $check['message'] ="New Password & Confirm password is not matched";
                          unset($check["data"]);
                          echoResponse(200,$check);
                      }

                   }
                   else
                   {   
                       $check["status"] = "failed";
                       $check['message'] ="Current password is not valid";
                       unset($check["data"]);
                       echoResponse(200,$check); 
                   }        
                        
               }
               else
               {
                   $json2['message'] ="Request parameter not valid";
                   echoResponse(200,$json2);
               }
         
             }
            else
            {
                $json1['message'] ="No Request parameter";
                echoResponse(200,$json1);  
            }
        }
        else
        {
            $check['status'] = "failed";
            $check['message'] = "Your Jokaamo account and has been temporarily suspended as a security precaution.";
            unset($check['data']);
            echoResponse(200,$check);
        }       
      }
      else
      {
          $check['status'] = "false";
          $check['message'] = "Invalid Token";
          unset($check['data']);
          echoResponse(200,$check);
      }
}
else
{
  $check['status'] = "false";
  $check['message'] = "Unauthorised access";
  unset($check['data']);
  echoResponse(200,$check);
}
});



$app->post('/Rating_Review',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
   $check = token_auth($headers['secret_key']);
   if($check['status']=="true")
   {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
           $data = json_decode($json1);
           $rating = $data->rating;
           $review = $data->review;
           $sub_cat_id = $data->sub_cat_id;
           $provider_id = $data->provider_id;
           $order_id = $data->order_id;
           $user_id = $check['data'][0]['user_id'];
           $name = $check['data'][0]['full_name'];

           $condition = array('user_id'=>$user_id, 'provider_id'=>$provider_id,'sub_cat_id'=>$sub_cat_id,'order_id'=>$order_id);
           global $db;

           if(!empty($rating)/* && !empty($review)*/ && !empty($provider_id))
           {
              $data = array('rating'=>$rating,
                          'review'=>$review,
                          'provider_id'=>$provider_id,
                          'sub_cat_id'=>$sub_cat_id,
                          'user_id'=>$user_id,
                          'order_id'=>$order_id,
                          'create_at'=>militime
                          );
               $rowse = $db->select("rating","*",$condition);
               if($rowse["status"]=="success")
               {
                  $provider_id =$rowse['data'][0]['provider_id'];
                  $order_id =$rowse['data'][0]['order_id'];
                  $rating =$rowse['data'][0]['rating'];

                   $providselct = $db->select("customer_booking","*",array('order_id'=>$order_id));
                   // $u_id = $providselct['data'][0]['user_id'];
                   $sub = $providselct['data'][0]['subcat_id'];

                   $get_sub = $db->select("subcategory","sub_name",array('sub_id'=>$sub));
                   $sub_name1 =$get_sub['data'][0]['sub_name'];

                  $rowess = $db->update("rating",array('rating'=>$rating,'review'=>$review,'update_at'=>militime),$condition,array());
                  if($rowess["status"]=="success")
                  {
                      /*$select1 = $db->select("users","device_token",array('user_id'=>$provider_id));

                               $msg ="".$name." gave you ".$rating." stars on ".$sub_name1."";
         
                               $message = array("message" =>$msg,"image" =>'',"title" =>'Review on service',"type"=>'review',"timestamp" =>militime);

                               $notification_array = array('sender_id'=>$user_id,
                                                           'reciver_id'=>$provider_id,
                                                           'message'=>$msg,
                                                           'type'=>'review',
                                                           'title'=>'Review on service',
                                                           'Create_at'=>militime
                                                           );
                               $notification_id = $db->insert("notification_tb",$notification_array, array());
                               AndroidNotification($select1['data'][0]['device_token'],$message);
*/
                      $rowess["status"] = "success"; 
                      $rowess["message"] = "rating and review updated successfully";
                      unset($rowess['data']);
                      echoResponse(200, $rowess);
                  }
                  else
                  {
                      $rowess["status"] = "failed";
                      $rowess["message"] = "Somthing went wrong! please try again later";
                      unset($rowess['data']);
                      echoResponse(200, $rowess);
                  }  
               }
               else
               {
                  
                  $rowess = $db->insert("rating",$data,array());
                  if($rowess["status"]=="success")
                  {    
                      

                      /* $select1 = $db->select("users","device_token",array('user_id'=>$provider_id));

                               $msg ="".$name." gave you ".$rating." stars on Booking #".$order_id."";
         
                               $message = array("message" =>$msg,"image" =>'',"title" =>'Review on service',"type"=>'review',"timestamp" =>militime);

                               $notification_array = array('sender_id'=>$user_id,
                                                           'reciver_id'=>$provider_id,
                                                           'message'=>$msg,
                                                           'type'=>'review',
                                                           'title'=>'Review on service',
                                                           'Create_at'=>militime
                                                           );
                               $notification_id = $db->insert("notification_tb",$notification_array, array());
                               AndroidNotification($select1['data'][0]['device_token'],$message);  */

                      $rowess["status"] = "success"; 
                      $rowess["message"] = "rating and review added successfully";
                      unset($rowess['data']);
                      echoResponse(200, $rowess);
                  }
                  else
                  {
                      $rowess["status"] = "failed";
                      $rowess["message"] ="Somthing went wrong! please try again later";
                      unset($rowess["data"]);
                      echoResponse(200, $rowess);
                  }
               }
           }
           else
           {
              $json2["status"] = "failed";
              $json2['message'] ="Request parameter not valid";
              echoResponse(200,$json2);  
           }
        }
        else
        {
           $json1["status"] = "failed"; 
           $json1['message'] ="No Request parameter";
           echoResponse(200,$json1); 
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);
     } 
   }
   else
   {
      $check['status'] = "false";
      $check['message'] = "Invalid Token";
      unset($check['data']);
      echoResponse(200,$check);
   }
}
else
{
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
}
});
//<----------------------- Rating and Review list --------------->

$app->post('/Rating_and_Review_List',function() use ($app){
    $headers = apache_request_headers();
    if($headers['secret_key'] =='abcd')
    {
        $userid = 'abcd';
    }
    else
    {                
        $check = token_auth($headers['secret_key']);
        if($check['status']=="true")
        { 
            $userid = 'efgh';
        }else
        {
            $userid = 0;
        }
    }
    if(ctype_alpha($userid))
    {   
       $json = file_get_contents('php://input');    
        if(!empty($json))
        {
            $data = json_decode($json); 
            if(!empty($data))
            {          
                $provider_id = $data->provider_id;
                if($userid=='efgh')
                {
                    if($check['data'][0]['type'] != 1) 
                    {
                        $provider_id = $check['data'][0]['user_id'];
                    }
                }
            }else
            {
                $provider_id = 0;
            }  
        }else
        {
          $provider_id = 0;
        }  
            global $db;
            $usrname= '';
            $arr = array();
            $select = $db->customQueryselect("SELECT * FROM rating WHERE provider_id = $provider_id ORDER BY rating_id DESC LIMIT 10");
              //$select = $db->select("rating","*",array('provider_id'=>$provider_id));
            if($select['status']=="success")
            {
                foreach ($select['data'] as $key){
                  
                  $userdata = $db->select("users","full_name",array('user_id'=>$key['user_id']));
                  if($userdata['status']=="success")
                  {
                    $usrname = $userdata['data'][0]['full_name'];
                  }
                  $arr[] = array('rating_id'=>$key['rating_id'],'user_id'=>$key['user_id'],'username'=>$usrname,'rating'=>$key['rating'],'review'=>$key['review']);
                }
                $select['status']="success";
                $select['message']="Rating & Review List";
                $select['data'] = $arr;
                echoResponse(200,$select);
            }else
            {
                $select['status']="failed";
                $select['message']="No Rating & Review found";
                unset($select['data']);
                echoResponse(200,$select);
            }
    }else
    {
        $check['status'] = "false";
        $check['message'] = "Invalid Token";
        unset($check['data']);
        echoResponse(200,$check);
    }
});

//End Rating and Review List


$app->post('/Total_Earning',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {    
          $provider_id = $check['data'][0]['user_id'];
          global $db;
           //$dw = date( "w", $timestamp);
           $date_time = date('Y-m-d: H:i:s');
           $day = date('D');
           if($day=="Mon")
           {
            //$date = new DateTime('-4 day');
            $date1=date('Y-m-d');
           }
           else if($day=="Tue")
           {
            $date = new DateTime('-1 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Wed")
           {
            $date = new DateTime('-2 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Thu")
           {
            $date = new DateTime('-3 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Fri")
           {
            $date = new DateTime('-4 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Sat")
           {
            $date = new DateTime('-5 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Sun")
           {
            $date = new DateTime('-6 day');
            $date1=$date->format('Y-m-d');
           }
            
            /*echo "SELECT SUM(service_amt) AS week_amonut FROM customer_booking WHERE provider_id = $provider_id AND booking_date_time BETWEEN $date1 AND $date_time ";
            exit;       */

          //$date_time = $db->select("customer_booking","booking_date_time",array('provider_id'=>$provider_id));
   
           $week_amt = $db->customQueryselect("SELECT SUM(service_amt) AS week_amonut FROM customer_booking WHERE provider_id = '".$provider_id."' AND booking_status_datetime BETWEEN '".$date1."' AND '".$date_time."' AND booking_status ='completed' ");
           
            $amt = $week_amt['data'][0]['week_amonut'];
            if($week_amt['data'][0]['week_amonut']==''){
              $amt=0;
            }

           $amount = $db->customQueryselect("SELECT SUM(service_amt) AS amount FROM customer_booking WHERE provider_id ='".$provider_id."' AND booking_status ='completed'");
           $tamont= $amount['data'][0]['amount'];
           if($amount['data'][0]['amount']==''){
              $tamont=0;
            }

           $ccount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id ='".$provider_id."' AND booking_status ='completed'");
           $acount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id ='".$provider_id."' AND booking_status ='accepted'");
           $bcount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id ='".$provider_id."' AND booking_status ='booked'");
           $v = $db->select("users","is_id_verify",array('user_id'=>$provider_id));

           $ides = $v['data'][0]['is_id_verify'];

           $ccounts= $ccount['data'][0]['booking_count'];
           $acounts= $acount['data'][0]['booking_count'];
           $bcounts= $bcount['data'][0]['booking_count'];
                               
           $arr[] = array(
                    'is_id_verify'=>$ides,
                    'weekly_amount'=>$amt,
                    'total_earning'=>$tamont,
                    'accepted_count'=>$acounts,
                    'booked_count'=>$bcounts,
                    'completed_count'=>$ccounts,
                   );
              
            $row['status'] = "success";
            $row['message'] = "Data selected successfully";
            $row['data'] = $arr;
            $row['is_id_verify'] = $ides;
            echoResponse(200,$row);
    }
    else
    {
       $check['status'] = "failed";
       $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
       unset($check['data']);
       echoResponse(200,$check);            
    } 
  }  
  else
  {
       $check['status'] = "false";
       $check['message'] = "Invalid Token";
       unset($check['data']);
       echoResponse(200,$check);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});


$app->post('/Weekly_Total_Earning',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {          

         $provider_id = $check['data'][0]['user_id'];
          global $db;
          $no_mon="";
           //$dw = date( "w", $timestamp);
           $date_time = date('Y-m-d');
           $day = date('D');
           if($day=="Mon")
           {
            //$date = new DateTime('-4 day');
            $date1=date('Y-m-d');
           }
           else if($day=="Tue")
           {
            $date = new DateTime('-1 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Wed")
           {
            $date = new DateTime('-2 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Thu")
           {
            $date = new DateTime('-3 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Fri")
           {
            $date = new DateTime('-4 day');
            $date1=$date->format('Y-m-d');
            /*$week_amt = $db->customQueryselect("SELECT SUM(service_amt) AS week_amonut FROM customer_booking WHERE provider_id = '".$provider_id."' AND booking_date_time BETWEEN '".$date1."' AND '".$date_time."' AND booking_status ='completed'");
            $amt = $week_amt['data'][0]['week_amonut'];*/
           }
           else if($day=="Sat")
           {
            $date = new DateTime('-5 day');
            $date1=$date->format('Y-m-d');
           }
           else if($day=="Sun")
           {
            $date = new DateTime('-6 day');
            $date1=$date->format('Y-m-d');
           }

           $arr_no_days=array('0','0','0','0','0','0','0');
           $dt = getDatesFromRange( $date1, $date_time );

           $iz=0;
           foreach ($dt as $key )
           {
             // echo "SELECT SUM(service_amt) AS week_amonut FROM customer_booking WHERE provider_id = '".$provider_id."' AND booking_date_time = '$key' AND booking_status ='completed'<br>";
              $week_amt = $db->customQueryselect("SELECT SUM(service_amt) AS week_amonut FROM customer_booking WHERE provider_id = '".$provider_id."' AND booking_status_datetime like '$key%' AND booking_status ='completed'");
              //print_r($week_amt['data']);
              
              //echo "-".$week_amt['data'][0]['week_amonut']."-";
              if($week_amt['data'][0]['week_amonut'] !='' && !empty($week_amt['data'][0]['week_amonut']))
              {
                $weekz=$week_amt['data'][0]['week_amonut'];
              }
              else
              {
                $weekz=0;
              }
                $arr_no_days[$iz]=$weekz;
                $iz++;
              
               
           } 
             $i=0;
             //print_r($arr_no_days);exit;
              foreach ($arr_no_days as $key1 ) 
              {
                $new123 =array("Monday","Tuesday","Wednesday","Thusday","Friday","Saturday","Sunday");
                $arr[] = array(
                              'day'=>$new123[$i],
                              'amount'=>$key1,                                
                             );
                $i++;
              }
                $week_amt['status'] = "success";
                $week_amt['message'] = "Data selected successfully";
                $week_amt['data'] = $arr;
                echoResponse(200,$week_amt);
     }
    else
    {
       $check['status'] = "failed";
       $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
       unset($check['data']);
       echoResponse(200,$check);            
    } 
  }  
  else
  {
       $check['status'] = "false";
       $check['message'] = "Invalid Token";
       unset($check['data']);
       echoResponse(200,$check);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});


$app->post('/User_wallet',function() use ($app){
  $headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {
      global $db;
      $wallett = $db->select("user_wallet","user_id,wallet_amt",array('user_id'=>$check['data'][0]['user_id']));
      if($wallett['status']=="success")
      {
        $wallett['status'] = "success";
        $wallett['message'] = "Successfull";
        $wallett['data'] = array(
                            'wallet_amt'=>$wallett['data'][0]['wallet_amt']
                              );
        echoResponse(200,$wallett);
      }else
      {
        $wallett['status'] = "success";
        $wallett['message'] = "Successfull";
        $wallett['data'] = array(
                            'wallet_amt'=>0
                              );
        echoResponse(200,$wallett);
      }
    }else
    {
      $check['status'] = "false";
      $check['message'] = "Invalid Token";
      unset($check['data']);
      echoResponse(200,$check);
    } 
  }else
  {
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
  } 
});
//End User Wallet api

$app->post('/Request_Quotes',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {
         if($check['data'][0]['admin_status'] == 0) 
         {

           $json1 = file_get_contents('php://input');
           if(!empty($json1))
           {
               $data = json_decode($json1);
               $name = $data->name;
               $email = $data->email;
               $mobile = $data->mobile;
               $budget= $data->budget;
               $description= $data->description;
               $provider_id = $data->provider_id;
               global $db;
            
               if(!empty($email) && !empty($mobile) && !empty($provider_id) && !empty($name) && !empty($budget))
               {

               $data = array(
                     'name' => $name,
                     'email' => $email,
                     'mobile'=>$mobile,
                     'budget'=>$budget,
                     'description'=>$description,
                     'provider_id'=>$provider_id,
                     'create_at'=>militime,
                     //'start_date'=>$data->start_date
                     );
              
                  $rows = $db->insert("request_quotes",$data,array());
                  if($rows)
                  {
                      $rows['status'] = "success";
                      $rows['message'] = "Quote posted successfully.Provider will contact you soon";
                      echoResponse(200,$rows);
                  }
                  else
                  {
                     $rows['status'] = "failed";
                     $rows['message'] = "something went wrong";
                     echoResponse(200,$rows);
                  }
               }
               else
               {
                 $json2['status'] ="failed";
                 $json2['message'] ="No Request parameter";
                 echoResponse(200,$json2);
               }
            }
            else
            {
              $json1['message'] ="Invalid Request parameter";
              echoResponse(200,$json1);
            }   
          }
          else
          {
            $check['status'] = "failed";
            $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
            unset($check['data']);
            echoResponse(200,$check);
            
          }
    }        
    else
    {
       $msg['message'] = "Invalid Token";
       echoResponse(200,$msg);
    }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
} 
});


$app->post('/Provider_Offer_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          $data = json_decode($json1);
          $provider_id = $data->provider_id;
          //$orderby = "ORDER BY provider_id DESC";
          $date = date("Y-m-d H:i:s");
          global $db;
            if(($provider_id == 0))
            {
                $provider_id=$check['data'][0]['user_id']; 
            }
                //$condition=array('provider_id'=>$provider_id,'status'==0 );
                //$row = $db->select2("provider_offers","*", $condition,$orderby);
                $row = $db->customQueryselect("SELECT * FROM provider_offers WHERE provider_id = $provider_id AND status=0 AND end_date >= '$date'");
                if($row['status']=='success')
                {
                    foreach ($row['data'] as $key) {
                                $start_date=$key['start_date'];
                                $end_date=$key['end_date'];
                                $date1 = date_create($end_date);
                                $date2 = date_format($date1,"d M, Y");
                                $date3 = date_create($start_date);
                                $date4 = date_format($date3,"d M, Y"); 
                               
                        $arr[] = array(
                                'offer_id'=>$key['offer_id'],
                                'title'=>$key['title'],
                                'description'=>$key['description'],
                                'amount'=>$key['amount'],
                                'start_date'=>$date4,
                                'end_date'=>$date2,

                               );
                      }
                    $row['status'] = "success";
                    $row['message'] = "Data selected successfully";
                    $row['data'] = $arr;
                    echoResponse(200,$row);
                }else
                {
                    $row['status'] = "failed" ;
                    $row['message'] = "No offers available now";
                    unset($row['data']);
                    echoResponse(200,$row);
                }
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}

});

$app->post('/Delete_Upload_Document',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          $data = json_decode($json1);
          $document_id = $data->document_id;
          $id = $check['data'][0]['user_id'];
          $condition2 = array('document_id'=>$document_id,'user_id'=>$id);
          global $db;
            if(!empty($document_id))
            {
               $rows = $db->delete("upload_document",$condition2);
               if($rows['status']=='success')
               {
                  $rows['status'] = "success";
                  $rows['message'] = "Delete Successfully";
                  echoResponse(200,$rows);
               }else{
                  $rows['status'] = "failed";
                  $rows['message'] = "This id already deleted";
                  echoResponse(200,$rows);
               }
            }
            else
            {
                $json2['status'] ="failed";
                $json2['message'] ="No Request parameter";
                echoResponse(200,$json2);
            }
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});



$app->post('/Delete_Offer_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          $data = json_decode($json1);
          $offer_id = $data->offer_id;
          $condition = array('offer_id'=>$offer_id);
          $id = $check['data'][0]['user_id'];
          global $db;
            if(!empty($offer_id))
            {
               $rows = $db->select("provider_offers","*", $condition);
               if($rows['data'][0]['status'] == 0)
               {
                  
                   $rowss =$db->update("provider_offers",array('status'=>1,'update_at'=>militime),array('offer_id'=>$offer_id,'provider_id'=>$id),array());
                   if($rowss['status']=='success')
                   {

                      $rowss['status'] ="success";
                      $rowss['message'] ="Offer delete successfully";
                      unset($rowss['data']);
                      echoResponse(200,$rowss);

                   }else
                   {
                      $rowss['status'] ="failed";
                      $rowss['message'] ="Something went wrong";
                      unset($rowss['data']);
                      echoResponse(200,$rowss);   
                   }
               } 
               else
               {
                  $rows['status'] ="failed";
                  $rows['message'] ="This offer is alreay deleted";
                  unset($rows['data']);
                  echoResponse(200,$rows);
               }         
            }
            else
            {
               $json2['status'] ="failed";
               $json2['message'] ="No Request parameter";
               echoResponse(200,$json2);
            }
       
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Add_Favourite',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          $data = json_decode($json1);
          $provider_id = $data->provider_id;
          $id = $check['data'][0]['user_id'];
          global $db;
          if(!empty($provider_id))
            {
              $user_data = array(
                   'provider_id' =>$provider_id,
                   'user_id' =>$id,
                   'create_at'=>militime,
                   );
              $condition2 = array('user_id'=>$id,'provider_id'=>$provider_id);
              $query_login = $db->select("favourite","*",$condition2);
              if($query_login["status"] == "success")
              {
                         $query_login['status'] ="failed";
                         $query_login['message'] ="you already Like this Provider";
                         unset($query_login['data']);
                         echoResponse(200,$query_login);

              }else
              {
                    $rows1 = $db->insert("favourite",$user_data, array(),array());
                     if($rows1['status']=="success")
                     {
                           $rows1['status'] ="success";
                           $rows1['message'] ="Provider has been added in favorites";
                           //$rows1['data'];
                           echoResponse(200,$rows1);
                     }else
                     {
                         $rows1['status'] ="failed";
                         $rows1['message'] ="something went wrong! Please try again later";
                         unset($rows1['data']);
                         echoResponse(200,$rows1);
                     }     
              }           
            }
            else
            {
               $json2['message'] ="Invalid Request parameter";
               echoResponse(200,$json2);
            }
       
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}

});

$app->post('/Favourite_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     { 
        
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
           $data = json_decode($json1);
           $start_date = $data->create_at;
           global $db; 
           $start ='';
           if($start_date != 0 && $start_date != '')
           {
              $start = "AND favourite.create_at < '$start_date'";
           }
          $id = $check['data'][0]['user_id'];
          $arr = array();
          $query_login = $db->customQueryselect("SELECT favourite.*,user.user_id,user.company_name,user.full_name,user.city,user.image,user.about_me,user.facebook,user.google_plus,user.twitter,user.linkedin FROM favourite INNER JOIN user ON favourite.provider_id = user.user_id WHERE favourite.user_id = '$id' ".$start." ORDER BY fav_id DESC LIMIT 10");
        
          if($query_login["status"] == "success")
          {
              foreach ($query_login['data'] as $key) {
                  if($key['image']!='')
                  {
                      $image = base_url.'uploads/user_image/'.$key['image'];
                  }else
                  {
                    $image = '';
                  }          
                  $avesel= $db->customQueryselect("SELECT AVG(rating) as averating FROM rating WHERE provider_id ='".$key['provider_id']."'");
                  if($avesel['data'][0]['averating']!=null)
                  { 
                    $averat = $avesel['data'][0]['averating'];
                  }else
                  {
                    $averat = 0;
                  }

                  $idproof = $db->select("provider_id_proof","id_status,bankid_status",array('provider_id'=>$key['provider_id']));
                  if($idproof['status']=='status')
                  {
                      $id_status = $idproof['data'][0]['id_status'];
                      $bankid_status = $idproof['data'][0]['bankid_status'];

                  }else
                  {
                      $id_status = 0;
                      $bankid_status = 0;
                  }
                  $bcount = $db->customQueryselect("SELECT COUNT('booking_status') AS booking_count FROM customer_booking WHERE provider_id ='".$key['user_id']."' AND booking_status ='completed'");
                  $counts= $bcount['data'][0]['booking_count'];
                  $arr[] = array(
                         'user_id' =>$key['provider_id'],
                         'full_name'=>$key['full_name'],  
                         'company_name'=>$key['company_name'],
                         'image'=>$image,
                         'city'=>$key['city'],
                         'projectdone' =>$counts,  
                         'about_me'=>$key['about_me'],
                         'facebook'=>$key['facebook'],
                         'google_plus'=>$key['google_plus'],
                         'twitter'=>$key['twitter'],
                         'linkedin'=>$key['linkedin'],
                         'is_id_verify'=>$id_status,
                         'is_bank_verify'=>$bankid_status,
                         'ave_rating'=>$averat,
                         'create_at'=>$key['create_at']
                   );
              }
          }
          if(!empty($arr))
          {
              $rowss['status'] ="success";
              $rowss['message'] ="Favourite List";
              $rowss['data'] = $arr;
              echoResponse(200,$rowss);
          }else
          {
             $rows1['status'] ="failed";
             $rows1['message'] ="Empty favourite list";
             unset($rows1['data']);
             echoResponse(200,$rows1);
          }
        }else{
           $json1['message'] ="Invalid Request parameter";
           echoResponse(200,$json1);
        }             
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Delete_Favourite',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          $data = json_decode($json1);
          $provider_id = $data->provider_id;
          $id = $check['data'][0]['user_id'];
          $condition = array('user_id'=>$id,'provider_id'=>$provider_id);
          global $db;
          if(!empty($provider_id))
            {
                $rows1 = $db->delete("favourite",$condition);
                 if($rows1['status']=="success")
                 {
                       $rows1['status'] ="success";
                       $rows1['message'] ="Provider has been removed from favorites.";
                       //$rows1['data'];
                       echoResponse(200,$rows1);
                 }else
                 {
                     $rows1['status'] ="failed";
                     $rows1['message'] ="This Id already deleted";
                     unset($rows1['data']);
                     echoResponse(200,$rows1);
                 } 
            }
            else
            {
               $json2['message'] ="No Request parameter";
               echoResponse(200,$json2);
            }
       
        }  
        else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Request_Quotes_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
         $json1 = file_get_contents('php://input');
         if(!empty($json1))
         {
           $data = json_decode($json1);
            //$orderby = "ORDER BY provider_id DESC";
            global $db;
            $start_date = $data->create_at; 
            $start ='';
            if($start_date != 0 && $start_date != '')
            {
               $start = "AND create_at < '$start_date'";
            }
            //$order = $this->db->query("SELECT * FROM order_table WHERE buyer_id ='$user_id'  AND type=1 ".$create." ORDER BY order_id DESC LIMIT 10")->result();
  
            $provider_id=$check['data'][0]['user_id']; 
            //$condition=array('provider_id'=>$provider_id);
            //$rows = $db->select2("request_quotes","*", $condition,$orderby);
          // echo "SELECT * FROM request_quotes WHERE start_date BETWEEN DATE_SUB(NOW(), INTERVAL 15 DAY) AND NOW(); AND provider_id = $provider_id  ".$start." ORDER BY req_qut_id DESC LIMIT 10";exit;
            $rows = $db->customQueryselect("SELECT * FROM request_quotes WHERE start_date BETWEEN DATE_SUB(NOW(), INTERVAL 15 DAY) AND NOW() AND provider_id = $provider_id  ".$start." ORDER BY req_qut_id DESC LIMIT 10");
            //print_r($rows);exit;
            if($rows['status']=='success')
            {
                foreach ($rows['data'] as $key) {
                    $arr[] = array(
                            'request_id'=>$key['req_qut_id'],
                            'name'=>$key['name'],
                            'email'=>$key['email'],
                            'mobile'=>$key['mobile'],
                            'provider_id'=>$key['provider_id'],
                            'description'=>$key['description'],
                            'budget'=>$key['budget'],
                            'request_dt'=>$key['start_date'],                             
                            'create_at'=>$key['create_at']                             
                           );
                        }
                $rows['status'] = "success";
                $rows['message'] = "Data selected successfully";
                $rows['data'] = $arr;
                echoResponse(200,$rows);
            }else
            {
                $rows['status'] = "failed" ;
                $rows['message'] = "No request found";
                unset($rows['data']);
                echoResponse(200,$rows);
            }  
        }else
        {
          $json1['message'] ="Invalid Request parameter";
          echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Booking_Provider_service1',function() use ($app){
  $headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {
        if($check['data'][0]['admin_status'] == 0) 
        {
           $json1 = file_get_contents('php://input');
           if(!empty($json1))
           {
             $data =json_decode($json1);
              //$orderby = "ORDER BY provider_id DESC";
              global $db;
              $payment_id = $data->payment_id;         
              $provider_id = $data->provider_id;
              $subcat_id = $data->subcategory_id;
              $service_id = $data->service_id;
              $coupon_code = $data->coupon_code;
              $tooreest_charge = $data->tooreest_charge;
              $service_charge = $data->service_charge;
              $total_amount = $data->total_amount;
              $booking_date = $data->booking_date;
              $booking_date_error = $data->booking_date_error;
              $device_error_type = $data->device_error_type;
              $name = $check['data'][0]['full_name']; 
              $user_id = $check['data'][0]['user_id'];
              $booking_code = randomuniqueCode();
              $curnt_date = date('Y-m-d 00:00:00'); 
              //print_r($booking_code);exit;
              
              $providselct = $db->customQueryselect("SELECT provider_services.category_id,subcategory.sub_name FROM provider_services INNER JOIN subcategory WHERE subcategory.sub_id='$subcat_id' AND provider_services.provider_id = '$provider_id'AND provider_services.sub_category_id = $subcat_id");

                if($providselct['status']=="success")
                {
                    if($device_error_type == 'android')
                    {
                       $booking_date = date('Y-m-d 12:00:00',strtotime($booking_date_error));
                    }
                
                     $user_data = array(  
                                        'provider_id' =>$provider_id,
                                        'subcat_id' =>$subcat_id,
                                        'user_id' => $user_id,
                                        'service_id' =>$service_id,
                                        'service_amt' =>$service_charge, 
                                        'booking_status' =>'booked',
                                        'booking_date_time'=>$booking_date,
                                        'booking_status_datetime'=>$booking_date,
                                        'total_amount' =>$total_amount,
                                        'payment_id' =>$payment_id,
                                        'tooreest_charge' =>$tooreest_charge,
                                        'booking_code' =>$booking_code,
                                        'coupon_code' =>$coupon_code,
                                        'create_at' =>militime
                                        );
                     
                       if($coupon_code !='')
                       {

                          $query_login = $db->select("coupon_code","*",array('subcategory_id'=>$subcat_id,'coupon'=>$coupon_code));
                          if($query_login['status'] == 'success')
                          { 
                            $query_login4 = $db->select("checking_coupon_code","*",array('coupon_code'=>$coupon_code,'user_id'=>$user_id));
                            if($query_login4['status'] == 'success')
                            {
                                $query_login4["status"] = "failed"; 
                                $query_login4['message'] = "You alredy use this code";
                                unset($query_login4['data']);
                                echoResponse(200,$query_login4);
                            }
                            else
                            {
                                if($query_login['data'][0]['start_date'] > $curnt_date)
                              {  
                                $query_login['status'] ="failed";
                                $query_login['message'] ="Coupon code not activate";
                                unset($query_login['data']);
                              }elseif($query_login['data'][0]['end_date'] < $curnt_date)
                              {
                                $query_login['status'] ="failed";
                                $query_login['message'] ="Coupon code has been expired";
                                unset($query_login['data']);
                              } 
                              else
                              {   
                                   $new_data = array(
                                                    'user_id'=>$user_id,
                                                    'coupon_code'=>$coupon_code,
                                                    'create_at'=>militime
                                                    );
                                   $chehck_code = $db->insert("checking_coupon_code",$new_data,array());
                              }
                               echoResponse(200,$query_login);
                            }
                          }
                          else
                          {
                            $msg["status"] = "failed"; 
                            $msg['message'] = "Invalid coupon code. Please check if you're apply right coupon code on right category and it is not expired.";
                            echoResponse(200,$msg);      
                          }
                        }

                      $rows1 = $db->insert("customer_booking",$user_data,array());
                      if($rows1['status']=="success")
                      {
                        if($total_amount =='')
                        {
                          $free_count = $db->select("users","count",array('user_id'=>$user_id));
                          
                          $c= $free_count['data'][0]['count']-1;

                          $update12 = $db->update("users",array('count'=>$c),array('user_id'=>$user_id),array());
                        }
                        

                         $select = $db->select("users","device_token,full_name,address,device_type",array('user_id'=>$provider_id));
                         $msg = " ".$name." booked ".$providselct['data'][0]['sub_name']." service ";
   
                         $message = array("message" =>$msg,"image" =>'',"title" =>'New Booking',"type"=>'Booking',"timestamp" =>militime);

                         $notification_array = array('sender_id'=>$user_id,
                                                     'reciver_id'=>$provider_id,
                                                     'message'=>$msg,
                                                     'type'=>'Booking',
                                                     'title' =>'New Booking',
                                                     'create_at'=>militime
                                                     );
                         $notification_id = $db->insert("notification_tb",$notification_array, array());

                         //AndroidNotification($select['data'][0]['device_token'],$message);

                          if($select['data'][0]['device_type']=='Android')
                         {
                            AndroidNotification($select['data'][0]['device_token'],$message);
                         }else
                         {
                            iOSPushNotification($select['data'][0]['device_token'],$msg,'New Booking','Booking','0',2);
                         }

                         $rows1['status'] ="success";
                         $rows1['message'] ="Successfully Booked";
                         echoResponse(200,$rows1);

                       }else
                       {
                           $rows1['status'] ="success";
                           $rows1['message'] ="something went wrong! Please try again later.";
                           unset($rows1['data']);
                           echoResponse(200,$rows1);                              
                       }
                }
                else
                {
                  $providselct['status'] ="failed";
                  $providselct['message'] ="Invalid Service";
                  unset($providselct['data']);
                  echoResponse(200,$providselct);    
                }
            }else
            {
               $json1['status'] ="failed";
               $json1['message'] ="Invalid Request parameter";
               echoResponse(200,$json1);
            }
        }
        else
        {
           $check['status'] = "failed";
           $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
           unset($check['data']);
           echoResponse(200,$check);
        }    
    }else
    {
      $check['status'] = "false";
      $check['message'] = "Invalid Token";
      unset($check['data']);
      echoResponse(200,$check);
    } 
  }else
  {
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
  } 
});

$app->post('/booking_list',function() use ($app){
  $headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {
      $json1 = file_get_contents('php://input');
      if(!empty($json1))
      {
          $data =json_decode($json1);
          $user_type = $data->user_type;
          $order_type = $data->order_type;
          global $db;
          $user_id = $check['data'][0]['user_id']; 
          //$orderby = "ORDER BY order_id DESC LIMIT 10";
          //$cur_date = date('Y-m-d H:i:s');
          $create = '';
          if($data->create_at !=0 && $data->create_at !='')
          {
            $create = "AND create_at < '$data->create_at'";
          }

          if($user_type == 1)
          {
              $userid = "user_id = '$user_id'";
          }else
          {
              $userid = "provider_id = '$user_id'";
          }
          
          if($order_type==1)
          {
              $ordersel = $db->customQueryselect("SELECT * FROM customer_booking WHERE ".$userid." AND booking_status = 'booked'".$create." ORDER BY create_at DESC LIMIT 10");
             
          }elseif($order_type==2)
          {
              $ordersel = $db->customQueryselect("SELECT * FROM customer_booking WHERE ".$userid." AND booking_status = 'accepted'".$create." ORDER BY create_at DESC LIMIT 10");
          }
          elseif($order_type==3)
          {
              $ordersel = $db->customQueryselect("SELECT * FROM customer_booking WHERE ".$userid." AND (booking_status = 'cancelled' OR booking_status = 'completed')".$create." ORDER BY create_at DESC LIMIT 10");
          }
          if($ordersel['status']=='success')
          {
              foreach ($ordersel['data'] as $key){
                   
                  if($user_type==1){
                      $userids = $key['provider_id'];
                  }else{
                      $userids = $key['user_id'];
                  }
                  $offeramt = 0;

                  $providselct = $db->customQueryselect("SELECT user_id,company_name,full_name,mobile_no,email,category,address,postal_code,country,city,image,facebook,google_plus,twitter,linkedin FROM users WHERE user_id = '$userids'");
              
                  $subcate123 = $db->select("provider_services",'*',array('provider_id'=>$key['provider_id'],'service_id'=>$key['service_id']));
                  
                  if($subcate123['data'][0]['image']=='')
                  {
                    $s_image = '';
                  }else
                  {
                    $s_image = base_url.'uploads/subcategory_image/'.$subcate123['data'][0]['image'];
                  }
	

                  $subcate = $db->select("subcategory",'sub_name',array('sub_id'=>$key['subcat_id']));
                  
                  if(!empty($key['offer']))
                  {
                    $offer = json_decode($key['offer'],true);
                    $offeramt = $offer['offer_amt'];
                  }
                  /*$paydetail = json_decode($key['payment_details'],true);
                  $paymenthod = json_decode($key['payment_method'],true);*/
                  if($providselct['data'][0]['image']=='')
                  {
                    $image = '';
                  }else
                  {
                    $image = base_url.'uploads/user_image/'.$providselct['data'][0]['image'];
                  }
                  
                   $review ='';
                   $rating =0;
                   $rating_review = $db->select("rating","*",array('order_id'=>$key['order_id']));
                      
                   if($rating_review['status'] =='success'){

                   $rating = $rating_review['data'][0]['rating'];
                   if($rating_review['data'][0]['rating']==''){
                       $rating =0;
                   } 

                   $review = $rating_review['data'][0]['review'];

                   if($rating_review['data'][0]['review']==''){
                   $review ='';
                   }
                   }
                   
                   
                 /* $idproof = $db->select("provider_id_proof","id_status,bankid_status",array('provider_id'=>$user_id));
                  if($idproof['status']=='status')
                  {
                      $id_status = $idproof['data'][0]['id_status'];
                      $bankid_status = $idproof['data'][0]['bankid_status'];

                  }else
                  {
                      $id_status = 0;
                      $bankid_status = 0;
                  }*/
                  
                  $arr[] = array(
                      'order_id'=>$key['order_id'],
                      'service_id'=>$subcate123['data'][0]['service_id'],
                      'user_id'=>$providselct['data'][0]['user_id'], 
                      'sub_category'=>$subcate['data'][0]['sub_name'],
                      'sub_cat_id'=>$key['subcat_id'],
                      'service_type'=>$subcate123['data'][0]['service_type'],
                      'service_description'=>$subcate123['data'][0]['description'],
                      //'service_charge'=>$subcate123['data'][0]['service_charge'],
                      'full_name'=>$providselct['data'][0]['full_name'],  
                      'company_name'=>$providselct['data'][0]['company_name'],
                      'email'=>$providselct['data'][0]['email'],  
                      'mobile_no'=>$providselct['data'][0]['mobile_no'],
                      'image'=>$image,
                      'service_image'=>$s_image,  
                      'service_amt'=>$key['service_amt'],
                      'total_amount'=>$key['total_amount'],
                      'booking_status'=>$key['booking_status'],
                      'booking_date_time'=>$key['booking_date_time'],
                      'rating'=>$rating,
                      'review'=>$review,
                      'booking_code' =>$key['booking_code'],
                      'coupon_code'=>$key['coupon_code'],
                      'create_at'=>$key['create_at']

                      //'offer_amt'=>$offeramt,
                      //'address'=>$providselct['data'][0]['address'],  
                      //'postal_code'=>$providselct['data'][0]['postal_code'],  
                      //'language'=>$providselct['data'][0]['language'],  
                      //'country'=>$providselct['data'][0]['country'],  
                      //'city'=>$providselct['data'][0]['city'],  
                      //'is_id_verify'=>$id_status,
                      //'is_bank_verify'=>$bankid_status,
                      //'about_me'=>$providselct['data'][0]['about_me'],
                      //'tax'=>$key['tax'],
                      //'advance_paid'=>$key['advance_paid'],
                      //'remain_amt'=>$paydetail['remain_amt'],
                       );
              }

              if(!empty($arr))
              {
                  $ordersel['status'] = "success";
                  $ordersel['message'] = "Successfully";
                  $ordersel['data']=$arr;
                  echoResponse(200,$ordersel);
              }else
              {
                  $ordersel['status'] = "false";
                  $ordersel['message'] = "Order list not found";
                  unset($ordersel['data']);
                  echoResponse(200,$ordersel);
              }

          }else
          {
              $ordersel['status'] = "false";
              $ordersel['message'] = "Order list not found";
              unset($ordersel['data']);
              echoResponse(200,$ordersel);
          }
      }else
      {
         $json1['message'] ="Invalid Request parameter";
         echoResponse(200,$json1);
      }
    }else
    {
      $check['status'] = "false";
      $check['message'] = "Invalid Token";
      unset($check['data']);
      echoResponse(200,$check);
    } 
  }else
  {
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
  } 
});


$app->post('/accept_and_reject',function() use ($app){
  $headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {
      $json1 = file_get_contents('php://input');
      if(!empty($json1))
      {
          $data =json_decode($json1);
          $ac_type = $data->ac_type;
          $order_id = $data->order_id;
          $reason = $data->reason;
          $provider_id = $check['data'][0]['user_id']; 
          $cur_date = date('Y-m-d H:i:s');
          global $db;
           
          $first_c = $db->select("customer_booking","*",array('order_id'=>$order_id));
          if($first_c['status']=="success")
          { 
              $plus ="-1";
              if($ac_type == 2){
                $rows1 = $db->update('customer_booking',array('booking_status'=>'accepted','update_at'=>militime,'booking_status_datetime'=>$cur_date),array('order_id'=>$order_id),array());
                $abc = 0;
                
              }
              else
              {
                /*echo "hihih";
                exit;*/
                if($reason =='') {
                    $rows1 = $db->update('customer_booking',array('booking_status'=>'cancelled','update_at'=>militime,'booking_status_datetime'=>$cur_date),array('order_id'=>$order_id),array());
                    $abc = 0;
                    if($first_c['data'][0]['total_amount'] == 0){
                       $new_u = $first_c['data'][0]['user_id'];
                        $n_conut = $db->select("users","count",array('user_id'=>$new_u));
                        $plus =$n_conut['data'][0]['count']+1;
                        $rows123 = $db->update('users',array('count'=>$plus,'update_at'=>militime),array('user_id'=>$new_u),array());
                    }
                }
                else
                {
                  /*echo "hihih12313131";
                  exit();*/
                    $rows1 = $db->update('customer_booking',array('booking_status'=>'cancelled','update_at'=>militime,'booking_status_datetime'=>$cur_date),array('order_id'=>$order_id),array());
                    $abc = 2;
                    if($first_c['data'][0]['total_amount'] == 0){
                       $new_u = $first_c['data'][0]['user_id'];
                        $n_conut = $db->select("users","count",array('user_id'=>$new_u));
                        $plus =$n_conut['data'][0]['count']+1;
                        $rows123 = $db->update('users',array('count'=>$plus,'update_at'=>militime),array('user_id'=>$new_u),array());
                    }
                }
                
              }      
                  if($rows1['status']=="success")
                  {  
                    if($abc == 2){
                      /*echo "hihih";
                      exit;*/
                       $providselct = $db->select("customer_booking","*",array('order_id'=>$order_id));
                       $u_id = $providselct['data'][0]['user_id'];
                       $sub = $providselct['data'][0]['subcat_id'];

                       $date1 =$providselct['data'][0]['booking_date_time'];
                       $date2 = date('Y-m-d');
                       $timenow = date("Y-m-d",strtotime($date1));
                       $date1=date_create($date1);
                       $date2=date_create($date2);
                       $diff=date_diff($date1,$date2);
                       $get_diff = $diff->days;
                       //print_r($get_diff);
                       //exit;
                       if(8 <= $get_diff ){
                         //echo "hihih1";
                         $percentage= 15;
                         $tt = $providselct['data'][0]['tooreest_charge'] + $providselct['data'][0]['service_amt'];
                         //print_r($tt);
                         $new_width = ($percentage / 100) * $tt;
                       }elseif(8 >$get_diff && 5 <= $get_diff){
                         $percentage= 30;
                         $tt = $providselct['data'][0]['tooreest_charge'] + $providselct['data'][0]['service_amt'];
                         //print_r($tt);
                         $new_width = ($percentage / 100) * $tt;
                       }elseif(5 >$get_diff && 3 <= $get_diff){
                         $percentage= 50;
                         $tt = $providselct['data'][0]['tooreest_charge'] + $providselct['data'][0]['service_amt'];
                         //print_r($tt);
                         $new_width = ($percentage / 100) * $tt;
                       }elseif(3 > $get_diff){
                         $new_width = $providselct['data'][0]['tooreest_charge'] + $providselct['data'][0]['service_amt'];
                       } 
                       
                       $new_data1 =array(
                                        'provider_id'=>$provider_id,
                                        'order_id'=>$order_id,
                                        'amount'=>$new_width,
                                        'status'=>0,
                                        'type'=>'deduct',
                                        'create_at'=>$cur_date
                                        ); 
                       $new_details = $db->insert('provider_payment_detail',$new_data1,array());
                       //exit;
                       $get_sub = $db->select("subcategory","sub_name",array('sub_id'=>$sub));
                       $sub_name1 =$get_sub['data'][0]['sub_name'];

                       $providselct1 = $db->select("users","device_token,device_type",array('user_id'=>$u_id));
                       $tok =$providselct1['data'][0]['device_token'];

                       $providselct12 = $db->select("users","full_name",array('user_id'=>$provider_id));
                       $name =$providselct12['data'][0]['full_name'];

                       $text = " ".$name." has cancelled your booking because ".$reason." . You may continue to book for other guide. Thank you.";
                       $m ="You have cancelled  this Booking";
                       $title =array('title' =>'Booking Cancelled');
                       $mg ='Booking Cancelled';
                    }
                    else
                    {
                       $providselct = $db->select("customer_booking","*",array('order_id'=>$order_id));
                       $u_id = $providselct['data'][0]['user_id'];
                       $sub = $providselct['data'][0]['subcat_id'];

                       $get_sub = $db->select("subcategory","sub_name",array('sub_id'=>$sub));
                       $sub_name1 =$get_sub['data'][0]['sub_name'];

                       $providselct1 = $db->select("users","device_token,device_type",array('user_id'=>$u_id));
                       $tok =$providselct1['data'][0]['device_token'];

                       $providselct12 = $db->select("users","full_name",array('user_id'=>$provider_id));
                       $name =$providselct12['data'][0]['full_name'];

                       if($ac_type == 2){            
                          $name_type = "accepted";
                          $m ="Successfully Booked";
                          $title =array('title' =>'Booking Accepted');
                          $mg ='Booking Accepted';
                       }
                       else
                       {
                          $name_type = "cancelled";
                          $m ="You have cancelled  this Booking";
                          $title =array('title' =>'Booking Cancelled');
                          $mg ='Booking Cancelled';
                       }

                       $text = " ".$name." has ".$name_type." your booking for ".$sub_name1;
                     }

                     $msg = $text;

                     $message = array("message" =>$msg,"image" =>'',$title,"type"=>'Booking', "refer_count"=>$plus, "timestamp" =>militime);
                     /*print_r($message);
                     exit;*/

                     $notification_array = array('sender_id'=>$provider_id,
                                                 'reciver_id'=>$u_id,
                                                 'message'=>$msg,
                                                 'type'=>'Booking',
                                                 'title' =>$mg,
                                                 'Create_at'=>militime
                                                 );
                     $notification_id = $db->insert("notification_tb",$notification_array, array());
                     //AndroidNotification($tok,$message);
                     
                     if($providselct1['data'][0]['device_type']=='Android')
                      {
                          AndroidNotification($tok,$message);
                      }else
                      {
                          iOSPushNotification($tok,$msg,$title,'Booking',$plus,1);
                      }



                     $rows1['status'] ="success";
                     $rows1['message'] =$m;
                     echoResponse(200,$rows1);      
                    }else{
                      $rows1['status'] = "false";
                      $rows1['message'] = "something went wrong";
                      unset($rows1['data']);
                      echoResponse(200,$rows1);
                    }
              }else
              {
                $first_c['status'] = "false";
                $first_c['message'] = "No order id found";
                unset($first_c['data']);
                echoResponse(200,$first_c);
              }
      }else
      {
         $check['status'] = "false";
         $json1['message'] ="Invalid Request parameter";
      }
    }else
    {
      $check['status'] = "false";
      $check['message'] = "Invalid Token";
      unset($check['data']);
      echoResponse(200,$check);
    } 
  }else
  {
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
  } 
});


$app->post('/Complete_job',function() use ($app){
$headers = apache_request_headers();
  if(!empty($headers['secret_key']))
  {
    $check = token_auth($headers['secret_key']);
    if($check['status']=="true")
    {
      $json1 = file_get_contents('php://input');
      if(!empty($json1))
      {
          global $db;
          $userid = $check['data'][0]['user_id'];
          $name = $check['data'][0]['full_name'];
          $data = json_decode($json1);
          $b_code = $data->booking_code;
          $select = $db->select("customer_booking",'subcat_id,booking_status,user_id,order_id,booking_code,service_amt',array('order_id'=>$data->booking_id,'provider_id'=>$userid));
          if($select['status']=="success")
            {
               if($select['data'][0]['booking_code']==$b_code){

                   $customer_id =$select['data'][0]['user_id'];
                   $order_id =$select['data'][0]['order_id'];

                   $sub = $select['data'][0]['subcat_id'];
                   $get_sub = $db->select("subcategory","sub_name",array('sub_id'=>$sub));
                   $sub_name1 =$get_sub['data'][0]['sub_name'];

                   $update = $db->update("customer_booking",array('booking_status'=>'completed','booking_status_datetime'=>date('Y-m-d H:i:s')),array('order_id'=>$data->booking_id),array());
                    if($update['status']=="success")
                    {
                       $select1 = $db->select("users","device_token,device_type",array('user_id'=>$customer_id));

                             $msg = $sub_name1." has been completed by ".$name." ";
       
                             $message = array("message" =>$msg,"image" =>'',"title" =>'Booking completed',"type"=>'Booking',"refer_count"=>'',"timestamp" =>militime);

                             $notification_array = array('sender_id'=>$userid,
                                                         'reciver_id'=>$customer_id,
                                                         'message'=>$msg,
                                                         'type'=>'Booking',
                                                         'title'=>'Booking completed',
                                                         'Create_at'=>militime
                                                         );
                             $notification_id = $db->insert("notification_tb",$notification_array, array());
                             //AndroidNotification($select1['data'][0]['device_token'],$message);
                            
                              if($select1['data'][0]['device_type']=='Android')
                              {
                                  AndroidNotification($select1['data'][0]['device_token'],$message);
                              }else
                              {
                                  iOSPushNotification($select1['data'][0]['device_token'],$msg,'Booking completed','Booking','0',1);
                              }



                        $insert_history = $db->insert("provider_payment_detail",array('provider_id'=>$userid,'order_id'=>$order_id,'amount'=>$select['data'][0]['service_amt'],'type'=>'add','create_at'=>date('Y-m-d H:i:s')),array());
                        $update['status']='success';
                        $update['message']='Job Successfully Completed';
                        unset($update['data']);
                        echoResponse(200,$update);
                    }
                    else
                    {
                        $update['status']='failed';
                        $update['message']='Something went wrong! please try again later';
                        unset($update['data']);
                    }

                }
                else
                {
                  $select['status']='failed';
                  $select['message']='Please enter correct booking code';
                  unset($select['data']);
                  echoResponse(200,$select);
                }   
                  
              }else
              {
                  $booking_sel['status']='failed';
                  $booking_sel['message']='Booking not found';
                  unset($booking_sel['data']);
                  echoResponse(200,$booking_sel);
              }
      }
      else
      {
         $msg['status'] ="failed";
         $msg['message'] ="No Request parameter";
         echoResponse(200,$msg);
      }
    }
    else
    {
      $check['status'] = "false";
      $check['message'] = "Invalid Token";
      unset($check['data']);
      echoResponse(200,$check);
    } 
  }
  else
  {
    $check['status'] = "false";
    $check['message'] = "Unauthorised access";
    unset($check['data']);
    echoResponse(200,$check);
  }
});


/*$app->post('/Post_Job',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {
        $category_id = $app->request()->params('category_id');
        $subcat_id = $app->request()->params('subcat_id');
        $need_local_provider = $app->request()->params('need_local_provider'); 
        $description = $app->request()->params('description');
        $budget = $app->request()->params('budget');
        $is_urgent = $app->request()->params('is_urgent');
        $country = $app->request()->params('country');
        $city = $app->request()->params('city');
        $user_id = $check['data'][0]['user_id'];
        $name = $check['data'][0]['full_name'];


        global $db;
          
          if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name']))
          {
              $image= $_FILES['image']['tmp_name'];
              $image_name= $_FILES['image']['name'];
              $image_name = militime.$image_name;
              move_uploaded_file($image,"../../uploads/post_job/".$image_name);
              $u_image1 = base_url."uploads/document/".$image_name; 
          }else
          {             
              $image_name = '';
          }

          if(!empty($category_id) && !empty($subcat_id) && !empty($country) && !empty($city) && ($need_local_provider !='') && !empty($description) && !empty($budget) && ($is_urgent!=''))
          {
              
              $data = array(
                   'category_id' => $category_id,
                   'subcat_id' => $subcat_id,
                   'need_local_provider' =>$need_local_provider,
                   'image'=>$image_name,
                   'description' =>$description,
                   'budget' => $budget,
                   'is_urgent' =>$is_urgent,
                   'user_id' =>$user_id,
                   'country' =>$country,
                   'city' =>$city,
                   'create_at'=>militime
                   );
              $rows =$db->insert("post_job",$data,array());
              if($rows['status'] =='success')
              {
                  
                  $subselect = $db->select("subcategory","sub_name",array('sub_id'=>$subcat_id));
                  $sub = $subselect['data'][0]['sub_name'];
                 
                  
                  if($need_local_provider==1)
                  {
                    $select1 = $db->customQueryselect("SELECT device_token,user_id FROM user where city = '$city' AND FIND_IN_SET($subcat_id,comma_id)");

                  }else
                  {
                    $select1 = $db->customQueryselect("SELECT device_token,user_id FROM user WHERE FIND_IN_SET($subcat_id,comma_id)");
                  }
                  if($select1['status']=='success')
                  {
                      foreach ($select1['data'] as $key)                         
                      {            
                               $provider_id_arr[]= $key['user_id'];
                             
                               $msg = "".$name." posted a new job for ".$sub."";
                               $message = array("message" =>$msg,"image" =>'',"title" =>"New job in ".$sub."","type"=>'Job',"timestamp" =>militime);                            
                               AndroidNotification($key['device_token'],$message);
                      }
                      $provider_id =implode(',',$provider_id_arr);
                      $notification_array = array(
                                                 "sender_id"=>$user_id,
                                                 "reciver_id"=>$provider_id,
                                                 "message"=>$msg,
                                                 "type"=>'Job',
                                                 "title"=>"New job in ".$sub."",
                                                 "Create_at"=>militime
                                                 ); 
                      $notification_id = $db->insert("notification_tb",$notification_array, array());
                      
                  }     
                                                                                  
                    $rows['status'] = "success";
                    $rows['message'] = "upload successfully";
                    echoResponse(200,$rows);
              }
              else
              {
                  $rows['status'] = "failed";
                  $rows['message'] = "something went wrong";
                  echoResponse(200,$rows);
              }
          }else
          {
              $check['status'] = "failed";
              $check['message'] = "All parameter required";
              unset($check['data']);
              echoResponse(200,$check);        
          }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}

});*/

$app->post('/Notification_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {     
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
            $data = json_decode($json1);
            $start_date = $data->create_at;
            $user_id=$check['data'][0]['user_id']; 
            global $db; 
            $start ='';
            if($start_date != 0 && $start_date != '')
            {
               $start = "AND create_at < '$start_date'";
            }
      
            $select = $db->customQueryselect("SELECT * FROM notification_tb WHERE reciver_id = $user_id  ".$start." ORDER BY notification_id DESC LIMIT 10");
            if($select['status']=='success')
            {
                foreach ($select['data'] as $key)
                {
                  $arr[] = array(
                                'title'=>$key['title'],
                                'message'=>$key['message'],
                                'type'=>$key['type'],
                                'create_at'=>$key['create_at']
                                );
                }
            $select['status'] = "success";
            $select['message'] = "Successfully";
            $select['data'] = $arr;
            echoResponse(200,$select);
            }
            else
            {
              $select['status']='failed';
              $select['message']='No data Found';
              unset($select['data']);
              echoResponse(200,$select);
            }
        }
        else
        {
            $msg['status'] ="failed";
            $msg['message'] ="No Request parameter";
            echoResponse(200,$msg);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Post_Job_List',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {     
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
          

            $data = json_decode($json1);
            $start_date = $data->create_at;

            $subcat = $check['data'][0]['sub_cat'];
            $city = $check['data'][0]['city'];

            $subcat1= json_decode($subcat);
            global $db; 
            $start ='';

              if($start_date != 0 && $start_date != '')
              {
               $start = "AND create_at < '$start_date'";
              }  

              foreach ($subcat1 as $keys)
              {
                   
                  $cat[] = $keys->category_id;
                  $sub[] = $keys->subcategory_id;
                  
              }
                $category = implode(',',$cat);
                $subcategory = implode(',',$sub);
                
                 $select = $db->customQueryselect("SELECT * FROM `post_job` WHERE category_id IN($category) AND subcat_id IN($subcategory) ".$start." ORDER BY `post_id` DESC LIMIT 10");
               
                  if($select['status']=='success')
                  {
                   
                        foreach ($select['data'] as $key)
                        { 

                          $userid=$key['user_id'];
                          $select1 = $db->select("user","full_name,image",array('user_id'=>$userid));
                          $customer_name = $select1['data'][0]['full_name'];
                          
                            $category_id = $select['data'][0]['category_id'];
                            $subcategory = $select['data'][0]['subcat_id'];
                            $select2 = $db->select("category","category_name",array('category_id'=>$category_id));
                            $select3 = $db->select("subcategory","sub_name",array('sub_id'=>$subcategory));
                            $cat_name = $select2['data'][0]['category_name'];
                            $sub_name = $select3['data'][0]['sub_name'];

                           
                          
                            if($select['data'][0]['image']=='')
                            {
                              $image = '';
                            }else
                            {
                              $image = base_url.'uploads/post_job/'.$select['data'][0]['image'];
                            }

                            if($select1['data'][0]['image']=='')
                            {
                              $cus_image = '';
                            }else
                            {
                              $cus_image = base_url.'uploads/user_image/'.$select1['data'][0]['image'];
                            }

                            if($key['need_local_provider'] == 1){
                                
                            if($key['city'] ==$city){
                               $arr[] = array(
                                      'job_id'=>$key['post_id'],
                                      'city'=>$key['city'],
                                      'country'=>$key['country'],
                                      'image'=>$image, 
                                      'job_category'=>$cat_name,
                                      'subcatgory'=>$sub_name,
                                      'budget'=>$key['budget'],
                                      'is_urgent'=>$key['is_urgent'],
                                      'description'=>$key['description'],
                                      'customer_name'=>$customer_name,
                                      'customer_image'=>$cus_image,
                                      'create_at'=>$key['create_at'],
                                      );    
                                }

                            }else{
                               $arr[] = array(
                                          'job_id'=>$key['post_id'],
                                          'city'=>$key['city'],
                                          'country'=>$key['country'],
                                          'image'=>$image, 
                                          'job_category'=>$cat_name,
                                          'subcatgory'=>$sub_name,
                                          'budget'=>$key['budget'],
                                          'is_urgent'=>$key['is_urgent'],
                                          'description'=>$key['description'],
                                          'customer_name'=>$customer_name,
                                          'customer_image'=>$cus_image,
                                          'create_at'=>$key['create_at'],
                                          );    
                            }
                                                                                          
                        }
                      }   

                      if(!empty($arr))
                      {                                                   
                          $select['status'] = "success";
                          $select['message'] = "Successfully";
                          $select['data']=$arr;
                          echoResponse(200,$select);
                      }else
                      {
                          $select['status'] = "false";
                          $select['message'] = "job list not found";
                          unset($select['data']);
                          echoResponse(200,$select);
                      }
                
        }
        else
        {
            $msg['status'] ="failed";
            $msg['message'] ="No Request parameter";
            echoResponse(200,$msg);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});


$app->post('/Transactions_History',function() use ($app){
$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {
     if($check['data'][0]['admin_status'] == 0) 
     {     
        $json1 = file_get_contents('php://input');
        if(!empty($json1))
        {
            $data = json_decode($json1);
            $start_date = $data->create_at;
            $user_id=$check['data'][0]['user_id']; 
            global $db; 
            $start ='';
            if($start_date != 0 && $start_date != '')
            {
               $start = "AND create_at < '$start_date'";
            }
            $select = $db->customQueryselect("SELECT * FROM transaction_history WHERE user_id = $user_id  ".$start." ORDER BY trans_id DESC LIMIT 10");
            if($select['status']=='success')
            {
                foreach ($select['data'] as $key)
                {
                  $arr[] = array(
                                'trans_id'=>$key['trans_id'],
                                'user_id'=>$key['user_id'],
                                'title'=>$key['action'],
                                'order_id'=>$key['order_id'],
                                'amount'=>$key['value'],
                                'add_or_deduct'=>$key['type'],
                                'create_at'=>$key['create_at']
                                );
                }
            $select['status'] = "success";
            $select['message'] = "Successfully";
            $select['data'] = $arr;
            echoResponse(200,$select);
            }
            else
            {
              $select['status']='failed';
              $select['message']='No data Found';
              unset($select['data']);
              echoResponse(200,$select);
            }
                                        
        }
        else
        {
            $json1['status'] ="failed";
            $json1['message'] ="No Request parameter";
            echoResponse(200,$json1);
        }
     }
     else
     {
        $check['status'] = "failed";
        $check['message'] = "Your Jokaamo account has been temporarily suspended as a security precaution.";
        unset($check['data']);
        echoResponse(200,$check);            
     } 
  }  
  else
  {
  $msg['message'] = "Invalid Token";
  echoResponse(200,$msg);
  }
}
else
{
$msg['message'] = "Unauthorised access";
echoResponse(200,$msg);
}
});

$app->post('/Promo_code',function() use ($app){

$headers = apache_request_headers();
if(!empty($headers['secret_key']))
{
  $check = token_auth($headers['secret_key']);
  if($check['status']=="true")
  {  
      $json1 = file_get_contents('php://input');
      if(!empty($json1))
        {
            $data = json_decode($json1);
            $sub_id = $data->subcategory_id;
            $coupon_code = $data->coupon_code;
            $user_id= $check['data'][0]['user_id'];
            global $db;
            if($sub_id !='' && $coupon_code !='' )
            {
              $curnt_date = date('Y-m-d 00:00:00');
              $query_login = $db->select("coupon_code","*",array('subcategory_id'=>$sub_id,'coupon'=>$coupon_code));
              if($query_login['status'] == 'success')
              { 
                $query_login4 = $db->select("checking_coupon_code","*",array('coupon_code'=>$coupon_code,'user_id'=>$user_id));
                if($query_login4['status'] == 'success')
                {
                    $query_login4["status"] = "failed"; 
                    $query_login4['message'] = "You alredy use this code";
                    unset($query_login4['data']);
                    echoResponse(200,$query_login4);
                }
                else
                {
                    if($query_login['data'][0]['start_date'] > $curnt_date)
                  {  
                    /*echo "hihih";
                exit;*/
                    $query_login['status'] ="failed";
                    $query_login['message'] ="Coupon code not activate";
                    unset($query_login['data']);
                  }elseif($query_login['data'][0]['end_date'] < $curnt_date)
                  {
                    /*echo "hihih";
                exit;*/
                    $query_login['status'] ="failed";
                    $query_login['message'] ="Coupon code has been expired";
                    unset($query_login['data']);
                  } 
                  else
                  {
                      $arr = array(
                            'coupon_code_id'=>$query_login['data'][0]['coupon_code_id'],  
                            'tilte'=>$query_login['data'][0]['title'],
                            'start_date'=>$query_login['data'][0]['start_date'],  
                            'end_date'=>$query_login['data'][0]['end_date'],  
                            'discount'=>$query_login['data'][0]['discount'],  
                            'max_discount'=>$query_login['data'][0]['max_discount'],  
                            );
                                            
                           $query_login['status'] ="success";
                           $query_login['message'] ="Successfully accepted";
                           $query_login['data'] = $arr;
                           
                  }
                   echoResponse(200,$query_login);
                }
              }
              else
              {
                $msg["status"] = "failed"; 
                $msg['message'] = "Invalid coupon code. Please check if you're apply right coupon code on right category and it is not expired.";
                echoResponse(200,$msg);      
              }
            }
            else
            {
                $msg["status"] = "failed"; 
                $msg['message'] = "Invalid parameter";
                echoResponse(200,$msg);
            }
        }
        else
        {
            $msg["status"] = "failed"; 
            $msg['message'] = "No Request parameter";
            echoResponse(200,$msg);
        }  
  }else
  {
    $msg['message'] = "Invalid Token";
    echoResponse(200,$msg);
  }
}else
{
    $msg['message'] = "Unauthorised access";
    echoResponse(200,$msg);
} 

});

/*$app->post('/AppVersion',function() use ($app){
   $app_version = $app->request()->params('app_version');
   $type = $app->request()->params('type');
   $user_type = $app->request()->params('user_type'); //1 user, 2 guide
   global $db;
   if($type=='android')
   {
        if($user_type==1)
        {
            $version = $db->customQuery("SELECT android_version FROM `app_version` WHERE `android_version` >= '$app_version'");
        }else
        {
            $version = $db->customQuery("SELECT android_version_guide FROM `app_version` WHERE `android_version_guide` >= '$app_version'");
        }
   }
   else
   {
       if($user_type==1)
        {
            $version = $db->customQuery("SELECT ios_version FROM `app_version` WHERE `ios_version` BETWEEN 1 AND $app_version");
        }else
        {
            $version = $db->customQuery("SELECT ios_version_guide FROM `app_version` WHERE `ios_version_guide` BETWEEN 1.2 AND $app_version");
        }
   }          
   if($version['status']=="success")
   {
       $version['status'] = "success";
       $version['message'] = "Successfully";
       unset($version['data']);
   }          
   else
   {
       $version['status']='failed';
       $version['message']='Plz Update Old Version';
       unset($version['data']);
   }
   echoResponse(200,$version);
});*/

$app->post('/AppVersion',function() use ($app){
   $app_version = $app->request()->params('app_version');
   $type = $app->request()->params('type');
   $user_type = $app->request()->params('user_type'); //1 user , 2 guide
   global $db;
   if($type=='android')
   {
        if($user_type==1)
        {
            $version = $db->customQuery("SELECT android_version FROM `app_version` WHERE `min_android_version` <= '$app_version'");
        }else
        {
            $version = $db->customQuery("SELECT android_version_guide FROM `app_version` WHERE `min_android_version_guide` <= '$app_version'");
        }
   }
   else
   {
       if($user_type==1)
        {
            $version = $db->customQuery("SELECT ios_version FROM `app_version` WHERE `min_ios_version` <= $app_version");
        }else
        {
            $version = $db->customQuery("SELECT ios_version FROM `app_version` WHERE `min_ios_version_guide` <= $app_version");
        }
   }          
   if($version['status']=="success")
   {
       $version['status'] = "success";
       $version['message'] = "Successfully";
       unset($version['data']);
   }          
   else
   {
       $version['status']='failed';
       $version['message']='Plz Update Old Version';
       unset($version['data']);
   }
   echoResponse(200,$version);
});
function getDatesFromRange($start, $end, $format = 'Y-m-d') {
    $array = array();
    $interval = new DateInterval('P1D');

   $realEnd = new DateTime($end);
    $realEnd->add($interval);

   $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

   foreach($period as $date) {
        $array[] = $date->format($format);
    }

   return $array;
}



function echoResponse($status_code, $response) {
    global $app;
    $app->status($status_code);
    $app->contentType('application/json');
    echo json_encode($response,JSON_NUMERIC_CHECK);
}
$app->run();

?>














