<?php
/**
 * @category    Example5 - Pay-Per-Post (single crypto currency in payment box)
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2015 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, Litecoin, Dogecoin, Speedcoin, Darkcoin, Vertcoin, Reddcoin, Feathercoin, Vericoin, Potcoin
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p3
 * @live_demo   http://gourl.io/lib/examples/pay-per-post.php
 */ 
	
	require_once( "../cryptobox.class.php" );

	
	/**** CONFIGURATION VARIABLES ****/ 
	
	$userID 		= "";				// place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
										// if userID is empty, it will autogenerate userID and save in cookies
	$userFormat		= "COOKIE";			// save userID in cookies (or you can use IPADDRESS, SESSION)
	$orderID 		= "post1";			// if you manual setup userID, you need to update orderID for users who already paid before: post1, post2, post3  
	$amountUSD		= 0.5;				// price per one post - 0.5 USD
	$period			= "NOEXPIRY";		// one time payment for each new user post, not expiry
	$def_language	= "en";				// default Payment Box Language
	$public_key		= "-your public key for coin box-"; // from gourl.io
	$private_key	= "-your private key for coin box-";// from gourl.io

	
	/********************************/


	
	
	

	/** PAYMENT BOX **/
	$options = array(
			"public_key"  => $public_key, 	// your public key from gourl.io
			"private_key" => $private_key, 	// your private key from gourl.io
			"webdev_key"  => "", 		// optional, gourl affiliate key
			"orderID"     => $orderID, 		// order id
			"userID"      => $userID, 		// unique identifier for each your user
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
			// save text in db / publish user post on your website ...
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
	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?= $coinName ?> Pay-Per-Post Cryptocoin Payment Example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<meta name='robots' content='all'>
<script src='../cryptobox.min.js' type='text/javascript'></script>
</head>
<body style='font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#666;margin:0'>
<div align='center'>
<div style='width:100%;height:auto;line-height:50px;background-color:#f1f1f1;border-bottom:1px solid #ddd;color:#49abe9;font-size:18px;'>
	5. GoUrl <b>Pay-Per-Post</b> Example (<?= $coinName ?> payments). Use it on your website. 
	<div style='float:right;'><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/Examples/pay-per-post.php'>View Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='<?= "//".$_SERVER["HTTP_HOST"].str_replace(".php", "-multi.php", $_SERVER["REQUEST_URI"]); ?>'>Multiple Crypto</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html'>Other Examples</a></div>
</div>
<h1>Example - Paid Posts</h1>
You can sell right to publish new posts on your website
<br><br><br>
<img alt='Invoice' border='0' src='https://gourl.io/images/example6.png'>

<a name='i'></a>

<?php if ($successful): ?>

	<div align='center'>
		<img alt='New Post' border='0' src='https://gourl.io/images/example7.png'>
		<div style='margin:40px;font-size:24px;color:#339e2e;font-weight:bold'>Your text has been successfully posted on our website!</div>
		<a href='pay-per-post.php'>Publish new posts &#187;</a>
	</div>	
	
<? else: ?>

	<form name='form1' style='font-size:14px;color:#444' action="pay-per-post.php#i" method="post">
		<table cellspacing='20'>
			<tr><td colspan='2'><img alt='New Post' border='0' src='https://gourl.io/images/example7.png'><?= $error ?></td></tr>
			<tr><td width='100'>Title: </td><td width='300'><input style='padding:6px;font-size:18px;' size='40' type="text" name="ftitle" value="<?= $ftitle ?>"></td></tr>
			<tr><td>Text: </td><td><textarea style='padding:6px;font-size:18px;' rows="4" cols="40" name="ftext"><?= $ftext ?></textarea></td></tr>
			<?php if (!$box->is_paid()): ?>
				<tr><td colspan='2'>* You need to pay <?= $coinName ?>s (~<?= $amountUSD ?> US$) for posting your text on our website</td></tr>
			<? endif; ?>
		</table>
	</form>

	<div style='width:600px;background-color:#f9f9f9;padding-top:10px'>
			<div style='font-size:12px; margin:5px 0 5px 390px;'>Language: &#160; <?= $languages_list ?></div>
			<?= $box->display_cryptobox() ?>
	</div>
	
	<br><br><br>
	<button onclick='document.form1.submit()' style='padding:6px 20px;font-size:18px;'>Post Your Article/Comment</button>
	
<? endif; ?> 	


</div><br><br><br><br><br><br>
<div style='position:absolute;left:0;'><a target="_blank" href="http://validator.w3.org/check?uri=<?= "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>"><img src="https://gourl.io/images/w3c.png" alt="Valid HTML 4.01 Transitional"></a></div>
</body>
</html>