/**
 * Call this function After a form post to do pesapal payment
 * param parentdivId - Parent Id of div where you want the iframe to load
 * param email - payers email
 * param amount - total amount
 */
function pesapal_pay_no_invoice(parentdivId,email, amount){
	jQuery.ajax({
		type: "POST",
		url: p_pay_js.ajaxurl,
		data: 'action=pesapal_save_transaction&pesapal_donate_email=' + email+'&pesapal_donate_amount='+amount+'&pesapal_donate_no_invoice=true',
		success:function(data){
			jQuery('#'+parentdivId).html(data);
		}
	});
}