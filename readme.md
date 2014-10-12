@package     CRYPTOCOIN PAYMENT BOX / CRYPTOCOIN CAPTCHA 
@copyright   Copyright (c) 2014 Delta Consultants
@category    Libraries
@website     https://gourl.io
@api         https://gourl.io/cryptocoin_payment_api.html
@demo        https://gourl.io/#section6
@version     1.0
@crypto      Supported Cryptocoins - Bitcoin, Litecoin, Dogecoin, Speedcoin, Darkcoin, Vertcoin, Reddcoin, Feathercoin, Vericoin, Potcoin 


			 

-----------------------------------------------------
1. PHP Script - Cryptocoin Payment Box / Captcha
-----------------------------------------------------

PHP Cryptocoin Payment Box/Captcha is a simple PHP/MySQL script which 
you can easily integrate into your own website in minutes.

Gourl.io committed to your online security. SSL is used to secure cryptocoin 
transactions, data transfer and cryptocoin payment information.

The big benefit of our Cryptocoin Payment Box is that it fully integrated 
on your website, no external payment pages opens (as other payment gateways offer). 
Your website will receive full user payment information immediately after cryptocoin 
payment is made and you can process it in automatic mode. 
You can install multiple payment boxes for different products on the same webpage.

HOW IT WORKS
------------
Usually there will be the following -
a) You install payment box directly on your website and dynamically configure order id, currency, amount to pay, etc.
b) All your users will see coin payment box on your webpage, and some users will use them coin wallets and make payments to you.
c) In around 5 seconds after cryptocoin payment is made, user will see confirmation on your website page that payment is received (i.e. very fast).
d) Your website will automatically immediately receive current user id with full payment information from our payment server.
e) The user will still be on your webpage and see that successful payment result, your script can automatically process payment and give user confirmation (for example, upgrading user membership or giving download link on your products, etc). All in automatic mode - no manual actions are needed.
f) For user that payment procedure on your website will be looking very similar visually and compare with normal credit cards for its speed.
g) During the next 30 minutes (after transaction is verified) payment will be automatically forwarded to your own wallet address.

No paperwork, no chargebacks, no monthly fee and low transaction fee (from 0%). 




-----------------------------------------------------
2. SCRIPT ACCEPT PRICES IN US DOLLARS
-----------------------------------------------------

You can specify your price in USD and submit it in cryptobox using variable 'amountUSD'. 
Cryptobox will automatically convert that USD amount to cryptocoin amount using today live 
cryptocurrency exchange rates. 

Live Exchange Rates obtained from sites cryptsy.com and bitstamp.net and are updated every 30 minutes!

Using that functionality, you don't need to worry if cryptocurrency prices go down or go up. 
User will pay you all times the actual price which is linked on current exchange price in USD on the 
datetime of purchase. 

You can accepting cryptocoins on your website with cryptobox variable 'amountUSD'. 
It increase your online sales and also use Cryptsy.com AutoSell feature 
(to trade your cryptocoins to USD/BTC during next 30 minutes after payment received).




-----------------------------------------------------
3. DIFFERENCE BETWEEN PAYMENT BOX AND CAPTCHA
-----------------------------------------------------

The Payment Box and Captcha are absolutely identical technically 
except their visual effect. It uses the same code to get your user payment, 
to process that  payment and to forward received coins to you. They have 
only two visual differences - users will see different logos and different 
text on successful result page.

For example, for dogecoin it will be - 'Dogecoin Payment'  or 'Dogecoin Captcha' 
logos and when payment is received we will publish 'Payment received successfully' 
or 'Captcha Passed successfully'. We have made it for more easy you adopt our 
crypto coin payment boxes on your website. On signup page you can use 
'Dogecoin Captcha' and on sell products page - 'Dogecoin Payment'. 




-----------------------------------------------------
4. INSTALLATION INSTRUCTIONS
-----------------------------------------------------
a) Free Registration on gourl.io and get private/public keys
b) Edit file cryptobox_config.php, add your db details and your private key
c) Create mysql table cryptobox_payments (sql query below)
d) Place your public/private keys in example.php and run it

THAT'S IT! CRYPTOCOIN PAYMENT BOX SHOULD NOW BE WORKING ON YOUR SITE.

Read more - https://gourl.io/cryptocoin_payment_api.html




---------------------------
5. Archive has seven files
---------------------------
5.1) cryptobox_config.php        - configuration file; please edit it and place your gourl.io details
5.2) cryptobox.class.php         - cryptocoin payment box class PHP/MySQL
5.3) cryptobox.callback.php      - file which processes call-backs from Cryptocoin Payment Box server when new payment from your users comes in. Please link this file in your cryptobox configuration on gourl.io - Callback url: http://yoursite.com/cryptobox.callback.php
5.4) cryptobox.js                - cryptocoin payment box JavaScript
5.5) cryptobox.min.js            - minimized version of JavaScript
5.6) example.php                 - example
5.7) readme.txt                  - this readme file
				  
				  

				  
-----------------------
6. Example of usage
-----------------------

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
		"amount" 	  => 0,			// convert amountUSD to cryptocoin using live exchange rate
		"amountUSD"   => 2,  		// 2 USD
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

		// Your code here to handle a successful cryptocoin payment/captcha verification
		// For example, give user 24 hour access to your member pages
		// ...
	  }  
	  else echo "The payment has not been made yet";

	  // B. One-time Process Received Payment
	  if ($box1->is_paid() && !$box1->is_processed()) 
	  {
		echo "B. User will see this message one time after payment has been made!";	

		// Your code here - for example, send confirmation email to user
		// ...

		// Also you can use $box1->is_confirmed() - retutn true if payment confirmed 
		// Average transaction confirmation time - 10-20min for 6 confirmations  

		// Set Payment Status to Processed
		$box1->set_status_processed(); 

		// Optional, cryptobox_reset() will delete cookies/sessions with userID and 
		// new cryptobox with new payment amount will be show after page reload.
		// Cryptobox will recognize user as a new one with new generated userID
		// $box1->cryptobox_reset(); 
	  }
	  
	echo "</body></html>";
	?> 

API: https://gourl.io/cryptocoin_payment_api.html


-----------------
7. MySQL Table
-----------------

Please also run MySQL query below which will create MySQL
table where will be stored all cryptocoin payments to you.
You can have multiple crypto boxes on site, all of them
relates to your different crypto boxes and will be stored
in that one table :


	CREATE TABLE `crypto_payments` (
	  `paymentID` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `boxID` int(11) unsigned NOT NULL DEFAULT '0',
	  `boxType` enum('paymentbox','captchabox') NOT NULL,
	  `orderID` varchar(50) NOT NULL DEFAULT '',
	  `userID` varchar(50) NOT NULL DEFAULT '',
	  `countryID` varchar(3) NOT NULL DEFAULT '',
	  `coinLabel` varchar(6) NOT NULL DEFAULT '',
	  `amount` double(20,8) NOT NULL DEFAULT '0.00000000',
	  `amountUSD` double(20,8) NOT NULL DEFAULT '0.00000000',
	  `unrecognised` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `addr` varchar(34) NOT NULL DEFAULT '',
	  `txID` char(64) NOT NULL DEFAULT '',
	  `txDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `txConfirmed` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `txCheckDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `processed` tinyint(1) unsigned NOT NULL DEFAULT '0',
	  `processedDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `recordCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`paymentID`),
	  KEY `boxID` (`boxID`),
	  KEY `boxType` (`boxType`),
	  KEY `userID` (`userID`),
	  KEY `countryID` (`countryID`),
	  KEY `orderID` (`orderID`),
	  KEY `amount` (`amount`),
	  KEY `amountUSD` (`amountUSD`),
	  KEY `coinLabel` (`coinLabel`),
	  KEY `unrecognised` (`unrecognised`),
	  KEY `addr` (`addr`),
	  KEY `txID` (`txID`),
	  KEY `txDate` (`txDate`),
	  KEY `txConfirmed` (`txConfirmed`),
	  KEY `txCheckDate` (`txCheckDate`),
	  KEY `processed` (`processed`),
	  KEY `processedDate` (`processedDate`),
	  KEY `recordCreated` (`recordCreated`),
	  KEY `key1` (`boxID`,`orderID`),
	  KEY `key2` (`boxID`,`orderID`,`userID`),
	  KEY `key3` (`boxID`,`orderID`,`userID`,`txID`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
