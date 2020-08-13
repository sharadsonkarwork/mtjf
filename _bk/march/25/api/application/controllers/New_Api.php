<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class New_Api extends MY_Controller
{
	function __construct() {
		parent::__construct();
		$militime =round(microtime(true) * 1000);
		$datetime =date('Y-m-d h:i:s');
		define('militime', $militime);
		define('datetime', $datetime);
		date_default_timezone_set('Asia/Calcutta'); 	
		/*if($this->check_authentication() != 'success')
        die;*/
	}
	function testnotification()
	{
		$massage = 'You have a Getting Naughty Match with jaipal singh solanki';
		$massage = 'Jaipal singn solanki has shown Interest in some activity...';
		$msgss = array('title'=>'New Match for Getting Naughty','msg'=>$massage,'image'=>'','type'=>5,'match_type'=>1,'create_at'=>militime);
		//$msgss = array('title'=>'You have new Interest' ,'msg'=>$massage,'image'=>'','type'=>5,'match_type'=>0,'create_at'=>militime);
		$select = $this->db->select('user_device_token')->get_where("mtjf_user",array('user_id'=>4))->row();
		//$message = array('title'=>$title,'msg'=>$notify_msg,'image'=>'','type'=>$type,'match_type'=>$mtype,'create_at'=>militime);
		$this->common_model->ios_notification($select->user_device_token,$msgss);
	}

	function test_msg()
	{
	
		$sms_msg = "Ahoy! Some of your friends want to date you and has sent you an anonymous message on MTJF. To read and reply to the message, sign-up on the MTJF app using your mobile number."."\r\n".""."\r\n"."MTJF| World's First App For Dating Your Friends"."\r\n"."https://play.google.com/store/apps/details?id=co.mtjf";	
		$sms_msg = "Ahoy! Some of your friends want to date you and have secretly liked you on MTJF. To find out who it is, sign-up on the MTJF app using your mobile number."."\r\n".""."\r\n"."MTJF| World's First App For Dating Your Friends"."\r\n"."http://onelink.to/rbzmmn";	
		echo $sms_msg;exit;
		  //$this->common_model->other_sms_send($contact_no,$sms_msg);
		//$this->common_model->sms_send('9754743271','Your OTP for MTJF app is:1234');
	}

	function login()
	{
		$json = file_get_contents('php://input');
	    $json_array = json_decode($json);
	    $final_output = array();
    	if($json_array->user_contact!='')
    	{
    		$contact = $json_array->user_contact;
    		$otp = $this->common_model->random_number();
			//$otp = '123456';
			$otpmsg = 'Your OTP for MTJF app is:'.$otp;
    		$seleuser = $this->common_model->common_getRow('mtjf_user',array('user_contact'=>$contact));
    		if(!empty($seleuser))
    		{
				$update = $this->common_model->updateData('mtjf_user',array('user_otp'=>md5($otp),'user_status'=>1,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$seleuser->user_id));
				$object = array(
					'wallet'=>$seleuser->user_wallet,
					'user_id'=>(string)$seleuser->user_id
					);
				if($json_array->user_country_id == '+91')
				{
					//msg91 for india
					$this->common_model->sms_send($json_array->user_country_id.$contact,$otpmsg);
				}else
				{
					$this->common_model->twilio_sms($json_array->user_country_id.$contact,$otpmsg);
				}
				$final_output['status'] = 'success';
				$final_output['message'] = 'Successfully login';
				$final_output['data'] = $object;
    		}else
    		{
    			$json_array->user_otp = md5($otp);
    			$json_array->user_wallet = 10;
    			$json_array->create_date = date('Y-m-d H:i:s');
    			$insert = $this->common_model->common_insert("mtjf_user",$json_array);
    			if($insert!=false)
    			{
					$object = array(
    					'wallet'=>0,
						'user_id'=>(string)$insert
					);
    				
    				if($json_array->user_country_id == '+91')
					{
						//msg91 for india
						$this->common_model->sms_send($json_array->user_country_id.$contact,$otpmsg);
					}else
					{
						$this->common_model->twilio_sms($json_array->user_country_id.$contact,$otpmsg);
					}

    				$final_output['status'] = 'success';
					$final_output['message'] = 'Successfully login';
					$final_output['data'] = $object;
    			}else
    			{
					$final_output['status'] = 'failed';
					$final_output['message'] = some_error;
    			}
    		}
	    }else
	    {
	    	$final_output['status'] = 'failed';
	    	$final_output['message'] = param_error;
	    }
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//end login + signup(Y)

	function otp_verification()
	{
		$json = file_get_contents('php://input');
	    $json_array = json_decode($json);
	    $final_output = array();
	    if(!empty($json_array->mobile_otp) && !empty($json_array->user_id))
	    {
	    	$checkotp = $this->common_model->common_getRow("mtjf_user",array('user_id'=>$json_array->user_id,'user_otp'=>md5($json_array->mobile_otp)));
	    	if(!empty($checkotp))
	    	{	
    			$token = bin2hex(openssl_random_pseudo_bytes(16));
				$token = $token.militime;
	    		$updateotp = $this->common_model->updateData("mtjf_user",array('user_otp'=>'','user_status'=>1,'user_device_type'=>$json_array->user_device_type,'user_device_id'=>$json_array->user_device_id,'user_device_token'=>$json_array->user_device_token,'user_token'=>$token,'update_date'=>date('Y-m-d H:s:i'),'first_login'=>1),array('user_id'=>$json_array->user_id));
	    		if($updateotp!=false)
	    		{
					if($checkotp->first_login ==0)
					{
						$msgs = "Welcome to MTJF. 10 Coins added to your wallet. Happy dating!";
						$insertcoin = $this->db->insert("coin_history",array('user_id'=>$json_array->user_id,'actions'=>'+','msg'=>$msgs,'coin'=>10,'coin_balance'=>1,'create_date'=>date('Y-m-d H:i:s')));
					
						$this->common_model->common_insert("mtjf_notification",array('sender_id'=>0,'receiver_id'=>$json_array->user_id,'type'=>10,'match_type'=>0,'msg'=>$msgs,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));	
					
						$updatedevicetoken = $this->common_model->updateData('mtjf_notification',array('receiver_id'=>$json_array->user_id),array('receiver_number'=>$checkotp->user_country_id.$checkotp->user_contact,'receiver_id'=>0));
					}

					$updatedevicetoken = $this->common_model->updateData('mtjf_user',array('user_device_token'=>''),array('user_id !='=>$checkotp->user_id,'user_device_id'=>$json_array->user_device_id));
		    		
		    		$updaelikeuserid = $this->db->update('mtjf_user_like_unlike',array('second_user_id'=>$checkotp->user_id,'update_date'=>date('Y-m-d H:i:s')),array('contact_no'=>$checkotp->user_country_id.$checkotp->user_contact,'second_user_id'=>0));
		    			
	    			$image = '';
					if(!empty($checkotp->user_image))
					{
						if (filter_var($checkotp->user_image, FILTER_VALIDATE_URL)) {
						    $image = $checkotp->user_image;
						}else
						{
							$image = base_url().'uploads/user_image/'.$checkotp->user_image;
						}
					}
	    			$object = array(
						'user_id'=>$checkotp->user_id,
						'user_name'=>$checkotp->user_name,
						'user_facebook_id'=>$checkotp->user_facebook_id,
						'user_image'=>$image,
						'user_gender'=>$checkotp->user_gender,
						'user_country_id'=>$checkotp->user_country_id,
						'user_country'=>$checkotp->user_country,
						'user_device_type'=>$json_array->user_device_type,
						'user_device_id'=>$json_array->user_device_id,
						'user_device_token'=>$json_array->user_device_token,
						'wallet'=>$checkotp->user_wallet,
						'user_token'=>$token,
					);
	    			$final_output['status'] = 'success';
					$final_output['message'] = 'OTP has been verified successfully.';
					$final_output['data'] = $object;
				}else
				{
					$final_output['status'] = 'failed';
					$final_output['message'] = some_error;
				}
	    	}else
	    	{
	    		$final_output['status'] = 'failed';
	    		$final_output['message'] = "Otp does not match.";		
	    	}
	    }else
	    {
	    	$final_output['status'] = 'failed';
	    	$final_output['message'] = some_error;
	    }
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	// End otp verification
	
	function update_profile()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$data['user_name'] = $this->input->post('user_name');
			$data['user_gender'] = $this->input->post('user_gender');
			$facebookid = $this->input->post('user_facebook_id');
			$fb_image = $this->input->post('facebook_image');
			$data['login_type'] = 1;
			if($facebookid!='')
			{
				$data['user_facebook_id'] = $facebookid;
				$data['login_type'] = 2;
			}
			$image = $fb_image;
			if(!isset($_FILES["image"]) || $_FILES["image"]=='')
            {
             	if($fb_image=='')
				{
					$getdata = $this->db->select('user_image')->get_where("mtjf_user",array('user_id'=>$aa['data']->user_id))->row();	
					if($getdata->user_image!=''){
						if (filter_var($getdata->user_image, FILTER_VALIDATE_URL)) {
				    		$image = $getdata->user_image;
						}else
						{
							$image = base_url().'uploads/user_image/'.$getdata->user_image;
						}
					}
				}else
				{
					$data['user_image'] = $fb_image;
				}
            }
            else
            {
                $images=$_FILES["image"]["name"];
    			//$subFileName = explode('.',$_FILES['image']['name']);
				// $ExtFileName = end($subFileName);
			    $images = md5(militime.$images).'.png';
                move_uploaded_file($_FILES["image"]["tmp_name"],"uploads/user_image/".$images);
               	$image = base_url().'uploads/user_image/'.$images;
            	$data['user_image'] = $images;
            }
			$data['update_date'] = date('Y-m-d H:i:s');
			$update_data = $this->common_model->updateData("mtjf_user",$data,array('user_id'=>$aa['data']->user_id));
			if($this->db->affected_rows())
			{
				$data['user_image'] = $image;
				$final_output['status'] = 'success'; 
			 	$final_output['message'] = 'Profile successfully updated.';
				$final_output['data'] = $data;
			}else
			{
				$final_output['status'] = 'failed'; 
			 	$final_output['message'] = some_error;
			}
		}else
		{
			$final_output = $aa;
		}
		header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//end update profile (Y)
	
	function view_profile()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{ 
            $user_id = $aa['data']->user_id;
		
	    	$checkotp = $this->db->select('user_name,user_facebook_id,user_image,user_country_id,user_country,user_gender,user_wallet')->get_where("mtjf_user",array('user_id'=>$user_id))->row();
	    	if(!empty($checkotp))
	    	{	
				$image = ''; $contact = array();
				if(!empty($checkotp->user_image))
				{
					if (filter_var($checkotp->user_image, FILTER_VALIDATE_URL)) {
					    $image = $checkotp->user_image;
					}else
					{
						$image = base_url().'uploads/user_image/'.$checkotp->user_image;
					}
				}
				$seluservote = $this->db->query("SELECT like_id,second_user_id,contact_no FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND like_status = 1")->result(); //OLD
				//$seluservote = $this->db->query("SELECT mtjf_user_like_unlike.like_id,mtjf_user_like_unlike.second_user_id,mtjf_user_like_unlike.contact_no FROM mtjf_user_like_unlike INNER JOIN mtjf_user_contact_list ON mtjf_user_like_unlike.contact_no = mtjf_user_contact_list.contact_no WHERE mtjf_user_like_unlike.user_id = '$user_id' AND mtjf_user_contact_list.user_id = '$user_id'")->result();
				if(!empty($seluservote))
				{
					foreach ($seluservote as $key) {
						if($key->second_user_id==0)
						{
							$selectuser = $this->db->query("SELECT user_id FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '".$key->contact_no."' OR user_contact = '".$key->contact_no."'")->row();
				            if($selectuser)
		                    {
		                        $id[] = $selectuser->user_id;
		                    }else
		                    {
		                        $selectuser1 = $this->db->query("SELECT user_id FROM mtjf_user WHERE REPLACE(CONCAT(user_country_id,'',user_contact), '+', '') = '".$key->contact_no."' OR CONCAT('0','',user_contact) = '".$key->contact_no."'")->row();
		                        if($selectuser1)
		                        {
		                            $id[] = $selectuser1->user_id;
		                        }else
		                        {
		           	            	$contact[] = $key->contact_no;
		                        }
		                    }
						}else
						{
							$id[] = $key->second_user_id;
						}
					}
				} 
				if(!empty($id))
				{
					//$lcount = count($id);
					$impid = implode(',',$id);
				}else
				{
					$impid = 0;
					$id = array();
				}
				$matchcount = 0;
				//$selmatch = $this->db->query("SELECT count(user_id) as mcount FROM `mtjf_user_like_unlike` where (second_user_id = '$user_id' OR contact_no = '".$aa['data']->user_country_id.$aa['data']->user_contact."' OR contact_no = '".$aa['data']->user_contact."' OR contact_no = '".'0'.$aa['data']->user_contact."') AND (user_id IN ($impid))")->row();
	            $selmathch = $this->db->query("SELECT count(like_id) as mcount FROM mtjf_user_like_unlike  WHERE ( ( user_id IN ($impid) ) AND ( second_user_id = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%' OR contact_no LIKE '%".'0'.$aa['data']->user_contact."%')  AND (like_status = 1) )")->row();
				if(!empty($selmathch))
				{
					$matchcount = $selmathch->mcount;
				}	
				$likcount = count($id) - $matchcount + count($contact);
				$fan = $this->db->query("SELECT user_id FROM mtjf_user_like_unlike WHERE ( second_user_id = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%')  AND (like_status = 1)")->result();
            	if(!empty($fan))
            	{
					foreach ($fan as $value) {
						$rr[] = $value->user_id;
					}
				}else
            	{
            		$rr = array();
            	}
            	$kk = array_merge(array_diff($id, $rr), array_diff($rr, $id ));
            	$kk = array_diff($kk, $id);
                $fcount = count($kk);
	            $object = array(
					'user_name'=>$checkotp->user_name,
					'user_facebook_id'=>$checkotp->user_facebook_id,
					'user_image'=>$image,
					'user_gender'=>$checkotp->user_gender,
					'user_country_id'=>$checkotp->user_country_id,
					'user_country'=>$checkotp->user_country,
					'user_fans'=>(string)$fcount,
					'user_likes'=>(string)$likcount,
					'user_matches'=>(string)$matchcount,
					'wallet'=>$checkotp->user_wallet
					);
	    			$final_output['status'] = 'success';
					$final_output['message'] = 'Successfully get.';
					$final_output['data'] = $object;
			}else
			{
				$final_output['status'] = 'failed';
				$final_output['message'] = some_error;
			}
	    }else
		{
			$final_output = $aa;
		}  
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	// End view profile

	function contact_list()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{ 
            $user_id = $aa['data']->user_id;
            $myno = $aa['data']->user_contact;
            $json = file_get_contents('php://input');
            if(!empty($json))
            {   
                $data = json_decode($json);
               	$dcount = count($data->friend_data);
               	$date = date('Y-m-d H:i:s');
          	    $userdata = array();
              	$final_output = $contactarr = array();
                //$this->common_model->common_insert("testing_table",array('user_id'=>$user_id,'response'=>$json));
                if($dcount!=0)
                {
                	$delete = $this->common_model->deleteData("mtjf_user_contact_list",array('user_id'=>$user_id));
					
					for ($i=0; $i < $dcount; $i++) { 
	                	//$newdata[] = "('','" .$user_id. "','','" .$data->friend_data[$i]->contact_no."','" .$data->friend_data[$i]->contact_name."','','".$date."','')";
	                	$newdata[] = '("","' .$user_id. '","","'.$data->friend_data[$i]->contact_no.'","' .$data->friend_data[$i]->contact_name.'","","'.$date.'","")';
	                }
	                $contactdata = implode(',',$newdata);
	                $insertdata = $this->db->query("INSERT INTO mtjf_user_contact_list VALUES ".$contactdata."");
	                if(!empty($insertdata) && $insertdata===true)
	                {
	                	$listsel = $this->db->query("SELECT uc.contact_id,uc.contact_no,uc.contact_name,uc.facebook_id as user_facebook_id,mtjf_user.user_name,mtjf_user.user_id,mtjf_user.user_image FROM `mtjf_user_contact_list` as uc LEFT JOIN mtjf_user ON uc.contact_no IN (mtjf_user.user_contact,CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact)) WHERE uc.user_id =  '$user_id' ORDER BY uc.contact_name ASC")->result();
			 			if(!empty($listsel))
		            	{
		            	 	foreach ($listsel as $values)
			                { 
			                    $contactarr[] = $values->contact_no;
			                    $usermobile = $values->contact_no;
			                    if($values->user_id !='' && $values->user_id != NULL)
			                    {
			                        $img = $values->user_image;
			                   		$uid = $values->user_id;
			                    }else
			                    {
			                    	$img = '';
		                        	$uid = 0;
		                        }
			                    $fstatus = $likeid = 0; $hint = '';
			                    $mathch = $this->db->query("SELECT a.like_id,b.like_id as lid FROM mtjf_user_like_unlike as a INNER JOIN mtjf_user_like_unlike as b WHERE ( ( a.user_id = '$user_id' ) AND (a.second_user_id = '$uid' OR a.contact_no LIKE '%$usermobile%') AND (a.like_status = 1)) AND ( ( b.user_id = '$uid' ) AND ( b.second_user_id = '$user_id' OR b.contact_no LIKE '%".$aa['data']->user_country_id."$myno%' OR b.contact_no LIKE '%$myno%' OR b.contact_no LIKE '%".'0'."$myno%') AND (b.like_status = 1) )")->row();
			                    if(!empty($mathch))
			                    {
			                    	if($values->user_name!=NULL)
			                    	{
			                    		$uname = $values->user_name;
			                    	}
			                    	$fstatus = 1; //match
			                    }else
			                    {
			                    	$like = $this->db->query("SELECT like_id,like_hint FROM mtjf_user_like_unlike WHERE (user_id = '$user_id' AND contact_no = '$usermobile' AND like_status = 1)")->row();
			                    	if(!empty($like))
			                    	{
			                    		$fstatus = 2; //like
			                    		$likeid = $like->like_id;
					                	$hint = $like->like_hint;
			                    	}
			                    	$uname = $values->contact_name;

			                    }
			                    
			                    if($img!='' )
								{
									if (filter_var($img, FILTER_VALIDATE_URL)) {
							    		$values->user_image = $img;
									}else
									{
										$values->user_image = base_url().'uploads/user_image/'.$img;
									}
								}else
								{
									$values->user_image = '';
								}

								unset($values->user_id);
								unset($values->user_name);
								$values->full_contact_no = $usermobile;
								$values->contact_name = $uname;
								$values->friend_status = $fstatus; 
								$values->like_hint = $hint; 
								$values->like_id = $likeid;
								$values->remove_contact = 0; 
								$values->userid = (string)$uid;
								$arr[] = $values;
			                }
			         	}
			        	if(!empty($contactarr))
		                {
		                	$implocont = implode(',', $contactarr);
		                	//OLD QUERY**** = $listsel = $this->db->query("SELECT contact_no,contact_name,second_user_id,like_hint,like_id FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND contact_no NOT IN ($implocont)")->result();
							$listsel = $this->db->query("SELECT liketbl.like_hint,liketbl.like_id,liketbl.contact_name,liketbl.second_user_id,liketbl.contact_no,mtjf_user.user_id,mtjf_user.user_name,mtjf_user.user_image  FROM `mtjf_user_like_unlike` as liketbl LEFT JOIN mtjf_user ON  liketbl.contact_no IN (mtjf_user.user_contact,CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact)) WHERE liketbl.user_id = '$user_id' AND liketbl.like_status = 1 AND liketbl.contact_no NOT IN ($implocont)")->result();
		                	if(!empty($listsel))
			            	{
			            	 	foreach ($listsel as $values)
				                { 
			                		if($values->user_id!='' && $values->user_id!=NULL)
			                		{
				                		// $usrname = $this->db->query("SELECT user_id,user_name,user_country_id,user_contact,user_image FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '".$values->contact_no."' OR user_contact = '".$values->contact_no."' ")->row();
				                		// if(!empty($usrname))
				                		// {
			                			$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$values->user_id."') AND (second_user_id = '".$user_id."' OR contact_no = '".$aa['data']->user_country_id.$myno."' OR contact_no = '".$myno."') AND (like_status = 1) ")->row();
			                			//print_r($this->db->last_query());exit;
			                			if(!empty($selmat))
			                			{
			                				$status=1;
			                			}else{
			                				$status=2;
			                			}
			                			if(!empty($values->user_image))
			                			{
			                				if (filter_var($values->user_image, FILTER_VALIDATE_URL)) {
									    		$image = $values->user_image;
											}else
											{
												$image = base_url().'uploads/user_image/'.$values->user_image;
											}
			                			}else
			                			{
			                				$image = '';
			                			}

			                			$arr[] = array(
			                					'contact_name'=>$values->user_name,
			                					'user_facebook_id'=>'',
			                					'user_image'=>$image,
			                					'contact_no'=>$values->contact_no,
			                					'full_contact_no'=>$values->contact_no,
			                					'userid'=>(string)$values->user_id,
			                					'like_id'=>$values->like_id,
			                					'like_hint'=>$values->like_hint,
				                				'remove_contact' => 1,
			                					'friend_status'=>$status
			                					);
			                		}else
			                		{
			                			$arr[] = array(
			                					'contact_name'=>$values->contact_name,
			                					'user_facebook_id'=>'',
			                					'user_image'=>'',
			                					'contact_no'=>$values->contact_no,
			                					'full_contact_no'=>$values->contact_no,
			                					'userid'=>(string)$values->second_user_id,
			                					'like_id'=>$values->like_id,
			                					'like_hint'=>$values->like_hint,
				                				'remove_contact' => 1,
			                					'friend_status'=>2
			                					);
			                		}

						    	}
				            }
			            }
		            	if(!empty($arr))
		                {
		                	$final_output["status"] = "success";
	                		$final_output["message"] = "Contact list successfully added.";	
	                		$final_output["data"] = $arr;	
		                }else
		                {
		                	$final_output["status"] = "failed";
	                		$final_output["message"] = some_error;	
		                }
	                }
	                else
	                {
	                    $final_output["status"] = "failed";
	                    $final_output["message"] = some_error;
	        	    }
            	}else
            	{
            		$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";		
            	}
            }
            else
            {
                $final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            } 
		}else
		{
			$final_output = $aa;
		}  
		header("content-type: application/json");
		echo json_encode($final_output);
	}

	// function Like_unlike_user()
	// {	
	// 	$aa = $this->check_authentication();
	// 	if($aa['status']=='true')
	// 	{
	// 		$json = file_get_contents('php://input');
	// 	    $json_array = json_decode($json); 
	// 	  	$userid = $json_array->userid;
	// 	  	$contact_no = $json_array->contact_no;
	// 	  	$like_hint = $json_array->like_hint;
	// 	  	$like_status = $json_array->status; //1=like, 0=unlike
	// 	  	$user_id = $aa['data']->user_id;
	// 		$final_output = array();
		
	// 		$response = ''; $likes = false; $like_id = $frndid = 0;
	// 		$coinbal = $this->db->select('user_wallet,user_name,user_image')->get_where('mtjf_user',array('user_id'=>$user_id))->row();
	// 		$new_wallet = $coinbal->user_wallet;
	// 		$uname = $username1 = $devicetype = $devicetoken = $image = ''; $matchimage = $matchimage1 = '';
	// 		$name = $this->db->query("SELECT user_id,user_name,user_device_type,user_device_token,user_image FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '$contact_no' OR user_contact = '$contact_no'")->row();
	// 		if(!empty($name))
	// 		{
	// 			if(!empty($name->user_image)){ $matchimage= base_url().'uploads/user_image/'.$name->user_image; } 
	// 			$frndid = $name->user_id;
	// 			$uname = $name->user_name;
	// 			$devicetype = $name->user_device_type;
	// 			$devicetoken = $name->user_device_token;
	// 		}else
	// 		{
	// 			$name = $this->db->query("SELECT contact_name FROM mtjf_user_contact_list WHERE contact_no = '$contact_no' AND user_id = '$user_id'")->row();
	// 			if(!empty($name))
	// 			{
	// 				$uname = $name->contact_name;
	// 			}
	// 		}
	// 		$seluservote = $this->db->query("SELECT like_id,like_status FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND contact_no LIKE '%$contact_no%'")->row();
	// 		if(!empty($seluservote))
	// 		{
	// 			$like_id = $seluservote->like_id;
	// 			if($like_status != 1)
	// 			{
	// 				if($seluservote->like_status == 1)
	// 				{
	// 					$response = 'true';
	// 				}else
	// 	   			{
	// 					$response = 'false';
	// 	   			}
	// 	   		}else{
	// 				if($seluservote->like_status == 1)
	// 				{
	// 					$response = 'false';
	// 				}else
	// 				{
	// 					$response = 'true';
	// 					$stap = 1;
	// 				}
	// 	    	}	
	// 		}else
	// 		{
	// 			if($like_status== 1)
	// 			{
	// 				$response = 'true';
	// 				$stap = 2;
	// 	   		}else{
	// 				$response = 'false';
	// 	        }	
	// 		} 
	// 		if($response=='true')
	// 		{
	// 			if($like_status == 1)
	// 			{ 
	// 				if($like_hint == '')
	// 				{
	// 					$response = 'cointrue';
	// 				}else
	// 				{
	// 					if($coinbal->user_wallet >= 2)
	// 					{
	// 						$response = 'cointrue';
	// 					}else
	// 					{
	// 						$response = 'coinfalse';
	// 					}
	// 				}
	// 				if($response=='cointrue')
	// 				{
	// 					if($stap == 1)
	// 					{
	// 						$likes = $this->common_model->updateData("mtjf_user_like_unlike",array('like_hint'=>$like_hint,'like_status'=>1,'update_date'=>date('Y-m-d H:i:s')),array('like_id'=>$like_id));
	// 					}else
	// 					{
	// 						$likes = $this->common_model->common_insert("mtjf_user_like_unlike",array('user_id'=>$user_id,'second_user_id'=>$userid,'contact_no'=>$contact_no,'contact_name'=>$uname,'like_hint'=>$like_hint,'like_status'=>1,'create_date'=>date('Y-m-d H:i:s')));
	// 						$like_id = $this->db->insert_id();
	// 					}
	// 					if($likes!=false)
	// 					{
	// 						if($like_hint!='')
	// 						{
	// 							$msg = 'Liked '.$uname;
	// 							$new_wallet = $new_wallet-2;
	// 							$updatecoin = $this->db->query("UPDATE mtjf_user SET user_wallet = '$new_wallet' WHERE user_id = '$user_id'");
	// 							$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$msg,'coin'=>2,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
	// 						}
	// 						if($frndid != 0){ $uid = $frndid; }else{ $uid = 0; }
	// 						$matchcheck = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '$uid') AND ('second_user_id' = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%') AND (like_status = 1)")->row();
	// 						if(!empty($matchcheck))
	// 						{
	// 							$sms_msg = 'You have new match!';
	// 							$status = 1;
	// 							$intdata = $this->db->select('id')->get_where("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$uid))->row();
	// 							if(empty($intdata))
	// 							{
	// 								$intinsert = $this->db->insert("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$uid,'create_date'=>date('Y-m-d H:i:s')));
	// 							}
								
	// 							$notify_msg = 'You have a Match with '.$coinbal->user_name.'!'; $title='You have a Match.'; $type=2; 
	// 							$image = '';
	// 							if(!empty($coinbal->user_image)){ 
	// 								if (filter_var($coinbal->user_image, FILTER_VALIDATE_URL)) {
	// 								    $image = $coinbal->user_image;
	// 								}else
	// 								{
	// 									$image= base_url().'uploads/user_image/'.$coinbal->user_image; 
	// 								}
	// 							}
	// 							$username1 = $uname; $matchimage1 = $matchimage;
	// 							$mtype = 1;
	// 						}else
	// 						{
	// 							$sms_msg = 'Eg. Ahoy! Some of your friend has secretly liked you on MTJF. You have 10 total fans. To find out who your fans are, login on MTJF using your Mobile Number '.$contact_no.'<br>'. 'MTJF | An App for Dating Your Friends!';
	// 							$status = 2;

	// 							$title='You have a new Fan!'; 
	// 							if($like_hint!=''){ $notify_msg = 'Your new Fan has sent you a Hint! Click to read or reply to the Hint.'; 
	// 								$string1 = str_shuffle('a1b2c3d4e5f6g7h8i9j0k11l12m13n14o15p16q17r18s19t20u21v22w23x24y25zjfjlljlfl4546f4s6fs4f6s4f');
	// 	                            $random = substr($string1,0,15);
	// 	                            //$auth_key1 = bin2hex(openssl_random_pseudo_bytes(16));
	// 	                            $msgid = $random.'-'.$like_id.'-'.$user_id.'-'.militime;
	// 								$this->db->insert("mtjf_user_hint_response",array('like_id'=>$like_id,'user_id'=>$user_id,'second_user_id'=>$uid,'message'=>$like_hint,'old_response_id'=>$msgid,'contact_no'=>$contact_no,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s'),'create_at'=>militime));
	// 								$type=9; 
	// 							}else{ 
	// 								$notify_msg = "You have a new Fan!"; $type=3;
	// 							}	
	// 							$mtype = 0;
	// 						}
	// 						//********Start Check send sms condition 24 hr**********\\

	// 						// $checksms = $this->db->query("SELECT msg_count,create_date FROM mtjf_sms_managment WHERE contact_num = '".$contact_no."'")->row();
	// 						// if(!empty($checksms))
	// 						// {
	// 						// 	$hourdiff = round((strtotime(date('Y-m-d H:i:s')) - strtotime($checksms->create_date))/3600, 1);
	// 						// 	if($hourdiff > 24)
	// 						// 	{
	// 						// 		$updatecount = $this->common_model->updateData("mtjf_sms_managment",array('msg_count'=>1,'create_date'=>date('Y-m-d H:i:s')),array('contact_num'=>$contact_no));
	// 						// 		//Send SMS Code
	// 						// 	}else
	// 						// 	{
	// 						// 		if($checksms->msg_count < 3)
	// 						// 		{
	// 						// 			$totalcount = $checksms->msg_count+1;
	// 						// 			$updatecount = $this->common_model->updateData("mtjf_sms_managment",array('msg_count'=>$totalcount),array('contact_num'=>$contact_no));
	// 						// 			//Send SMS Code
	// 						// 		}
	// 						// 	}
	// 						// }else
	// 						// {
	// 						// 	$insertsms = $this->db->insert("mtjf_sms_managment",array('msg_count'=>1,'contact_num'=>$contact_no,'create_date'=>date('Y-m-d H:i:s')));
	// 						// }
	// 						//********End Check send sms condition 24 hr**********\\
	// 						if(!empty($devicetoken))
	// 						{
	// 							if($devicetype=='android')
	// 							{
	// 								$message = array('title'=>$title,'msg'=>$notify_msg,'image'=>$image,'type'=>$type,'match_type'=>$mtype,'create_at'=>militime);
	// 								$this->common_model->sendPushNotification($devicetoken,$message);	
	// 							}else
	// 							{
	// 								$message = array('title'=>$title,'msg'=>$notify_msg,'image'=>'','type'=>$type,'match_type'=>$mtype,'create_at'=>militime);
	// 								$this->common_model->ios_notification($devicetoken,$message);
	// 							}	
	// 						}
	// 						$this->common_model->common_insert("mtjf_notification",array('sender_id'=>$user_id,'receiver_id'=>$frndid,'type'=>$type,'match_type'=>$mtype,'msg'=>$notify_msg,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
	// 						$response = 'success';
	// 						$msg = 'Successfully Liked'; 
	// 					}else
	// 					{ 
	// 						$response = 'failed';
	// 					}
	// 				}
	// 			}else
	// 			{ 
	// 				if($coinbal->user_wallet >= 1)
	// 				{
	// 					$unlike = $this->common_model->updateData("mtjf_user_like_unlike",array('like_status'=>0),array('like_id'=>$like_id));
	// 					if($unlike==TRUE)
	// 					{
	// 						$msg = 'Undo liking '.$uname;
	// 						$new_wallet = $new_wallet-1;
	// 						$updatecoin = $this->db->query("UPDATE mtjf_user SET user_wallet = '$new_wallet' WHERE user_id = '$user_id'");
	// 						$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$msg,'coin'=>1,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
	// 						$response = 'success';
	// 		    			$status = 0;
	// 						$msg = 'Successfully Unliked'; 
	// 					}else
	// 		   	 		{
	// 						$response = 'failed';
	// 			  		}
	// 				}else
	// 				{
	// 					$response = 'coinfalse';
	// 				}	
	// 			}
	// 		}
	// 		if($response=='success')
	// 		{
	// 			$final_output['status'] = 'success';
 //   		  		$final_output['message'] = $msg;
 //   	  			$final_output['data'] = array('friend_status'=>$status,'wallet'=>$new_wallet,'like_id'=>$like_id,'user_image'=>$matchimage1,'user_name'=>$username1);
	// 		}
	// 		elseif($response=='coinfalse')
	// 		{
	// 			$final_output['status'] = 'failed';
	//   			$final_output['message'] = "Insufficient Coins";
	// 		}
	// 		elseif($response=='false')
	// 		{
	// 			if($like_status == 1){ $msg = 'Already Liked'; }else{ $msg = 'Already Unliked'; }
	// 			$final_output['status'] = 'failed';
 //   	  			$final_output['message'] = $msg;
 //   	  			//$final_output['data'] = array('friend_status'=>$status,'wallet'=>$new_wallet);
	// 		}
	// 		else
	// 		{
	//  			$final_output['status'] = 'failed';
	//   			$final_output['message'] = some_error;
	// 		}
	// 	}else
	// 	{
	// 		$final_output = $aa;
	// 	}
	// 	header("content-type: application/json");
	//     echo json_encode($final_output);
	// }

	function Like_unlike_user()
	{	
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$json = file_get_contents('php://input');
		    $json_array = json_decode($json); 
		  	$userid = $json_array->userid;
		  	$contact_no = $json_array->contact_no;
		  	$like_hint = $json_array->like_hint;
		  	$like_status = $json_array->status; //1=like, 0=unlike
		  	$user_id = $aa['data']->user_id;
			$final_output = array();
		
			$response = ''; $likes = false; $like_id = $frndid = 0;
			$coinbal = $this->db->select('user_wallet,user_name,user_image')->get_where('mtjf_user',array('user_id'=>$user_id))->row();
			$new_wallet = $coinbal->user_wallet;
			$uname = $username1 = $devicetype = $devicetoken = $image = ''; $matchimage = $matchimage1 = '';
			$name = $this->db->query("SELECT user_id,user_name,user_device_type,user_device_token,user_image FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '$contact_no' OR user_contact = '$contact_no'")->row();
			if(!empty($name))
			{
				if(!empty($name->user_image)){ $matchimage= $name->user_image; } 
				$frndid = $name->user_id;
				$uname = $name->user_name;
				$devicetype = $name->user_device_type;
				$devicetoken = $name->user_device_token;
			}else
			{
				$name = $this->db->query("SELECT contact_name FROM mtjf_user_contact_list WHERE contact_no = '$contact_no' AND user_id = '$user_id'")->row();
				if(!empty($name))
				{
					$uname = $name->contact_name;
				}
			}
			$seluservote = $this->db->query("SELECT like_id,like_status FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND contact_no LIKE '%$contact_no%'")->row();
			if(!empty($seluservote))
			{
				$like_id = $seluservote->like_id;
				if($like_status != 1)
				{
					if($seluservote->like_status == 1)
					{
						$response = 'true';
					}else
		   			{
						$response = 'false';
		   			}
		   		}else{
					if($seluservote->like_status == 1)
					{
						$response = 'false';
					}else
					{
						$response = 'true';
						$stap = 1;
					}
		    	}	
			}else
			{
				if($like_status== 1)
				{
					$response = 'true';
					$stap = 2;
		   		}else{
					$response = 'false';
		        }	
			} 
			if($response=='true')
			{
				if($like_status == 1)
				{ 
					if($like_hint == '')
					{
						$response = 'cointrue';
					}else
					{
						if($coinbal->user_wallet >= 2)
						{
							$response = 'cointrue';
						}else
						{
							$response = 'coinfalse';
						}
					}
					if($response=='cointrue')
					{
						if($stap == 1)
						{
							$likes = $this->common_model->updateData("mtjf_user_like_unlike",array('like_hint'=>$like_hint,'like_status'=>1,'update_date'=>date('Y-m-d H:i:s')),array('like_id'=>$like_id));
						}else
						{
							$likes = $this->common_model->common_insert("mtjf_user_like_unlike",array('user_id'=>$user_id,'second_user_id'=>$userid,'contact_no'=>$contact_no,'contact_name'=>$uname,'like_hint'=>$like_hint,'like_status'=>1,'create_date'=>date('Y-m-d H:i:s')));
							$like_id = $this->db->insert_id();
						}
						if($likes!=false)
						{
							if($like_hint!='')
							{
								$msg = 'Liked '.$uname.' with hint';
								$new_wallet = $new_wallet-2;
								$updatecoin = $this->db->query("UPDATE mtjf_user SET user_wallet = '$new_wallet' WHERE user_id = '$user_id'");
								$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$msg,'coin'=>2,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
							}
							if($frndid != 0){ $uid = $frndid; }else{ $uid = 0; }
							
							$matchcheck = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '$uid') AND ('second_user_id' = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%') AND (like_status = 1)")->row();
							if(!empty($matchcheck))
							{
								$sms_msg = 'You have new match!';
								$status = 1;
								$intdata = $this->db->select('id')->get_where("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$uid))->row();
								if(empty($intdata))
								{
									$intinsert = $this->db->insert("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$uid,'create_date'=>date('Y-m-d H:i:s')));
								}
								
								$notify_msg = 'You have a Match with '.$coinbal->user_name.'!'; $title='You have a Match.'; $type=2; 
								$image = '';
								if(!empty($coinbal->user_image)){ 
									if (filter_var($coinbal->user_image, FILTER_VALIDATE_URL)) {
									    $image = $coinbal->user_image;
									}else
									{
										$image= base_url().'uploads/user_image/'.$coinbal->user_image; 
									}
								}
								if(!empty($matchimage)){ 
									if (filter_var($matchimage, FILTER_VALIDATE_URL)) {
									    $matchimage1 = $matchimage;
									}else
									{
										$matchimage1= base_url().'uploads/user_image/'.$matchimage; 
									}
								}
								$username1 = $uname;
								$mtype = 1;
							}else
							{
								$status = 2;

								$title='You have a new Fan!'; 
								if($like_hint!='')
								{
									$sms_msg = "Ahoy! Some of your friends want to date you and has sent you an anonymous message on MTJF. To read and reply to the message, sign-up on the MTJF app using your mobile number."."\r\n".""."\r\n"."MTJF| World's First App For Dating Your Friends"."\r\n"."http://onelink.to/rbzmmn";	
								
								 	$notify_msg = 'Your new Fan has sent you a Hint! Go to Received Hints to read and reply to the Hint.'; 
								
									$string1 = str_shuffle('a1b2c3d4e5f6g7h8i9j0k11l12m13n14o15p16q17r18s19t20u21v22w23x24y25zjfjlljlfl4546f4s6fs4f6s4f');
		                            $random = substr($string1,0,15);
		                            //$auth_key1 = bin2hex(openssl_random_pseudo_bytes(16));
		                            $msgid = $random.'-'.$like_id.'-'.$user_id.'-'.militime;
									$this->db->insert("mtjf_user_hint_response",array('like_id'=>$like_id,'user_id'=>$user_id,'second_user_id'=>$uid,'message'=>$like_hint,'old_response_id'=>$msgid,'contact_no'=>$contact_no,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s'),'create_at'=>militime));
									$type=9; 
								
								}else{ 
									$sms_msg = "Ahoy! Some of your friends want to date you and have secretly liked you on MTJF. To find out who it is, sign-up on the MTJF app using your mobile number."."\r\n".""."\r\n"."MTJF| World's First App For Dating Your Friends"."\r\n"."http://onelink.to/rbzmmn";	
										
									$notify_msg = "You have a new Fan!"; $type=3;
								}	
								$mtype = 0;
							}
							//********Start Check send sms condition 24 hr**********\\
							if($frndid==0)
							{
								if(substr($contact_no,0,3) =='+91') //check country code
								{
									$msgcount = 3;
								}else
								{
									$msgcount = 1;
								}
								$checksms = $this->db->query("SELECT msg_count,create_date FROM mtjf_sms_managment WHERE contact_num = '".$contact_no."'")->row();
								if(!empty($checksms))
								{
									$hourdiff = round((strtotime(date('Y-m-d H:i:s')) - strtotime($checksms->create_date))/3600, 1);
									if($hourdiff > 24)
									{
										$updatecount = $this->common_model->updateData("mtjf_sms_managment",array('msg_count'=>1,'create_date'=>date('Y-m-d H:i:s')),array('contact_num'=>$contact_no));
										//Send SMS Code
										if($msgcount == 3)
										{
											//msg91 for india
											$this->common_model->other_sms_send($contact_no,$sms_msg);
										}else
										{
											$this->common_model->twilio_sms($contact_no,$sms_msg);
										}
									}else
									{
										if($checksms->msg_count < $msgcount)
										{
											$totalcount = $checksms->msg_count+1;
											$updatecount = $this->common_model->updateData("mtjf_sms_managment",array('msg_count'=>$totalcount),array('contact_num'=>$contact_no));
											//Send SMS Code
											if($msgcount == 3)
											{
												//msg91 for india
												$this->common_model->other_sms_send($contact_no,$sms_msg);
											}else
											{
												$this->common_model->twilio_sms($contact_no,$sms_msg);
											}
										}
									}
								}else
								{
									$insertsms = $this->db->insert("mtjf_sms_managment",array('msg_count'=>1,'contact_num'=>$contact_no,'create_date'=>date('Y-m-d H:i:s')));
									//Send SMS Code
									if($msgcount == 3)
									{
										//msg91 for india
										$this->common_model->other_sms_send($contact_no,$sms_msg);
									}else
									{
										$this->common_model->twilio_sms($contact_no,$sms_msg);
									}
								}
							}
							//********End Check send sms condition 24 hr**********\\
							if(!empty($devicetoken))
							{
								if($devicetype=='android')
								{
									$message = array('title'=>$title,'msg'=>$notify_msg,'image'=>$image,'type'=>$type,'match_type'=>$mtype,'create_at'=>militime);
									$this->common_model->sendPushNotification($devicetoken,$message);	
								}else
								{
									$message = array('title'=>$title,'msg'=>$notify_msg,'image'=>'','type'=>$type,'match_type'=>$mtype,'create_at'=>militime);
									$this->common_model->ios_notification($devicetoken,$message);
								}	
							}
							$this->common_model->common_insert("mtjf_notification",array('sender_id'=>$user_id,'receiver_id'=>$frndid,'receiver_number'=>$contact_no,'type'=>$type,'match_type'=>$mtype,'msg'=>$notify_msg,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
							$response = 'success';
							$msg = 'Successfully Liked'; 
						}else
						{ 
							$response = 'failed';
						}
					}
				}else
				{ 
					if($coinbal->user_wallet >= 1)
					{
						$unlike = $this->common_model->updateData("mtjf_user_like_unlike",array('like_status'=>0),array('like_id'=>$like_id));
						if($unlike==TRUE)
						{
							$msg = 'Undo liking '.$uname;
							$new_wallet = $new_wallet-1;
							$updatecoin = $this->db->query("UPDATE mtjf_user SET user_wallet = '$new_wallet' WHERE user_id = '$user_id'");
							$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$msg,'coin'=>1,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
							$response = 'success';
			    			$status = 0;
							$msg = 'Successfully Unliked'; 
						}else
			   	 		{
							$response = 'failed';
				  		}
					}else
					{
						$response = 'coinfalse';
					}	
				}
			}
			if($response=='success')
			{
				$final_output['status'] = 'success';
   		  		$final_output['message'] = $msg;
   	  			$final_output['data'] = array('friend_status'=>$status,'wallet'=>$new_wallet,'like_id'=>$like_id,'user_image'=>$matchimage1,'user_name'=>$username1);
			}
			elseif($response=='coinfalse')
			{
				$final_output['status'] = 'failed';
	  			$final_output['message'] = "Insufficient Coins";
			}
			elseif($response=='false')
			{
				if($like_status == 1){ $msg = 'Already Liked'; }else{ $msg = 'Already Unliked'; }
				$final_output['status'] = 'failed';
   	  			$final_output['message'] = $msg;
   	  			//$final_output['data'] = array('friend_status'=>$status,'wallet'=>$new_wallet);
			}
			else
			{
	 			$final_output['status'] = 'failed';
	  			$final_output['message'] = some_error;
			}
		}else
		{
			$final_output = $aa;
		}
		header("content-type: application/json");
	    echo json_encode($final_output);
	}

	function match_like_detail()  
	{	
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$json = file_get_contents('php://input');
		    $json_array = json_decode($json); 
		  	$user_id = $aa['data']->user_id;
			$type = $json_array->type; //1 = match, 2 = like
			$final_output = array();
			$id = $idss = $arr = $seldetail = $contact = array();
			//$seluservote = $this->db->query("SELECT like_id,second_user_id,contact_no FROM mtjf_user_like_unlike WHERE user_id = '$user_id'")->result();
			$seluservote = $this->db->query("SELECT liketbl.second_user_id,liketbl.contact_no,mtjf_user.user_id as reguser  FROM `mtjf_user_like_unlike` as liketbl LEFT JOIN mtjf_user ON  liketbl.contact_no IN (user_contact,CONCAT(user_country_id,user_contact),REPLACE(CONCAT(user_country_id,'',user_contact), '+', '')) WHERE liketbl.user_id = '$user_id' AND liketbl.like_status = 1")->result();
			if(!empty($seluservote))
			{
				foreach ($seluservote as $key) {
					if($key->reguser=='' || $key->reguser==NULL)
					{
					 	$contact[] = $key->contact_no;
		            }else
					{
						$id[] = $key->reguser;
					}
				}
				if(!empty($id))
				{
					$impid = implode(',',$id);
				}else
				{
					$impid = 0;
				}
				$uid =  0; $mat = $seldetailmatch = array(); //match condition
				$selmatch = $this->db->query("SELECT liketbl.user_id,liketbl.like_id,liketbl.like_hint,mtjf_user.user_name,mtjf_user.user_image,mtjf_user.user_country_id,mtjf_user.user_contact FROM `mtjf_user_like_unlike` as liketbl INNER JOIN mtjf_user ON liketbl.user_id = mtjf_user.user_id where (liketbl.second_user_id = '$user_id' OR liketbl.contact_no = '".$aa['data']->user_country_id.$aa['data']->user_contact."' OR liketbl.contact_no = '".$aa['data']->user_contact."') AND (liketbl.like_status = 1) AND (liketbl.user_id IN ($impid))")->result();
				if(!empty($selmatch))
				{
					foreach ($selmatch as $value) {
				
						$seldetailmatch[]  = (object)array('userid'=>$value->user_id,'contact_name'=>$value->user_name,'user_image'=>$value->user_image,'user_country_id'=>$value->user_country_id,'user_contact'=>$value->user_contact,'like_id'=>$value->like_id,'like_hint'=>$value->like_hint);	
			
						$mat[] = $value->user_id; 
					}
				}	
					if($type==1)
					{
						//*********match condition///*******
							if(!empty($mat))
							{
								$intuid = implode(',',$mat);
								$getintresponse = $this->db->query("SELECT contact_user_id,interest_1,interest_2,interest_3,interest_4,interest_5 FROM mtjf_interest_data_store WHERE user_id= '$user_id' AND contact_user_id IN ($intuid)")->result();
								if(!empty($getintresponse))
								{
									$ccount = count($seldetailmatch);
									for ($i=0; $i < $ccount ; $i++) { 
										
										foreach ($getintresponse as $keyvalue) {
										$array = array();	
											if($keyvalue->contact_user_id==$seldetailmatch[$i]->userid)
											{
												$array = array(
													//'contact_user_id'=>$keyvalue->contact_user_id,
													'interest_1'=>$keyvalue->interest_1,
													'interest_2'=>$keyvalue->interest_2,
													'interest_3'=>$keyvalue->interest_3,
													'interest_4'=>$keyvalue->interest_4,
													'interest_5'=>$keyvalue->interest_5,
												);
												$seldetailmatch[$i]->interest = $array;
											break;
											}else
											{
												$array = array(
													//'contact_user_id'=>$keyvalue->contact_user_id,
													'interest_1'=>2,
													'interest_2'=>2,
													'interest_3'=>2,
													'interest_4'=>2,
													'interest_5'=>2,
												);
												$seldetailmatch[$i]->interest = $array;
											}
										}

									}
								
								}
							}
							$seldetail = $seldetailmatch; 
					}else
					{
						//Like Condition
						$kk = array_merge(array_diff($id, $mat), array_diff($mat, $id));
						$cc = implode(',', $kk);
						if($cc==''){ $cc = 0; }
						$reguserdetail = $this->db->query("SELECT liketbl.user_id,liketbl.like_id,liketbl.like_hint,liketbl.contact_name,liketbl.contact_no FROM `mtjf_user_like_unlike` as liketbl INNER JOIN mtjf_user ON liketbl.contact_no IN (user_contact,CONCAT(user_country_id,user_contact)) where mtjf_user.user_id IN ($cc) AND liketbl.user_id = '$user_id' AND liketbl.like_status = 1")->result(); //register user
						if(!empty($reguserdetail))
						{
							foreach ($reguserdetail as $kevalue) {
								$seldetail[]	 =  (object)array('userid'=>$kevalue->user_id,'contact_name'=>$kevalue->contact_name,'user_image'=>'','user_country_id'=>'','contact_no'=>$kevalue->contact_no,'like_id'=>$kevalue->like_id,'like_hint'=>$kevalue->like_hint);	
							}
						}
						if(!empty($contact))
						{
							$imcont = implode(',', $contact);
						}else
						{
							$imcont = 0;
						}
						
						$seluservote = $this->db->query("SELECT contact_no,contact_name,like_hint,like_id,contact_name FROM mtjf_user_like_unlike WHERE contact_no IN ($imcont) AND user_id = '$user_id' AND like_status = 1")->result();
						if(!empty($seluservote))
						{
							foreach ($seluservote as $keyvalue) {
								$seldetail[] =  (object)array('userid'=>'','contact_name'=>$keyvalue->contact_name,'user_image'=>'','contact_no'=>$keyvalue->contact_no,'like_id'=>$keyvalue->like_id,'like_hint'=>$keyvalue->like_hint);	
							}
						}
					}
				if(!empty($seldetail))
				{
					//$data = (object)$seldetail;
					foreach ($seldetail as $key) {
					
				//print_r($key);exit;
						if(isset($key->user_image) && !empty($key->user_image))
						{
							if (filter_var($key->user_image, FILTER_VALIDATE_URL)) {
				    			$key->user_image = $key->user_image;
							}else
							{
								$key->user_image = base_url().'uploads/user_image/'.$key->user_image;
							}
						}else
						{
							$key->user_image = '';
						}
						if(!isset($key->contact_no))
						{
							$key->contact_no = $key->user_contact;
							//$key->contact_name = $key->user_name;
							$usermobile = $key->user_country_id.$key->user_contact;
							unset($key->user_contact);
							unset($key->user_name);
						}else
						{
							$usermobile = $key->contact_no;
						}
						$checkcontact = $this->db->query("SELECT contact_id FROM mtjf_user_contact_list WHERE user_id = '$user_id' AND contact_no LIKE '%".$key->contact_no."%'")->row();
						if(!empty($checkcontact))
						{
							$remove_status = 1;
						}else
						{
							$remove_status = 0;
						}
						$key->friend_status = (int)$type;
						$key->full_contact_no = $usermobile;
						$key->remove_contact = $remove_status;
						$arr[] = $key;
					}
				}
			}
			if(!empty($arr))
			{
				$final_output['status'] = 'success';
		  		$final_output['message'] = 'Successfully get';
	  			$final_output['data'] = $arr;
			}
			else
			{
				$final_output['status'] = 'failed';
		  		$final_output['message'] = "List not available";
		  	}
		}else
		{
			$final_output = $aa;
		}
		header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//End match like detail

	function Get_like_reomve_contact()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{ 
            	$user_id = $aa['data']->user_id;
                $mycontact = $aa['data']->user_contact;
                $mycoountry = $aa['data']->user_country_id;
                $arr = array(); 
              	$final_output = $contactt = array();
            	$listsel = $this->db->query("SELECT contact_no,contact_name,second_user_id FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND like_status = 1")->result();
		    	if(!empty($listsel))
            	{
            	 	foreach ($listsel as $values)
	                { 
	                	$selectcont = $this->db->query("SELECT contact_no FROM mtjf_user_contact_list WHERE user_id = '$user_id' AND contact_no = '".$values->contact_no."'")->row();
			       		if(!empty($selectcont))
			       		{
			       		}else
			       		{
			       			//$contactt[] = $values->contact_no;
			       			$usrname = $this->db->query("SELECT user_id,user_name,user_country_id,user_contact FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '".$values->contact_no."' OR user_contact = '".$values->contact_no."' ")->row();
	                		if(!empty($usrname))
	                		{
	                			$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$usrname->user_id."') AND (contact_no = '".$mycoountry.$mycontact."' OR contact_no = '$mycontact') AND (like_status = 1)")->row();
	                			if(!empty($selmat))
	                			{
	                				$status=1;
	                			}else{
	                				$status=2;
	                			}
	                			$arr[] = array(
	                					'contact_name'=>$usrname->user_name,
	                					'contact_no'=>$usrname->user_country_id.$username->user_contact,
	                					'userid'=>$usrname->user_id,
	                					'friend_status'=>$status
	                					);
	                		}else
	                		{
	                			$arr[] = array(
	                					'contact_name'=>$values->contact_name,
	                					'contact_no'=>$values->contact_no ,
	                					'userid'=>$values->second_user_id,
	                					'friend_status'=>2
	                					);
	                		}
			       		}
	                }
               	}
        	    if(!empty($arr))
                {
                	$final_output["status"] = "success";
            		$final_output["message"] = "Contact List";	
            		$final_output["data"] = $arr;	
                }else
                {
                	$final_output["status"] = "failed";
            		$final_output["message"] = "No data found";	
                }
     	}else
		{
			$final_output = $aa;
		}  
		header("content-type: application/json");
		echo json_encode($final_output);
	}
	//End Get Contact list

	function Get_fans_list()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{ 
            $user_id = $aa['data']->user_id;
			$user_cont_num = $aa['data']->user_contact;
			$user_country = $aa['data']->user_country_id;  
			$arr= array(); $contactno = '';
				$seluservote = $this->db->query("SELECT like_id,user_id,like_hint FROM mtjf_user_like_unlike WHERE (contact_no LIKE '%".$user_country.$user_cont_num."%' OR contact_no LIKE '%".$user_cont_num."%' OR second_user_id = '$user_id') AND (like_hint != '') AND (like_status = 1)")->result(); //OLD
				if(!empty($seluservote))
				{
					foreach ($seluservote as $value) {
						//$contactlist[] = $key->contact_no;
						//$userids[] = $key->user_id;
						
						$username = $this->db->select('user_country_id,user_contact')->get_where("mtjf_user",array("user_id"=>$value->user_id))->row();
						if(!empty($username))
						{
							$contactno = $username->user_country_id.$username->user_contact;
						}
						$contsele = $this->db->query("SELECT like_id,like_hint FROM mtjf_user_like_unlike WHERE contact_no LIKE '%".$contactno."%' AND user_id = '$user_id' AND like_status = 1")->row();
						if(!empty($contsele))
						{
						}else
						{
							$arr[] = array(
								'like_id'=>$value->like_id,
								'userid'=>$value->user_id,
								'contact_no'=>$contactno,
								'like_hint'=>$value->like_hint
							);
						}
					} 
				} 
		
				if(!empty($arr))
				{
					$final_output['status'] = 'success';
					$final_output['message'] = 'Successfully get fans';
					$final_output['data'] = $arr;
				}else
				{
					$final_output['status'] = 'failed';
					$final_output['message'] = 'Fans list not found';
				}
	    }else
		{
			$final_output = $aa;
		}  
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	// End Fan List
	function Get_all_like_match()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{ 
            $user_id = $aa['data']->user_id;
			$myno = $aa['data']->user_contact;
			$my_country = $aa['data']->user_country_id;  
			$arr= $final_output =array();
	    	$listsel = $this->db->query("SELECT likes.contact_no,likes.contact_name,likes.second_user_id,likes.like_hint,likes.like_id,mtjf_user.user_id,mtjf_user.user_name,mtjf_user.user_country_id,mtjf_user.user_contact,mtjf_user.user_image FROM mtjf_user_like_unlike as likes LEFT JOIN mtjf_user ON likes.contact_no IN (CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact),mtjf_user.user_contact) WHERE likes.user_id = '$user_id' AND likes.like_status = 1")->result();
	    	if(!empty($listsel))
        	{  
			 	foreach ($listsel as $values)
                { 
            			//$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$usrname->user_id."') AND (contact_no = '".$usrname->user_country_id.$usrname->user_contact."' OR contact_no = '".$usrname->user_contact."')")->row();
                	//$status = '';
    				$image='';
    				if($values->user_id != '' && $values->user_id!= NULL)
    				{
    					$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$values->user_id."') AND (second_user_id = '".$user_id."' OR contact_no = '".$my_country.$myno."' OR contact_no = '".$myno."') AND (like_status = 1)")->row();
    					
            			if(!empty($selmat))
            			{
            				$status = 1;
            				$name = $values->user_name;
            			}else{
            				$status = 2;
            				$name = $values->contact_name;
            			}

            			if($status == 1)
            			{
            				if(!empty($values->user_image))
            			    {
	            				if (filter_var($values->user_image, FILTER_VALIDATE_URL)) {
						    		$image = $values->user_image;
								}else
								{
									$image = base_url().'uploads/user_image/'.$values->user_image;
								}
            				}	
            			}	
            		}else
            		{
            			$name = $values->contact_name;
            			$status = 2;
            		}
    				$checkcontact = $this->db->query("SELECT contact_id FROM mtjf_user_contact_list WHERE user_id = '$user_id' AND contact_no LIKE '%".$values->contact_no."%'")->row();
					if(!empty($checkcontact))
					{
						$remove_status = 1;
					}else
					{
						$remove_status = 0;
					}
    					
        			$arr[] = array(
        					'contact_name'=>$name,
        					'user_facebook_id'=> '',
							'user_image'=> $image,
							'user_id'=>'',
        					'full_contact_no'=>$values->contact_no,
        					'contact_no'=>$values->contact_no,
        					'userid'=>(string)$values->user_id,
        					'like_id'=>$values->like_id,
        					'like_hint'=>$values->like_hint,
        					'remove_contact' => $remove_status,
        					'friend_status'=>$status
        					);
      	    	}
		    }
            if(!empty($arr))
            {
            	$final_output["status"] = "success";
        		$final_output["message"] = "Like and Match List";	
        		$final_output["data"] = $arr;	
            }else
            {
            	$final_output["status"] = "failed";
        		$final_output["message"] = "Record not found";
            }
		}else
		{
			$final_output = $aa;
		}  

		header("content-type: application/json");
	    echo json_encode($final_output);
	}

	function crude_contact()
	{ 
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;

			$json = file_get_contents('php://input');
			if(!empty($json))
            {
            	$data = json_decode($json);
            	$status = $data->status; //status = 1 for add,update and status 2 for delete
               	$dcount = count($data->friend_data);
               	$final_output = $contactarr = array();

               	if($dcount != 0)
               	{
               		if($status == 1)
               		{ 
               			for ($i=0; $i < $dcount; $i++)
               			{
               			   $checkexisting = $this->common_model->common_getRow("mtjf_user_contact_list",array('user_id'=>$user_id,'contact_no'=>$data->friend_data[$i]->contact_no));

               			   if(!empty($checkexisting))
               			   {
               			   	  $update = $this->common_model->updateData('mtjf_user_contact_list',array('contact_name'=>$data->friend_data[$i]->contact_name,'update_date'=>datetime),array('contact_no'=>$data->friend_data[$i]->contact_no,'user_id'=>$user_id));
               			   }
               			   else
               			   {
               			   	  $insert = $this->common_model->common_insert('mtjf_user_contact_list',array('user_id'=>$user_id,'contact_no'=>$data->friend_data[$i]->contact_no,'contact_name'=>$data->friend_data[$i]->contact_name,'create_date'=>datetime));
               			   }	

               			   		$contact_arr[] = array(

               			   			  'contact_no'=>$data->friend_data[$i]->contact_no,
               			   			  'contact_name'=>$data->friend_data[$i]->contact_name

               			   			);

               			}

               			if($update == true || !empty($insert))
               			{
               				$final_output["status"] = "success";
               				$final_output["crude_status"] = "1";
                        	$final_output["message"] = "successfully";
                        	$final_output["data"] = $contact_arr;
               			}
               			else
               			{
               				$final_output["status"] = "failed";
                    		$final_output["message"] = "Something went wrong,please try after some time";
               			}	

               		}
               		else if($status == 2)
               		{ 
               			for($i=0; $i < $dcount; $i++)
               			{
               				$delete_arr[] = $data->friend_data[$i]->contact_no;

               				$contact_arr[] = array(

               			   			  'contact_no'=>$data->friend_data[$i]->contact_no,
               			   			  'contact_name'=>$data->friend_data[$i]->contact_name
               			   			);

               			}

               			$contact_no_all = implode(',',$delete_arr);

               			$delete = $this->db->query("DELETE FROM `mtjf_user_contact_list` WHERE `contact_no` IN($contact_no_all) AND `user_id`= $user_id");

               			if($delete)
               			{
               				$final_output["status"] = "success";
               				$final_output["crude_status"] = "2";
                        	$final_output["message"] = "successfully";
                        	$final_output["data"] = $contact_arr;
               			}
               			else
               			{
               				$final_output["status"] = "failed";
                    		$final_output["message"] = "Something went wrong,please try after some time";
               			}	

               		}	
               	}
               	else
               	{
               		$final_output["status"] = "failed";
                    $final_output["message"] = "No required parameter";
               	}	
            }
            else
            {
            	$final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            }	
		}
		else
		{
			$final_output = $aa;
		}	
		header("content-type: application/json");
	    echo json_encode($final_output);
	}

	function reply_on_hint()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;

			$json = file_get_contents('php://input');

			if(!empty($json))
            {
            	$data = json_decode($json);
            	$final_output = array();

				if($data->like_id != '' && $data->message != '' && $data->contact_no != '')
				{	
					$seleclike = $this->common_model->common_getRow("mtjf_user_like_unlike",array('like_id'=>$data->like_id,'like_status'=>1));
					if(!empty($seleclike))
					{
						$arr = array('like_id'=>$data->like_id,
									 'user_id'=>$user_id,
									 'second_user_id'=>$data->second_user_id,
									 'message'=>$data->message,
									 'old_response_id'=>$data->message_id,
									 'contact_no'=>$data->contact_no,
									 'create_date'=>datetime,
									 'update_date'=>datetime,
									 'create_at'=>militime
									);

						$insert = $this->common_model->common_insert('mtjf_user_hint_response',$arr);
						if(!empty($insert))
						{
							$getdevice_token = $this->db->query("SELECT `user_device_token`,`user_device_type`,`user_id` FROM mtjf_user WHERE 
								CONCAT(user_country_id,user_contact) = '".$data->contact_no."'")->row();

							$contact_name1 = $this->db->select('contact_name')->get_where("mtjf_user_contact_list",array("user_id"=>$getdevice_token->user_id,'contact_no'=>$aa['data']->user_country_id.$aa['data']->user_contact))->row();

						  	$msg = array('title'=>'new message','msg'=>$data->message,'message_id'=>$insert,'old_message_id'=>$data->message_id,'image'=>'','type'=>1,'match_type'=>0,'like_id'=>$data->like_id,'user_id'=>$data->second_user_id,'contact_no'=>$aa['data']->user_country_id.$aa['data']->user_contact,'contact_name'=>$contact_name1->contact_name,'create_at'=>militime);
							
							
							if(!empty($getdevice_token->user_device_token) && $getdevice_token->user_device_type == 'android')
							{
							   $this->common_model->sendPushNotification($getdevice_token->user_device_token,$msg);	
							}elseif(!empty($getdevice_token->user_device_token))
							{
							 	$this->common_model->ios_notification($getdevice_token->user_device_token,$msg);
							}

							$arr = (object)array();
							$arr = array('like_id'=>$data->like_id,'message_id'=>$insert,'old_message_id'=>$data->message_id,'create_at'=>militime); 

							$final_output["status"] = "success";
                        	$final_output["message"] = "successfully";
                        	$final_output["data"] = $arr;
						}
						else
						{
               				$final_output["status"] = "failed";
                    		$final_output["message"] = "Something went wrong,please try after some time";
						}	
					}else
					{
						$final_output["status"] = "failed";
                    	$final_output["message"] = "Inactive like id";
					}
				}
				else
				{
 					$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";
				}           	 	
            }
            else
            {
            	$final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            }	
		}
		else
		{
           $final_output = $aa;
		}	

		header("content-type: application/json");
	    echo json_encode($final_output);

	}

	function reply_on_hint_list()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');

			if(!empty($json))
            {
            	$data = json_decode($json);
            	$final_output = array();  //type = 0 for all && type = 1 for sender && type = 2 for reciever

            	if($data->like_id != '')
            	{ 

            		$seleclike = $this->common_model->common_getRow("mtjf_user_like_unlike",array('like_id'=>$data->like_id,'like_status'=>1));
					if(!empty($seleclike))
					{
	            		if($data->create_at == 0)
	            		{ 
	            			if($data->type == 1)
	            			{
	            				$type = 1;
	            				$where_arr = array('like_id'=>$data->like_id,'user_id'=>$user_id);
	            			}
	            			else if($data->type == 2)
	            			{ 
	            				$type = 2;
	            				$where_arr = array('like_id'=>$data->like_id,'user_id'=>$data->second_user_id);
	            			}
	            			else
	            			{
	            				$where_arr = array('like_id'=>$data->like_id);      
	            			}	
	                            			
	            		}
	            		else
	            		{
	            			if($data->type == 1)
	            			{
	            				
	            				$where_arr = array('like_id'=>$data->like_id,'create_at >'=>$data->create_at,'user_id'=>$user_id);
	            			}
	            			else if($data->type == 2)	
	            			{
	            				
	            				$where_arr = array('like_id'=>$data->like_id,'create_at >'=>$data->create_at,'user_id'=>$data->second_user_id);
	            			}
	            			else
	            			{
	            				$where_arr = array('like_id'=>$data->like_id,'create_at >'=>$data->create_at);
	            			}	
	            			
	            		}	

	            		$get_response = $this->common_model->getData("mtjf_user_hint_response",$where_arr,'create_at','ASC',array());

	            		//print_r($this->db->last_query());exit;

	            		if(!empty($get_response))
	            		{
	            			foreach($get_response as $value)
	            			{
	            				if($data->type ==1)
	            				{
	            					$type = 1;
	            				}
	            				else if($data->type ==2)	
	            				{
	            					$type = 2;
	            				}
	            				else
	            				{
	            				   if($user_id == $value->user_id){ $type = 1;} else { $type = 2; }
	            				}

	            				$arr[] = array(
	        							  'type'=>$type,
	        							  'userid'=>$data->second_user_id,
	        					          'message'=>$value->message,
	        					          'message_id'=>$value->response_id,
	        					          'old_message_id'=>$value->old_response_id,
	        							  'contact_no'=>$value->contact_no,
	        							  'create_at'=>$value->create_at,
	        							  'update_at'=>strtotime($value->update_date)*1000

		            					);	
	            			}	
	            			    $final_output["status"] = "success";
	                        	$final_output["message"] = "successfully";
	                        	$final_output["data"] = $arr;
	            		}
	            		else
	            		{
	            			$final_output["status"] = "failed";
	                		$final_output["message"] = "No data found";
	            		}
	            	}else
	            	{
	            		$final_output["status"] = "failed";
                    	$final_output["message"] = "Inactive like id";
	            	}	
            	}
            	else
            	{
            		$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";
            	}	
            }
            else
            {
            	$final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            }	
		}
		else
		{
			$final_output = $aa;
		}

		header("content-type: application/json");
	    echo json_encode($final_output);	
	}	

	function interest()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');
			if(!empty($json))
            {
            	$data = json_decode($json);
            	$final_output = array();
            	$insertt = 'failed'; $response = 'failed';
            	if($data->interest_id != '' && $data->full_mobile_number != '' && $data->full_mobile_number != 0)
            	{
            		$userdata = $this->db->query("SELECT user_id,user_name,user_device_token,user_device_type FROM mtjf_user WHERE (CONCAT(user_country_id,user_contact) = '".$data->full_mobile_number."' OR REPLACE(CONCAT(user_country_id,'',user_contact), '+', '') = '".$data->full_mobile_number."' ) AND user_status = 1")->row();
            		if(!empty($userdata))
            		{
	            		$type= $data->type;
	            		$interestpoint = array('1'=>2,'2'=>3,'3'=>4,'4'=>5,'5'=>6);
	            		$dedamt = $interestpoint[$data->interest_id];
	            		$checkreqest = $this->db->select('user_wallet')->get_where("mtjf_user",array('user_id'=>$user_id,'user_wallet >='=>$dedamt))->row();
	            		if(!empty($checkreqest))
	            		{
	            			$new_wallet = $checkreqest->user_wallet;
		            	}else
		            	{
		            		$response = 'wallet';
	            		}	
		            		//$checkreqest = $this->db->query("SELECT a.response as myresponse, b.response as friend_response FROM mtjf_user_interest as a LEFT JOIN mtjf_user_interest as b ON a.contact_user_id = b.user_id WHERE (a.user_id = '$user_id' AND a.contact_user_id='".$data->userid."' AND a.interest_id = '".$data->interest_id."') ORDER BY a.id DESC LIMIT 1")->row();
	           			$this->db->order_by('id','DESC');
	           			$this->db->limit(1);
	           			$checkreqesttwo = $this->db->get_where("mtjf_user_interest",array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id,'interest_id'=>$data->interest_id))->row(); 		
	           			$this->db->order_by('id','DESC');
	           			$this->db->limit(1);
	           			$checkreqestone = $this->db->get_where("mtjf_user_interest",array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id,'interest_id'=>$data->interest_id))->row(); 		
	            		if(!empty($checkreqestone))
	            		{
	            			if($type == 1)
	            			{
	            				if($checkreqestone->response == 3 && $checkreqestone->status == 2)
	            				{
	            					if($response=='wallet')
	            					{
	            					}else
	            					{
	            						if(!empty($checkreqesttwo))
	            						{
	            							$int_response = $checkreqesttwo->response;
	            							//$int_status = $checkreqesttwo->status;
	            							if($int_response==1)
	            							{
	            								$res = 3;
				            					$insertt = $this->common_model->updateData("mtjf_user_interest",array('response'=>$res,'status'=>$res,'update_date'=>date('Y-m-d H:i:s')),array('id'=>$checkreqesttwo->id));
				            					//Notification send for match
				            					$newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id));
				            				}else
				            				{
				            					$res=1;
				            				}
	            						}else
	            						{
	            							$res = 1;
	            						}
		            					$insertt = $this->db->insert("mtjf_user_interest",array('user_id'=>$user_id,'interest_id'=>$data->interest_id,'contact_user_id'=>$userdata->user_id,'response'=>$res,'status'=>$res,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
				            			$newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id));
		            					$msg= 'Successfully interested';
	            					}
	            				}else
	            				{
	            						$response = "false";
				                        $msg = "already interested.";
				                        $res = $checkreqestone->status;
	            				}
	            			}elseif($type==2)
	            			{
	            				$res = 2;
	            				if($checkreqestone->response == 3)
	            				{
	            					//update\
	            					$insertt = $this->common_model->updateData("mtjf_user_interest",array('status'=>2,'update_date'=>date('Y-m-d H:i:s')),array('id'=>$checkreqestone->id));
	            					// $newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id));
								}elseif($checkreqestone->response == 1)
	            				{
	            					//delete
	            					$insertt = $this->common_model->deleteData("mtjf_user_interest",array('id'=>$checkreqestone->id));
	            				}
				            	$newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id));

	            				$msg= 'Successfully undo interest';
	            			}
	            		}else
	            		{
	            			if($type == 1)
	            			{
	            				if($response=='wallet')
            					{
            					}else
            					{
		            				if(!empty($checkreqesttwo))
            						{
            							$int_response = $checkreqesttwo->response;
            							//$int_status = $checkreqesttwo->status;
            							if($int_response==1)
            							{
            								$res = 3;
			            					$insertt = $this->common_model->updateData("mtjf_user_interest",array('response'=>$res,'status'=>$res,'update_date'=>date('Y-m-d H:i:s')),array('id'=>$checkreqesttwo->id));
			            					//Notification send for match
			            				}else
			            				{
			            					$res=1;
			            				}
            						}else
            						{
            							$res = 1;
            						}
		            				$insertt = $this->db->insert("mtjf_user_interest",array('user_id'=>$user_id,'interest_id'=>$data->interest_id,'contact_user_id'=>$userdata->user_id,'response'=>$res,'status'=>$res,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
				        	    	$selectdata = $this->db->select('user_id')->get_where("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id))->row();
				        	    	if(!empty($selectdata))
				        	    	{
				        	    		$newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id));
				        	    	}else
				        	    	{
				        	    		$newinsertt = $this->common_model->common_insert("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id,'response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
				        	    	}
		            				$msg= 'Successfully interested';
            					}
	            			}else
	            			{	
	            				$response = "false";
			                   	$msg = "No interest for undo.";
			                    $res = 2;
		             		}	
	            		}
	            		if($insertt=='true')
	            		{
	            			if($type==1)
	            			{
	            				//$msg = array('title'=>'new message','msg'=>$data->message,'image'=>'','type'=>1,'like_id'=>$data->like_id,'user_id'=>$data->second_user_id,'contact_no'=>$aa['data']->user_country_id.$aa['data']->user_contact);
 							   	//$this->common_model->sendPushNotification($getdevice_token->user_device_token,$msg);	
	            				//$this->common_model->ios_notification($devicetoken,$message);
	            				$interestmsg = array('1'=>'Movie','2'=>'Road Trip','3'=>'Date','4'=>'Kiss','5'=>'Getting Naughty');
	            				$massage = 'Interested in '.$interestmsg[$data->interest_id].' with '.$aa['data']->user_name;
	            				$coinmassage = 'Interested in '.$interestmsg[$data->interest_id].' with '.$userdata->user_name;
	            				$new_wallet = $new_wallet-$dedamt;
	            				$updatewallet = $this->common_model->updateData("mtjf_user",array('user_wallet'=>$new_wallet,'update_date'=>date("Y-m-d H:i:s")),array('user_id'=>$user_id));
								$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$coinmassage,'coin'=>$dedamt,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
	            				$type = $data->interest_id+3;
	            				if($aa['data']->user_gender==1) { $gender = 'he'; }elseif($aa['data']->user_gender==2){ $gender = 'she'; }else{ $gender = '<he/she>'; }
	            				if($res==3)
		            			{
	            					$massage = 'You have a '.$interestmsg[$data->interest_id].' Match with '.$aa['data']->user_name;
					        	    $newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id));
		            				$mtype= 1;
	            					$msgss = array('title'=>'New Match for '.$interestmsg[$data->interest_id],'msg'=>$massage,'image'=>'','type'=>$type,'match_type'=>1,'create_at'=>militime);
		            			}else
		            			{
	            					$massage = $aa['data']->user_name.' has shown Interest in some activity. Swipe on the activity you think '.$gender.' is interested in to get a Match!';
	            					if($userdata->user_device_type=='android')
	            					{
	            						$massage_new = $aa['data']->user_name.' has shown Interest in some activity. Swipe on the activity you think '.$gender.' is interested in to get a Match!';
	            					}else
	            					{
	            						$massage_new = $aa['data']->user_name.' has shown Interest in some activity...';
	            					}
		            				$mtype= 0;
	            					$msgss = array('title'=>'You have new Interest' ,'msg'=>$massage_new,'image'=>'','type'=>$type,'match_type'=>0,'create_at'=>militime);
		            			}
	            				if($userdata->user_device_type=='android')
            					{
            						 $this->common_model->sendPushNotification($userdata->user_device_token,$msgss);	
								}else
								{
								 	$this->common_model->ios_notification($userdata->user_device_token,$msgss);
								}
								
								$this->common_model->common_insert("mtjf_notification",array('sender_id'=>$user_id,'receiver_id'=>$userdata->user_id,'type'=>$type,'match_type'=>$mtype,'msg'=>$massage,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
	            			}
	            			$final_output["status"] = "success";
	                        $final_output["message"] = $msg;
	                        $final_output["interest_status"] = $res;
	                        $final_output["wallet"] = $new_wallet;

	                    }elseif($response == 'false')
	            		{
	            			$final_output["status"] = "failed";
	                        $final_output["message"] = $msg;
	                        $final_output["interest_status"] = $res;
	            		}elseif($response == 'wallet')
	            		{
							$final_output["status"] = "failed";
	                        $final_output["message"] = "Insufficient coins.";
	            		}else
	            		{
	            			$final_output["status"] = "failed";
	                        $final_output["message"] = "Something went wrong! please try again later.";
	            		}
            		}else
            		{
            			$final_output["status"] = "failed";
                		$final_output["message"] = "User not active.";
            		}
	          	}
            	else
            	{
            		$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";
            	}	
            }
            else
            {
            	$final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            }	
		}
		else
		{
			$final_output = $aa;
		}

		header("content-type: application/json");
	    echo json_encode($final_output);	
	}
	//Interest

	function coin_history()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');

			if(!empty($json))
            {
            	$data = json_decode($json);
            	$final_output = array();

            	if($user_id != '')
            	{
            		if($data->create_at == 0)
            		{
                        $where_arr = array('user_id'=>$user_id);          			
            		}
            		else
            		{
            			$where_arr = array('create_date >'=>$data->create_at,'user_id'=>$user_id);
            		}

					$get_response = $this->common_model->getData("coin_history",$where_arr,'create_date','DESC',array());   

					if(!empty($get_response))
					{
						foreach($get_response as $value)
            			{
            				$arr[] = array(
        							  'history_id'=>$value->history_id,
        					          'message'=>$value->msg,
        					          'coin'=>$value->actions.$value->coin,
        					          'coin_balance'=>$value->coin_balance,
        							  'create_at'=>$value->create_date
	            					);
            			}

            			$final_output["status"] = "success";
                    	$final_output["message"] = "Coin History";
                    	$final_output["data"] = $arr;
					}   
					else
					{
						$final_output["status"] = "failed";
                		$final_output["message"] = "History Not Found";
					}      		
            	}
            	else
            	{
            		$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";
            	}	
            }
            else
            {
            	$final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            }	
		}
		else
		{
			$final_output = $aa;
		}	
		header("content-type: application/json");
	    echo json_encode($final_output);

	}

	function bulk_message()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');

			if(!empty($json))
			{
                $data = json_decode($json);
               	$dcount = count($data->message_data);

               	$final_output = $contactarr = array();

				if($dcount != 0)
				{
					for ($i=0; $i < $dcount; $i++)
					{
						$msg_count = count($data->message_data[$i]->messageLists);

						//$message = $data->message_data[$i]->last_message;

						for($j=0; $j < $msg_count; $j++)
						{
							$insert_message[] = '("","'.$data->message_data[$i]->messageLists[$j]->like_id.'","'.$user_id.'","'.$data->message_data[$i]->messageLists[$j]->second_user_id.'","'.$data->message_data[$i]->messageLists[$j]->message.'","'.$data->message_data[$i]->messageLists[$j]->message_id.'","'.$data->message_data[$i]->messageLists[$j]->contact_no.'","'.datetime.'","'.datetime.'","'.militime.'")';
						}	
						//notification function
					}	

					$insertt = implode(',',$insert_message);

					$insert_message = $this->db->query("INSERT INTO mtjf_user_hint_response VALUES $insertt");

					if(!empty($insert_message))
					{
						$final_output["status"] = "success";
        				$final_output["message"] = "successfully";	
					}
					else
					{
						$final_output["status"] = "failed";
                    	$final_output["message"] = "Something went wrong,please try after some time";
					}	
				}
				else
				{
					$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";
				}	
			}
			else
			{
			  $final_output["status"] = "failed";
              $final_output["message"] = "No required parameter";
			}	
		}
		else
		{
			$final_output = $aa;
		}

		header("content-type: application/json");
	    echo json_encode($final_output);	
	}

	function Get_interest_detail()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$json = file_get_contents('php://input');
		    $json_array = json_decode($json);
		    $user_id = $aa['data']->user_id;
			$user_cont_num = $json_array->full_mobile_number;
			$userdata = $this->db->query("SELECT user_id FROM mtjf_user WHERE CONCAT(user_country_id,user_contact) = '$user_cont_num' AND user_status = 1")->row();
			if(!empty($userdata))
			{
				$getintresponse = $this->db->query("SELECT interest_1,interest_2,interest_3,interest_4,interest_5 FROM mtjf_interest_data_store WHERE user_id = '$user_id' AND contact_user_id= '".$userdata->user_id."'")->row();
				if(!empty($getintresponse)) 
				{ 
					$object = array(
								'interest_1'=>$getintresponse->interest_1,
								'interest_2'=>$getintresponse->interest_2,
								'interest_3'=>$getintresponse->interest_3,
								'interest_4'=>$getintresponse->interest_4,
								'interest_5'=>$getintresponse->interest_5,
								);	
				}else
				{
					$object = array(
								'interest_1'=>"2",
								'interest_2'=>"2",
								'interest_3'=>"2",
								'interest_4'=>"2",
								'interest_5'=>"2",
								);	
				}
				$final_output['status'] = 'success';
				$final_output['message'] = 'Interest data.';
				$final_output['interest'] = $object;
			}else
			{
				$final_output['status'] = 'failed';
				$final_output['message'] = 'User not registered.';
			}
		}
		else
		{
			$final_output = $aa;
		}
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//interest detail

	function notification_list()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$json = file_get_contents('php://input');
		    $json_array = json_decode($json);
		    $user_id = $aa['data']->user_id;
			$array = array(); $more_data = '';
			if($json_array->create_date!=0)
			{
				$more_data = "AND create_date > '$json_array->create_date'";
			}
			//$intarr= array("4","5","6","7","8");
			$notificationdata = $this->db->query("SELECT * FROM mtjf_notification WHERE receiver_id = '$user_id' ".$more_data." ORDER BY notify_id DESC")->result();
			if(!empty($notificationdata))
			{
				foreach ($notificationdata as $key) {
					$date = date('d F Y, h:i A',strtotime($key->create_date));
					$image = '';
					// if(in_array($key->type, $intarr))
					// {
					// 	$msg = json_decode($key->msg);
						if($key->type!=3 && $key->type!=9)
						{
							$userimage= $this->db->select('user_image')->get_where("mtjf_user",array('user_id'=>$key->sender_id))->row();
							if(isset($userimage) && isset($userimage->user_image) && $userimage->user_image!='')
							{ 
								if (filter_var($userimage->user_image, FILTER_VALIDATE_URL)) {
								    $image = $userimage->user_image;
								}else
								{
									$image = base_url().'uploads/user_image/'.$userimage->user_image; 
								}
							}
						}
						//$key->msg = $msg->msg;	
					//}


					$array[] = array(
							'notify_id'=>$key->notify_id,
							'type'=>$key->type,
							'match_type'=>$key->match_type,
							'msg'=>$key->msg,
							'user_image'=>$image,
							'create_date'=>$key->create_date,
							'display_date'=>$date
							);
				}
				$final_output['status'] = 'success';
				$final_output['message'] = 'notification list.';
				$final_output['data'] = $array;
			}else
			{
				$final_output['status'] = 'failed';
				$final_output['message'] = 'Empty notification list.';
			}
		}
		else
		{
			$final_output = $aa;
		}
	    header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//notification list
	function unmatched()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');

			if(!empty($json))
			{
				$data = json_decode($json);
                $final_output = array();

                if($data->full_mobile_number != '')
                {
                	 $checkexisting = $this->common_model->common_getRow("mtjf_user_like_unlike",array('user_id'=>$user_id,'contact_no'=>$data->full_mobile_number,'like_status'=>1));

                	if(!empty($checkexisting))
	            	{
	            		$update = $this->common_model->updateData('mtjf_user_like_unlike',array('like_status'=>0),array('contact_no'=>$data->full_mobile_number,'user_id'=>$user_id));
	            		if($update !=false)
	            		{
	            			$contact_no = $aa['data']->user_country_id.$aa['data']->user_contact;
	            			$userid = $this->db->query("SELECT user_id FROM mtjf_user WHERE CONCAT(user_country_id,user_contact) = '".$data->full_mobile_number."' ")->row();
        					if(!empty($userid->user_id))
        					{
        						$update1 = $this->common_model->updateData('mtjf_user_like_unlike',array('like_status'=>0),array('contact_no'=>$contact_no,'user_id'=>$userid->user_id));
        					}
    						$final_output["status"] = "success";
                			$final_output["message"] = "Successfully Unmatched.";
	            		}
	            		else
	            		{
	            			$final_output["status"] = "failed";
                            $final_output["message"] = "Something Went Wrong.";
	            		}	            		
	            	}
	            	else
	            	{ 
	            		$final_output["status"] = "failed";
                        $final_output["message"] = "Already Unmatched.";
	            	}	
                }
                else
                {
                	$final_output["status"] = "failed";
                    $final_output["message"] = "No required parameter";
                }	
			}
			else
			{
			   $final_output["status"] = "failed";
               $final_output["message"] = "No required parameter";
			}	
		}
		else
		{
			$final_output = $aa;
		}	
		header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//unmatched

	function purchase_coin()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');

			if(!empty($json))
			{
				$data = json_decode($json);
                $final_output = array();

                if($data->transaction_id != '' && $data->coin != '')
                {
	               	$checkexisting = $this->common_model->common_getRow("mtjf_purchase_coin",array('user_id'=>$user_id,'transaction_id'=>$data->transaction_id));
	              	if(empty($checkexisting))
	            	{
	             		$coinarr = array('250'=>10,'600'=>30,'1100'=>60,'1600'=>100);
	            		if($coinarr[$data->price] == $data->coin)
	            		{
		            		$data->user_id = $user_id;
		            		$data->create_date = date("Y-m-d H:i:s");
		            		$update = $this->common_model->common_insert('mtjf_purchase_coin',$data);
		            		if($update !=false)
		            		{
								$oldwallet = $this->db->select('user_wallet')->get_where("mtjf_user",array('user_id'=>$user_id))->row();
								$newwallet = $oldwallet->user_wallet+$data->coin;
								$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'+','msg'=>"Coins Purchased",'coin'=>$data->coin,'coin_balance'=>$newwallet,'create_date'=>date('Y-m-d H:i:s')));
		            			$updatewalt = $this->db->update('mtjf_user',array('user_wallet'=>$newwallet,'update_date'=>date("Y-m-d H:i:s")),array('user_id'=>$user_id));	            			
	    						$final_output["status"] = "success";
	                			$final_output["message"] = "Coin successfully added in wallet.";
	                			$final_output['wallet']= $newwallet;
		            		}
		            		else
		            		{
		            			$final_output["status"] = "failed";
	                            $final_output["message"] = "Something Went Wrong.";
		            		}	            		
	            		}else
	            		{
	            			$final_output["status"] = "failed";
	                		$final_output["message"] = "Transaction failed.";
	            		}
	            	}
	            	else
	            	{  
	            		$final_output["status"] = "failed";
                        $final_output["message"] = "Transaction already completed.";
	            	}	
                }
                else
                {
                	$final_output["status"] = "failed";
                    $final_output["message"] = "No required parameter";
                }	
			}
			else
			{
			   $final_output["status"] = "failed";
               $final_output["message"] = "No required parameter";
			}	
		}
		else
		{
			$final_output = $aa;
		}	
		header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//purchase coin

	// function Get_data_testing() not in use
	// {
	// 	$final_output['message'] = 'For Match:

	// 	You have a Match with <Full Name>!

	// 	For Fan:
	// 	You have a new Fan! 

	// 	(In the circle show the Fan Heart)

	// 	For Fan with Hint:
	// 	Your new Fan has sent you a hint! Click to read or reply to the hint. 

	// 	(In the circle icon show here the lightbulb. When someone clicks this notification, take him to the hint and reply conversation box of that fan)

	// 	For Activities Match:

	// 	You have a Movie/Road Trip/ Date/ Kiss/ Get Naughty Match with <Full Name>!

	// 	For Activity interest created by Match:

	// 	<Full Name> has shown interest in some activty. Swipe on the activity you think <he/she> is interested in to get a Match! 

	// 	Pls note the exclamation marks, Captial cases wherever and icons.';

	// 	header("content-type: application/json");
	//     echo json_encode($final_output);
	// }

	function new_contact_list()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{ 
            $user_id = $aa['data']->user_id;
            $myno = $aa['data']->user_contact;
            $json = file_get_contents('php://input');
            if(!empty($json))
            {   
                $data = json_decode($json);
               	$contarr = $data->friend_data;
               	$date = date('Y-m-d H:i:s');
          	    $userdata = $arr = array();
              	$final_output = $contactarr = $contactnmarr = $deluser = array();
				$dcount = count($contarr);
                if($dcount!=0)
                {
					for ($i=0; $i < $dcount; $i++) { 
	                	
	                	if($contarr[$i]->contact_status == 2)
	                	{
	                		$deluser[] = $contarr[$i]->contact_no;
	                	}else
	                	{
							$contactarr[] = $contarr[$i]->contact_no;
							$contactnmarr[] = $contarr[$i]->contact_name;
	                	}
	                }
	                if(!empty($deluser)){ 
	                	$implodd = implode(',', $deluser);
	                	$delete = $this->db->query("DELETE FROM mtjf_user_contact_list WHERE contact_no IN ($implodd)");
	           		}
	                $newdata = array(); $contactarrimp = 0;
                	if(!empty($contactarr))
                	{
	                	$contactarrimp = implode(',',$contactarr);
	                	$cont_count = count($contactarr);
	                	for ($i=0; $i < $cont_count; $i++) { 
		                	$select = $this->db->query("SELECT contact_no,contact_id FROM mtjf_user_contact_list WHERE contact_no = '".$contactarr[$i]."' AND user_id = '$user_id'")->row();
		                	if(!empty($select))
		                	{
		                		$this->common_model->updateData("mtjf_user_contact_list",array('contact_name'=>$contactnmarr[$i]),array('contact_id'=>$select->contact_id));
		                	}else
		                	{
		                		$newdata[] = '("","' .$user_id. '","","'.$contactarr[$i].'","' .$contactnmarr[$i].'","","'.$date.'","")';
		                	} 
	                	}
	              	}
            	 	$contactdata = implode(',',$newdata);
                    if(!empty($contactdata)){
	                	$insertdata = $this->db->query("INSERT INTO mtjf_user_contact_list VALUES ".$contactdata."");
                    }else
                    {
                    	$insertdata = false;
                    }
	                if(!empty($insertdata) && $insertdata===true)
	                {
	                	$listsel = $this->db->query("SELECT uc.contact_id,uc.contact_no,uc.contact_name,uc.facebook_id as user_facebook_id,mtjf_user.user_name,mtjf_user.user_id,mtjf_user.user_image FROM `mtjf_user_contact_list` as uc LEFT JOIN mtjf_user ON uc.contact_no IN (mtjf_user.user_contact,CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact)) WHERE uc.user_id = '$user_id' AND uc.contact_no IN ($contactarrimp) ORDER BY uc.contact_name ASC")->result();
			 			if(!empty($listsel))
		            	{
		            	 	foreach ($listsel as $values)
			                { 
			                    $contactarrnew[] = $values->contact_no;
			                    $usermobile = $values->contact_no;
			                    if($values->user_id !='' && $values->user_id != NULL)
			                    {
			                        $img = $values->user_image;
			                   		$uid = $values->user_id;
			                    }else
			                    {
			                    	$img = '';
		                        	$uid = 0;
		                        }
			                    $fstatus = $likeid = 0; $hint = '';
			                    $mathch = $this->db->query("SELECT a.like_id,b.like_id as lid FROM mtjf_user_like_unlike as a INNER JOIN mtjf_user_like_unlike as b WHERE ( ( a.user_id = '$user_id' ) AND (a.second_user_id = '$uid' OR a.contact_no LIKE '%$usermobile%') AND (a.like_status = 1)) AND ( ( b.user_id = '$uid' ) AND ( b.second_user_id = '$user_id' OR b.contact_no LIKE '%".$aa['data']->user_country_id."$myno%' OR b.contact_no LIKE '%$myno%' OR b.contact_no LIKE '%".'0'."$myno%') AND (b.like_status = 1) )")->row();
			                    if(!empty($mathch))
			                    {
			                    	if($values->user_name!=NULL)
			                    	{
			                    		$uname = $values->user_name;
			                    	}
			                    	$fstatus = 1; //match
			                    }else
			                    {
			                    	$like = $this->db->query("SELECT like_id,like_hint FROM mtjf_user_like_unlike WHERE (user_id = '$user_id' AND contact_no = '$usermobile' AND like_status = 1)")->row();
			                    	if(!empty($like))
			                    	{
			                    		$fstatus = 2; //like
			                    		$likeid = $like->like_id;
					                	$hint = $like->like_hint;
			                    	}
			                    	$uname = $values->contact_name;

			                    }
			                    
			                    if($img!='' )
								{
									if (filter_var($img, FILTER_VALIDATE_URL)) {
							    		$values->user_image = $img;
									}else
									{
										$values->user_image = base_url().'uploads/user_image/'.$img;
									}
								}else
								{
									$values->user_image = '';
								}

								unset($values->user_id);
								unset($values->user_name);
								$values->full_contact_no = $usermobile;
								$values->contact_name = $uname;
								$values->friend_status = $fstatus; 
								$values->like_hint = $hint; 
								$values->like_id = $likeid;
								$values->remove_contact = 0; 
								$values->userid = (string)$uid;
								$arr[] = $values;
			                }
			         	}
			         	if(!empty($deluser))
		                {
		                	$implocont = implode(',', $deluser);
		                	//OLD QUERY**** = $listsel = $this->db->query("SELECT contact_no,contact_name,second_user_id,like_hint,like_id FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND contact_no NOT IN ($implocont)")->result();
							$listsel = $this->db->query("SELECT liketbl.like_hint,liketbl.like_id,liketbl.contact_name,liketbl.second_user_id,liketbl.contact_no,mtjf_user.user_id,mtjf_user.user_name,mtjf_user.user_image  FROM `mtjf_user_like_unlike` as liketbl LEFT JOIN mtjf_user ON  liketbl.contact_no IN (mtjf_user.user_contact,CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact)) WHERE liketbl.user_id = '$user_id' AND liketbl.like_status = 1 AND liketbl.contact_no IN ($implocont)")->result();
		                	if(!empty($listsel))
			            	{
			            	 	foreach ($listsel as $values)
				                { 
			                		if($values->user_id!='' && $values->user_id!=NULL)
			                		{
				                		// $usrname = $this->db->query("SELECT user_id,user_name,user_country_id,user_contact,user_image FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '".$values->contact_no."' OR user_contact = '".$values->contact_no."' ")->row();
				                		// if(!empty($usrname))
				                		// {
			                			$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$values->user_id."') AND (second_user_id = '".$user_id."' OR contact_no = '".$aa['data']->user_country_id.$myno."' OR contact_no = '".$myno."') AND (like_status = 1) ")->row();
			                			//print_r($this->db->last_query());exit;
			                			if(!empty($selmat))
			                			{
			                				$status=1;
			                			}else{
			                				$status=2;
			                			}
			                			if(!empty($values->user_image))
			                			{
			                				if (filter_var($values->user_image, FILTER_VALIDATE_URL)) {
									    		$image = $values->user_image;
											}else
											{
												$image = base_url().'uploads/user_image/'.$values->user_image;
											}
			                			}else
			                			{
			                				$image = '';
			                			}

			                			$arr[] = array(
			                					'contact_name'=>$values->user_name,
			                					'user_facebook_id'=>'',
			                					'user_image'=>$image,
			                					'contact_no'=>$values->contact_no,
			                					'full_contact_no'=>$values->contact_no,
			                					'userid'=>(string)$values->user_id,
			                					'like_id'=>$values->like_id,
			                					'like_hint'=>$values->like_hint,
				                				'remove_contact' => 1,
			                					'friend_status'=>$status
			                					);
			                		}else
			                		{
			                			$arr[] = array(
			                					'contact_name'=>$values->contact_name,
			                					'user_facebook_id'=>'',
			                					'user_image'=>'',
			                					'contact_no'=>$values->contact_no,
			                					'full_contact_no'=>$values->contact_no,
			                					'userid'=>(string)$values->second_user_id,
			                					'like_id'=>$values->like_id,
			                					'like_hint'=>$values->like_hint,
				                				'remove_contact' => 1,
			                					'friend_status'=>2
			                					);
			                		}
						    	}
				            }
			            }
		            	if(!empty($arr))
		                {
		                	$final_output["status"] = "success";
	                		$final_output["message"] = "Contact list successfully added.";	
	                		$final_output["data"] = $arr;	
		                }else
		                {
		                	$final_output["status"] = "failed";
	                		$final_output["message"] = some_error;	
		                }
	                }
	                else
	                {
	                    $final_output["status"] = "failed";
	                    $final_output["message"] = some_error;
	        	    }
            	}else
            	{
            		$final_output["status"] = "failed";
                	$final_output["message"] = "No required parameter";		
            	}
            }
            else
            {
                $final_output["status"] = "failed";
                $final_output["message"] = "No required parameter";
            } 
		}else
		{
			$final_output = $aa;
		}  
		header("content-type: application/json");
		echo json_encode($final_output);
	}

	function enable_disable_msg()
	{
		$aa = $this->check_authentication();
		if($aa['status']=='true')
		{
			$user_id = $aa['data']->user_id;
			$json = file_get_contents('php://input');
			if(!empty($json))
			{
				$data = json_decode($json);
                $final_output = array();
    			$update = $this->common_model->updateData('mtjf_user',array('match_msg_status'=>$data->match_status,'fan_msg_status'=>$data->fan_status,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$user_id));
        		if($update !=false)
        		{
      				$final_output["status"] = "success";
        			$final_output["message"] = "Status successfully changed.";
        		}
        		else
        		{
        			$final_output["status"] = "failed";
                    $final_output["message"] = "Something Went Wrong.";
        		}	            		
	        }
			else
			{
			   $final_output["status"] = "failed";
               $final_output["message"] = "No required parameter";
			}	
		}
		else
		{
			$final_output = $aa;
		}	
		header("content-type: application/json");
	    echo json_encode($final_output);
	}
	//Enable disable msg status


	function check_authentication()
	{
	    $response = '';
	 	$headers = $_SERVER['HTTP_SECRET_KEY'];
		if(!empty($headers))
		{
			$check = $this->ChechAuth($headers);
			if($check['status']=="true")
			{
				$final_output['data'] = $check['data'];
				$final_output['status'] = "true";
			}else
			{
				$final_output['status'] ="false";
				$final_output['message'] = "Invalid Token";
			}   
		}else
		{
			$final_output['status'] ="false";
			$final_output['message'] = "Unauthorised access";
		}
	    return $final_output;	
	}
	
	function ChechAuth($token)
	{
		$auth = $this->common_model->getDataField('user_id,user_facebook_id,user_country_id,user_contact,user_name,user_gender','mtjf_user',array('user_token'=>$token));
		if(!empty($auth))
		{
			$abc['status'] = "true";
			$abc['data'] = $auth[0];
			return $abc;
		}else
		{
			$abc['status'] = "false";
			return $abc;
		}
	}
	
} 
