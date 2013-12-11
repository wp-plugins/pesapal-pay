<?php
/*
Plugin Name: Pesapal Pay
Description: A quick way to integrate pesapal to your website to handle the payment process. All you need to do is set up what parameters to capture from the form and the plugin will do the rest
Version: 1.2.3
Author: rixeo
Author URI: http://thebunch.co.ke/
Plugin URI: http://thebunch.co.ke/
*/

define('PESAPAL_PAY_PLUGIN_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
define('PESAPAL_PAY_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)));


require_once(PESAPAL_PAY_PLUGIN_DIR.'/OAuth.php');
require_once(PESAPAL_PAY_PLUGIN_DIR.'/pesapal_pay_donate_widget.php');

/**
 * Set up database
 */
add_action( 'init', 'pesapal_pay_setup_database');
function pesapal_pay_setup_database(){
	global $wpdb;
	$table_name = $wpdb->prefix."pesapal_pay";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
		$sql =  "CREATE TABLE `$table_name` (
		`id` INT( 5 ) NOT NULL AUTO_INCREMENT,
		`invoice` VARCHAR(50) NOT NULL,
		`date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		`email` VARCHAR(100) NOT NULL,
		`total` FLOAT NOT NULL,
		`payment_status` ENUM ('Pending', 'Paid', 'Canceled'),
		UNIQUE (`invoice`),
		PRIMARY KEY  (id)
		)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

/**
 * Load Resources
 */
add_action( 'init', 'pesapal_pay_resources');
function pesapal_pay_resources(){
	wp_enqueue_script('pesapal_pay_js', PESAPAL_PAY_PLUGIN_URL.'/pesapal_pay.js', array('jquery'), '', false);
	wp_enqueue_script("pesapal_pay_js");
	wp_localize_script('pesapal_pay_js', 'p_pay_js', array( 'p_pay_url' => get_bloginfo('url') , 'ajaxurl' => admin_url('admin-ajax.php')) );
}
/**
 * Create Admin menu
 */
add_action('admin_menu', 'pesapal_pay_create_admin_menu');
function pesapal_pay_create_admin_menu(){
	add_object_page(__('Pesapal Pay'), __('Pesapal Pay'), 'edit_others_posts', 'pesapal-pay', '', PESAPAL_PAY_PLUGIN_URL . '/pesapal_pay.png');
	add_submenu_page('pesapal-pay', __('Settings'), __('Settings'), 'edit_others_posts', 'pesapal-pay', 'pesapal_pay_setup');
	add_submenu_page('pesapal-pay', __('Payment Log'), __('Payment Log'), 'edit_others_posts', 'pesapal-pay-payment-log', 'pesapal_pay_payment_log');
}




/**
 * Admin options
 */
function pesapal_pay_setup(){
	if(@$_POST['pesapal_settings']){
		$required_fields = array(
								'customer_key' => '',
								'customer_secret' => '',
								'sandbox' => '',
								'form_invoice' => '',
								'form_email' => '',
								'form_cost' => '',
								'form_function' => '',
								'thankyou_page' => '');
		$required_fields['customer_key'] = $_POST['customer_key'];
		$required_fields['customer_secret'] = $_POST['customer_secret'];
		$required_fields['sandbox'] = $_POST['sandbox'];
		$required_fields['form_invoice'] = $_POST['form_invoice'];
		$required_fields['form_email'] = $_POST['form_email'];
		$required_fields['form_cost'] = $_POST['form_cost'];
		$required_fields['form_function'] = $_POST['form_function'];
		$required_fields['thankyou_page'] = $_POST['thankyou_page'];
		update_option('pesapal_pay_setup', $required_fields);
	}
	$options = get_option('pesapal_pay_setup');
	?>
	<div class="wrap">
		<h2><?php _e("Pesapal Pay Settings"); ?></h2>
		<form method="POST" action="">
			<table class="widefat">
				<tr>
				    <th scope="row"><?php _e('PesaPal Checkout') ?></th>
				    <td>
						<p>
							<?php _e('PesaPal requires Full names and email/phone number. To handle APN return requests, please set the url '); ?>
							<strong><?php echo get_bloginfo('url').'?pesapal_ipn_return=true' ; ?></strong>
							<?php _e(' on your <a href="https://www.pesapal.com/merchantdashboard" target="_blank">pesapal</a> account settings'); ?>
						</p>
						
				    </td>
				</tr>
				<tr>
				    <th scope="row"><?php _e('PesaPal Merchant Credentials'); ?></th>
				    <td>
						<p>
							<label><?php _e('Use PesaPal Sandbox'); ?><br />
							  <input value="checked" name="sandbox" type="checkbox" <?php echo ($options['sandbox'] == 'checked') ? "checked='checked'": ""; ?> />
							</label>
						</p>
						<p>
							<label><?php _e('Customer Key') ?><br />
							  <input value="<?php echo $options['customer_key']; ?>" size="30" name="customer_key" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('Customer Secret') ?><br />
								 <input value="<?php echo $options['customer_secret']; ?>" size="30" name="customer_secret" type="text" />
							</label>
						</p>
				    </td>
				</tr>
				<tr>
					<th scope="row"><?php _e('PesaPal Form Settings. These are the names of the fields to be used by the gateway'); ?></th>
					<td>
						<p>
							<label><?php _e('Invoice Form name'); ?><br />
							  <input value="<?php echo $options['form_invoice']; ?>" size="30" name="form_invoice" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('Email Form name'); ?><br />
							  <input value="<?php echo $options['form_email']; ?>" size="30" name="form_email" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('Total Cost Form name') ?><br />
							  <input value="<?php echo $options['form_cost']; ?>" size="30" name="form_cost" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('Function to be called before payment'); ?> ( <?php _e('your function that is called before processing payment'); ?> ) <br />
								<input value="<?php echo $options['form_function']; ?>" size="30" name="form_function" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('Thank You page'); ?><br />
								<select name="thankyou_page">
									<?php 
									$pages = get_pages(); 
									foreach ( $pages as $pagg ) {
										$cont_selected = '';
										if (intval($options['thankyou_page']) === $pagg->ID) {
											$cont_selected = 'selected="selected"';
										}
										$option = '<option value="' .$pagg->ID. '" '.$cont_selected.'>';
										$option .= $pagg->post_title;
										$option .= '</option>';
										echo $option;
									}
									?>
								</select>
							</label>
						</p>
				    </td>
				</tr>
			</table>
			<p class="submit">
				<input class='button-primary' type='submit' name='pesapal_settings' value='<?php _e('Save Settings'); ?>'/><br/>
			</p>
		</form>
	</div>
	<?php
}

function pesapal_pay_payment_log(){
	global $wpdb;
	$table_name = $wpdb->prefix."pesapal_pay";
	echo '<a class="button add-new-h2" href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=pesapal-pay-payment-log&delete_all=true">Delete All</a>';
	if (@$_GET['delete_all'] === 'true') {
		$wpdb->query("DELETE FROM `{$table_name}`");
	}
	$sql = "SELECT * FROM {$table_name} ORDER BY `id` DESC";
	$pagenum = isset($_GET['paged']) ? $_GET['paged'] : 1;
	$per_page = 15;
	$action_count = count($wpdb->get_results($sql));
	$total = ceil($action_count / $per_page);
	$action_offset = ($pagenum-1) * $per_page;
	$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => ceil($action_count / $per_page),
			'current' => $pagenum
	));
	$sql .= " LIMIT {$action_offset}, {$per_page}";
	$results = $wpdb->get_results($sql);
	if (is_array($results) && count($results) > 0) {
		if ($page_links) {
			?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php
					$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
														number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
														number_format_i18n( min( $pagenum * $per_page, $action_count ) ),
														number_format_i18n( $action_count ),
														$page_links
														);
					echo $page_links_text;
					?>
				</div>
			</div>
			<?php
		}
		?>
		<table class="widefat post fixed">
			<thead>
				<tr>
					<th></th>
					<th><?php _e("Invoice Number");?></th>
					<th><?php _e("Email");?></th>
					<th><?php _e("Date");?></th>
					<th><?php _e("Amount");?></th>
					<th><?php _e("Status");?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$count = (($pagenum-1)*$per_page)+1;
					foreach ($results as $result) {
						?>
						<tr id="pesapal_order_<?php echo $result->id; ?>">
							<td><?php printf(__("%d"),$count);?></td>
							<td><?php printf(__("%s"),$result->email);?></td>
							<td>
								<?php echo $result->invoice;?>
								<p>
									<a href="javascript:void(null);" onclick="pesapal_pay_delete('<?php echo $result->id; ?>');"><?php _e("Delete");?></a>
								</p>
							</td>
							<td><?php printf(__("%s"),$result->date);?></td>
							<td><?php printf(__("%s"),$result->total);?></td>
							<td><span id="pesapal_order_status_<?php echo $result->id; ?>"><?php printf(__("%s"),$result->payment_status);?></span> <a href="javascript:void(null);" onclick="pesapal_pay_status('<?php echo $result->id; ?>','<?php echo $result->payment_status; ?>');"><?php _e("Change status"); ?></a></td>
						</tr>
						<?php
						$count++;
					}
				?>
			</tbody>
		</table>
		<?php
		if ($page_links) {
			?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php
					$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
														number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
														number_format_i18n( min( $pagenum * $per_page, $action_count ) ),
														number_format_i18n( $action_count ),
														$page_links
														);
					echo $page_links_text;
					?>
				</div>
			</div>
			<?php
		}
		?>
		<script type="text/javascript">
			function pesapal_pay_delete(id){
				jQuery.ajax({
					type: "POST",
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data: 'action=pesapal_delete_order&id=' + id,
					success:function(){
						jQuery('tr#pesapal_order_'+id).remove();
					}
				});
			}

			function pesapal_pay_status(id,status){
				jQuery.ajax({
					type: "POST",
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data: 'action=pesapal_change_order_status&id=' + id+'&current='+status,
					success:function(msg){
						jQuery('span#pesapal_order_status_'+id).html(msg);
					}
				});
			}
		</script>
		<?php
	}else{
		 ?>
		 <br/>
		 <h2><?php _e('No Records Found'); ?></h2>
		 <?php
	}
	
}

/**
 * Change status
 */
add_action('wp_ajax_pesapal_change_order_status', 'pesapal_change_order_status');
function pesapal_change_order_status() {
	global $wpdb;
	$table_name = $wpdb->prefix."pesapal_pay";
	$order_id = intval($_POST['id']);
	$current_status = $_POST['current'];
	if ($order_id > 0) {
		 if ($current_status === "Pending") {
            $updated_status = "Paid";
        }
        elseif ($current_status === "Paid"){
            $updated_status = "Canceled";
        }
        else {
            $updated_status = "Pending";
        }
		$query = "UPDATE {$table_name} SET `payment_status`='{$updated_status}' WHERE `id`={$order_id}";
		$wpdb->query($query);
	}
	
	die($updated_status);
}

/** 
 * Delete Order
 */
add_action('wp_ajax_pesapal_delete_order', 'pesapal_delete_order');
function pesapal_delete_order() {
	global $wpdb;
	$table_name = $wpdb->prefix."pesapal_pay";
	$order_id = intval($_POST['id']);
	if ($order_id > 0) {
		$query = "DELETE FROM {$table_name} WHERE `id`={$order_id}";
		$wpdb->query($query);
	}
	die();
}


/**
 * Shortcode
 */
add_shortcode('pesapal_pay_button', 'pesapal_pay_button');
function pesapal_pay_button($atts){
	extract(shortcode_atts(array(
				'button_name' => 'Pay Using Pesapal'), $atts));
	$options = get_option('pesapal_pay_setup');
	$output = '<form id="pesapal_checkout">
				<input type="hidden" name="'.$options['form_invoice'].'" value="'.@$_REQUEST[$options['form_invoice']].'"/>
				<input type="hidden" name="'.$options['form_email'].'" value="'.@$_REQUEST[$options['form_email']].'"/>
				<input type="hidden" name="'.$options['form_cost'].'" value="'.@$_REQUEST[$options['form_cost']].'"/>
				<input type="hidden" name="ajax" value="true" />
				<input type="hidden" name="action" value="pesapal_save_transaction"/>
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
	$output .= 'jQuery("#pesapal_checkout").parent().html(data)';
	$output .= '}';
	$output .= '})';
	$output .= '});';
	$output .= '});';
	$output .= '</script>';
	
	return $output;
}
 
/** 
 *	Generate Invoice ID
 */
function pesapal_pay_generate_order_id() {
	$order_id = date('yzB');
	$order_id = apply_filters( ' pesapal_pay_order_id', $order_id ); //Very important to make sure order numbers are unique and not sequential if filtering
	return $order_id;
}

//PesaPal Donate Shortcode
add_shortcode('pesapal_donate', 'pesapal_pay_donate');

/**
 * Generate PesaPal Donate box
 */
function pesapal_pay_donate($text){
	$content = '<form id="pesapal_donate_widget">';
	$content .= '<table class="pesapal_pay_widget_table">';
	if(!empty($text)){
		$content .= '<tr>';
		$content .= '<td>';
		$content .= $text;
		$content .= '</td>';
		$content .= '</tr>';
	}
	$content .= '<tr>';
	$content .= '<td>';
	$content .= __("Email :");
	$content .= '<br/>';
	$content .= '<input type="text" name="pesapal_donate_email" id="pesapal_donate_email" value=""/>';
	$content .= '</td>';
	$content .= '</tr>';
	$content .= '<tr>';
	$content .= '<td>';
	$content .= __("Amount :");
	$content .= '<br/>';
	$content .= '<input type="text" name="pesapal_donate_amount" id="pesapal_donate_amount" value=""/>';
	$content .= '</td>';
	$content .= '</tr>';
	$content .= '</table>';
	$content .= '<input type="hidden" name="pesapal_donate_invoice" id="pesapal_donate_invoice" value="'.pesapal_pay_generate_order_id().'"/>';
	$content .= '<input type="hidden" name="ajax" value="true" />';
	$content .= '<input type="hidden" name="action" value="pesapal_save_transaction"/>';
	$content .= '</form>';
	$content .= '<button name="pespal_pay_donate" id="pespal_pay_donate">'.__("Donate Using PesaPal").'</button>';
	$content .= '<script type="text/javascript">';
	$content .= 'jQuery(document).ready(function(){';
	$content .= 'jQuery("#pespal_pay_donate").click(function(){';
	$content .= 'jQuery("#pespal_pay_donate").val("Processing......");';
	$content .= 'jQuery.ajax({';
	$content .= 'type: "POST",';
	$content .= 'data: jQuery("#pesapal_donate_widget").serialize(),';
	$content .= 'url: "'.admin_url('admin-ajax.php').'",';
	$content .= 'success:function(data){';
	$content .= 'jQuery("#pesapal_donate_widget").parent().html(data)';
	$content .= '}';
	$content .= '})';
	$content .= '});';
	$content .= '});';
	$content .= '</script>';
	return $content;
}
	
	
/**
 * Save Transaction
 */
add_action( 'wp_ajax_nopriv_pesapal_save_transaction', 'pesapal_save_transaction');
add_action( 'wp_ajax_pesapal_save_transaction', 'pesapal_save_transaction');
function pesapal_save_transaction(){
	global $wpdb;
	$table_name = $wpdb->prefix."pesapal_pay";
	$options = get_option('pesapal_pay_setup');
	
	$post_url = 'https://www.pesapal.com/api/PostPesapalDirectOrderV4';
	
	$test_post_url = 'http://demo.pesapal.com/api/PostPesapalDirectOrderV4';
	
	$status_request = 'https://www.pesapal.com/api/querypaymentstatus';
	
	$test_status_request = 'https://demo.pesapal.com/api/querypaymentstatus';
	
	$form_function = $options['form_function'];
	if(function_exists ($form_function)){
		call_user_func($form_function);
	}
	
	//Form info
	
	if(@$_REQUEST['pesapal_donate_no_invoice']){
		$form_invoice =pesapal_pay_generate_order_id();
	}else{
		if(@$_REQUEST['pesapal_donate_invoice']){
			$form_invoice = $_REQUEST['pesapal_donate_invoice'];
		}else{
			$form_invoice = $_REQUEST[$options['form_invoice']];
		}
	}
	
	if(@$_REQUEST['pesapal_donate_email']){
		$form_email = $_REQUEST['pesapal_donate_email'];
	}else{
		$form_email = $_REQUEST[$options['form_email']];
	}
	
	if(@$_REQUEST['pesapal_donate_amount']){
		$form_cost = $_REQUEST['pesapal_donate_amount'];
	}else{
		$form_cost = $_REQUEST[$options['form_cost']];
	}
	
	
	$form_cost = floatval($form_cost);
	
	$sql = "INSERT INTO {$table_name}(`date`,`email`,`total`,`invoice`,`payment_status`) VALUES(now(), '{$form_email}', {$form_cost}, '{$form_invoice}','Pending')";
	
	$wpdb->query($sql);
	
	$return_path = get_page_link($options['thankyou_page']);
	$check_return_path = explode('?', $return_path);
	if (count($check_return_path) > 1) {
		$return_path .= '&id=' . $form_invoice.'&pesapal_ipn_return=true';
	} else {
		$return_path .= '?id=' . $form_invoice.'&pesapal_ipn_return=true';
	}
	
	$token = $params = NULL;
	$consumer_key = $options['customer_key'];
	$consumer_secret = $options['customer_secret'];
	$signature_method = new OAuthSignatureMethod_HMAC_SHA1();
	
	//get form details
	$desc = 'Your Order No.: '.$form_invoice;
	$type = 'MERCHANT';
	$reference = $form_invoice;
	$first_name = '';
	$fullnames = 
	$last_name = '';
	$email = $form_email;
	$username = $email; //same as email
	$phonenumber = '';//leave blank
	$payment_method = '';//leave blank
	$code = '';//leave blank
	
	$callback_url = $return_path; //redirect url, the page that will handle the response from pesapal.
	$post_xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PesapalDirectOrderInfo xmlns:xsi=\"http://www.w3.org/2001/XMLSchemainstance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" Amount=\"".$form_cost."\" Description=\"".$desc."\" Code=\"".$code."\" Type=\"".$type."\" PaymentMethod=\"".$payment_method."\" Reference=\"".$reference."\" FirstName=\"".$first_name."\" LastName=\"".$last_name."\" Email=\"".$email."\" PhoneNumber=\"".$phonenumber."\" UserName=\"".$username."\" xmlns=\"http://www.pesapal.com\" />";
	$post_xml = htmlentities($post_xml);
	
	$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
	//post transaction to pesapal
	$pp_post_url = $post_url;
	if($options['sandbox'] == 'checked'){
		$pp_post_url = $test_post_url;
	}
	$iframe_src = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $pp_post_url, $params);
	$iframe_src->set_parameter("oauth_callback", $callback_url);
	$iframe_src->set_parameter("pesapal_request_data", $post_xml);
	$iframe_src->sign_request($signature_method, $consumer, $token);
	
	$output = '<iframe src="'.$iframe_src.'" width="100%" height="620px"  scrolling="no" frameBorder="0" >';
	$output .= '</iframe>';
	echo $output;
	exit();
}


/**
 * Handle IPN
 */
if (@$_REQUEST['pesapal_ipn_return'] === 'true') {
    add_action('init', 'pesapal_ipn_return');
}
add_action( 'wp_ajax_nopriv_pesapal_ipn_return', 'pesapal_ipn_return');
add_action( 'wp_ajax_pesapal_ipn_return', 'pesapal_ipn_return');
function pesapal_ipn_return(){
	global $wpdb;
	$table_name = $wpdb->prefix."pesapal_pay";
	$options = get_option('pesapal_pay_setup');
	
	$post_url = 'https://www.pesapal.com/api/PostPesapalDirectOrderV4';
	
	$test_post_url = 'http://demo.pesapal.com/api/PostPesapalDirectOrderV4';
	
	$status_request = 'https://www.pesapal.com/api/querypaymentstatus';
	
	$test_status_request = 'https://demo.pesapal.com/api/querypaymentstatus';
	
	$consumer_key = $options['customer_key'];
	$consumer_secret = $options['customer_secret'];
	
	$transaction_tracking_id = $_REQUEST['pesapal_transaction_tracking_id'];
	$payment_notification = $_REQUEST['pesapal_notification_type'];
	$invoice = $_REQUEST['pesapal_merchant_reference'];
	$statusrequestAPI = $status_request;
	if($options['sandbox'] == 'checked'){
		$statusrequestAPI = $test_status_request;
	}
	
	if($pesapalNotification=="CHANGE" && $pesapalTrackingId!=''){
		$token = $params = NULL;
		$consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		$signature_method = new OAuthSignatureMethod_HMAC_SHA1();

		//get transaction status
		$request_status = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $statusrequestAPI, $params);
		$request_status->set_parameter("pesapal_merchant_reference", $pesapal_merchant_reference);
		$request_status->set_parameter("pesapal_transaction_tracking_id",$invoice);
		$request_status->sign_request($signature_method, $consumer, $token);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_status);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		 if(defined('CURL_PROXY_REQUIRED')) if (CURL_PROXY_REQUIRED == 'True'){
			$proxy_tunnel_flag = (defined('CURL_PROXY_TUNNEL_FLAG') && strtoupper(CURL_PROXY_TUNNEL_FLAG) == 'FALSE') ? false : true;
			curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_tunnel_flag);
			curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt ($ch, CURLOPT_PROXY, CURL_PROXY_SERVER_DETAILS);
		}

		$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$raw_header  = substr($response, 0, $header_size - 4);
		$headerArray = explode("\r\n\r\n", $raw_header);
		$header      = $headerArray[count($headerArray) - 1];

		 //transaction status
		$elements = preg_split("/=/",substr($response, $header_size));
		$status = $elements[1];

		curl_close ($ch);
		switch ($status) {
			case 'PENDING':
				$updated_status = 'Pending';
				break;
			case 'COMPLETED':
				$updated_status = 'Paid';
				break;
			case 'FAILED':
				$updated_status = 'Canceled';
				break;
			default:
				$updated_status = 'Canceled';
				break;
		}
		$query = "UPDATE {$table_name} SET `payment_status`='{$updated_status}' WHERE `invoice`={$invoice}";
		$wpdb->query($query);
	}
}
?>