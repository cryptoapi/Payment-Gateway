<?php
/**
 * @category    Example3 - Pay-Per-Download (single crypto currency in payment box)
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2018 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, BitcoinCash, Litecoin, Dash, Dogecoin, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Peercoin, MonetaryUnit, UniversalCurrency
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p2
 * @live_demo   https://gourl.io/lib/examples/pay-per-download.php
 */ 
	
	require_once( "../cryptobox.class.php" );

	
	/**** CONFIGURATION VARIABLES ****/ 
	
	$filename 		= "my_file1.zip";	// filename for download
	$dir 			= "protected"; 		// name of your directory with your files; nobody should have direct web access to that directory
	$userID 		= "";				// optional; place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
										// or leave empty userID - system will autogenerate userID and save in cookies
	$userFormat		= "COOKIE";			// save userID in cookies (or you can use IPADDRESS, SESSION)
	$orderID 		= md5($dir.$filename);	// file name hash as order id
	$amountUSD		= 0.2;				// file download price (0.2 USD)
	$period			= "24 HOURS";		// download link will be valid for 24 hours
	$def_language	= "en";				// default Payment Box Language
	$public_key		= "-your public key for coin box-"; // from gourl.io
	$private_key	= "-your private key for coin box-";// from gourl.io

	
	// *** For convert Euro/GBP/etc. to USD/Bitcoin, use function convert_currency_live() with Google Finance
	// *** examples: convert_currency_live("EUR", "BTC", 22.37) - convert 22.37 Euro to Bitcoin
	// *** convert_currency_live("EUR", "USD", 22.37) - convert 22.37 Euro to USD

	// IMPORTANT: Please read description of options here - https://gourl.io/api-php.html#options  
	
	/********************************/


	
	
	
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





	// ...
	// Also you need to use IPN function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "") 
	// for send confirmation email, update database, update user membership, etc.
	// You need to modify file - cryptobox.newpayment.php, read more - https://gourl.io/api-php.html#ipn
	// ...
		
	
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?php echo $coinName; ?> Pay-Per-Download Cryptocoin Payment Example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<meta name='robots' content='all'>
<script src='../cryptobox.min.js' type='text/javascript'></script>
</head>
<body style='font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#666;margin:0'>
<div align='center'>
<div style='width:100%;height:auto;line-height:50px;background-color:#f1f1f1;border-bottom:1px solid #ddd;color:#49abe9;font-size:18px;'>
	3. GoUrl <b>Pay-Per-Download</b> Example (<?php echo $coinName; ?> payments). Use it on your website. 
	<div style='float:right;'><a style='font-size:15px;color:#389ad8;margin-right:20px' href='<?= "//".$_SERVER["HTTP_HOST"].str_replace(".php", "-multi.php", $_SERVER["REQUEST_URI"]); ?>'>Multiple Crypto</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html#p2'>PHP Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://github.com/cryptoapi/Bitcoin-Payment-Gateway-ASP.NET/tree/master/GoUrl/Views/Examples/PayPerDownload.cshtml'>ASP.NET Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://wordpress.org/plugins/gourl-bitcoin-payment-gateway-paid-downloads-membership/'>Wordpress</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html'>Other Examples</a></div>
</div>

<h2>Example - Paid File Downloads</h2>

<br><h1>File: <?php echo $filename; ?></h1>

Price: ~<?php echo $amountUSD; ?> US$<br>

<a <?php echo $download_link; ?>><img alt='Download File' border='0' src='https://gourl.io/images/zip.png'></a><br>
<a <?php echo $download_link; ?>>Download File</a>

<div style='margin:70px 0 5px 300px'>Language: &#160; <?php echo $languages_list; ?></div>
<?php echo $box->display_cryptobox(); ?>


</div>
<br><br><br><br><br><br>
<div style='position:absolute;left:0;'><a target="_blank" href="http://validator.w3.org/check?uri=<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>"><img src="https://gourl.io/images/w3c.png" alt="Valid HTML 4.01 Transitional"></a></div>
</body>
</html>