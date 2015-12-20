<?php
/**
 * @category    Basic Example
 * @package     GoUrl Cryptocurrency Payment API 
 * copyright 	(c) 2014-2016 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, Litecoin, Paycoin, Dogecoin, Dash, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Vericoin, Peercoin, MonetaryUnit
 * @website     https://gourl.io/api-php.html
 */ 
	require_once( "../cryptobox.class.php" );

	$options = array( 
	"public_key"  => "", 		// place your public key from gourl.io
	"private_key" => "", 		// place your private key from gourl.io
	"webdev_key" => "", 		// optional, gourl affiliate key
	"orderID"     => "your_product1_or_signuppage1_etc", // few your users can have the same orderID but combination 'orderID'+'userID' should be unique. 
								// for example, on premium page you can use for all visitors: orderID="premium" and userID="" (empty).
	"userID" 	  => "", 		// optional; when userID value is empty - system will autogenerate unique identifier for every user and save it in cookies
	"userFormat"  => "COOKIE", 	// save your user identifier userID in cookies. Available values: COOKIE, SESSION, IPADDRESS, MANUAL 
	"amount" 	  => 0,			// amount in cryptocurrency or in USD below
	"amountUSD"   => 2,  		// price is 2 USD; it will convert to cryptocoins amount, using Live Exchange Rates
								// For convert fiat currencies Euro/GBP/etc. to USD, use function convert_currency_live() 
	"period"      => "24 HOUR",	// payment valid period, after 1 day user need to pay again
	"iframeID"    => "",    	// optional; when iframeID value is empty - system will autogenerate iframe html payment box id
	"language" 	  => "EN" 		// english, please contact us and we can add your language	
	);  
	// IMPORTANT: Please read description of options here - https://gourl.io/api-php.html#options  

	
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
		// Please use also IPN function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "") for update db records, etc
		// ...
	}  
	else $message .= "The payment has not been made yet";

	
	// B. One-time Process Received Payment
	if ($box1->is_paid() && !$box1->is_processed()) 
	{
		$message .= "B. User will see this message one time after payment has been made!";	
	
		// Your code here - for example, publish order number for user
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
	
	
	
	
	

	/*
	 *  IPN - User Instant Payment Notification Function 
	 *  function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "")
	 *  
	 *  This user-defined function called every time when a new payment from any user is received successfully.
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
	 *  Read more - https://gourl.io/api-php.html
	 *  
	 *  Function gets $paymentID from your table crypto_payments,
	 *  $box_status = 'cryptobox_newrecord' OR 'cryptobox_updated' (description above)
	 *  
	 *  and payment details as array -
	 *  
	 *  1. EXAMPLE - CORRECT PAYMENT - 
	 *  $payment_details = array(
	 *  				"status":			"payment_received",
	 *  				"err":				"",
	 *  				"private_key":		"ZnlH0aD8z3YIkhwOKHjK9GmZl",
	 *  				"box":				"7",
	 *  				"boxtype":			"paymentbox",
	 *  				"order":			"91f7c3edc0f86b5953cf1037796a2439",
	 *  				"user":				"115",
	 *  				"usercountry":		"USA",
	 *  				"amount":			"1097.03916195",
	 *  				"amountusd":		"0.2",
	 *  				"coinlabel":		"DOGE",
	 *  				"coinname":			"dogecoin",
	 *  				"addr":				"DBJBibi39M2Zzyk51dJd5EHqdKbDxR11BH",
	 *  				"tx":				"309621c28ced8ba348579b152a0dbcfdc90586818e16e526c2590c35f8ac2e08",
	 *  				"confirmed":		0,
	 *  				"timestamp":		"1420215494",
	 *  				"date":				"02 January 2015",
	 *  				"datetime":			"2015-01-02 16:18:14"
	 *  			);
	 *						
	 *  2. EXAMPLE - INCORRECT PAYMENT/WRONG AMOUNT - 
	 *  $payment_details = array(
	 *  				"status":			"payment_received_unrecognised",
	 *  				"err":				"An incorrect dogecoin amount has been received",
	 *  				"private_key":		"ZnlH0aD8z3YIkhwOKHjK9GmZl",
	 *  				"box":				"7",
	 *  				"boxtype":			"paymentbox",
	 *  				"order":			"",
	 *  				"user":				"",
	 *  				"usercountry":		"",
	 *  				"amount":			"12",
	 *  				"amountusd":		"0.002184",
	 *  				"coinlabel":		"DOGE",
	 *  				"coinname":			"dogecoin",
	 *  				"addr":				"DBJBibi39M2Zzyk51dJd5EHqdKbDxR11BH",
	 *  				"tx":				"96dadd51287bb7dea904607f7076e8ce121c8428106dd57b403000b0d0a11c6f",
	 *  				"confirmed":		0,
	 *  				"timestamp":		"1420215388",
	 *  				"date":				"02 January 2015",
	 *  				"datetime":			"2015-01-02 16:16:28"
	 *  			);	
	*/

        /********************************************************************************************************/
        /**  This IPN function is used every time a new payment from any user is received successfully         **/
        /**  Function receives paymentID - current payment ID (record id in your mysql table crypto_payments), **/
        /**  payment details as array and box_status - 'cryptobox_newrecord' OR 'cryptobox_updated'.           **/
        /**                                                                                                    **/
        /**  Move this function to the bottom of the file cryptobox.class.php or create a separate file        **/
        /**  More info: https://gourl.io/api-php.html#ipn                                                      **/
        /********************************************************************************************************/
        function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "")
        {
		// Your php code here to handle a successful cryptocoin payment/captcha verification
		// for example, send confirmation email to user
		// update user membership, etc - https://gourl.io/api-php.html#ipn

		// .... ....
		
		return true;
         }
		
	
	
	
?>

<!DOCTYPE html>
<html><head>
<meta http-equiv='cache-control' content='no-cache'>
<meta http-equiv='Expires' content='-1'>
<script src='../cryptobox.min.js' type='text/javascript'></script>
</head>
<body>

<?php echo $paymentbox; ?>
<?php echo $message; ?>

</body>
</html>