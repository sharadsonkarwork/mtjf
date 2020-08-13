<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Twilio_demo extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		$this->load->library('twilio');

		$from = '+919975178217';
		$to = '+918109059062';//+994505522228
		$message = 'This is a testing msg'; 
		///$response = $this->twilio->sms($from, $to, $message);


		if($response->IsError)
			echo 'Error: ' . $response->ErrorMessage;
		else
			echo 'Sent message to ' . $to;
	}

}

/* End of file twilio_demo.php */