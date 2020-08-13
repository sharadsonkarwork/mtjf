 <?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		if(!$userid = $this->session->userdata('admin_id')){
			redirect(base_url('login'));
		}
		date_default_timezone_set('Asia/Kolkata');
	}
	
	public function category()
	{ 
	   $data['category_data'] = $this->common_model->getData('categories');
	   
	   $this->load->view('admin/category/show_category',$data);
	}
	public function add_category()
	{ 
	   if($this->input->server('REQUEST_METHOD') === 'POST')
	   { 
	   	  $category = array(
					'category_name' =>$this->input->post('category'),
					'category_image'=>$this->input->post('category_image'),
					'category_alias'=>$this->input->post('category_alias')
					);

	   	  $insert = $this->common_model->common_insert('categories',$category);
	   	  if($insert)
	   	  {
	   	  	 redirect(base_url().'setting/category');
	   	  }	

	   }
	   
	}
	public function details($company_id=false)
	{
     $data['user_data'] =  $this->common_model->getData('users',array('user_type'=>'2'));
     $this->load->view('admin/user/show_user',$data);

	}
	public function  delete_developer($company_id=false)
	{  
	$company_delete = $this->common_model->deleteData('company_tb',array('company_id'=>$company_id));
	
	if($company_delete)
	{
		echo "1000"; exit;
	}

	}
	
}
