<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Api extends MY_Controller
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

	function login()
	{
		$json = file_get_contents('php://input');
	    $json_array = json_decode($json);
	    $final_output = array();
    	if($json_array->user_contact!='')
    	{
    		$contact = $json_array->user_contact;
    		
				//$otp = $this->common_model->random_number();
			$otp = '123456';
    		$seleuser = $this->common_model->common_getRow('mtjf_user',array('user_contact'=>$contact));
    		if(!empty($seleuser))
    		{
				$update = $this->common_model->updateData('mtjf_user',array('user_otp'=>md5($otp),'user_status'=>0,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$seleuser->user_id));
				$object = array(
					'wallet'=>$seleuser->user_wallet,
					'user_id'=>(string)$seleuser->user_id
					);
				$final_output['status'] = 'success';
				$final_output['message'] = 'Successfully login';
				$final_output['data'] = $object;
    		}else
    		{
    			$json_array->user_otp = md5($otp);
    			$json_array->create_date = date('Y-m-d H:i:s');
    			$insert = $this->common_model->common_insert("mtjf_user",$json_array);
    			if($insert!=false)
    			{
    				$object = array(
    					'wallet'=>0,
						'user_id'=>(string)$insert
					);
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
	    		$updateotp = $this->common_model->updateData("mtjf_user",array('user_otp'=>'','user_status'=>1,'user_device_type'=>$json_array->user_device_type,'user_device_id'=>$json_array->user_device_id,'user_device_token'=>$json_array->user_device_token,'user_token'=>$token,'update_date'=>date('Y-m-d H:s:i')),array('user_id'=>$json_array->user_id));
	    		if($updateotp!=false)
	    		{
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
				$seluservote = $this->db->query("SELECT like_id,second_user_id,contact_no FROM mtjf_user_like_unlike WHERE user_id = '$user_id'")->result(); //OLD
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
	            $selmathch = $this->db->query("SELECT count(like_id) as mcount FROM mtjf_user_like_unlike  WHERE ( ( user_id IN ($impid) ) AND ( second_user_id = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%' OR contact_no LIKE '%".'0'.$aa['data']->user_contact."%') )")->row();
				if(!empty($selmathch))
				{
					$matchcount = $selmathch->mcount;
				}	
				$likcount = count($id) - $matchcount + count($contact);
				$fan = $this->db->query("SELECT user_id FROM mtjf_user_like_unlike WHERE ( second_user_id = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%')")->result();
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
			                    $mathch = $this->db->query("SELECT a.like_id,b.like_id as lid FROM mtjf_user_like_unlike as a INNER JOIN mtjf_user_like_unlike as b WHERE ( ( a.user_id = '$user_id' ) AND (a.second_user_id = '$uid' OR a.contact_no LIKE '%$usermobile%') ) AND ( ( b.user_id = '$uid' ) AND ( b.second_user_id = '$user_id' OR b.contact_no LIKE '%".$aa['data']->user_country_id."$myno%' OR b.contact_no LIKE '%$myno%' OR b.contact_no LIKE '%".'0'."$myno%') )")->row();
			                    if(!empty($mathch))
			                    {
			                    	if($values->user_name!=NULL)
			                    	{
			                    		$uname = $values->user_name;
			                    	}
			                    	$fstatus = 1; //match
			                    }else
			                    {
			                    	$like = $this->db->query("SELECT like_id,like_hint FROM mtjf_user_like_unlike WHERE (user_id = '$user_id' AND contact_no = '$usermobile')")->row();
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
							$listsel = $this->db->query("SELECT liketbl.like_hint,liketbl.like_id,liketbl.contact_name,liketbl.second_user_id,liketbl.contact_no,mtjf_user.user_id,mtjf_user.user_name,mtjf_user.user_image  FROM `mtjf_user_like_unlike` as liketbl LEFT JOIN mtjf_user ON  liketbl.contact_no IN (mtjf_user.user_contact,CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact)) WHERE liketbl.user_id = '$user_id' AND liketbl.contact_no NOT IN ($implocont)")->result();
		                	if(!empty($listsel))
			            	{
			            	 	foreach ($listsel as $values)
				                { 
			                		if($values->user_id!='' && $values->user_id!=NULL)
			                		{
				                		// $usrname = $this->db->query("SELECT user_id,user_name,user_country_id,user_contact,user_image FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '".$values->contact_no."' OR user_contact = '".$values->contact_no."' ")->row();
				                		// if(!empty($usrname))
				                		// {
			                			$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$values->user_id."') AND (second_user_id = '".$user_id."' OR contact_no = '".$aa['data']->user_country_id.$myno."' OR contact_no = '".$myno."')")->row();
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
			$uname = $devicetype = $devicetoken = $image = '';
			$name = $this->db->query("SELECT user_id,user_name,user_device_type,user_device_token FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) = '$contact_no' OR user_contact = '$contact_no'")->row();
			if(!empty($name))
			{
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

			$seluservote = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE user_id = '$user_id' AND contact_no LIKE '%$contact_no%' ")->row();
			if(!empty($seluservote))
			{
				if($like_status != 1)
				{
					if($coinbal->user_wallet!=0 && $coinbal->user_wallet!=null)
					{
						$unlike = $this->common_model->deleteData("mtjf_user_like_unlike",array('like_id'=>$seluservote->like_id));
						if($unlike==TRUE)
						{
							$msg = 'Undo liking '.$uname;
							$new_wallet = $new_wallet-1;
							$updatecoin = $this->db->query("UPDATE mtjf_user SET user_wallet = '$new_wallet' WHERE user_id = '$user_id'");
							$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$msg,'coin'=>1,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
							$response = 'true';
			    			$status = 0;
						}else
			   	 		{
							$response = 'failed';
				  		}
					}else
					{
						$response = 'nocoin';
					}	
		   		}else{
					$response = 'false';
		    		//$seluser = $this->db->query("SELECT user_id FROM mtjf_user WHERE CONCAT(user_country_id,'',user_contact) LIKE  '%".$contact_no."%' OR user_contact LIKE '%".$contact_no."%'")->row();
					//if(!empty($seluser)){ $uid = $seluser->user_id; }else{ $uid = 0; }		
					if($frndid != 0){ $uid = $frndid; }else{ $uid = 0; }
					$matchcheck = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '$uid') AND (second_user_id = '$user_id' OR contact_no = '".$aa['data']->user_contact."' OR contact_no = '".$aa['data']->user_country_id.$aa['data']->user_contact."')")->row();
					if(!empty($matchcheck))
					{
						$status = 1;
					}else
					{
						$status = 2;
					}
		    	}	
			}else
			{
				if($like_status== 1)
				{
		    		if($like_hint == '')
					{
						$likes = $this->common_model->common_insert("mtjf_user_like_unlike",array('user_id'=>$user_id,'second_user_id'=>$userid,'contact_no'=>$contact_no,'contact_name'=>$uname,'like_hint'=>$like_hint,'create_date'=>date('Y-m-d H:i:s')));
					}else
					{	
						if($coinbal->user_wallet!=0 && $coinbal->user_wallet!=null)
						{
							$likes = $this->common_model->common_insert("mtjf_user_like_unlike",array('user_id'=>$user_id,'second_user_id'=>$userid,'contact_no'=>$contact_no,'contact_name'=>$uname,'like_hint'=>$like_hint,'create_date'=>date('Y-m-d H:i:s')));
						}else
						{
							$response = 'nocoin';
						}
					}
					if($likes==TRUE)
					{
						$like_id = $this->db->insert_id();
						if($like_hint!='')
						{
							$msg = 'Liked '.$uname;
							$new_wallet = $new_wallet-2;
							$updatecoin = $this->db->query("UPDATE mtjf_user SET user_wallet = '$new_wallet' WHERE user_id = '$user_id'");
							$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$msg,'coin'=>2,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
						}
						$matchcheck = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '$userid') AND ('second_user_id' = '$user_id' OR contact_no LIKE '%".$aa['data']->user_country_id.$aa['data']->user_contact."%' OR contact_no LIKE '%".$aa['data']->user_contact."%')")->row();
						if(!empty($matchcheck))
						{
							$sms_msg = 'You have new match!';
							$status = 1;
						}else
						{
							//$countfan = $this->db->query("SELECT count(like_id) as fcount FROM mtjf_user_like_unlike WHERE ")
							$sms_msg = 'Eg. Ahoy! Some of your friend has secretly liked you on MTJF. You have 10 total fans. To find out who your fans are, login on MTJF using your Mobile Number '.$contact_no.'<br>'. 'MTJF | An App for Dating Your Friends!';
							$status = 2;
						}
						//Check send sms condition 24 hr
						$checksms = $this->db->query("SELECT msg_count,create_date FROM mtjf_sms_managment WHERE contact_num = '".$contact_no."'")->row();
						if(!empty($checksms))
						{
							$hourdiff = round((strtotime(date('Y-m-d H:i:s')) - strtotime($checksms->create_date))/3600, 1);
							if($hourdiff > 24)
							{
								$updatecount = $this->common_model->updateData("mtjf_sms_managment",array('msg_count'=>1,'create_date'=>date('Y-m-d H:i:s')),array('contact_num'=>$contact_no));
								//Send SMS Code
							}else
							{
								if($checksms->msg_count < 3)
								{
									$totalcount = $checksms->msg_count+1;
									$updatecount = $this->common_model->updateData("mtjf_sms_managment",array('msg_count'=>$totalcount),array('contact_num'=>$contact_no));
									//Send SMS Code
								}
							}
						}else
						{
							$insertsms = $this->db->insert("mtjf_sms_managment",array('msg_count'=>1,'contact_num'=>$contact_no,'create_date'=>date('Y-m-d H:i:s')));
						}
						$response = 'true';

					}elseif($response!='nocoin')
		   	 		{
						$response = 'failed';
			  		}
				}else{
					$response = 'false';
		    		$status = 0;
		        }	
			} 
				if($response=='true')
				{
					if($like_status == 1)
					{ 
						$msg = 'Successfully Liked'; 
						if($status==1)
						{ 
							if($frndid != 0)
							{
								$intdata = $this->db->select('id')->get_where("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$frndid))->row();
								if(empty($intdata))
								{
									$intinsert = $this->db->insert("mtjf_interest_data_store",array('user_id'=>$user_id,'contact_user_id'=>$frndid,'create_date'=>date('Y-m-d H:i:s')));
								}
							}
							$notify_msg = 'You have a Match with '.$coinbal->user_name.'!'; $title='You have a Match.'; $type=2; 
							if(!empty($coinbal->user_image)){ $image= base_url().'uploads/user_image/'.$coinbal->user_image; } 
							$mtype = 1;
						}else{ 
							 $title='You have a new Fan!'; 
							if($like_hint!=''){ $notify_msg = 'Your new Fan has sent you a Hint! Click to read or reply to the Hint.'; $type=9; }else{ $notify_msg = "You have a new Fan!"; $type=3;}	
							$mtype = 0;
						}
						
							if(!empty($devicetoken))
							{
								$message = array('title'=>$title,'msg'=>$notify_msg,'image'=>$image,'type'=>$type,'match_type'=>$mtype,'create_at'=>militime);
								if($devicetype=='android')
								{
									$this->common_model->sendPushNotification($devicetoken,$message);	
								}else
								{
									$this->common_model->ios_notification($devicetoken,$message);
									
								}	
								$this->common_model->common_insert("mtjf_notification",array('sender_id'=>$user_id,'receiver_id'=>$frndid,'type'=>$type,'match_type'=>$mtype,'msg'=>$notify_msg,'create_date'=>date('Y-m-d H:i:s'),'update_date'=>date('Y-m-d H:i:s')));
							}
					}else
					{ 
						$msg = 'Successfully Unliked'; 
					}
					
					$final_output['status'] = 'success';
	   		  		$final_output['message'] = $msg;
	   	  			$final_output['data'] = array('friend_status'=>$status,'wallet'=>$new_wallet,'like_id'=>$like_id);
				}
				elseif($response=='nocoin')
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
	//end like unlike post

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
			$seluservote = $this->db->query("SELECT liketbl.second_user_id,liketbl.contact_no,mtjf_user.user_id as reguser  FROM `mtjf_user_like_unlike` as liketbl LEFT JOIN mtjf_user ON  liketbl.contact_no IN (user_contact,CONCAT(user_country_id,user_contact),REPLACE(CONCAT(user_country_id,'',user_contact), '+', ''),CONCAT('0','',user_contact)) WHERE liketbl.user_id = '$user_id'")->result();
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
				$selmatch = $this->db->query("SELECT liketbl.user_id,liketbl.like_id,liketbl.like_hint,mtjf_user.user_name,mtjf_user.user_image,mtjf_user.user_country_id,mtjf_user.user_contact FROM `mtjf_user_like_unlike` as liketbl INNER JOIN mtjf_user ON liketbl.user_id = mtjf_user.user_id where (liketbl.second_user_id = '$user_id' OR liketbl.contact_no = '".$aa['data']->user_country_id.$aa['data']->user_contact."' OR liketbl.contact_no = '".$aa['data']->user_contact."' OR liketbl.contact_no = '".'0'.$aa['data']->user_contact."') AND (liketbl.user_id IN ($impid))")->result();
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
						$reguserdetail = $this->db->query("SELECT liketbl.user_id,liketbl.like_id,liketbl.like_hint,liketbl.contact_name,liketbl.contact_no FROM `mtjf_user_like_unlike` as liketbl INNER JOIN mtjf_user ON liketbl.contact_no IN (user_contact,CONCAT(user_country_id,user_contact),CONCAT('0','',user_contact)) where mtjf_user.user_id IN ($cc) AND liketbl.user_id = '$user_id'")->result(); //register user
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
						
						$seluservote = $this->db->query("SELECT contact_no,contact_name,like_hint,like_id,contact_name FROM mtjf_user_like_unlike WHERE contact_no IN ($imcont) AND user_id = '$user_id'")->result();
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
            	$listsel = $this->db->query("SELECT contact_no,contact_name,second_user_id FROM mtjf_user_like_unlike WHERE user_id = '$user_id'")->result();
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
	                			$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$usrname->user_id."') AND (contact_no = '".$mycoountry.$mycontact."' OR contact_no = '$mycontact')")->row();
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
				$seluservote = $this->db->query("SELECT like_id,user_id,like_hint FROM mtjf_user_like_unlike WHERE (contact_no LIKE '%".$user_country.$user_cont_num."%' OR contact_no LIKE '%".$user_cont_num."%' OR second_user_id = '$user_id') AND (like_hint != '')")->result(); //OLD
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
						$contsele = $this->db->query("SELECT like_id,like_hint FROM mtjf_user_like_unlike WHERE contact_no LIKE '%".$contactno."%' AND user_id = '$user_id'")->row();
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
	    	$listsel = $this->db->query("SELECT likes.contact_no,likes.contact_name,likes.second_user_id,likes.like_hint,likes.like_id,mtjf_user.user_id,mtjf_user.user_name,mtjf_user.user_country_id,mtjf_user.user_contact,mtjf_user.user_image FROM mtjf_user_like_unlike as likes LEFT JOIN mtjf_user ON likes.contact_no IN (CONCAT(mtjf_user.user_country_id,mtjf_user.user_contact),mtjf_user.user_contact) WHERE likes.user_id = '$user_id' ")->result();
	    	if(!empty($listsel))
        	{  
			 	foreach ($listsel as $values)
                { 
            			//$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$usrname->user_id."') AND (contact_no = '".$usrname->user_country_id.$usrname->user_contact."' OR contact_no = '".$usrname->user_contact."')")->row();
                	//$status = '';
    				$image='';
    				if($values->user_id != '' && $values->user_id!= NULL)
    				{
    					$selmat = $this->db->query("SELECT like_id FROM mtjf_user_like_unlike WHERE (user_id = '".$values->user_id."') AND (second_user_id = '".$user_id."' OR contact_no = '".$my_country.$myno."' OR contact_no = '".$myno."')")->row();
    					
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
							$getdevice_token = $this->db->query("SELECT `user_device_token`,`user_device_type` FROM mtjf_user WHERE 
								CONCAT(user_country_id,user_contact) = '".$data->contact_no."'")->row();

						  	$msg = array('title'=>'new message','msg'=>$data->message,'message_id'=>$insert,'old_message_id'=>$data->message_id,'image'=>'','type'=>1,'match_type'=>0,'like_id'=>$data->like_id,'user_id'=>$data->second_user_id,'contact_no'=>$aa['data']->user_country_id.$aa['data']->user_contact,'create_at'=>militime);
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
            		$userdata = $this->db->query("SELECT user_id,user_name,user_device_token,user_device_type FROM mtjf_user WHERE CONCAT(user_country_id,user_contact) = '".$data->full_mobile_number."' AND user_status = 1")->row();
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
	           			$checkreqesttwo = $this->common_model->common_getRow("mtjf_user_interest",array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id,'interest_id'=>$data->interest_id),array(),'id','DESC',array(),'1'); 		
	           			$checkreqestone = $this->common_model->common_getRow("mtjf_user_interest",array('user_id'=>$user_id,'contact_user_id'=>$userdata->user_id,'interest_id'=>$data->interest_id),array(),'id','DESC',array(),'1'); 		
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
			                        $msg = "you can't performe this action.";
			                        $res = $checkreqestone->status;
	            				}
	            			}elseif($type==2)
	            			{
	            				$res = 2;
	            				if($checkreqestone->response == 3)
	            				{
	            					//update\
	            					$insertt = $this->common_model->updateData("mtjf_user_interest",array('status'=>2,'update_date'=>date('Y-m-d H:i:s')),array('id'=>$checkreqestone->id));
	            					$newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id));
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
	            				$massage = 'interested in '.$interestmsg[$data->interest_id].' with '.$aa['data']->user_name;
	            				$new_wallet = $new_wallet-$dedamt;
	            				$updatewallet = $this->common_model->updateData("mtjf_user",array('user_wallet'=>$new_wallet,'update_date'=>date("Y-m-d H:i:s")),array('user_id'=>$user_id));
								$insertcoin = $this->db->insert("coin_history",array('user_id'=>$user_id,'actions'=>'-','msg'=>$massage,'coin'=>$dedamt,'coin_balance'=>$new_wallet,'create_date'=>date('Y-m-d H:i:s')));
	            				$type = $data->interest_id+3;
	            				if($res==3)
		            			{
	            					$massage = 'You have a '.$interestmsg[$data->interest_id].' Match with '.$aa['data']->user_name;
					        	    $newinsertt = $this->common_model->updateData("mtjf_interest_data_store",array('response'=>$res,'status'=>$res,'interest_'.$data->interest_id =>$res,'update_date'=>date('Y-m-d H:i:s')),array('user_id'=>$userdata->user_id,'contact_user_id'=>$user_id));
		            				$mtype= 1;
	            					$msgss = array('title'=>'New Match for '.$interestmsg[$data->interest_id],'msg'=>$massage,'image'=>'','type'=>$type,'match_type'=>1,'create_at'=>militime);
		            			}else
		            			{
	            					if($aa['data']->user_gender==1) { $gender = 'he'; }elseif($aa['data']->user_gender==2){ $gender = 'she'; }else{ $gender = '<he/she>'; }
	            					$massage = $aa['data']->user_name.' has shown interest in some activity. Swipe on the activity you think '.$gender.' is interested in to get a Match!';
		            				$mtype= 0;
	            					$msgss = array('title'=>'You have new interest' ,'msg'=>$massage,'image'=>'','type'=>$type,'match_type'=>0,'create_at'=>militime);
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
					$date = date('d F Y, h:i A');
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
