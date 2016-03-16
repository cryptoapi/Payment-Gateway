<?php
/**
 *
 * Cryptobox Server Callbacks
 *
 * @package     Cryptobox callbacks
 * @copyright   2014-2016 Delta Consultants
 * @category    Libraries
 * @website     https://gourl.io
 * @version     1.7.6
 *
 * 
 * This file processes call-backs from Cryptocoin Payment Box server when new payment  
 * from your users comes in. Please link this file in your cryptobox configuration on   
 * gourl.io - Callback url: http://yoursite.com/cryptobox.callback.php
 * 
 * Usually user will see on bottom of payment box button 'Click Here if you have already sent coins' 
 * and when he will click on that button, script will connect to our remote cryptocoin payment box server
 * and check user payment. 
 * 
 * As backup, our server will also inform your server automatically every time when payment is
 * received through this callback file. I.e. if the user does not click on button, your website anyway 
 * will receive notification about a given user and save it in your database. And when your user next time 
 * comes on your website/reload page he will automatically will see message that his payment has been 
 * received successfully.
 *
 */


if(!defined("CRYPTOBOX_WORDPRESS")) define("CRYPTOBOX_WORDPRESS", false);

if (!CRYPTOBOX_WORDPRESS) include_once("cryptobox.class.php");
elseif (!defined('ABSPATH')) exit; // Exit if accessed directly in wordpress


// a.
if ($_POST) foreach ($_POST as $k => $v) if (is_string($v)) $_POST[$k] = trim($v);


// b.
if (isset($_POST["plugin_ver"]) && !isset($_POST["status"]) && isset($_POST["private_key"]) && strlen($_POST["private_key"]) == 50 && in_array($_POST["private_key"], explode("^", CRYPTOBOX_PRIVATE_KEYS)))
{
	echo "cryptoboxver_" . (CRYPTOBOX_WORDPRESS ? "wordpress_" . GOURL_VERSION : "php_" . CRYPTOBOX_VERSION);
	die; 
}


// c.
if (isset($_POST["status"]) && in_array($_POST["status"], array("payment_received", "payment_received_unrecognised")) &&
		$_POST["box"] && is_numeric($_POST["box"]) && $_POST["box"] > 0 && $_POST["amount"] && is_numeric($_POST["amount"]) && $_POST["amount"] > 0 &&
		strlen($_POST["private_key"]) == 50 && preg_replace('/[^A-Za-z0-9]/', '', $_POST["private_key"]) == $_POST["private_key"] && in_array($_POST["private_key"], explode("^", CRYPTOBOX_PRIVATE_KEYS)))
{
	
	foreach ($_POST as $k => $v)
	{
		if ($k == "datetime") 							$mask = '/[^0-9\ \-\:]/';
		elseif (in_array($k, array("err", "date")))		$mask = '/[^A-Za-z0-9\.\_\-\@\ ]/';
		else											$mask = '/[^A-Za-z0-9\.\_\-\@]/';
		if ($v && preg_replace($mask, '', $v) != $v) 	$_POST[$k] = "";
	}
	
	if (!$_POST["amountusd"] || !is_numeric($_POST["amountusd"]))	$_POST["amountusd"] = 0;
	if (!$_POST["confirmed"] || !is_numeric($_POST["confirmed"]))	$_POST["confirmed"] = 0;
	
	
	$dt			= gmdate('Y-m-d H:i:s');
	$obj 		= run_sql("select paymentID, txConfirmed from crypto_payments where boxID = ".$_POST["box"]." && orderID = '".$_POST["order"]."' && userID = '".$_POST["user"]."' && txID = '".$_POST["tx"]."' limit 1");
	
	
	$paymentID		= ($obj) ? $obj->paymentID : 0;
	$txConfirmed	= ($obj) ? $obj->txConfirmed : 0; 
	
	// Save new payment details in local database
	if (!$paymentID)
	{
		$sql = "INSERT INTO crypto_payments (boxID, boxType, orderID, userID, countryID, coinLabel, amount, amountUSD, unrecognised, addr, txID, txDate, txConfirmed, txCheckDate, recordCreated)
				VALUES (".$_POST["box"].", '".$_POST["boxtype"]."', '".$_POST["order"]."', '".$_POST["user"]."', '".$_POST["usercountry"]."', '".$_POST["coinlabel"]."', ".$_POST["amount"].", ".$_POST["amountusd"].", ".($_POST["status"]=="payment_received_unrecognised"?1:0).", '".$_POST["addr"]."', '".$_POST["tx"]."', '".$_POST["datetime"]."', ".$_POST["confirmed"].", '$dt', '$dt')";

		$paymentID = run_sql($sql);
		
		$box_status = "cryptobox_newrecord";
	}
	// Update transaction status to confirmed
	elseif ($_POST["confirmed"] && !$txConfirmed)
	{
		$sql = "UPDATE crypto_payments SET txConfirmed = 1, txCheckDate = '$dt' WHERE paymentID = $paymentID LIMIT 1";
		run_sql($sql);
		
		$box_status = "cryptobox_updated";
	}
	else 
	{
		$box_status = "cryptobox_nochanges";
	}
	
	
	/**
	 *  User-defined function for new payment - cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "").
	 *  You can add this function to the bottom of the file cryptobox.class.php or create a separate file.
	 *  For example, send confirmation email, update user membership, etc.
	 *  
	 *  The function will automatically appear for each new payment usually two times : 
	 *  a) when a new payment is received, with values: $box_status = cryptobox_newrecord, $payment_details[confirmed] = 0
	 *  b) and a second time when existing payment is confirmed (6+ confirmations) with values: $box_status = cryptobox_updated, $payment_details[confirmed] = 1.
	 *  
	 *  But sometimes if the payment notification is delayed for 20-30min, the payment/transaction will already be confirmed and the function will
	 *  appear once with values: $box_status = cryptobox_newrecord, $payment_details[confirmed] = 1
	 *  
	 *  If payment received with correct amount, function receive: $payment_details[status] = 'payment_received' and $payment_details[user] = 11, 12, etc (user_id who has made payment)
	 *  If incorrectly paid amount, the system can not recognize user; function receive: $payment_details[status] = 'payment_received_unrecognised' and $payment_details[user] = ''
	 *
	 *  Function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "")
	 *  gets $paymentID from your table crypto_payments, $box_status = 'cryptobox_newrecord' OR 'cryptobox_updated' (description above)
	 *  and payment details as array -
	 * 
	 *  1. EXAMPLE - CORRECT PAYMENT - 
	 *  -----------------------------------------------------
	 *  $payment_details = Array
	 *        {
	 *            "status":"payment_received"
	 *            "err":""
	 *            "private_key":"1206lO6HX76cw9Bitcoin77DOGED82Y8eyBExZ9kZpX"
	 *            "box":"120"
	 *            "boxtype":"paymentbox"
	 *            "order":"order15620A"
	 *            "user":"user26"
	 *            "usercountry":"USA"
	 *            "amount":"0.0479166"
	 *            "amountusd":"11.5"
	 *            "coinlabel":"BTC"
	 *            "coinname":"bitcoin"
	 *            "addr":"14dt2cSbvwghDcETJDuvFGHe5bCsCPR9jW"
	 *            "tx":"95ed924c215f2945e75acfb5650e28384deac382c9629cf0d3f31d0ec23db08d"
	 *            "confirmed":0
	 *            "timestamp":"1422624765 "
	 *            "date":"30 January 2015"
	 *            "datetime":"2015-01-30 13:32:45"
	 *        }
	 *         						
	 *  2. EXAMPLE - INCORRECT PAYMENT/WRONG AMOUNT -
	 *  -----------------------------------------------------
	 *     $payment_details = Array 
	 *        {
	 *            "status":"payment_received_unrecognised"
	 *            "err":"An incorrect bitcoin amount has been received"
	 *            "private_key":"1206lO6HX76cw9Bitcoin77DOGED82Y8eyBExZ9kZpX"
	 *            "box":"120"
	 *            "boxtype":"paymentbox"
	 *            "order":""
	 *            "user":""
	 *            "usercountry":""
	 *            "amount":"12.26"
	 *            "amountusd":"0.05"
	 *            "coinlabel":"BTC"
	 *            "coinname":"bitcoin"
	 *            "addr":"14dt2cSbvwghDcETJDuvFGHe5bCsCPR9jW"
	 *            "tx":"6f1c6f34189a27446d18e25b9c79db78be55b0bb775b1768b5aa4520f27d71a8"
	 *            "confirmed":0
	 *            "timestamp":"1422623712"
	 *            "date":"30 January 2015"
	 *            "datetime":"2015-01-30 13:15:12"
	 *        }	 
	 *        
	 *        See more - https://gourl.io/api-php.html#ipn
     */

	if (in_array($box_status, array("cryptobox_newrecord", "cryptobox_updated")) && function_exists('cryptobox_new_payment')) cryptobox_new_payment($paymentID, $_POST, $box_status);
}   

else
	$box_status = "Only POST Data Allowed"; 


	echo $box_status; // don't delete it  
           
?>