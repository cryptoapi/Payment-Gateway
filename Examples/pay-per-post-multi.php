<?php
/**
 * @category    Example6 - Pay-Per-Post (payments in multiple cryptocurrencies, you can use original price in USD)
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2020 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, BitcoinCash, BitcoinSV, Litecoin, Dash, Dogecoin, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Peercoin, MonetaryUnit, UniversalCurrency
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p3
 * @live_demo   https://gourl.io/lib/examples/pay-per-post-multi.php
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
	
	$userID 		= "";				// place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
										// if userID is empty, system will autogenerate userID and save in cookies
	$userFormat		= "COOKIE";			// save userID in cookies (or you can use IPADDRESS, SESSION)
	$orderID 		= "post1";			// if you manual setup userID, you need to update orderID for users who already paid before: post1, post2, post3  
	$amountUSD		= 0.5;				// price per one post - 0.5 USD
										// for convert fiat currencies Euro/GBP/etc. to USD, use function convert_currency_live()
	$period			= "NOEXPIRY";		// one time payment for each new user post, not expiry
	$def_language	= "en";				// default Payment Box Language
	$def_payment	= "bitcoin";		// Default Coin in Payment Box

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
			"orderID"     => $orderID, 		// order id
			"userID"      => $userID, 		// unique identifier for every user
			"userFormat"  => $userFormat, 	// save userID in COOKIE, IPADDRESS or SESSION
			"amount"   	  => 0,				// post price in coins OR in USD below
			"amountUSD"   => $amountUSD,	// we use post price in USD
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
	$coins_list = display_currency_box($available_payments, $def_payment, $def_language, 60, "margin: 80px 0 0 0", "images");
	
	
	
	
	// Form Data
	// --------------------------
	$ftitle	 = (isset($_POST["ftitle"])) ? $_POST["ftitle"] : "";
	$ftext 	 = (isset($_POST["ftext"])) ? $_POST["ftext"] : "";
	
	$error = "";
	$successful = false;
	
	if (isset($_POST) && isset($_POST["ftitle"]))
	{
		if (!$ftitle)    		$error .= "<li>Please enter Title</li>";
		if (!$ftext)   			$error .= "<li>Please enter Text</li>";
		if (!$box->is_paid()) 	$error .= "<li>".$coinName."s have not yet been received</li>";
		if ($error)				$error  = "<br><ul style='color:#eb4847'>$error</ul>";
		
		if ($box->is_paid() && !$error)
		{
			// Successful Cryptocoin Payment received
			// Your code here - 
			// ...
			// ...
					
			// Set Payment Status to Processed
			$successful = true;
			$box->set_status_processed();
			
			// Optional, cryptobox_reset() will delete cookies/sessions with userID and
			// new cryptobox with new payment amount will be show after page reload.
			// Cryptobox will recognize user as a new one with new generated userID
			// If you manual setup userID, you need to change orderID also
			$box->cryptobox_reset();
		}
	}
	// --------------------------






	// ...
	// Also you need to use IPN function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "") 
	// for send confirmation email, update database, update user membership, etc.
	// You need to modify file - cryptobox.newpayment.php, read more - https://gourl.io/api-php.html#ipn
	// ...
		
	
	
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title>Pay-Per-Post Cryptocoin (payments in multiple cryptocurrencies) Payment Example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<meta name='robots' content='all'>
<script src='../js/cryptobox.min.js' type='text/javascript'></script>
</head>
<body style='font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#666;margin:0'>
<div align='center'>
<div style='width:100%;height:auto;line-height:50px;background-color:#f1f1f1;border-bottom:1px solid #ddd;color:#49abe9;font-size:18px;'>
	6. GoUrl <b>Pay-Per-Post</b> Example (multiple cryptocurrencies). Use it on your website. 
	<div style='float:right;'><a style='font-size:15px;color:#389ad8;margin-right:20px' href='<?= "//".$_SERVER["HTTP_HOST"].str_replace("-multi.php", ".php", $_SERVER["REQUEST_URI"]); ?>'>Single Crypto</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html#p3'>PHP Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://github.com/cryptoapi/Bitcoin-Payment-Gateway-ASP.NET/tree/master/GoUrl/Views/Examples'>ASP.NET Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/lib/examples/example_customize_box.php'>NEW - Payment Box 2018 (Mobile Friendly)</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://wordpress.org/plugins/gourl-bitcoin-payment-gateway-paid-downloads-membership/'>Wordpress</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html'>Other Examples</a></div>
</div>
<h1>Example - Paid Posts (multi coins below)</h1>
You can sell right to publish new posts on your website
<br><br><br>
<img alt='Invoice' border='0' src='https://gourl.io/images/example6.png'>

<a name='i'></a>

<?php if ($successful): ?>

	<div align='center'>
		<img alt='New Post' border='0' src='https://gourl.io/images/example7.png'>
		<div style='margin:40px;font-size:24px;color:#339e2e;font-weight:bold'>Your text has been successfully posted on our website!</div>
		<a href='pay-per-post-multi.php'>Publish new posts &#187;</a>
	</div>	
	
<?php else: ?>

	<form name='form1' style='font-size:14px;color:#444' action="pay-per-post-multi.php#i" method="post">
		<table cellspacing='20'>
			<tr><td colspan='2'><img alt='New Post' border='0' src='https://gourl.io/images/example7.png'><?php echo $error; ?></td></tr>
			<tr><td width='100'>Title: </td><td width='300'><input style='padding:6px;font-size:18px;' size='40' type="text" name="ftitle" value="<?php echo $ftitle; ?>"></td></tr>
			<tr><td>Text: </td><td><textarea style='padding:6px;font-size:18px;' rows="4" cols="40" name="ftext"><?php echo $ftext; ?></textarea></td></tr>
			<?php if (!$box->is_paid()): ?>
				<tr><td colspan='2'>* You need to pay <?php echo $coinName; ?>s (~<?php echo $amountUSD; ?> US$) for posting your text on our website</td></tr>
			<?php endif; ?>
		</table>
	</form>

	<div style='width:600px;background-color:#f9f9f9;padding-top:10px'>
			<div style='font-size:12px;<?php if ($box->is_paid()) echo "margin:5px 0 5px 390px;"; else echo "margin:5px 0 5px 390px; position:absolute;"; ?>'>Language: &#160; <?php echo $languages_list; ?></div>
			<?php if (!$box->is_paid()) echo "<div align='left'>".$coins_list."</div>"; ?>
			<?php echo $box->display_cryptobox(); ?>
	</div>
	
	<br><br><br>
	<button onclick='document.form1.submit()' style='padding:6px 20px;font-size:18px;'>Post Your Article/Comment</button>
	
<?php endif; ?> 	


</div><br><br><br><br><br><br>
<div style='position:absolute;left:0;'><a target="_blank" href="http://validator.w3.org/check?uri=<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>"><img src="https://gourl.io/images/w3c.png" alt="Valid HTML 4.01 Transitional"></a></div>
</body>
</html>