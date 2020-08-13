<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class MY_Controller extends CI_Controller {
	
	public $data = array();

	public function __construct()
	{
		parent::__construct();
	}

	

	public function randno($tot=false)
	{
		if($tot=='')
		{
			$tot=6;	
		}
		return $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $tot);	
	}
	/*function calculate_discount($referral_code,$product_id,$qty)
	{
  		$dode_dd=$this->db->query("SELECT product_to_interior_designer.* FROM product_to_interior_designer  LEFT JOIN product_tb ON product_tb.brand=product_to_interior_designer.brand_id WHERE product_to_interior_designer.coupon_code='$referral_code' AND product_tb.product_id='$product_id'");
		$code_data=$dode_dd->row();
      	if($code_data)
      	{
      		$pro_data = $this->common_model->common_getRow('product_tb',array('product_id'=>$product_id));
	   		$discount=$code_data->discount;
	   		$price=$pro_data->price;
	   		$price_base=$pro_data->price_base;

	   		if($code_data->discount_type==0)
	   		{
	   			$discount_amount = (($price*$discount)/100);
	   			$price_after_discounted = $price-$discount_amount;
	   			if($price_after_discounted <= $price_base)
	   			{
	   				return $final_discount = $price_base*$qty;
	   			}else
	   			{
	   				return $final_discount = $discount_amount*$qty;
	   			}
	   		}else
	   		{
	   			return $final_discount = $discount*$qty;
	   		}
      	}
	}	*/
}
