<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

	class Common_model extends CI_Model {
	public function __construct()
    {
        parent::__construct();
	}
	
	public function common_insert($tbl_name = false, $data_array = false)
	{
		$ins_data = $this->db->insert($tbl_name, $data_array);
		if($ins_data){
			return $last_id = $this->db->insert_id();
		}
		else{
			return false;
		}
	}

	public function updateData($table,$data,$where_array)
	{ 
	    $this->db->where($where_array);
		if($this->db->update($table,$data)){
			//print_r($this->db->last_query()); exit;
			return true;
		}
		else{
			//print_r($this->db->last_query()); exit;
			return false;
		}
	}

	// Function for select data
	public function getData($table,$where='', $order_by = false, $order = false, $join_array = false, $limit = false)
	{
		//$this->db->select('*');
		$this->db->from($table);

		if(!empty($where))
		{
			$this->db->where($where);
		}
		
		if(!empty($order_by))
		{
			$this->db->order_by($order_by, $order); 	
		}



		if(!empty($join_array))
		{
			foreach ($join_array as $key => $value) {

				$this->db->join($key, $value); 	
			}
			
		}

		if(!empty($limit))
		{
			$this->db->limit($limit); 	
		}

		$result = $this->db->get();
		

		//print_r($this->db->last_query()); exit;
		return $result->result();
		//return $result;
	}

	// Function for select data
	public function getDataField($field = false, $table, $where='', $order_by = false, $order = false, $join_array = false, $limit = false, $join_type = false )
	{
		$this->db->select($field);

		$this->db->from($table);

		if(!empty($where))
		{
			$this->db->where($where);
		}
		
		if(!empty($order_by))
		{
			$this->db->order_by($order_by, $order); 	
		}



		if(!empty($join_array))
		{
			foreach ($join_array as $key => $value) {

				if(!empty($join_type))
					$this->db->join($key, $value, 'left');
				else
					$this->db->join($key, $value); 	
			}
			
		}

		if(!empty($limit))
		{
			$this->db->limit($limit); 	
		}

		$result = $this->db->get();
		

		//print_r($this->db->last_query()); exit;
		return $result->result();
		//return $result;
	}

	public function common_getRow($tbl_name = flase, $where = false, $join_array = false)
	{
		$this->db->select('*');
		$this->db->from($tbl_name);
		
		if(isset($where) && !empty($where))
		{
			$this->db->where($where);	
		}
		
		if(!empty($join_array))
		{
			foreach($join_array as $key=>$value){
				$this->db->join($key,$value);
			}	
		}
		
		$query = $this->db->get();
		
		$data_array = $query->row();
		//print_r($this->db->last_query()); exit;
		if($data_array)
		{
			return $data_array;
		}
		else{
			return false;
		}
	}
	public function deleteData($table,$where)
	{ 
		$this->db->where($where);
		if($this->db->delete($table))
		{
			return true;
		}
		else{
			return false;
		}
	}
	
	public function sqlcount($table = false,$where = false)
	{
		$this->db->select('*');	
		$this->db->from($table); 
		if(isset($where) && !empty($where))
		{
			$this->db->where($where);	
		}
		//$this->db->limit($limit, $start);       
		$query = $this->db->get();
		//print_r($this->db->last_query()); exit;
		return $query->num_rows(); 
	}

	public function milliseconds() // Id Decryption
	{
		$str = round(microtime(true) * 1000);
		return ($str);
	}
	
	public function randomuniqueCode() 
	{
	    $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789";
	    $pass = array(); /*remember to declare $pass as an array*/
	    $alphaLength = strlen($alphabet) - 1; /*put the length -1 in cache*/
	    for ($i = 0; $i < 6; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); /*turn the array into a string*/
	}

	public function random_number() 
	{
	    $alphabet = "123456789";
	    $pass = array(); /*remember to declare $pass as an array*/
	    $alphaLength = strlen($alphabet) - 1; /*put the length -1 in cache*/
	    for ($i = 0; $i < 6; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass); /*turn the array into a string*/
	}

	function sendPushNotification($registration_ids, $message)
	{
	  
	   $url = 'https://fcm.googleapis.com/fcm/send';
	   $fields = array(
	       'registration_ids' => array($registration_ids),
	       'data' => $message,
	   );
	   $headers = array(
	       'Authorization:key=' . GOOGLE_API_KEY,
	       'Content-Type: application/json'
	   );

	   $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url); 
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	   // curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

	    $result = curl_exec($ch);
	    curl_close($ch);
	 
	   //return $result;
	  //echo $result; exit;
	}

	function sendNotification($registration_ids, $message)
	{
	  // echo '<pre>';	
	  // print_r($registration_ids);exit;
	   $url = 'https://fcm.googleapis.com/fcm/send';
	   $fields = array(
	       'registration_ids' => $registration_ids,
	       'data' => $message
	   );

	   // echo '<pre>';
	   // print_r($fields);exit;
	   $headers = array(
	       'Authorization:key=' . GOOGLE_API_KEY,
	       'Content-Type: application/json'
	   );

	   $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url); 
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	   // curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

	    $result = curl_exec($ch);
	    curl_close($ch);
	 
	   //return $result;
	  //$result; 
	}
	function iosnotification($deviceToken, $msg ) // type = 1 = user and 2 = guide
	{ 

	  $passphrase = "";
	  // $payload['aps'] = array(
	  //   'alert' => array(
	  //                 "title"=>$msg,
	  //                 "body"=>$message
	  //                 ),
	  //   'badge' => 1, 
	  //   'type' => $type,
	  //   'refer_count' => $code,
	  //   'sound' => 'default'
	  // );   
	  if($msg['type']!=1)
		{
			$msg['message_id'] = '';
			$msg['old_message_id'] = '';
			$msg['like_id'] = '';
			$msg['user_id'] = '';
			$msg['contact_no'] = '';
		}
	$payload['aps'] = array(
				'alert' => array(
					'title'=>$msg['title'],
					'body'=>$msg['msg']
					),
				'badge' => 1, 
				'sound' => 'default',
				'message_id'=>$msg['message_id'],
				'like_id'=>$msg['like_id'],
				'user_id'=>$msg['user_id'],
				'image'=>$msg['image'],
				'contact_no'=>$msg['contact_no'],
				'old_message_id'=>$msg['old_message_id'],
				'type'=>$msg['type'],
				'match_type'=>$msg['match_type'],
				'create_at'=>$msg['create_at']
			);
	// print_r($payload);exit;
	  $payload = json_encode($payload);
	  $apnsHost = 'gateway.sandbox.push.apple.com';    
	  $apnsPort = 2195;
	  $apnsCert = base_url().'pemFile/mtjf_dev.pem';
	  //print_r($apnsCert);exit;
	  $streamContext = stream_context_create();
	  
	  stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
	  stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);
	  //stream_context_set_option($streamContext, 'ssl', 'cafile', 'entrust_2048_ca.cer');
	  $apns = stream_socket_client('ssl://' . $apnsHost . ':' . 
	  $apnsPort,$error,$errorString,60,STREAM_CLIENT_CONNECT,$streamContext); 
	  $apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', $deviceToken) . chr(0) . chr(strlen($payload)) . $payload;
	  $result = fwrite($apns, $apnsMessage);
	  @socket_close($apns);
	  fclose($apns);
	}

	function ios_notification($token,$msg)
	{
		// if($msg['type']!=1)
		// {
		// 	$msg['message_id'] = '';
		// 	$msg['old_message_id'] = '';
		// 	$msg['like_id'] = '';
		// 	$msg['user_id'] = '';
		// 	$msg['contact_no'] = '';
		// }
		//print_r($msg);die();
		$this->load->library('ios');
        $this->ios->message($msg);
        $this->ios->to($token);
		$aa = $this->ios->send();
	}

	function encryptor_ym($action, $string) {
	    $output = false;

	    $encrypt_method = "AES-256-CBC";
	    //pls set your unique hashing key
	    $secret_key = 'muni';
	    $secret_iv = 'muni123';

	    // hash
	    $key = hash('sha256', $secret_key);

	    // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
	    $iv = substr(hash('sha256', $secret_iv), 0, 16);

	    //do the encyption given text/string/number
	    if( $action == 'encrypt' ) {
	        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
	        $output = base64_encode($output);
	    }
	    else if( $action == 'decrypt' ){
	    	//decrypt the given text/string/number
	        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
	    }

	    return $output;
	}
	
	function sms_send($mobileNumber,$message1) //only for OTP
	{
		$authKey = "191327AqVWRIYT5a4df639";
		//$mobileNumber = "9999999";
		$senderId = "MTJFap";
		$message = urlencode($message1);
		//Define route 
		$route = "4";
		$postData = array(
			'authkey' => $authKey,
			'mobiles' => $mobileNumber,
			'message' => $message,
			'sender' => $senderId,
			'route' => $route
		);
		$url="https://control.msg91.com/api/sendhttp.php";
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postData
			//,CURLOPT_FOLLOWLOCATION => true
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
		//get response
		$output = curl_exec($ch);
		if(curl_errno($ch))
		{
			echo 'error:' . curl_error($ch);
		}
		curl_close($ch);
	   // echo $output;exit;
	}

	function other_sms_send($mobileNumber,$message1) //for other massage
	{
		$authKey = "191331AHecTjzHB4K5a4dfa68";
		//$mobileNumber = "9999999";
		$senderId = "MTJFap";
		$message = urlencode($message1);
		//Define route 
		$route = "4";
		$postData = array(
			'authkey' => $authKey,
			'mobiles' => $mobileNumber,
			'message' => $message,
			'sender' => $senderId,
			'route' => $route
		);
		$url="https://control.msg91.com/api/sendhttp.php";
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postData
			//,CURLOPT_FOLLOWLOCATION => true
		));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	
		//get response
		$output = curl_exec($ch);
		if(curl_errno($ch))
		{
			echo 'error:' . curl_error($ch);
		}
		curl_close($ch);
	   	// echo $output;exit;
	}

	function twilio_sms($mobilenum,$msg)
	{
		$this->load->library('twilio');

		$from = '+919975178217';
		// $to = '+918109059062';
		// $message = 'This is a test MTJF'; 
		$response = $this->twilio->sms($from, $mobilenum, $msg);
		//print_r($response);exit;
		// if($response->IsError)
		// 	echo 'Error: ' . $response->ErrorMessage;
		// else
		// 	echo 'Sent message to ' . $to;
	}

}

?>