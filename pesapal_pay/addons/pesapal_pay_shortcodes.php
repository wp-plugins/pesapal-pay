<?php
/**
 * Payment form
 *
 */
add_shortcode('pesapal_pay_payment_form', 'pesapal_pay_payment_form');
function pesapal_pay_payment_form($atts){
	global $pesapal_pay;
	extract(shortcode_atts(array(
				'button_name' => 'Buy Using Pesapal',
				'amount' => '10'), $atts));
	$options = $pesapal_pay->get_options();
	$output = '<form id="pesapal_checkout" class="pesapal_payment_form">
			<input type="hidden" name="ppform" id="ppform" value="ppform"/>
			<input type="hidden" name="ajax" value="true" />
			<input type="hidden" name="action" value="pesapal_save_transaction"/>
			<input type="hidden" name="ppamount" value="'.$amount.'"/>
			<fieldset>
				<legend> User Details</legend>
				<div class="control-group">
					<label>First Name</label>		
					<div><input type="text" size="40" class="required" value="" id="ppfname" name="ppfname"></div>
				</div>
				<div class="control-group">
					<label>Last Name</label>		
					<div><input type="text" size="40" class="required" value="" id="pplname" name="pplname"></div>
				</div>
				<div class="control-group">
					<label>Email</label>		
					<div><input type="text" class="required" value="" id="ppemail" name="ppemail"></div>
				</div>	
				<div class="control-group">
					<label>Amount</label>		
					<div> '.$amount.' ('.$options['currency'].')</div>
				</div>				
			</fieldset>	 	 
		</form>
	<button name="pespal_pay" id="pespal_pay_btn">'.$button_name.'</button>';
	
	$output .= '<script type="text/javascript">';
	$output .= 'jQuery(document).ready(function(){';
	$output .= 'jQuery("#pespal_pay_btn").click(function(){';
	$output .= 'jQuery("#pespal_pay_btn").val("Processing......");';
	$output .= 'jQuery.ajax({';
	$output .= 'type: "POST",';
	$output .= 'data: jQuery("#pesapal_checkout").serialize(),';
	$output .= 'url: "'.admin_url('admin-ajax.php').'",';
	$output .= 'success:function(data){';
	if($options['full_frame'] === 'true'){
		$output .= 'jQuery("body").html(data)';
	}else{
		$output .= 'jQuery("#pesapal_checkout").parent().html(data)';
	}
	$output .= '}';
	$output .= '})';
	$output .= '});';
	$output .= '});';
	$output .= '</script>';
	return $output;
}

/**
 * Shortcode
 */
add_shortcode('pesapal_pay_button', 'pesapal_pay_button');
function pesapal_pay_button($atts){
	global $pesapal_pay;
	$invoice = $pesapal_pay->generate_order_id();
	$user_email = get_bloginfo( 'admin_email' );
	extract(shortcode_atts(array(
				'button_name' => 'Pay Using Pesapal',
				'amount' => '10',
				'use_options' => 'false'), $atts));
	$options = $pesapal_pay->get_options();
	$formid= mt_rand();
	if($use_options === 'false'){
		
		$output = '<form id="pesapal_checkout_'.$formid.'">
					<input type="hidden" name="'.$options['form_invoice'].'" value="'.@$_REQUEST[$options['form_invoice']].'"/>
					<input type="hidden" name="'.$options['form_email'].'" value="'.@$_REQUEST[$options['form_email']].'"/>
					<input type="hidden" name="'.$options['form_cost'].'" value="'.@$_REQUEST[$options['form_cost']].'"/>
					<input type="hidden" name="ajax" value="true" />
					<input type="hidden" name="action" value="pesapal_save_transaction"/>
					</form>
					<button name="pespal_pay_'.$formid.'" id="pespal_pay_btn_'.$formid.'">'.$button_name.'</button>';
	}else{
		$output = '<form id="pesapal_checkout_'.$formid.'">
					<input type="hidden" name="'.$options['form_invoice'].'" value="'.$invoice.'"/>
					<input type="hidden" name="'.$options['form_email'].'" value="'.$user_email.'"/>
					<input type="hidden" name="'.$options['form_cost'].'" value="'.$amount.'"/>
					<input type="hidden" name="ajax" value="true" />
					<input type="hidden" name="action" value="pesapal_save_transaction"/>
					</form>
					<button name="pespal_pay_'.$formid.'" id="pespal_pay_btn_'.$formid.'" class="pesapal_btn">'.$button_name.'</button>';
	}
	$output .= '<script type="text/javascript">';
	$output .= 'jQuery(document).ready(function(){';
	$output .= 'jQuery("#pespal_pay_btn_'.$formid.'").click(function(){';
	$output .= 'jQuery("#pespal_pay_btn_'.$formid.'").val("Processing......");';
	$output .= 'jQuery.ajax({';
	$output .= 'type: "POST",';
	$output .= 'data: jQuery("#pesapal_checkout_'.$formid.'").serialize(),';
	$output .= 'url: "'.admin_url('admin-ajax.php').'",';
	$output .= 'success:function(data){';
	if($options['full_frame'] === 'true'){
		$output .= 'jQuery("body").html(data)';
	}else{
		$output .= 'jQuery("#pesapal_checkout_'.$formid.'").parent().parent().html(data)';
	}
	$output .= '}';
	$output .= '})';
	$output .= '});';
	$output .= '});';
	$output .= '</script>';
	
	return $output;
}
 


//PesaPal Donate Shortcode
add_shortcode('pesapal_donate', 'pesapal_pay_donate');

/**
 * Generate PesaPal Donate box
 */
function pesapal_pay_donate($text){
	global $pesapal_pay;
	$invoice = $pesapal_pay->generate_order_id();
	$options = $pesapal_pay->get_options();
	$content = '<form id="pesapal_donate_widget">';
	
	$content .= '<div class="pesapal_pay_widget_table">';
	$content .= '<fieldset>';
	if(!empty($text)){
		$content .= '<div class="control-group">';
		$content .= $text;
		$content .= '</div>';
	}
	$content .= '<div class="control-group">';
	$content .= '<label>';
	$content .= __("Email :");
	$content .= '</label><br/>';
	$content .= '<div><input type="text" name="pesapal_donate_email" id="pesapal_donate_email" value=""/>';
	$content .= '</div>';
	$content .= '</div>';
	
	$content .= '<div class="control-group">';
	$content .= '<label>';
	$content .= __("Amount : ");
	$content .= '('.$options['currency'].')';
	$content .= '</label><br/>';
	$content .= '<div><input type="text" name="pesapal_donate_amount" id="pesapal_donate_amount" value=""/>';
	$content .= '</div>';
	$content .= '</div>';
	
	$content .= '</fieldset>';
	$content .= '</div>';
	$content .= '<input type="hidden" name="pesapal_donate_invoice" id="pesapal_donate_invoice" value="'.$invoice.'"/>';
	$content .= '<input type="hidden" name="ajax" value="true" />';
	$content .= '<input type="hidden" name="action" value="pesapal_save_transaction"/>';
	$content .= '</form>';
	$content .= '<button name="pespal_pay_donate" class="pesapal_btn" id="pespal_pay_donate">'.__("Donate Using PesaPal").'</button>';
	$content .= '<script type="text/javascript">';
	$content .= 'jQuery(document).ready(function(){';
	$content .= 'jQuery("#pespal_pay_donate").click(function(){';
	$content .= 'jQuery("#pespal_pay_donate").val("Processing......");';
	$content .= 'jQuery.ajax({';
	$content .= 'type: "POST",';
	$content .= 'data: jQuery("#pesapal_donate_widget").serialize(),';
	$content .= 'url: "'.admin_url('admin-ajax.php').'",';
	$content .= 'success:function(data){';
	if($options['full_frame'] === 'true'){
		$content .= 'jQuery("body").html(data)';
	}else{
		$content .= 'jQuery("#pesapal_donate_widget").parent().html(data)';
	}
	$content .= '}';
	$content .= '})';
	$content .= '});';
	$content .= '});';
	$content .= '</script>';
	return $content;
}
	
/**
 * Verify a transaction is paid for. This is to secure the page content
 *
 */
add_shortcode('pesapal_verify_transaction', 'pesapal_verify_transaction');
function pesapal_verify_transaction($atts, $content = null){
	global $pesapal_pay;
	$transactionid = $_REQUEST['id']; //Get the id of the invoice
	$transaction = $pesapal_pay->get_transaction($transactionid);
	if ($transaction->post_status == 'order_paid' ){
		return $content;
	}else{
		return "Transaction not yet verified";
	}
}
?>