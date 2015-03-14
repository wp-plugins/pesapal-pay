=== PesaPal Pay ===
Contributors: rixeo
Tags: Pesapal, e-commerce, ecommerce

Requires at least: 3.0
Tested up to: 4.1.1
Stable tag: 1.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PesaPal Pay allows you to easily integrate Pesapal to any ecommerce website

== Description ==

A quick way to integrate PesaPal to your website to handle the payment process. All you need to do is set up what parameters to capture from the form and the plugin will do the rest via the shortcode [pesapal_pay_button] where you can add the attribute button_name to be the text on the button. You can now alos accept donations via the PesaPal Donate Widget or using the shortcode [pesapal_donate].
Call the javascript function pesapal_pay_no_invoice(parentdivId,email, amount) with parentdivId being the ID you want pesapal to be loaded

Main Features:

* Set up PesaPal credentials
* Set up fields to be captured
* Log PesaPal transactions
* Allows calling of a function before the pesapal transaction
* Accept Donations
* Javascript Usage


We are still working to make this excellent

== Installation ==

1. Upload the pesapal_pay folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Once the plugin is activated there will be an admin section where you can configure the pesapal settings
4. Use the shortcode [pesapal_pay_button] to use pesapal
5. Use the PesaPal Donate Widget to accept donations or use the shortcode [pesapal_donate]

== Frequently Asked Questions ==

= How do I make transactions? =

Just post the parameters to a payment page that has the pesapal shortcode on it


= How do I use the shortcode? =
Just put the shortcode on a page after the products page. The shortcode will look for the parameters you set up in the previous page and set them to be used in the transaction to PesaPal

= How Do I use the Javascript Function =
Just call pesapal_pay_no_invoice(parentdivId,email, amount) function after the form has been posted and get the returned parameters for email and total amount in kenyan shillings


== Changelog ==

= 1.3.1 =
Small Fix

= 1.3 =

 * Fixed a bug on saving trasnactions. 
 * Added an option to pass amount on the payment button

= 1.2.7 =
Pesapal no longer support Sandbox testing. Removed the option in code

= 1.2.6 =
Invoice ID fix

= 1.2.5 =
Javascript bug fix

= 1.2.4 =
Javascript bug fix

= 1.2.3 =
Added Javascript for external use

= 1.2.2 =
Automatic Invoice generation

= 1.2.1 =
Author information

= 1.2 =
Added Pesapal Donate Widget and shortcode [pesapal_donate]. You can now accept donations via PesaPal.

= 1.1 =
Documentation

= 1.0 =
PesaPal Pay is brand new.  As such you won't be upgrading but joining our handsomely awesome family. We will be upgrading and fixes bugs as we improve the plugin