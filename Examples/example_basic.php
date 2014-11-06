<?php
/**
 * @category    Basic Example
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, Litecoin, Dogecoin, Speedcoin, Darkcoin, Vertcoin, Reddcoin, Feathercoin, Vericoin, Potcoin
 * @website     https://gourl.io/cryptocoin_payment_api.html
 */ 
	require_once( "../cryptobox.class.php" );

	$options = array( 
	"public_key"  => "", 		// place your public key from gourl.io
	"private_key" => "", 		// place your private key from gourl.io
	"webdev_key" => "", 		// optional, gourl affiliate key
	"orderID"     => "your_product1_or_signuppage1_etc", // order name, not unique
	"userID" 	  => "", 		// autogenerate unique identifier for each your user
	"userFormat"  => "COOKIE", 	// save your user identifier userID in cookies
	"amount" 	  => 0,			// convert amountUSD to dogecoin using live exchange rate
	"amountUSD"   => 2,  		// 2 USD
	"period"      => "24 HOUR",	// payment valid period, after 1 day user need to pay again
	"iframeID"    => "",    	// autogenerate iframe html payment box id
	"language" 	  => "EN" 		// english, please contact us and we can add your language	
	);  

	// Initialise Payment Class
	$box1 = new Cryptobox ($options);

	// Display Payment Box or successful payment result   
	$paymentbox = $box1->display_cryptobox();

	// Log
	$message = "";
	
	// A. Process Received Payment
	if ($box1->is_paid()) 
	{ 
		$message .= "A. User will see this message during 24 hours after payment has been made!";
		
		$message .= "<br>".$box1->amount_paid()." ".$box1->coin_label()."  received<br>";
		
		// Your code here to handle a successful cryptocoin payment/captcha verification
		// For example, give user 24 hour access to your member pages
		// ...
	}  
	else $message .= "The payment has not been made yet";

	
	// B. One-time Process Received Payment
	if ($box1->is_paid() && !$box1->is_processed()) 
	{
		$message .= "B. User will see this message one time after payment has been made!";	
	
		// Your code here - for example, send confirmation email to user
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
?>

<!DOCTYPE html>
<html><head>
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<script src='../cryptobox.min.js' type='text/javascript'></script>
</head>
<body>

<?= $paymentbox ?>
<?= $message ?>

</body>
</html>