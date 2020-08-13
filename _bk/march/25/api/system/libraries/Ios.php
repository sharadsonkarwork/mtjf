<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Usage: $this->ios->to('DEVICE_ID')->badge(3)->message('Hello world');
class CI_Ios
{

	private $host = 'gateway.push.apple.com';
	private $port = 2195;
	private $cert = 'pemFile/mtjf_pro.pem';
	private $new_cert = 'pemFile/entrust_2048_ca.cer';
	
	private $device = NULL;
	private $message = NULL;
	private $badge = NULL;
	private $sound = 'default';
	
	private $_CI;
	
	public function __construct()
	{
		$this->_CI =& get_instance();
		
		// $this->_CI->config->load('ios');

		// $config = $this->_CI->config->item('ios');
		
		/*foreach ($config as $key => $value)
		{
			$this->$key = $value;
		}*/
	}
	
	public function to($device)
	{
		$this->device = $device;
		
		return $this;
	}
	
	public function message($message)
	{
		$this->message = $message;
		
		return $this;
	}
	
	public function badge($badge = 1)
	{
		$this->badge = $badge;
		return $this;
	}
	
	public function sound($sound = 'default')
	{
		$this->sound = $sound;
		
		return $this;
	}

	public function send()
	{
		$passphrase = "";
		// Build the payload
		$msg = $this->message;
		
		if($msg['type']==1)
		{
			$payload['aps'] = array('alert' => array('title'=>$msg['title'],'body'=>$msg['msg']),'badge' => 1, 'sound' => $this->sound,'user_contact'=>$msg['contact_no'],'type'=>$msg['type'],'user_name'=>$msg['contact_name'],'like_id'=>$msg['like_id'],'s_id'=>$msg['user_id'],'match_type'=>$msg['match_type'],'create_at'=>militime);
		}else
		{
			$payload['aps'] = array('alert' => array('title'=>$msg['title'],'body'=>$msg['msg']),'badge' => 1, 'sound' => $this->sound,'user_contact'=>$msg['contact_no'],'type'=>$msg['type'],'match_type'=>$msg['match_type'],'create_at'=>militime);
		}

	
		//$payload1 = '{"aps":{"alert":{"title":'.$msg['title'].',"body":'.$msg['msg'].'},"badge":"+1","sound":'.$this->sound.',"image":'.$msg['image'].',"type":'.$msg['type'].',"match_type":'.$msg['match_type'].',"create_at":'.militime.'}}';
		///print_r($payload);exit;
		 $payload1 = json_encode($payload);
		
		$stream_context = stream_context_create();
		stream_context_set_option($stream_context, 'ssl', 'local_cert', $this->cert);
		stream_context_set_option($stream_context, 'ssl', 'passphrase', $passphrase);
		stream_context_set_option($stream_context, 'ssl', 'cafile', $this->new_cert);
		$apns = stream_socket_client('ssl://' . $this->host . ':' . $this->port, $error, $error_string, 60, STREAM_CLIENT_CONNECT, $stream_context);

		$message = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $this->device)) . chr(0) . chr(strlen($payload1)) . $payload1;
		$result = fwrite($apns, $message);
	  // print_r($result);
		@socket_close($apns);
		fclose($apns);
	}
}