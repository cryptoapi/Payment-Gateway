<?	
	  echo "<!DOCTYPE html><html><head>	
	  <meta http-equiv='cache-control' content='no-cache'>	
	  <meta http-equiv='Expires' content='-1'>	
	  <script src='cryptobox.min.js' type='text/javascript'></script>	
	  </head><body>";	

 	  require_once( "cryptobox.class.php" );	

 	  $options = array( 	
		"public_key"  => "", 		// place your public key from gourl.io	
		"private_key" => "", 		// place your private key from gourl.io	
		"orderID"     => "your_product1_or_signuppage1_etc", // order name, not unique	
		"userID" 	  => "", 		// autogenerate unique identifier for each your user	
		"userFormat"  => "COOKIE", 	// save your user identifier userID in cookies	
		"amount" 	  => 0,			// you can use amount (cryptocoins) or amountUSD (US$)	
		"amountUSD"   => 2,  		// 2 USD, convert amountUSD to cryptocoin amount using live exchange rate	
		"period"      => "24 HOUR",	// after 24 hours new payment box will be display	
		"iframeID"    => "",    	// autogenerate iframe html payment box id	
		"language" 	  => "EN" 		// english, please contact us and we can add your language	      	
	  );  	

 	  // Initialise Payment Class	
	  $box1 = new Cryptobox ($options);	

 	  // Display Payment Box or successful payment result   	
	  echo $box1->display_cryptobox();	

 	  // A. Process Received Payment	
	  if ($box1->is_paid()) 	
	  { 	
		echo "A. User will see this message during 24 hours after payment has been made!";	


 		echo "<br>".$box1->amount_paid()." ".$box1->coin_label()."  received<br>";
