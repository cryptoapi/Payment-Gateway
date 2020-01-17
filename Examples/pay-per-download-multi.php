<?php
/**
 * @category    Example4 - Pay-Per-Download (payments in multiple cryptocurrencies, you can use original price in USD)
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2020 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, BitcoinCash, BitcoinSV, Litecoin, Dash, Dogecoin, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Peercoin, MonetaryUnit, UniversalCurrency
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p2
 * @live_demo   https://gourl.io/lib/examples/pay-per-download-multi.php
 */ 

	/********************** NOTE - 2018 YEAR *******************************************************************************/ 
	/*****                                                                                                             *****/ 
	/*****     This is iFrame Bitcoin Payment Box Example (2014 - 2017)                                                *****/ 
	/*****                                                                                                             *****/ 
	/*****     Available - new 2018-2020 version; mobile friendly JSON payment box (own logo, white label product)     *****/
	/*****     New Demo with generation php payment box code - https://gourl.io/lib/examples/example_customize_box.php *****/
	/*****         White Theme - https://gourl.io/lib/examples/example_customize_box.php?theme=black                   *****/
	/*****         Black Theme - https://gourl.io/lib/examples/example_customize_box.php?theme=default     		   *****/
	/*****         Your Own Logo - https://gourl.io/lib/examples/example_customize_box.php?theme=default&logo=custom   *****/
	/*****                                                                                                             *****/ 
	/***********************************************************************************************************************/


	
	
	require_once( "../lib/cryptobox.class.php" );

	
	/**** CONFIGURATION VARIABLES ****/ 
	
	$filename 		= "my_file1.zip";	// filename for download
	$dir 			= "protected"; 		// name of your directory with your files; nobody should have direct web access to that directory
	$userID 		= "";				// optional; place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
										// or leave empty userID - system will autogenerate userID and save in cookies
	$userFormat		= "COOKIE";			// save userID in cookies (or you can use IPADDRESS, SESSION)
	$orderID 		= md5($dir.$filename);	// file name hash as order id
	$amountUSD		= 0.2;				// file download price (0.2 USD)
										// for convert fiat currencies Euro/GBP/etc. to USD, use function convert_currency_live() 
	$period			= "24 HOURS";		// download link will be valid for 24 hours
	$def_language	= "en";				// default Payment Box Language
	$def_payment	= "bitcoin";		// Default Coin in Payment Box
	
	// *** For convert Euro/GBP/etc. to USD/Bitcoin, use function convert_currency_live() with Google Finance
	// *** examples: convert_currency_live("EUR", "BTC", 22.37) - convert 22.37 Euro to Bitcoin
	// *** convert_currency_live("EUR", "USD", 22.37) - convert 22.37 Euro to USD

	// IMPORTANT: Please read description of options here - https://gourl.io/api-php.html#options  



	// List of coins that you accept for payments
	// For example, for accept payments in bitcoin, bitcoincash, litecoin use - $available_payments = array('bitcoin', 'bitcoincash', 'litecoin'); 
	$available_payments = array('bitcoin', 'bitcoincash', 'bitcoinsv', 'litecoin', 'dash', 'dogecoin', 'speedcoin', 'reddcoin', 'potcoin', 'feathercoin', 'vertcoin', 'peercoin', 'monetaryunit', 'universalcurrency');
	
	
	// Goto  https://gourl.io/info/memberarea/My_Account.html
	// You need to create record for each your coin and get private/public keys
	// Place Public/Private keys for all your available coins from $available_payments
	
	$all_keys = array(	"bitcoin"  => array("public_key" => "-your public key for Bitcoin box-",  "private_key" => "-your private key for Bitcoin box-"),
				"bitcoincash"  => array("public_key" => "-your public key for BitcoinCash box-",  "private_key" => "-your private key for BitcoinCash box-"),
				"litecoin" => array("public_key" => "-your public key for Litecoin box-", "private_key" => "-your private key for Litecoin box-")
				// etc.
			); 
	
	/********************************/


	// Re-test - that all keys for $available_payments added in $all_keys
	if (!in_array($def_payment, $available_payments)) $available_payments[] = $def_payment;  
	foreach($available_payments as $v)
	{
		if (!isset($all_keys[$v]["public_key"]) || !isset($all_keys[$v]["private_key"])) die("Please add your public/private keys for '$v' in \$all_keys variable");
		elseif (!strpos($all_keys[$v]["public_key"], "PUB"))  die("Invalid public key for '$v' in \$all_keys variable");
		elseif (!strpos($all_keys[$v]["private_key"], "PRV")) die("Invalid private key for '$v' in \$all_keys variable");
		elseif (strpos(CRYPTOBOX_PRIVATE_KEYS, $all_keys[$v]["private_key"]) === false) die("Please add your private key for '$v' in variable \$cryptobox_private_keys, file cryptobox.config.php.");
	}
	
	
	// Current selected coin by user
	$coinName = cryptobox_selcoin($available_payments, $def_payment);
	
	
	// Current Coin public/private keys
	$public_key  = $all_keys[$coinName]["public_key"];
	$private_key = $all_keys[$coinName]["private_key"];
	
	
	
	/** PAYMENT BOX **/
	$options = array(
			"public_key"  => $public_key, 	// your public key from gourl.io
			"private_key" => $private_key, 	// your private key from gourl.io
			"webdev_key"  => "", 		// optional, gourl affiliate key
			"orderID"     => $orderID, 		// file name hash as order id
			"userID"      => $userID, 		// unique identifier for every user
			"userFormat"  => $userFormat, 	// save userID in COOKIE, IPADDRESS or SESSION
			"amount"   	  => 0,				// file price in coins OR in USD below
			"amountUSD"   => $amountUSD,	// we use file price in USD
			"period"      => $period, 		// download link valid period
			"language"	  => $def_language  // text on EN - english, FR - french, etc
	);

	// Initialise Payment Class
	$box = new Cryptobox ($options);
	
	// coin name
	$coinName = $box->coin_name(); 
	
	// Generate Download Link
	$download_link =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . 
						(strpos($_SERVER["REQUEST_URI"], "?")?"&":"?")."dd=1";
	$download_link = "href='".htmlspecialchars($download_link, ENT_QUOTES, 'UTF-8')."'";
	
	// Warning message if not paid
	if (!$box->is_paid()) 
		$download_link = "onclick='alert(\"You need to send ".$coinName."s first !\")' href='#a'";

	// Check if file exists on your server 
	$file = rtrim($dir, "/ ")."/".$filename;
	if (!file_exists($file)) 
		echo "<h1><center><font color=red>Warning: $file not exists</font></center></h1>";
	
	
	// User Paid - Send file to user browser
	if ($box->is_paid() && isset($_GET["dd"]) && $_GET["dd"] == "1") 
	{
		// Starting Download
		$size = filesize($file);
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($file));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $size);
		readfile($file);
		
		// Set Status - User Downloaded File
		if ($size) $box->set_status_processed();
		
		die;
	}
	
	
	
	// Optional - Language selection list for payment box (html code)
	$languages_list = display_language_box($def_language);
	
	
	
	// Optional - Coin selection list (html code)
	$coins_list = display_currency_box($available_payments, $def_payment, $def_language, 60, "margin: 80px 0 0 0", "../images");




	// ...
	// Also you need to use IPN function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "") 
	// for send confirmation email, update database, update user membership, etc.
	// You need to modify file - cryptobox.newpayment.php, read more - https://gourl.io/api-php.html#ipn
	// ...

		
	
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title>Pay-Per-Download Cryptocoin (payments in multiple cryptocurrencies) Payment Example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<meta name='robots' content='all'>
<script src='../js/cryptobox.min.js' type='text/javascript'></script>
</head>
<body style='font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#666;margin:0'>
<div align='center'>
<div style='width:100%;height:auto;line-height:50px;background-color:#f1f1f1;border-bottom:1px solid #ddd;color:#49abe9;font-size:18px;'>
	4. GoUrl <b>Pay-Per-Download</b> Example (multiple cryptocurrencies). Use it on your website. 
	<div style='float:right;'><a style='font-size:15px;color:#389ad8;margin-right:20px' href='<?= "//".$_SERVER["HTTP_HOST"].str_replace("-multi.php", ".php", $_SERVER["REQUEST_URI"]); ?>'>Single Crypto</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html#p2'>PHP Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://github.com/cryptoapi/Bitcoin-Payment-Gateway-ASP.NET/tree/master/GoUrl/Views/Examples/PayPerDownloadMulti.cshtml'>ASP.NET Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/lib/examples/example_customize_box.php'>NEW - Payment Box 2018 (Mobile Friendly)</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://wordpress.org/plugins/gourl-bitcoin-payment-gateway-paid-downloads-membership/'>Wordpress</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html'>Other Examples</a></div>
</div>

<h2>Example - Paid File Downloads (multi coins below)</h2>

<br><h1>File: <?php echo $filename; ?></h1>

Price: ~<?php echo $amountUSD; ?> US$<br>

<a <?php echo $download_link; ?>><img alt='Download File' border='0' src='https://gourl.io/images/zip.png'></a><br>
<a <?php echo $download_link; ?>>Download File</a>

<?php if (!$box->is_paid()) echo $coins_list; else echo "<br><br><br><br>" ?>

<div style='margin:30px 0 5px 300px'>Language: &#160; <?php echo $languages_list; ?></div>
<?php echo $box->display_cryptobox(); ?>


</div><br><br><br><br><br><br>
<div style='position:absolute;left:0;'><a target="_blank" href="http://validator.w3.org/check?uri=<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>"><img src="https://gourl.io/images/w3c.png" alt="Valid HTML 4.01 Transitional"></a></div>
</body>
</html>