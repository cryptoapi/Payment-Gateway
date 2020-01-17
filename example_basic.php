<?php
/**
 * @category    Main Example - Custom Payment Box ((json, bootstrap4, mobile friendly, white label product, your own logo)        
 * @package     GoUrl Cryptocurrency Payment API
 * copyright 	(c) 2014-2020 Delta Consultants
 * @desc     	GoUrl Crypto Payment Box Example (json, bootstrap4, mobile friendly, optional - free White Label Product - Bitcoin/altcoin Payments with your own logo and all payment requests through your server, open source)
 * @crypto      Supported Cryptocoins -	Bitcoin, BitcoinCash, BitcoinSV, Litecoin, Dash, Dogecoin, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Peercoin, MonetaryUnit, UniversalCurrency
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p8
 * @live_demo   https://gourl.io/lib/examples/example_customize_box.php
 * @note	You can delete folders - 'Examples', 'Screenshots' from this archive
 */ 
    

	/********************** NOTE - 2018-2020 YEARS *************************************************************************/ 
	/*****                                                                                                             *****/ 
	/*****     This is NEW 2018-2020 latest Bitcoin Payment Box Example  (mobile friendly JSON payment box)            *****/ 
	/*****                                                                                                             *****/ 
	/*****     You can generate php payment box code online - https://gourl.io/lib/examples/example_customize_box.php  *****/
	/*****         White Label Product - https://gourl.io/lib/test/example_customize_box.php?method=curl&logo=custom   *****/
	/*****         Light Theme - https://gourl.io/lib/examples/example_customize_box.php?theme=black                   *****/
	/*****         Black Theme - https://gourl.io/lib/examples/example_customize_box.php?theme=default     		   *****/
	/*****         Your Own Logo - https://gourl.io/lib/examples/example_customize_box.php?theme=default&logo=custom   *****/
	/*****                                                                                                             *****/ 
	/***********************************************************************************************************************/




	
	
	// Change path to your files
	// --------------------------------------
	DEFINE("CRYPTOBOX_PHP_FILES_PATH", "lib/");        	// path to directory with files: cryptobox.class.php / cryptobox.callback.php / cryptobox.newpayment.php;         
                                                        // cryptobox.newpayment.php will be automatically call through ajax/php two times - payment received/confirmed
	DEFINE("CRYPTOBOX_IMG_FILES_PATH", "images/");      // path to directory with coin image files (directory 'images' by default)
	DEFINE("CRYPTOBOX_JS_FILES_PATH", "js/");			// path to directory with files: ajax.min.js/support.min.js
	
	
	// Change values below
	// --------------------------------------
	DEFINE("CRYPTOBOX_LANGUAGE_HTMLID", "alang");	// any value; customize - language selection list html id; change it to any other - for example 'aa';	default 'alang'
	DEFINE("CRYPTOBOX_COINS_HTMLID", "acoin");		// any value;  customize - coins selection list html id; change it to any other - for example 'bb';	default 'acoin'
	DEFINE("CRYPTOBOX_PREFIX_HTMLID", "acrypto_");	// any value; prefix for all html elements; change it to any other - for example 'cc';	default 'acrypto_'
	
	
	// Open Source Bitcoin Payment Library
	// ---------------------------------------------------------------
	require_once(CRYPTOBOX_PHP_FILES_PATH . "cryptobox.class.php" );
	
	
	
	/*********************************************************/
	/****  PAYMENT BOX CONFIGURATION VARIABLES  ****/
	/*********************************************************/
	
	// IMPORTANT: Please read description of options here - https://gourl.io/api-php.html#options
	
	$userID 			= "";        // place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
									  // You can use php $_SESSION["userABC"] for store userID, amount, etc
									  // You don't need to use userID for unregistered website visitors - $userID = "";
									  // if userID is empty, system will autogenerate userID and save it in cookies
	$userFormat		= "COOKIE";       // save userID in cookies (or you can use IPADDRESS, SESSION, MANUAL)
	$orderID		= "invoice000383";	  // invoice #000383
	$amountUSD		= 0.12;			  // invoice amount - 0.12 USD; or you can use - $amountUSD = convert_currency_live("EUR", "USD", 22.37); // convert 22.37EUR to USD
	
	$period			= "NOEXPIRY";	  // one time payment, not expiry
	$def_language	= "en";			  // default Language in payment box
	$def_coin		= "bitcoin";      // default Coin in payment box
	
	
	
	// List of coins that you accept for payments
	//$coins = array('bitcoin', 'bitcoincash', 'bitcoinsv', 'litecoin', 'dogecoin', 'dash', 'speedcoin', 'reddcoin', 'potcoin', 'feathercoin', 'vertcoin', 'peercoin', 'monetaryunit', 'universalcurrency');
	$coins = array('bitcoin', 'bitcoincash', 'litecoin', 'dogecoin', 'dash', 'speedcoin');  // for example, accept payments in bitcoin, bitcoincash, litecoin, 'dogecoin', dash, speedcoin 
	
	// Create record for each your coin - https://gourl.io/editrecord/coin_boxes/0 ; and get free gourl keys
	// It is not bitcoin wallet private keys! Place GoUrl Public/Private keys below for all coins which you accept
	
	
	
	
	$all_keys = array(	"bitcoin"  => 		array("public_key" => "-your public key for Bitcoin box-",  "private_key" => "-your private key for Bitcoin box-"),
					"bitcoincash"  =>	array("public_key" => "-your public key for BitcoinCash box-",  "private_key" => "-your private key for BitcoinCash box-"),
					"litecoin" => 		array("public_key" => "-your public key for Litecoin box-", "private_key" => "-your private key for Litecoin box-")); // etc.
			 
	// Demo Keys; for tests	(example - 5 coins)
	$all_keys = array(	"bitcoin"   => array("public_key" => "25654AAo79c3Bitcoin77BTCPUBqwIefT1j9fqqMwUtMI0huVL",  
										    "private_key" => "25654AAo79c3Bitcoin77BTCPRV0JG7w3jg0Tc5Pfi34U8o5JE"),
					  "bitcoincash" => array("public_key" => "25656AAeOGaPBitcoincash77BCHPUBOGF20MLcgvHMoXHmMRx", 
					  					    "private_key" => "25656AAeOGaPBitcoincash77BCHPRV8quZcxPwfEc93ArGB6D"),
					  "litecoin"   => array("public_key"  => "25657AAOwwzoLitecoin77LTCPUB4PVkUmYCa2dR770wNNstdk", 
					  					    "private_key" => "25657AAOwwzoLitecoin77LTCPRV7hmp8s3ew6pwgOMgxMq81F"),
					  "dogecoin"   => array("public_key"  => "25678AACxnGODogecoin77DOGEPUBZEaJlR9W48LUYagmT9LU8",
					  					    "private_key" => "25678AACxnGODogecoin77DOGEPRVFvl6IDdisuWHVJLo5m4eq"),
					  "dash"       => array("public_key"  => "25658AAo79c3Dash77DASHPUBqwIefT1j9fqqMwUtMI0huVL0J", 
					  					    "private_key" => "25658AAo79c3Dash77DASHPRVG7w3jg0Tc5Pfi34U8o5JEiTss"),
					  "speedcoin"  => array("public_key"  => "20116AA36hi8Speedcoin77SPDPUBjTMX31yIra1IBRssY7yFy", 
					  					    "private_key" => "20116AA36hi8Speedcoin77SPDPRVNOwjzYNqVn4Sn5XOwMI2c")); // Demo keys!

	//  IMPORTANT: Add in file /lib/cryptobox.config.php your database settings and your gourl.io coin private keys (need for Instant Payment Notifications) -
	/* if you use demo keys above, please add to /lib/cryptobox.config.php - 
		$cryptobox_private_keys = array("25654AAo79c3Bitcoin77BTCPRV0JG7w3jg0Tc5Pfi34U8o5JE", 
					"25656AAeOGaPBitcoincash77BCHPRV8quZcxPwfEc93ArGB6D", "25657AAOwwzoLitecoin77LTCPRV7hmp8s3ew6pwgOMgxMq81F", 
					"25678AACxnGODogecoin77DOGEPRVFvl6IDdisuWHVJLo5m4eq", "25658AAo79c3Dash77DASHPRVG7w3jg0Tc5Pfi34U8o5JEiTss", 
					"20116AA36hi8Speedcoin77SPDPRVNOwjzYNqVn4Sn5XOwMI2c");
	 	Also create table "crypto_payments" in your database, sql code - https://github.com/cryptoapi/Payment-Gateway#mysql-table
	 	Instruction - https://gourl.io/api-php.html 	 	
 	*/				   
	
	
	    
	
	// Re-test - all gourl public/private keys
	$def_coin = strtolower($def_coin);
	if (!in_array($def_coin, $coins)) $coins[] = $def_coin;  
	foreach($coins as $v)
	{
		if (!isset($all_keys[$v]["public_key"]) || !isset($all_keys[$v]["private_key"])) die("Please add your public/private keys for '$v' in \$all_keys variable");
		elseif (!strpos($all_keys[$v]["public_key"], "PUB"))  die("Invalid public key for '$v' in \$all_keys variable");
		elseif (!strpos($all_keys[$v]["private_key"], "PRV")) die("Invalid private key for '$v' in \$all_keys variable");
		elseif (strpos(CRYPTOBOX_PRIVATE_KEYS, $all_keys[$v]["private_key"]) === false) 
				die("Please add your private key for '$v' in variable \$cryptobox_private_keys, file /lib/cryptobox.config.php.");
	}
	
	
	
	
	
	// Current selected coin by user
	$coinName = cryptobox_selcoin($coins, $def_coin);
	
	
	// Current Coin public/private keys
	$public_key  = $all_keys[$coinName]["public_key"];
	$private_key = $all_keys[$coinName]["private_key"];
	
	
	
	
	
	
	/** PAYMENT BOX **/
	$options = array(
	    "public_key"  	=> $public_key,	    // your public key from gourl.io
	    "private_key" 	=> $private_key,	// your private key from gourl.io
	    "webdev_key"  	=> "", 			    // optional, gourl affiliate key
	    "orderID"     	=> $orderID, 		// order id or product name
	    "userID"      		=> $userID, 	// unique identifier for every user
	    "userFormat"  	=> $userFormat, 	// save userID in COOKIE, IPADDRESS, SESSION  or MANUAL
	    "amount"   	  	=> 0,			    // product price in btc/bch/bsv/ltc/doge/etc OR setup price in USD below
	    "amountUSD"   	=> $amountUSD,	    // we use product price in USD
	    "period"      		=> $period, 	// payment valid period
	    "language"	  	=> $def_language    // text on EN - english, FR - french, etc
	);
	
	// Initialise Payment Class
	$box = new Cryptobox ($options);
	
	// coin name
	$coinName = $box->coin_name();
	
	// php code end :)
	// ---------------------
	
	// NOW PLACE IN FILE "lib/cryptobox.newpayment.php", function cryptobox_new_payment(..) YOUR ACTIONS -
	// WHEN PAYMENT RECEIVED (update database, send confirmation email, update user membership, etc)
	// IPN function cryptobox_new_payment(..) will automatically appear for each new payment two times - payment received and payment confirmed
	// Read more - https://gourl.io/api-php.html#ipn
	
?>
        	
        	
        	
        	
        	
        	
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <title>Payment Box</title>


    <!-- Bootstrap4 CSS - -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" crossorigin="anonymous">   
      
    <!-- Note - If your website not use Bootstrap4 CSS as main style, please use custom css style below and delete css line above. 
    It isolate Bootstrap CSS to a particular class 'bootstrapiso' to avoid css conflicts with your site main css style -->
    <!-- <link rel="stylesheet" href="css/bootstrapcustom.min.css" crossorigin="anonymous"> -->

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" crossorigin="anonymous"></script>
	<script defer src="https://use.fontawesome.com/releases/v5.12.0/js/all.js" crossorigin="anonymous"></script>
    <script src="<?php echo CRYPTOBOX_JS_FILES_PATH; ?>support.min.js" crossorigin="anonymous"></script> 

    <!-- CSS for Payment Box -->
    <style>
            html { font-size: 14px; }
            @media (min-width: 768px) { html { font-size: 16px; } .tooltip-inner { max-width: 350px; } }
            .mncrpt .container { max-width: 980px; }
            .mncrpt .box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
            img.radioimage-select { padding: 7px; border: solid 2px #ffffff; margin: 7px 1px; cursor: pointer; box-shadow: none; }
            img.radioimage-select:hover { border: solid 2px #a5c1e5; }
            img.radioimage-select.radioimage-checked { border: solid 2px #7db8d9; background-color: #f4f8fb; }
    </style>
  </head>

  <body>

  <?php
  
    // Text above payment box
    $custom_text  = "<p class='lead'>Demo Text - Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>";
    $custom_text .= "<p class='lead'>Please contact us for any questions on aaa@example.com</p>";
     
    // Display payment box 	
    echo $box->display_cryptobox_bootstrap($coins, $def_coin, $def_language, $custom_text, 70, 200, true, "default", "default", 250, "", "curl", true);
    

    // You can setup method='curl' in function above and use code below on this webpage -
    // if successful bitcoin payment received .... allow user to access your premium data/files/products, etc.
    // if ($box->is_paid()) { ... your code here ... }


   ?>
    
  </body>
</html>