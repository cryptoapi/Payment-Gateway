<?php
/**
 *
 * Cryptobox Server Callbacks
 *
 * @package     Cryptobox callbacks
 * @copyright   2014-2015 Delta Consultants
 * @category    Libraries
 * @website     https://gourl.io
 * @version     1.4
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

include_once("cryptobox.class.php");


if (isset($_POST["status"]) && in_array($_POST["status"], array("payment_received", "payment_received_unrecognised")) && $_POST["amount"] && strpos($_POST["private_key"], " ") === false && in_array($_POST["private_key"], explode("^", CRYPTOBOX_PRIVATE_KEYS)))
{
	if (!$_POST["box"])			$_POST["box"] = 0;
	if (!$_POST["amountusd"])	$_POST["amountusd"] = 0;
	if (!$_POST["confirmed"])	$_POST["confirmed"] = 0;

	$dt		= gmdate('Y-m-d H:i:s');
	$paymentID 	= run_sql("select paymentID as nme from crypto_payments where boxID = ".$_POST["box"]." && orderID = '".$_POST["order"]."' && userID = '".$_POST["user"]."' && txID = '".$_POST["tx"]."' limit 1");
	
	// Save new payment details in local database
	if (!$paymentID)
	{
		$sql = "INSERT INTO crypto_payments (boxID, boxType, orderID, userID, countryID, coinLabel, amount, amountUSD, unrecognised, addr, txID, txDate, txConfirmed, txCheckDate, recordCreated)
				VALUES (".$_POST["box"].", '".$_POST["boxtype"]."', '".$_POST["order"]."', '".$_POST["user"]."', '".$_POST["usercountry"]."', '".$_POST["coinlabel"]."', ".$_POST["amount"].", ".$_POST["amountusd"].", ".($_POST["status"]=="payment_received_unrecognised"?1:0).", '".$_POST["addr"]."', '".$_POST["tx"]."', '".$_POST["datetime"]."', ".$_POST["confirmed"].", '$dt', '$dt')";

		$paymentID = run_sql($sql);
		
		// User-defined function for new payment - cryptobox_new_payment() - for example, send confirmation email, update user membership, etc.
		if (function_exists('cryptobox_new_payment')) cryptobox_new_payment($paymentID, $_POST);
		
	}
	// Update transaction status to confirmed
	elseif ($_POST["confirmed"])
	{
		$sql = "UPDATE crypto_payments SET txConfirmed = 1, txCheckDate = '$dt' WHERE paymentID = $paymentID LIMIT 1";
		run_sql($sql);
	}
	
	echo "Payment Processed";
}
	
else 
	echo "Only POST Data Allowed";


?>