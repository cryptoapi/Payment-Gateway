<?php
/**
 * @category    Example - Custom Payment Box (json format; customise your bitcoin/altcoin payment box with your own text / logo)   
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
	DEFINE("CRYPTOBOX_PHP_FILES_PATH", "../lib/");        	// path to directory with files: cryptobox.class.php / cryptobox.callback.php / cryptobox.newpayment.php;         
                                                        // cryptobox.newpayment.php will be automatically call through ajax/php two times - payment received/confirmed
	DEFINE("CRYPTOBOX_IMG_FILES_PATH", "../images/");      // path to directory with coin image files (directory 'images' by default)
	DEFINE("CRYPTOBOX_JS_FILES_PATH", "../js/");			// path to directory with files: ajax.min.js/support.min.js
	
	
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
	$orderID		= "invoice1";	  // invoice #1
	$amountUSD		= 0.10;			  // invoice amount - 0.10 USD; or you can use - $amountUSD = convert_currency_live("EUR", "USD", 22.37); // convert 22.37EUR to USD
	
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
	

    
        // Change payment box parameters online
        // Code for demo page below
        
        $page  = "//".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; // Current page url
        $page .= (strpos($page, "?")) ? "&" : "?";

        
        
        // Reset Settings
        // ---------------------
        if (isset($_GET["reset"]))
        {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-1000);
                setcookie($name, '', time()-1000, '/');
            }
            
            header("Location: //".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]); 
            echo "<script> window.location.href = '//".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]."'</script>";
            die();
        }
        
        
        
        // Theme Selection
        // ---------------------
        if (isset($_GET["theme"])) 
        {
            $theme = $_GET["theme"];
            setcookie("dtheme", $theme);
        }
        else $theme = (isset($_COOKIE["dtheme"])) ? $_COOKIE["dtheme"] : "default"; 
      
        if ($theme == "black")          $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "greyred")    $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/superhero/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "greygreen")  $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/solar/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "whiteblue")  $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/cerulean/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "whitered")   $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/united/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "whitegreen") $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/flatly/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "whiteblack") $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/lux/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "whitepurple")$css =  '<link rel="stylesheet" href="https://bootswatch.com/4/pulse/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "litera")     $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/litera/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "minty")      $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/minty/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "sandstone")  $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/sandstone/bootstrap.css" crossorigin="anonymous">';
        elseif ($theme == "sketchy")    $css =  '<link rel="stylesheet" href="https://bootswatch.com/4/sketchy/bootstrap.css" crossorigin="anonymous">';
        else                            $css =  '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" crossorigin="anonymous">';

        // If your website not use Bootstrap4 as main style, please use custom css style below.
        // It isolate Bootstrap CSS to a particular class 'bootstrapiso' to avoid css conflicts with your site main css style
        if ($theme == "black")          $css2 =  '<link rel="stylesheet" href="../css/darkly.min.css" crossorigin="anonymous">';
        elseif ($theme == "greyred")    $css2 =  '<link rel="stylesheet" href="../css/superhero.min.css" crossorigin="anonymous">';
        elseif ($theme == "greygreen")  $css2 =  '<link rel="stylesheet" href="../css/solar.min.css" crossorigin="anonymous">';
        elseif ($theme == "whiteblue")  $css2 =  '<link rel="stylesheet" href="../css/cerulean.min.css" crossorigin="anonymous">';
        elseif ($theme == "whitered")   $css2 =  '<link rel="stylesheet" href="../css/united.min.css" crossorigin="anonymous">';
        elseif ($theme == "whitegreen") $css2 =  '<link rel="stylesheet" href="../css/flatly.min.css" crossorigin="anonymous">';
        elseif ($theme == "whiteblack") $css2 =  '<link rel="stylesheet" href="../css/lux.min.css" crossorigin="anonymous">';
        elseif ($theme == "whitepurple")$css2 =  '<link rel="stylesheet" href="../css/pulse.min.css" crossorigin="anonymous">';
        elseif ($theme == "litera")     $css2 =  '<link rel="stylesheet" href="../css/litera.min.css" crossorigin="anonymous">';
        elseif ($theme == "minty")      $css2 =  '<link rel="stylesheet" href="../css/minty.min.css" crossorigin="anonymous">';
        elseif ($theme == "sandstone")  $css2 =  '<link rel="stylesheet" href="../css/sandstone.min.css" crossorigin="anonymous">';
        elseif ($theme == "sketchy")    $css2 =  '<link rel="stylesheet" href="../css/sketchy.min.css" crossorigin="anonymous">';
        else                            $css2 =  '<link rel="stylesheet" href="../css/bootstrapcustom.min.css" crossorigin="anonymous">';

        // -- End Theme ---------------------
        
        
        
        // Box Type
        // ---------------------
        if (isset($_GET["boxtype"]))
        {
            $boxtype = $_GET["boxtype"];
            setcookie("dboxtype", $boxtype);
        }
        else $boxtype = (isset($_COOKIE["dboxtype"])) ? $_COOKIE["dboxtype"] : "1";
        $boxtype = intval($boxtype);
        
        // payment received
        if ($boxtype == "2" && !$box->is_paid())
        {
            
            $options = array(
                "public_key"  => "20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL",        // your public key from gourl.io
                "private_key" => "20AAvZCcgBitcoin77BTCPRVkW3K4eNMfYTIQGiYG1QYpOOP1n",       // your private key from gourl.io
                "webdev_key"  => "",                 // optional, gourl affiliate key
                "orderID"     => "invoice1",         // order id or product name
                "userID"      => "demo",             // unique identifier for every user
                "userFormat"  => "MANUAL",           // save userID in COOKIE, IPADDRESS or SESSION
                "amount"   	  => 0,                  // product price in coins OR in USD below
                "amountUSD"   => 0.1,                // we use product price in USD
                "period"      => "NOEXPIRY",         // payment valid period
                "language"	  => $def_language       // text on EN - english, FR - french, etc
            );
            
            // Re-Initialise Payment Class
            $box = new Cryptobox ($options);
        }
        // -- End boxtype ---------------------
        
        
        
        // Logo Selection
        // ---------------------
        if (isset($_GET["logo"]))
        {
            $logo = $_GET["logo"];
            setcookie("dlogo", $logo);
        }
        else $logo = (isset($_COOKIE["dlogo"])) ? $_COOKIE["dlogo"] : "custom";
        
        if ($logo == "custom")         $logoimg_path =  CRYPTOBOX_IMG_FILES_PATH.'your_logo.png';
        elseif ($logo == "no")         $logoimg_path =  '';
        else                           $logoimg_path =  'default';
        // -- End logo ---------------------

        
        
        // Logo Selection
        // ---------------------
        if (isset($_GET["lan"]))
        {
            $lan = $_GET["lan"];
            setcookie("dlan", $lan);
        }
        else $lan = (isset($_COOKIE["dlan"])) ? $_COOKIE["dlan"] : "yes";
        
        if ($lan == "yes")            $show_languages =  true;
        else                          $show_languages =  false;
        // -- End lan ---------------------

        
        
        // Coins Menu
        // ---------------------
        if (isset($_GET["numcoin"]))
        {
            $numcoin = $_GET["numcoin"];
            setcookie("dnumcoin", $numcoin);
        }
        else $numcoin = (isset($_COOKIE["dnumcoin"])) ? $_COOKIE["dnumcoin"] : 6;
        $numcoin = intval($numcoin);
        
        if ($numcoin > 15) $numcoin = 6;
        $coins = array_slice($coins, 0, $numcoin);
        // -- End numcoin ---------------------
        
        
        
        // Coin Images Size Menu
        // ---------------------
        if (isset($_GET["coinImageSize"]))
        {
            $coinImageSize = $_GET["coinImageSize"];
            setcookie("dcoinImageSize", $coinImageSize);
        }
        else $coinImageSize = (isset($_COOKIE["dcoinImageSize"])) ? $_COOKIE["dcoinImageSize"] : 70;
        $coinImageSize = intval($coinImageSize);
        
        if ($coinImageSize > 200) $coinImageSize = 70;
        if ($coinImageSize == 70 && in_array($theme, array("black", "greyred", "greygreen"))) $coinImageSize = 71;
        
        // -- End coinImageSize ---------------------

        
        
        // Coin Images Size Menu
        // ---------------------
        if (isset($_GET["qrcodeSize"]))
        {
            $qrcodeSize = $_GET["qrcodeSize"];
            setcookie("dqrcodeSize", $qrcodeSize);
        }
        else $qrcodeSize = (isset($_COOKIE["dqrcodeSize"])) ? $_COOKIE["dqrcodeSize"] : 200;
        $qrcodeSize = intval($qrcodeSize);
        
        if ($qrcodeSize > 500) $qrcodeSize = 200;
        
        // -- End qrcodeSize ---------------------
        
        
        
        // Image on Result Page
        // ---------------------
        if (isset($_GET["resimage"]))
        {
            $resimage = $_GET["resimage"];
            setcookie("dresimage", $resimage);
        }
        else $resimage = (isset($_COOKIE["dresimage"])) ? $_COOKIE["dresimage"] : "default";
        
        if ($resimage == "image2")          $resultimg_path = "images/paid2.png";
        else if ($resimage == "image3")     $resultimg_path = "images/paid3.png";
        else if ($resimage == "custom")     $resultimg_path = "images/your_logo_res.jpg";
        else                                $resultimg_path = "default";
        
        // -- End resimage ---------------------
        
        
        
        
        // Image Size on Result Page
        // ---------------------
        if (isset($_GET["resultimgSize"]))
        {
            $resultimgSize = $_GET["resultimgSize"];
            setcookie("dresultimgSize", $resultimgSize);
        }
        else $resultimgSize = (isset($_COOKIE["dresultimgSize"])) ? $_COOKIE["dresultimgSize"] : 250;
        $resultimgSize = intval($resultimgSize);
        
        if ($resultimgSize > 500) $resultimgSize = 250;
        
        // -- End resultimgSize ---------------------
        
        
        
        
        // Data Method (ajax or curl)
        // ---------------------
        if (isset($_GET["method"]))
        {
            $method = $_GET["method"];
            setcookie("dmethod", $method);
        }
        else $method = (isset($_COOKIE["dmethod"])) ? $_COOKIE["dmethod"] : "curl";
        
        if (!in_array($method, array("ajax", "curl"))) $method = "curl";
        
        // -- End data method ---------------------        
        
        
        
        // Debug
        // ---------------------
        if (isset($_GET["deb"]))
        {
            $deb = $_GET["deb"];
            setcookie("ddeb", $deb);
        }
        else $deb = (isset($_COOKIE["ddeb"])) ? $_COOKIE["ddeb"] : "yes";
        
        if ($deb == "yes")            $debug =  true;
        else                          $debug =  false;
        // -- End debug ---------------------
        
      
        
    ?>

<!-- More info - https://gourl.io/bitcoin-payment-gateway-api.html -->


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>GoUrl.io Bitcoin Payment for Bootstrap 4 (Customize/Mobile Friendly/JSON Box)</title>


    <!-- Bootstrap CSS - -->
    <?php echo $css; ?>


    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" crossorigin="anonymous"></script>
	<script defer src="https://use.fontawesome.com/releases/v5.12.0/js/all.js" crossorigin="anonymous"></script>
    <script src="<?php echo CRYPTOBOX_JS_FILES_PATH; ?>support.min.js" crossorigin="anonymous"></script> 

    <!-- Custom styles for crypto payment box, copy it to your file also -->
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


	<div class='mncrpt'>
    <div class="d-flex flex-column flex-md-row align-items-center card card-body bg-light border-bottom" style="border-bottom: 1px solid #e5e5e5;">
      <h5 class="my-0 mr-md-auto font-weight-normal" style="line-height:30px"><b>GoUrl Crypto Payment Box Example</b> (json, bootstrap4, mobile friendly, optional - free <b><a target="_blank" href="https://www.google.com/search?q=white+label+product">White Label Product</a></b> - Bitcoin/altcoin Payments with your own logo and all payment requests through your server, open source).</h5>
      <nav class="my-3 my-md-0 mr-md-3">
        <a class="p-2" href="https://github.com/cryptoapi/Payment-Gateway/blob/master/example_basic.php">Page Source</a>
        <a class="p-2" href="https://gourl.io/api-php.html">Instruction</a>
        <a class="p-2" href="https://gourl.io/bitcoin-payment-gateway-api.html">Other Examples</a>
      </nav>
      <a class="btn btn-info" href="https://gourl.io">GoUrl.io</a>
    </div>


	<div class='mncrpt px-2 py-3 mx-auto my-4 text-center' style='max-width:1600px; white-space:normal'>
	<br>
	<div class='card card-body bg-light d-inline-block'>
			
		<h1 class="display-4">Customize GoUrl Bitcoin/ Altcoin Payment Box (2020 year)</h1>
		<p class='lead'>See live <a href='#dmgnpcode'>generated php/html code</a> for your website below (<a target="_blank" href="https://github.com/cryptoapi/Payment-Gateway">open source class</a>)</p>

        <div id="dropdown1" class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Colour Themes
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
            <a class="dropdown-item<?php if ($theme == "default") echo " active"; ?>" href="<?php echo $page . "theme=default#b" ?>">Default</a>
            <a class="dropdown-item<?php if ($theme == "black") echo " active"; ?>" href="<?php echo $page . "theme=black#b" ?>">Black</a>
            <a class="dropdown-item<?php if ($theme == "greyred") echo " active"; ?>" href="<?php echo $page . "theme=greyred#b" ?>">LightGrey/Red</a>
            <a class="dropdown-item<?php if ($theme == "greygreen") echo " active"; ?>" href="<?php echo $page . "theme=greygreen#b" ?>">DarkGrey/Green</a>
            <a class="dropdown-item<?php if ($theme == "whiteblue") echo " active"; ?>" href="<?php echo $page . "theme=whiteblue#b" ?>">White/Blue</a>
            <a class="dropdown-item<?php if ($theme == "whitered") echo " active"; ?>" href="<?php echo $page . "theme=whitered#b" ?>">White/Red</a>
            <a class="dropdown-item<?php if ($theme == "whitegreen") echo " active"; ?>" href="<?php echo $page . "theme=whitegreen#b" ?>">White/Green</a>
            <a class="dropdown-item<?php if ($theme == "sandstone") echo " active"; ?>" href="<?php echo $page . "theme=sandstone#b" ?>">White/Lime Green</a>
            <a class="dropdown-item<?php if ($theme == "whiteblack") echo " active"; ?>" href="<?php echo $page . "theme=whiteblack#b" ?>">White/Black</a>
            <a class="dropdown-item<?php if ($theme == "whitepurple") echo " active"; ?>" href="<?php echo $page . "theme=whitepurple#b" ?>">White/Purple</a>
            <a class="dropdown-item<?php if ($theme == "litera") echo " active"; ?>" href="<?php echo $page . "theme=litera#b" ?>">Light Blue (Rounded)</a>
            <a class="dropdown-item<?php if ($theme == "minty") echo " active"; ?>" href="<?php echo $page . "theme=minty#b" ?>">Light Green (Rounded)</a>
            <a class="dropdown-item<?php if ($theme == "sketchy") echo " active"; ?>" href="<?php echo $page . "theme=sketchy#b" ?>">Sketchy :)</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" target="_blank" href="https://bootswatch.com/">More ...</a>
          </div>
        </div>
        
    
        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Payment Box Type
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
            <a class="dropdown-item<?php if ($boxtype == 1) echo " active"; ?>" href="<?php echo $page . "boxtype=1#b" ?>">Awaiting Payment</a>
            <a class="dropdown-item<?php if ($boxtype == 2) echo " active"; ?>" href="<?php echo $page . "boxtype=2#b" ?>">Payment Received (demo)</a>
          </div>
        </div>
    
    
        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton3" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Box Logo
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
            <a class="dropdown-item<?php if ($logo == "default") echo " active"; ?>" href="<?php echo $page . "logo=default#b" ?>">Default Logo</a>
            <a class="dropdown-item<?php if ($logo == "custom") echo " active"; ?>" href="<?php echo $page . "logo=custom#b" ?>">Your Own Logo</a>
            <a class="dropdown-item<?php if ($logo == "no") echo " active"; ?>" href="<?php echo $page . "logo=no#b" ?>">No Logo</a>
          </div>
        </div>
    
    
        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton4" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Language Menu
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton4">
            <a class="dropdown-item<?php if ($lan == "yes") echo " active"; ?>" href="<?php echo $page . "lan=yes#b" ?>">Show</a>
            <a class="dropdown-item<?php if ($lan == "no") echo " active"; ?>" href="<?php echo $page . "lan=no#b" ?>">Hide</a>
          </div>
        </div>
    
        
        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton5" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Coins Menu
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton5">
            <a class="dropdown-item<?php if ($numcoin == 1) echo " active"; ?>" href="<?php echo $page . "numcoin=1#b" ?>">Single Coin</a>
            <a class="dropdown-item<?php if ($numcoin == 2) echo " active"; ?>" href="<?php echo $page . "numcoin=2#b" ?>">Two Coins</a>
            <a class="dropdown-item<?php if ($numcoin == 3) echo " active"; ?>" href="<?php echo $page . "numcoin=3#b" ?>">Three Coins</a>
            <a class="dropdown-item<?php if ($numcoin == 4) echo " active"; ?>" href="<?php echo $page . "numcoin=4#b" ?>">Four Coins</a>
            <a class="dropdown-item<?php if ($numcoin == 5) echo " active"; ?>" href="<?php echo $page . "numcoin=5#b" ?>">Five Coins</a>
            <a class="dropdown-item<?php if ($numcoin == 6) echo " active"; ?>" href="<?php echo $page . "numcoin=6#b" ?>">Six Coins</a>
            <a class="dropdown-item<?php if ($numcoin == 15) echo " active"; ?>" href="<?php echo $page. "numcoin=15#b" ?>">All Coins</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" target="_blank" href="https://github.com/cryptoapi/Payment-Gateway/tree/master/images">Edit images ...</a>
          </div>
        </div>


        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton6" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Coin Images Size
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton6">
            <a class="dropdown-item<?php if ($coinImageSize == 50) echo " active"; ?>" href="<?php echo $page . "coinImageSize=50#b" ?>">50px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 60) echo " active"; ?>" href="<?php echo $page . "coinImageSize=60#b" ?>">60px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 70 || $coinImageSize == 71) echo " active"; ?>" href="<?php echo $page . "coinImageSize=70#b" ?>">Default (70px)</a>
            <a class="dropdown-item<?php if ($coinImageSize == 80) echo " active"; ?>" href="<?php echo $page . "coinImageSize=80#b" ?>">80px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 90) echo " active"; ?>" href="<?php echo $page . "coinImageSize=90#b" ?>">90px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 110) echo " active"; ?>" href="<?php echo $page . "coinImageSize=110#b" ?>">110px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 130) echo " active"; ?>" href="<?php echo $page . "coinImageSize=130#b" ?>">130px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 150) echo " active"; ?>" href="<?php echo $page . "coinImageSize=150#b" ?>">150px</a>
            <a class="dropdown-item<?php if ($coinImageSize == 200) echo " active"; ?>" href="<?php echo $page. "coinImageSize=200#b" ?>">200px</a>
          </div>
        </div>


        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            QR Code Size 
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton7">
            <a class="dropdown-item<?php if ($qrcodeSize == 0) echo " active"; ?>" href="<?php echo $page . "qrcodeSize=no&boxtype=1#b" ?>">Hide in Payment Box</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 50) echo " active"; ?>" href="<?php echo $page . "qrcodeSize=50&boxtype=1#b" ?>">50px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 100) echo " active"; ?>" href="<?php echo $page . "qrcodeSize=100&boxtype=1#b" ?>">100px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 150) echo " active"; ?>" href="<?php echo $page . "qrcodeSize=150&boxtype=1#b" ?>">150px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 200) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=200&boxtype=1#b" ?>">Default (200px)</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 250) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=250&boxtype=1#b" ?>">250px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 300) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=300&boxtype=1#b" ?>">300px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 350) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=350&boxtype=1#b" ?>">350px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 400) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=400&boxtype=1#b" ?>">400px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 450) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=450&boxtype=1#b" ?>">450px</a>
            <a class="dropdown-item<?php if ($qrcodeSize == 500) echo " active"; ?>" href="<?php echo $page. "qrcodeSize=500&boxtype=1#b" ?>">500px</a>
          </div>
        </div>


        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton8" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Payment Received Image
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton8">
            <a class="dropdown-item<?php if ($resimage == "default") echo " active"; ?>" href="<?php echo $page . "resimage=default&boxtype=2#b" ?>">Default Image</a>
            <a class="dropdown-item<?php if ($resimage == "image2") echo " active"; ?>" href="<?php echo $page . "resimage=image2&boxtype=2#b" ?>">Image #2</a>
            <a class="dropdown-item<?php if ($resimage == "image3") echo " active"; ?>" href="<?php echo $page . "resimage=image3&boxtype=2#b" ?>">Image #3</a>
            <a class="dropdown-item<?php if ($resimage == "custom") echo " active"; ?>" href="<?php echo $page . "resimage=custom&boxtype=2#b" ?>">Your Own Image</a>
          </div>
        </div>


        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton9" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Payment Res Image Size
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton9">
            <a class="dropdown-item<?php if ($resultimgSize == 0) echo " active"; ?>" href="<?php echo $page . "resultimgSize=no&boxtype=2#b" ?>">Hide in Result Box</a>
            <a class="dropdown-item<?php if ($resultimgSize == 50) echo " active"; ?>" href="<?php echo $page . "resultimgSize=50&boxtype=2#b" ?>">50px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 100) echo " active"; ?>" href="<?php echo $page . "resultimgSize=100&boxtype=2#b" ?>">100px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 150) echo " active"; ?>" href="<?php echo $page . "resultimgSize=150&boxtype=2#b" ?>">150px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 200) echo " active"; ?>" href="<?php echo $page. "resultimgSize=200&boxtype=2#b" ?>">200px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 250) echo " active"; ?>" href="<?php echo $page. "resultimgSize=250&boxtype=2#b" ?>">Default (250px)</a>
            <a class="dropdown-item<?php if ($resultimgSize == 300) echo " active"; ?>" href="<?php echo $page. "resultimgSize=300&boxtype=2#b" ?>">300px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 350) echo " active"; ?>" href="<?php echo $page. "resultimgSize=350&boxtype=2#b" ?>">350px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 400) echo " active"; ?>" href="<?php echo $page. "resultimgSize=400&boxtype=2#b" ?>">400px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 450) echo " active"; ?>" href="<?php echo $page. "resultimgSize=450&boxtype=2#b" ?>">450px</a>
            <a class="dropdown-item<?php if ($resultimgSize == 500) echo " active"; ?>" href="<?php echo $page. "resultimgSize=500&boxtype=2#b" ?>">500px</a>
          </div>
        </div>


        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton10" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Data Methods (White Label/Ajax)
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton10">
            <a class="dropdown-item<?php if ($method == "curl") echo " active"; ?>" href="<?php echo $page . "method=curl&boxtype=1#b" ?>"><b>CURL</b> + Your Own Logo (White Label Product), user need to click on button when payment is sent</a>
            <a class="dropdown-item disabled" href="#a">curl - User browser receive payment data from your website only (not even know about gourl.io); your website receive data from our server gourl.io</a>
            <a class="dropdown-item<?php if ($method == "ajax") echo " active"; ?>" href="<?php echo $page . "method=ajax&boxtype=1#b" ?>"><b>AJAX</b> - user don't need click any submit buttons</a>
            <a class="dropdown-item disabled" href="#a">ajax - User browser receive payment data directly from our server and payment box show successful paid message automatically</a>
          </div>
        </div>


        <div class="d-inline-block dropdown mx-3 my-3">
          <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton11" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Debug Raw Values Box
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton11">
            <a class="dropdown-item<?php if ($deb == "yes") echo " active"; ?>" href="<?php echo $page . "deb=yes#b" ?>">Show</a>
            <a class="dropdown-item<?php if ($deb == "no") echo " active"; ?>" href="<?php echo $page . "deb=no#b" ?>">Hide</a>
          </div>
        </div>

        <div class="d-inline-block mx-3 my-3">
			<a class="btn btn-secondary" href="<?php echo $page . "reset=yes" ?>" role="button">Reset All Settings</a>
		</div>

		</div>
	</div>
    </div>


  <?php 
    
        if ($method == "curl" && !in_array($theme, array("black", "greyred", "greygreen"))) echo "<div class='text-center'><br><img style='max-width:100%; height:auto; width:auto\9;' alt='White Label Product' src='../images/white-label.png'><br><br><br></div>"; 
        echo "<a id='b'></a>"; // anchor for demo options only; don't need on live your server 
  
  
        // PAYMENT BOX 
        // --------------------------------------------------------
  
        $custom_text = "<p class='lead'>Demo Text - Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>";
        $custom_text .= "<p class='lead'>Please contact us for any questions on aaa@example.com</p>";
        
        // use function display_cryptobox_bootstrap ($coins = array(), $def_coin = "", $def_language = "en", $custom_text = "", $coinImageSize = 70, $qrcodeSize = 200, $show_languages = true, $logoimg_path = "default", $resultimg_path = "default", $resultimgSize = 250, $redirect = "", $method = "curl", $debug = false)
        echo $box->display_cryptobox_bootstrap($coins, $def_coin, $def_language, $custom_text, $coinImageSize, $qrcodeSize, $show_languages, $logoimg_path, $resultimg_path, $resultimgSize, "", $method, $debug);
        
        // End --------------------------------------------------------
        
        

  
        
        
  ?>
  
  	<?php if ($debug) { ?><div style="margin-top:-60px" class="text-center"><a href='<?php echo $page . "deb=no#b" ?>'>Hide Debug Log</a> <small>(or use dropdown <a href="#dropdown1">menu</a> above "Debug Raw Values Box")</small></div><?php } ?>
  

  
  	<div id='dmgnpcode' class='mncrpt px-2 py-3 mx-auto my-4 text-center' style='max-width:1450px; white-space:normal; display:none'>
		<br><br><br><br>
		<h1 class="display-6">Generated paymentbox php code for your website</h1>
		 <p class='lead'>create new php file with this code (default <a target="_blank" href="https://github.com/cryptoapi/Payment-Gateway">location</a> ); ready to use (php, json, bootstrap4, mobile friendly, white label product / your own logo). <a href='https://gourl.io/bitcoin-payment-gateway-api.html#p8'>Read instruction</a></p>  
      	<div class="form-group my-4">
        	<textarea class="form-control bg-light" id="exampleFormControlTextarea1" rows="50" readonly>
&lt;?php

	// bitcoin/altcoin payment box; open source
	
	// Change path to your files
	// --------------------------------------
	DEFINE("CRYPTOBOX_PHP_FILES_PATH", "lib/");        	// path to directory with files: cryptobox.class.php / cryptobox.callback.php / cryptobox.newpayment.php; 
												// cryptobox.newpayment.php will be automatically call through ajax/php two times - payment received/confirmed
	DEFINE("CRYPTOBOX_IMG_FILES_PATH", "images/");	// path to directory with coin image files (directory 'images' by default)
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
	
	$userID 				= "";			 // place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
									// You can use php $_SESSION["userABC"] for store userID, amount, etc
									// You don't need to use userID for unregistered website visitors - $userID = "";
									// if userID is empty, system will autogenerate userID and save it in cookies
	$userFormat		= "COOKIE";		// save userID in cookies (or you can use IPADDRESS, SESSION, MANUAL)
	$orderID			= "invoice1";	    	// invoice number - 000383
	$amountUSD		= 2.21;			// invoice amount - 2.21 USD; or you can use - $amountUSD = convert_currency_live("EUR", "USD", 22.37); // convert 22.37EUR to USD
	
	$period			= "NOEXPIRY";	// one time payment, not expiry
	$def_language	= "<?php echo CRYPTOBOX_LANGUAGE; ?>";			// default Language in payment box
	$def_coin			= "<?php echo strtolower($coinName); ?>";		// default Coin in payment box
	
	
	// List of coins that you accept for payments
	//$coins = array('bitcoin', 'bitcoincash', 'bitcoinsv', 'litecoin', 'dogecoin', 'dash', 'speedcoin', 'reddcoin', 'potcoin', 'feathercoin', 'vertcoin', 'peercoin', 'monetaryunit', 'universalcurrency');
	$coins = array('bitcoin', 'litecoin', 'dogecoin', 'dash', 'speedcoin');  // for example, accept payments in bitcoin, bitcoincash, litecoin, dash, speedcoin 
	<?php if ($numcoin == 1 && in_array($coinName, array('bitcoin', 'bitcoincash', 'litecoin', 'dogecoin', 'dash', 'speedcoin'))) echo "\$coins = array(\"$coinName\");  // accept payments in $coinName only \n\r"; ?>

	// Create record for each your coin - https://gourl.io/editrecord/coin_boxes/0 ; and get free gourl keys
	// It is not bitcoin wallet private keys! Place GoUrl Public/Private keys below for all coins which you accept
	
	$all_keys = array(	"bitcoin"  => 		array("public_key" => "-your public key for Bitcoin box-",  "private_key" => "-your private key for Bitcoin box-"),
					"bitcoincash"  =>	array("public_key" => "-your public key for BitcoinCash box-",  "private_key" => "-your private key for BitcoinCash box-"),
					"litecoin" => 		array("public_key" => "-your public key for Litecoin box-", "private_key" => "-your private key for Litecoin box-")); // etc.
			 
	// Demo Keys; for tests	(example - 5 coins)
	$all_keys = array("bitcoin" => array(	"public_key" => "25654AAo79c3Bitcoin77BTCPUBqwIefT1j9fqqMwUtMI0huVL",  
										"private_key" => "25654AAo79c3Bitcoin77BTCPRV0JG7w3jg0Tc5Pfi34U8o5JE"),
					  "bitcoincash" => array("public_key" => "25656AAeOGaPBitcoincash77BCHPUBOGF20MLcgvHMoXHmMRx", 
					  					"private_key" => "25656AAeOGaPBitcoincash77BCHPRV8quZcxPwfEc93ArGB6D"),
					  "litecoin" => array(	"public_key" => "25657AAOwwzoLitecoin77LTCPUB4PVkUmYCa2dR770wNNstdk", 
					  					"private_key" => "25657AAOwwzoLitecoin77LTCPRV7hmp8s3ew6pwgOMgxMq81F"),
					  "dogecoin" => array(	"public_key" => "25678AACxnGODogecoin77DOGEPUBZEaJlR9W48LUYagmT9LU8", 
					  					"private_key" => "25678AACxnGODogecoin77DOGEPRVFvl6IDdisuWHVJLo5m4eq"),
					  "dash" => array(		"public_key" => "25658AAo79c3Dash77DASHPUBqwIefT1j9fqqMwUtMI0huVL0J", 
					  					"private_key" => "25658AAo79c3Dash77DASHPRVG7w3jg0Tc5Pfi34U8o5JEiTss"),
					  "speedcoin" => array(	"public_key" => "20116AA36hi8Speedcoin77SPDPUBjTMX31yIra1IBRssY7yFy", 
					  					"private_key" => "20116AA36hi8Speedcoin77SPDPRVNOwjzYNqVn4Sn5XOwMI2c")); // Demo keys!

	//  IMPORTANT: Add in file /lib/cryptobox.config.php your database settings and your gourl.io coin private keys (need for Instant Payment Notifications) -
	/* if you use demo keys above, please add to /lib/cryptobox.config.php - 
		$cryptobox_private_keys = array("25654AAo79c3Bitcoin77BTCPRV0JG7w3jg0Tc5Pfi34U8o5JE", "25678AACxnGODogecoin77DOGEPRVFvl6IDdisuWHVJLo5m4eq", 
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
	    "public_key"  	=&gt; $public_key,	// your public key from gourl.io
	    "private_key" 	=&gt; $private_key,	// your private key from gourl.io
	    "webdev_key"  	=&gt; "", 			// optional, gourl affiliate key
	    "orderID"     	=&gt; $orderID, 		// order id or product name
	    "userID"      		=&gt; $userID, 		// unique identifier for every user
	    "userFormat"  	=&gt; $userFormat, 	// save userID in COOKIE, IPADDRESS, SESSION  or MANUAL
	    "amount"   	  	=&gt; 0,			// product price in btc/bch/bsv/ltc/doge/etc OR setup price in USD below
	    "amountUSD"   	=&gt; $amountUSD,	// we use product price in USD
	    "period"      		=&gt; $period, 		// payment valid period
	    "language"	  	=&gt; $def_language  // text on EN - english, FR - french, etc
	);
	
	// Initialise Payment Class
	$box = new Cryptobox ($options);
	
	// coin name
	$coinName = $box-&gt;coin_name();

	// php code end :)
	// ---------------------
	
	// NOW PLACE IN FILE "lib/cryptobox.newpayment.php", function cryptobox_new_payment(..) YOUR ACTIONS -
	// WHEN PAYMENT RECEIVED (update database, send confirmation email, update user membership, etc)
	// IPN function cryptobox_new_payment(..) will automatically appear for each new payment two times - payment received and payment confirmed
	// Read more - https://gourl.io/api-php.html#ipn
?&gt;
        	
        	
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
  &lt;head&gt;
    &lt;meta charset="utf-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;
    &lt;meta http-equiv="X-UA-Compatible" content="IE=edge"&gt;
    &lt;meta name="description" content=""&gt;
    &lt;title&gt;Payment Box&lt;/title&gt;

    &lt;!-- Bootstrap4 CSS - --&gt;
    <?php echo $css; ?>   
      
    &lt;!-- Note - If your website not use Bootstrap4 CSS as main style, please use custom css style below and delete css line above. 
    It isolate Bootstrap CSS to a particular class 'bootstrapiso' to avoid css conflicts with your site main css style --&gt;
    &lt;!-- <?php echo $css2; ?> --&gt;


    &lt;!-- JS --&gt;
    &lt;script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" crossorigin="anonymous"&gt;&lt;/script&gt;
    &lt;script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js" crossorigin="anonymous"&gt;&lt;/script&gt;
    &lt;script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" crossorigin="anonymous"&gt;&lt;/script&gt;
    &lt;script defer src="https://use.fontawesome.com/releases/v5.12.0/js/all.js" crossorigin="anonymous"&gt;&lt;/script&gt;
    &lt;script src="&lt;?php echo CRYPTOBOX_JS_FILES_PATH; ?&gt;support.min.js" crossorigin="anonymous"&gt;&lt;/script&gt;

    &lt;!-- CSS for Payment Box --&gt;
    &lt;style&gt;
            html { font-size: 14px; }
            @media (min-width: 768px) { html { font-size: 16px; } .tooltip-inner { max-width: 350px; } }
            .mncrpt .container { max-width: 980px; }
            .mncrpt .box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
            img.radioimage-select { padding: 7px; border: solid 2px #ffffff; margin: 7px 1px; cursor: pointer; box-shadow: none; }
            img.radioimage-select:hover { border: solid 2px #a5c1e5; }
            img.radioimage-select.radioimage-checked { border: solid 2px #7db8d9; background-color: #f4f8fb; }
    &lt;/style&gt;
  &lt;/head&gt;

  &lt;body&gt;

  &lt;?php
  
    // Text above payment box
    $custom_text  = "&lt;p class='lead'&gt;Demo Text - Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.&lt;/p&gt;";
    $custom_text .= "&lt;p class='lead'&gt;Please contact us for any questions on aaa@example.com&lt;/p&gt;";
     
    // Display payment box 	
    echo $box-&gt;display_cryptobox_bootstrap(<?php echo "\$coins, \$def_coin, \$def_language, \$custom_text, $coinImageSize, $qrcodeSize, ".($show_languages?"true":"false").", \"$logoimg_path\", \"$resultimg_path\", $resultimgSize, \"\", \"$method\", ".($debug?"true":"false"); ?>);
    

    // You can setup method='curl' in function above and use code below on this webpage -
    // if successful bitcoin payment received .... allow user to access your premium data/files/products, etc.
    // if ($box-&gt;is_paid()) { ... your code here ... }


   ?&gt;
  
  &lt;/body&gt;
&lt;/html&gt;</textarea>
      	</div>
  <br>

<br><br>
<p><b>Next Steps</b></p>
<ul class="list-group">
  <li class="list-group-item"><a target="_blank" href="https://gourl.io/lib/examples/box_only.php">1. View Payment Box Only (default settings, no menu, no footer)</a></li>
  <li class="list-group-item"><a target="_blank" href="https://gourl.io/api-php.html">2. Payment Class Installation Instruction</a></li>
  <li class="list-group-item"><a target="_blank" href="https://github.com/cryptoapi/Payment-Gateway">3. PHP Bitcoin Payment Class on GitHub (free open source)</a></li>
  <li class="list-group-item"><a target="_blank" href="https://gourl.io/bitcoin-payment-gateway-api.html#p8">4. JSON Payment Box Instruction</a></li>
</ul>


  	</div>
  
  
  
  

   <footer class="container pt-4 my-md-5 pt-md-5" style="border-top: 1px solid #e5e5e5;">
        <div class="row">
          <div class="col-12 col-md">
            <img class="mb-2" src="https://getbootstrap.com/docs/4.4/assets/brand/bootstrap-solid.svg" alt="" width="24" height="24">
            <small class="d-block mb-3 text-muted">&copy; 2014-2020</small>
            <br>
            <div><a target="_blank" href="https://validator.w3.org/nu/?showsource=yes&amp;doc=<?php echo "https://".$_SERVER[HTTP_HOST].urlencode($_SERVER[REQUEST_URI]); ?>"><img title="Markup Validation Service" src="https://gourl.io/images/w3c.png" alt="Valid HTML 5"></a></div>
          </div>
          <div class="col-6 col-md">
            <h5>Features</h5>
            <ul class="list-unstyled text-small">
              <li><a class="text-muted" href="#a">Cool stuff</a></li>
              <li><a class="text-muted" href="#a">Random feature</a></li>
              <li><a class="text-muted" href="#a">Team feature</a></li>
              <li><a class="text-muted" href="#a">Stuff for developers</a></li>
              <li><a class="text-muted" href="#a">Another one</a></li>
              <li><a class="text-muted" href="#a">Last time</a></li>
            </ul>
          </div>
          <div class="col-6 col-md">
            <h5>Resources</h5>
            <ul class="list-unstyled text-small">
              <li><a class="text-muted" href="#a">Resource</a></li>
              <li><a class="text-muted" href="#a">Resource name</a></li>
              <li><a class="text-muted" href="#a">Another resource</a></li>
              <li><a class="text-muted" href="#a">Final resource</a></li>
            </ul>
          </div>
          <div class="col-6 col-md">
            <h5>About</h5>
            <ul class="list-unstyled text-small">
              <li><a class="text-muted" href="#a">Team</a></li>
              <li><a class="text-muted" href="#a">Locations</a></li>
              <li><a class="text-muted" href="#a">Privacy</a></li>
              <li><a class="text-muted" href="#a">Terms</a></li>
            </ul>
          </div>
        </div>
      </footer>

  </body>

</html>
