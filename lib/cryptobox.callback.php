<?php
/**
 * ##########################################  
 * ###  PLEASE DO NOT MODIFY THIS FILE !  ###
 * ##########################################
 *
 *
 * Cryptobox Server Callbacks
 *
 * @package     Cryptobox callbacks
 * @copyright   2014-2020 Delta Consultants
 * @category    Libraries
 * @website     https://gourl.io
 * @version     2.2.0
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
 *
 */


if(!defined("CRYPTOBOX_WORDPRESS")) define("CRYPTOBOX_WORDPRESS", false);

if (!CRYPTOBOX_WORDPRESS) require_once( "cryptobox.class.php" ); 
elseif (!defined('ABSPATH')) exit; // Exit if accessed directly in wordpress


// a. check if private key valid
$valid_key = false;
if (isset($_POST["private_key_hash"]) && strlen($_POST["private_key_hash"]) == 128 && preg_replace('/[^A-Za-z0-9]/', '', $_POST["private_key_hash"]) == $_POST["private_key_hash"])
{
    $keyshash = array();
    $arr = explode("^", CRYPTOBOX_PRIVATE_KEYS);
    foreach ($arr as $v) $keyshash[] = strtolower(hash("sha512", $v));
    if (in_array(strtolower($_POST["private_key_hash"]), $keyshash)) $valid_key = true;
}


// b. alternative - ajax script send gourl.io json data
if (!$valid_key && isset($_POST["json"]) && $_POST["json"] == "1")
{
    $data_hash = $boxID = "";
    if (isset($_POST["data_hash"]) && strlen($_POST["data_hash"]) == 128 && preg_replace('/[^A-Za-z0-9]/', '', $_POST["data_hash"]) == $_POST["data_hash"]) { $data_hash = strtolower($_POST["data_hash"]); unset($_POST["data_hash"]); }
    if (isset($_POST["box"]) && is_numeric($_POST["box"]) && $_POST["box"] > 0) $boxID = intval($_POST["box"]);
    
    if ($data_hash && $boxID)
    {
        $private_key = "";
        $arr = explode("^", CRYPTOBOX_PRIVATE_KEYS);
        foreach ($arr as $v) if (strpos($v, $boxID."AA") === 0) $private_key = $v;
    
        if ($private_key)
        {
            $data_hash2 = strtolower(hash("sha512", $private_key.json_encode($_POST).$private_key));
            if ($data_hash == $data_hash2) $valid_key = true;
        }
        unset($private_key);
    }
    
    if (!$valid_key) die("Error! Invalid Json Data sha512 Hash!"); 
    
}


// c.
if ($_POST) foreach ($_POST as $k => $v) if (is_string($v)) $_POST[$k] = trim($v);



// d.
if (isset($_POST["plugin_ver"]) && !isset($_POST["status"]) && $valid_key)
{
	echo "cryptoboxver_" . (CRYPTOBOX_WORDPRESS ? "wordpress_" . GOURL_VERSION : "php_" . CRYPTOBOX_VERSION);
	die; 
}


// e.
if (isset($_POST["status"]) && in_array($_POST["status"], array("payment_received", "payment_received_unrecognised")) &&
		$_POST["box"] && is_numeric($_POST["box"]) && $_POST["box"] > 0 && $_POST["amount"] && is_numeric($_POST["amount"]) && $_POST["amount"] > 0 && $valid_key)
{
	
	foreach ($_POST as $k => $v)
	{
		if ($k == "datetime") 						$mask = '/[^0-9\ \-\:]/';
		elseif (in_array($k, array("err", "date", "period")))		$mask = '/[^A-Za-z0-9\.\_\-\@\ ]/';
		else								$mask = '/[^A-Za-z0-9\.\_\-\@]/';
		if ($v && preg_replace($mask, '', $v) != $v) 	$_POST[$k] = "";
	}
	
	if (!$_POST["amountusd"] || !is_numeric($_POST["amountusd"]))	$_POST["amountusd"] = 0;
	if (!$_POST["confirmed"] || !is_numeric($_POST["confirmed"]))	$_POST["confirmed"] = 0;
	
	
	$dt			= gmdate('Y-m-d H:i:s');
	$obj 		= run_sql("select paymentID, txConfirmed from crypto_payments where boxID = ".intval($_POST["box"])." && orderID = '".addslashes($_POST["order"])."' && userID = '".addslashes($_POST["user"])."' && txID = '".addslashes($_POST["tx"])."' && amount = ".floatval($_POST["amount"])." && addr = '".addslashes($_POST["addr"])."' limit 1");
	
	
	$paymentID		= ($obj) ? $obj->paymentID : 0;
	$txConfirmed	= ($obj) ? $obj->txConfirmed : 0; 
	
	// Save new payment details in local database
	if (!$paymentID)
	{
		$sql = "INSERT INTO crypto_payments (boxID, boxType, orderID, userID, countryID, coinLabel, amount, amountUSD, unrecognised, addr, txID, txDate, txConfirmed, txCheckDate, recordCreated)
				VALUES (".intval($_POST["box"]).", '".addslashes($_POST["boxtype"])."', '".addslashes($_POST["order"])."', '".addslashes($_POST["user"])."', '".addslashes($_POST["usercountry"])."', '".addslashes($_POST["coinlabel"])."', ".floatval($_POST["amount"]).", ".floatval($_POST["amountusd"]).", ".($_POST["status"]=="payment_received_unrecognised"?1:0).", '".addslashes($_POST["addr"])."', '".addslashes($_POST["tx"])."', '".addslashes($_POST["datetime"])."', ".intval($_POST["confirmed"]).", '$dt', '$dt')";

		$paymentID = run_sql($sql);
		
		$box_status = "cryptobox_newrecord";
	}
	// Update transaction status to confirmed
	elseif ($_POST["confirmed"] && !$txConfirmed)
	{
		$sql = "UPDATE crypto_payments SET txConfirmed = 1, txCheckDate = '$dt' WHERE paymentID = ".intval($paymentID)." LIMIT 1";
		run_sql($sql);
		
		$box_status = "cryptobox_updated";
	}
	else 
	{
		$box_status = "cryptobox_nochanges";
	}
	
	
	/**
	 *  User-defined function for new payment - cryptobox_new_payment(...)
	 *  For example, send confirmation email, update database, update user membership, etc.
	 *  You need to modify file - cryptobox.newpayment.php
	 *  Read more - https://gourl.io/api-php.html#ipn
         */

	if (in_array($box_status, array("cryptobox_newrecord", "cryptobox_updated")) && function_exists('cryptobox_new_payment')) cryptobox_new_payment($paymentID, $_POST, $box_status);
}   

else
	$box_status = "Only POST Data Allowed";


	echo $box_status; // don't delete it    
 
?>