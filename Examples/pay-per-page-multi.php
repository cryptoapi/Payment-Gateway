<?php
/**
 * @category    Example10 - Pay-Per-Page Access (payments in multiple cryptocurrencies, you can use original price in USD)
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2015 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, Litecoin, Dogecoin, Speedcoin, Darkcoin, Vertcoin, Reddcoin, Feathercoin, Vericoin, Potcoin
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p5
 * @live_demo   http://gourl.io/lib/examples/pay-per-page-multi.php
 */ 
	
	require_once( "../cryptobox.class.php" );

	
	/**** CONFIGURATION VARIABLES ****/ 
	
	$userID 		= "";				// leave empty for unregistered visitors on your website  
										// if userID is empty, it will autogenerate userID and save in cookies
										// or place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
	$userFormat		= "COOKIE";			// save userID in cookies (or you can use IPADDRESS, SESSION)
	$orderID 		= "page1";			// Separate payments for separate your web page(s); you can receive payments also for page2, page3, section1, etc. 
	$amountUSD		= 0.6;				// price per page(s) - 0.6 USD
	$period			= "24 HOUR";		// user will get access to page(s) for 24 hours; after need to pay again
	$def_language	= "en";				// default Payment Box Language
	$def_payment	= "bitcoin";		// Default Coin in Payment Box

	// List of coins that you accept for payments
	// For example, for accept payments in bitcoins, dogecoins, litecoins use - $available_payments = array('bitcoin', 'dogecoin', 'litecoin'); 
	$available_payments = array('bitcoin', 'litecoin', 'dogecoin', 'speedcoin', 'darkcoin', 'vertcoin', 'reddcoin', 'feathercoin', 'vericoin', 'potcoin');
	
	
	// Goto  https://gourl.io/info/memberarea/My_Account.html
	// You need to create record for each your coin and get private/public keys
	// Place Public/Private keys for all your available coins from $available_payments
	
	$all_keys = array(	"bitcoin"  => array("public_key" => "-your public key for Bitcoin box-",  "private_key" => "-your private key for Bitcoin box-"),
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
			"orderID"     => $orderID, 		// order id
			"userID"      => $userID, 		// unique identifier for each your user
			"userFormat"  => $userFormat, 	// save userID in COOKIE, IPADDRESS or SESSION
			"amount"   	  => 0,				// price in coins OR in USD below
			"amountUSD"   => $amountUSD,	// we use price in USD
			"period"      => $period, 		// payment valid period
			"language"	  => $def_language  // text on EN - english, FR - french, etc
	);

	// Initialise Payment Class
	$box = new Cryptobox ($options);
	
	// coin name
	$coinName = $box->coin_name(); 
	

	
	// Optional - Language selection list for payment box (html code)
	$languages_list = display_language_box($def_language);
	
	
	
	// Optional - Coin selection list (html code)
	if (!$box->is_paid()) $coins_list = display_currency_box($available_payments, $def_payment, $def_language, 60, "margin: 80px 0 0 0");
	
	
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title>Pay-Per-View / Page Access Cryptocoin (payments in multiple cryptocurrencies) Payment Example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<meta name='robots' content='all'>
<script src='../cryptobox.min.js' type='text/javascript'></script>
</head>
<body style='font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#666;margin:0'>
<div align='center'>
<div style='width:100%;height:auto;line-height:50px;background-color:#f1f1f1;border-bottom:1px solid #ddd;color:#49abe9;font-size:18px;'>
	10. GoUrl <b>Pay-Per-View/Page</b> Example (multiple cryptocurrencies). Use it on your website. 
	<div style='float:right;'><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html#p5'>View Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='<?= "//".$_SERVER["HTTP_HOST"].str_replace("-multi.php", ".php", $_SERVER["REQUEST_URI"]); ?>'>Single Crypto</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html'>Other Examples</a></div>
</div>
<br>
<h1>Example - Pay Per View - Video/Page Access for Unregistered Visitors (multi coins below)</h1>
<h3>Your Website Visitors have to pay for access to your premium video/webpage(s)</h3>
<br>
Price: ~<?= $amountUSD ?> US$ for <?= $period ?> access 
<br><br>

<?php if ($box->is_paid()): ?>

	<!-- Successful Cryptocoin Payment received -->
	<!-- You can use the same payment gateway code for few your pages (section1) -->	 
	<!-- Your Premium Page(s) Code  -->
	 
	<h2 style='color:#339e2e;'>Cryptocoin Payment received<br>Successful Access to Premium Page (during <?= $period ?>)</h2>
	<img alt='Pay Per View Access' border='0' src='https://gourl.io/images/example9_2.jpg'>
	
	
<? else: ?>

	 <!-- Awaiting Payment -->
	<a href='#gourlcryptocoins'><img alt='Awaiting Payment - Pay Per View' border='0' src='https://gourl.io/images/example9.jpg'></a>
	<br><br><br><br>
	<? echo $coins_list;  ?>
	<div style='font-size:12px;margin:30px 0 5px 370px'>Language: &#160; <?= $languages_list ?></div>
	<?= $box->display_cryptobox(true, 520, 230, "padding:3px 6px;margin:10px") ?>
	
<? endif; ?> 	


</div><br><br><br><br><br><br>
<div style='position:absolute;left:0;'><a target="_blank" href="http://validator.w3.org/check?uri=<?= "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>"><img src="https://gourl.io/images/w3c.png" alt="Valid HTML 4.01 Transitional"></a></div>
</body>
</html>