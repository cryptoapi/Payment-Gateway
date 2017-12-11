<?php
/**
 * @category    Basic Example
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2018 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, BitcoinCash, Litecoin, Dash, Dogecoin, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Peercoin, MonetaryUnit, UniversalCurrency
 * @website     https://gourl.io/api-php.html
 */ 
	require_once( "../cryptobox.class.php" );


	$orderID	=  "your_product1_or_signuppage1_etc";
	$userID		= "";
	$def_language	= "en";	// default payment box language; en - English, es - Spanish, fr - French, de - German, nl - Dutch, it - Italian, ru - Russian, pl - Polish, pt - Portuguese, fa - Persian, ko - Korean, ja - Japanese, id - Indonesian, tr - Turkish, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi

	// Remove all the characters from the string other than a..Z0..9_-@. 
	$orderID = preg_replace('/[^A-Za-z0-9\.\_\-\@]/', '', $orderID);
	$userID = preg_replace('/[^A-Za-z0-9\.\_\-\@]/', '', $userID);


	$options = array( 
	"public_key"  => "", 		// place your public key from gourl.io
	"private_key" => "", 		// place your private key from gourl.io
	"webdev_key" =>  "", 		// optional, gourl affiliate key
	"orderID"     => $orderID,  // few your users can have the same orderID but combination 'orderID'+'userID' should be unique. 
								// for example, on premium page you can use for all visitors: orderID="premium" and userID="" (empty).
	"userID" 	  => $userID, 	// optional; when userID value is empty - system will autogenerate unique identifier for every user and save it in cookies
	"userFormat"  => "COOKIE", 	// save your user identifier userID in cookies. Available values: COOKIE, SESSION, IPADDRESS, MANUAL 
	"amount" 	  => 0,			// amount in cryptocurrency or in USD below
	"amountUSD"   => 2,  		// price is 2 USD; it will convert to cryptocoins amount, using Live Exchange Rates
								// *** For convert Euro/GBP/etc. to USD/Bitcoin, use function convert_currency_live() with Google Finance
								// *** examples: convert_currency_live("EUR", "BTC", 22.37) - convert 22.37 Euro to Bitcoin
								// *** convert_currency_live("EUR", "USD", 22.37) - convert 22.37 Euro to USD
	"period"      => "24 HOUR",	// payment valid period, after 1 day user need to pay again
	"iframeID"    => "",    	// optional; when iframeID value is empty - system will autogenerate iframe html payment box id
	"language" 	  => $def_language // default language
	);  
	// IMPORTANT: Please read description of options here - https://gourl.io/api-php.html#options  

	
	// Initialise Payment Class
	$box1 = new Cryptobox ($options);

	// Display Payment Box or successful payment result   
	$paymentbox = $box1->display_cryptobox();

	// Language selection list for payment box (html code)
	$languages_list = display_language_box($def_language);

	// Log
	$message = "";
	

	// A. Process Received Payment
	if ($box1->is_paid()) 
	{ 
		$message .= "A. User will see this message during ".$options["period"]." period after payment has been made!";
		
		$message .= "<br>".$box1->amount_paid()." ".$box1->coin_label()."  received<br>";
		
		// Your code here to handle a successful cryptocoin payment/captcha verification
		// For example, give user 24 hour access to your member pages
	

	}  
	else $message .= "The payment has not been made yet";

	
	// B. One-time Process Received Payment
	if ($box1->is_paid() && !$box1->is_processed()) 
	{
		$message .= "B. User will see this message one time after payment has been made!";	
	
		// Your code here - for example, publish order number for user
		// !!For update db records, please use function cryptobox_new_payment()!!
		// ...

		// Also you can use $box1->is_confirmed() - return true if payment confirmed 
		// Average transaction confirmation time - 10-20min for 6 confirmations  
		
		// Set Payment Status to Processed
		$box1->set_status_processed(); 
		
		// Optional, cryptobox_reset() will delete cookies/sessions with userID and 
		// new cryptobox with new payment amount will be show after page reload.
		// Cryptobox will recognize user as a new one with new generated userID
		// $box1->cryptobox_reset(); 
	}
	
	
	
	

	// ...
	// Also you need to use IPN function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "")
	// for send confirmation email, update database, update user membership, etc.
	// You need to modify file - cryptobox.newpayment.php, read more - https://gourl.io/api-php.html#ipn
	// ...



?>

<!DOCTYPE html>
<html><head>
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<script src='../cryptobox.min.js' type='text/javascript'></script>
</head>
<body>

<div style='margin:30px 0 5px 480px'>Language: &#160; <?php echo $languages_list; ?></div>
<?php echo $paymentbox; ?>
<center><?php echo $message; ?></center>
 
</body>
</html>