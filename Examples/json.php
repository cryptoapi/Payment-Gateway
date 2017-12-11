<?php
/**
 * @category    Example14 - Custom Payment Box (json format; customise your bitcoin/altcoin payment box with your own text / logo)
 * @package     GoUrl Cryptocurrency Payment API
 * copyright 	(c) 2014-2018 Delta Consultants
 * @crypto      Supported Cryptocoins -	Bitcoin, BitcoinCash, Litecoin, Dash, Dogecoin, Speedcoin, Reddcoin, Potcoin, Feathercoin, Vertcoin, Peercoin, MonetaryUnit, UniversalCurrency
 * @website     https://gourl.io/bitcoin-payment-gateway-api.html#p8
 * @live_demo   https://gourl.io/lib/examples/json.php
 */ 

    $message    = "";
    $path       = "../";
    $page_url   = "//".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."#gourlcryptolang"; // Current page url
    
    require_once( $path."cryptobox.class.php" );
    
    
	/**** CONFIGURATION VARIABLES ****/ 
	
	$userID 		= "";			 // place your registered userID or md5(userID) here (user1, user7, uo43DC, etc).
							         // you don't need to use userID for unregistered website visitors
							         // if userID is empty, system will autogenerate userID and save in cookies
	$userFormat		= "COOKIE";		 // save userID in cookies (or you can use IPADDRESS, SESSION)
	$orderID 		= "invoice22";	 // invoice number 22
	$amountUSD		= 2.21;			 // invoice amount - 2.21 USD
	$period			= "NOEXPIRY";	 // one time payment, not expiry
	$def_language	= "en";			 // default Payment Box Language
	$public_key		= "-your public key for coin box-";   // from gourl.io
	$private_key	= "-your private key for coin box-";  // from gourl.io

	// IMPORTANT: Please read description of options here - https://gourl.io/api-php.html#options  
	
	// *** For convert Euro/GBP/etc. to USD/Bitcoin, use function convert_currency_live() with Google Finance
	// *** examples: convert_currency_live("EUR", "BTC", 22.37) - convert 22.37 Euro to Bitcoin
	// *** convert_currency_live("EUR", "USD", 22.37) - convert 22.37 Euro to USD

	/********************************/



    
	/** PAYMENT BOX **/
	$options = array(
			"public_key"  => $public_key,        // your public key from gourl.io
			"private_key" => $private_key,       // your private key from gourl.io
			"webdev_key"  => "",                 // optional, gourl affiliate key
			"orderID"     => $orderID,           // order id or product name
			"userID"      => $userID,            // unique identifier for every user
			"userFormat"  => $userFormat,        // save userID in COOKIE, IPADDRESS or SESSION
			"amount"   	  => 0,                  // product price in coins OR in USD below
			"amountUSD"   => $amountUSD,         // we use product price in USD
			"period"      => $period,            // payment valid period
			"language"	  => $def_language       // text on EN - english, FR - french, etc
	);
	// Please read description of options here - https://gourl.io/api-php.html#options  

	
	// Initialise Payment Class
	$box = new Cryptobox ($options);

	// coin name
	$coinName = $box->coin_name(); 

	
	// Payment Received
	if ($box->is_paid()) 
	{ 
		$text = "User will see this message during ".$period." period after payment has been made!"; // Example
		
		$text .= "<br>".$box->amount_paid()." ".$box->coin_label()."  received<br>";
		
		// Your code here to handle a successful cryptocoin payment/captcha verification
		// For example, give user 24 hour access to your member pages
	

	}  
	// Payment Not Received
	else 
	{
	    $text = "The payment has not been made yet";
	}

	
	
	
	// Notification when user click on button 'Refresh'
	if (isset($_POST["cryptobox_refresh_"]))
	{
	    $message = "<div class='gourl_msg'>";
	    if (!$box->is_paid()) $message .= '<div style="margin:50px" class="well"><i class="fa fa-info-circle fa-3x fa-pull-left fa-border" aria-hidden="true"></i> '.str_replace(array("%coinName%", "%coinNames%", "%coinLabel%"), array($box->coin_name(), ($box->coin_label()=='DASH'?$box->coin_name():$box->coin_name().'s'), $box->coin_label()), json_decode(CRYPTOBOX_LOCALISATION, true)[CRYPTOBOX_LANGUAGE]["msg_not_received"])."</div>";
	    elseif (!$box->is_processed())
	    {
	        // User will see this message one time after payment has been made
	        $message .= '<div style="margin:70px" class="alert alert-success" role="alert"> '.str_replace(array("%coinName%", "%coinLabel%", "%amountPaid%"), array($box->coin_name(), $box->coin_label(), $box->amount_paid()), json_decode(CRYPTOBOX_LOCALISATION, true)[CRYPTOBOX_LANGUAGE][($box->cryptobox_type()=="paymentbox"?"msg_received":"msg_received2")])."</div>";
	        $box->set_status_processed();
	    }
	    $message .="</div>";
	}
	
	
	// Alternatively, you can receive JSON values through php curl on server side - function get_json_values() and use it in your php/other files without using javascript and jquery/ajax.
	// print_r($box->get_json_values());
	
	
	
	// ...
	// !!! IMPORTANT !!!
	// Also you need to add your code to IPN function cryptobox_new_payment($paymentID = 0, $payment_details = array(), $box_status = "") 
	// for send confirmation email, update database, update user membership, etc.
	// Please modify file - cryptobox.newpayment.php, read more - https://gourl.io/api-php.html#ipn
	// ...

	
	
?>










<!DOCTYPE html>
<html lang="en">

<head>

	  
    <title><?php echo $coinName; ?> Your Custom JSON Bitcoin Payment Box Example</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" crossorigin="anonymous"></script>
    <script src="../cryptobox.js" crossorigin="anonymous"></script>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <style>.tooltip-inner { max-width: 350px; } </style>
</head>

<body>


<!-- JQuery Payment Box Script, see https://github.com/cryptoapi/Payment-Gateway/blob/master/cryptobox.js#L14 -->
<script> cryptobox_custom('<?php echo $box->cryptobox_json_url(); ?>', <?php echo intval($box->is_paid()); ?>, '<?php echo $path; ?>', 'gourl_', ''); </script>


<!-- HTML Bootstrap -->

<div style='text-align:center;width:100%;height:auto;line-height:50px;background-color:#f1f1f1;border-bottom:1px solid #ddd;color:#49abe9;font-size:18px;'>
	14. GoUrl <b>Custom JSON / JQUERY Payment Box</b> Example (<?php echo $coinName; ?> payments). &#160;Use it on your website. 
	<div style='float:right;'><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html#p8'>Instruction</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/Examples/json.php'>PHP Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://github.com/cryptoapi/Bitcoin-Payment-Gateway-ASP.NET/blob/master/GoUrl/Views/Examples/PayPerJson.cshtml'>ASP.NET Source</a><a style='font-size:15px;color:#389ad8;margin-right:20px' href='https://gourl.io/<?= strtolower($coinName) ?>-payment-gateway-api.html'>Other Examples</a></div>
</div>


 <div class="container theme-showcase" role="main">


    <!-- General Text -->

    <br>	
    <div class="page-header">
    	<h1>Customize GoUrl Bitcoin Payment Box / JSON Data</h1>
    </div>

    <br>
    <div class="well">Bitcoin JSON Data will allow you to easily customise your bitcoin payment boxes.<br> 
	For example, you can display payment amount and  bitcoin payment address with your own text, you can also accept payments in android/windows and other applications. 
	See <a href='https://gourl.io/bitcoin-payment-gateway-api.html#p8'>instruction</a> and <a href='https://github.com/cryptoapi/Payment-Gateway/blob/master/Examples/json.php'>php source</a><br>
    </div>
    
    <br><br>
    <div class="page-header">
    	<h1>Example #14 (Jquery) - </h1>
    </div>


    <!-- Loading ... -->

    <div class="gourl_loader">
    
        <div class="container text-center gourl_loader_button">
        	<a style="margin:80px 20px 40px 20px" href="<?php echo $page_url; ?>" class="btn btn-default btn-lg"><i class='fa fa-spinner fa-spin'></i> &#160; <?php echo $box->coin_name() ?> Box Loading ...</a>
        </div>
        
        <div style="margin:70px;display:none" class="panel panel-danger gourl_cryptobox_error">
        
            <div class="panel-heading">
            	<h3 class="panel-title">Error !</h3>
            </div>
            
            <div class="panel-body">
            	<div class="gourl_error_message"></div>
            </div>
            
        </div>
        
    </div>


         
	
    <!-- Area above Payment Box -->
    
    <div class="gourl_cryptobox_top" style="display:none">	
    
    
        <!-- Notifications -->
    
        <?php echo $message; ?>
        
        
            
        <div class="row">
        
            <!-- Box Language -->

            <div class="col-xs-6 col-md-3">
                <div class="dropdown" style='margin-bottom:20px'>
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Language<?php  echo " &#160; <span class='small'>" . json_decode(CRYPTOBOX_LOCALISATION, true)[CRYPTOBOX_LANGUAGE]["name"] . "</span>"; ?>
                <span class="caret"></span></button>
                <?php  echo display_language_box("en", "gourlcryptolang", false); ?>
                </div>
            </div>
             
            <!-- Logo -->

            <div class="col-xs-6 col-md-3 gourl_boxlogo_paid" style="display:none">
                <div class='text-right'><img class='gourl_boxlogo' alt='logo' src='#'></div>
                <br>
            </div>
            
            <div class="col-xs-6 col-md-9 gourl_boxlogo_unpaid"  style="display:none">
                <div class='text-right'><img class='gourl_boxlogo' alt='logo' src='#'></div>
                <br>
            </div>
        
        </div>
     
    </div>        
    


    
    <!-- Crypto Payment Box -->
    
    <div class="gourl_cryptobox_unpaid" style="display:none">        
            
        <div class="row">
          
            <div class="col-md-4">
                <div class="panel panel-info">
                
                    <div class="panel-heading">
                    	<h3 class="panel-title">1. <span class="gourl_texts_instruction"></span></h3>
                    </div>
                    
                    <div class="panel-body">
                        <div>
                        	<ol>
                        		<!-- <li class="gourl_texts_intro1"></li> -->
                        		<li data-site="circle.com" data-url="https://www.circle.com/" class="gourl_texts_intro1b"></li>
                        		<li class="gourl_texts_intro2"></li>
                        		<li><b class="gourl_texts_intro3"></b></li>
                        	</ol>
                        </div>
                    </div>
                    
            	</div>
            </div>
    
            
            <div class="col-md-4">
                <div class="panel panel-primary">
            
                    <div class="panel-heading">
                        <h3 class="panel-title gourl_addr_title">2. <span class="gourl_texts_coin_address"></span></h3>
                    </div>
                    
                    <div class="panel-body">
                        <div style="float:right; margin-bottom:10px">
                        	<a class='gourl_wallet_url' href='#'><img class='gourl_qrcode_image' alt='qrcode' data-size='100' src='#'></a>
                        </div>
                        <br>
                        <div class="gourl_texts_send"></div>
                        <br>
                       	<div><a class="gourl_addr gourl_wallet_url" href="#"></a> &#160; <a class="gourl_wallet_url gourl_wallet_open" href="#"><i class="fa fa-external-link" aria-hidden="true"></i></a></div>
                    </div>
                    
                </div>
            </div>
    
            
            <div class="col-md-4">
            
                <div class="panel panel-warning">
                    <div class="panel-heading">
                    	<h3 class="panel-title">3. <span class="gourl_paymentcaptcha_amount"></span></h3>
                    </div>
                    <div class="panel-body">
                    	<span class="gourl_amount"></span> <span class="gourl_coinlabel"></span>
                    	
                    </div>
                </div>
                
                <div class="panel panel-success">
                    <div class="panel-heading">
                    	<h3 class="panel-title">4. <span class="gourl_paymentcaptcha_status"></span></h3>
                    </div>
                    <div class="panel-body">
                    	<div class="gourl_paymentcaptcha_statustext"></div>
                    </div>
                </div>
                
            </div>
            
        </div>
    
        <br>
        
        <form action="<?php echo $page_url; ?>" method="post">
    		<input type="hidden" id="cryptobox_refresh_" name="cryptobox_refresh_" value="1">
            <button style="margin:10px 20px" class="gourl_button_refresh btn btn-default btn-lg"></button>
            <button style="margin:10px 20px" class="gourl_button_wait btn btn-info btn-lg"></button>
        </form>
        
        <br><br><br>
        
        <div class="gourl_texts_btn_wait_hint"></div>
    
    </div>
    
    
    
    
    <!-- Successful Result -->
    
    <div class="gourl_cryptobox_paid" style="display:none">	
        
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-success">
                
                    <div class="panel-heading">
                        <div style="float:right; margin-left:10px">  
                        	<span class="gourl_texts_total"></span>: <span class="gourl_amount"></span> <span class="gourl_coinlabel"></span>
                        </div>
                        <h3 class="panel-title gourl_paymentcaptcha_title">Result</h3>
                    </div>
                    
                	<div class="panel-body text-center">
                	
                        <div style="float:left" class="gourl_paidimg">
                            <br>
                            <img style='border:0' src='https://coins.gourl.io/images/paid.png' alt='Successful'>
                            <br><br>
                        </div>
                        
                        <h3 style='color:#3caf00;font-family:"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:22px;line-height:35px;font-weight:bold;' class="gourl_paymentcaptcha_successful">.</h3>
                        
                        <div class="gourl_paymentcaptcha_date"></div>
                        
                        <br>
                        <a style="margin:10px 20px" href="#" class="gourl_button_details btn btn-info"></a>
                        
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
    
    
    
    
    <!-- Debug / Raw Data -->
    
    <div class="gourl_cryptobox_rawdata" style="display:none">
    
        <br><br><br><br><br><br>
        <div class="page-header">
        	<h1>Raw JSON Data (from GoUrl.io payment gateway) -</h1>
        </div>
        <p>You can use php function <a target='_blank' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/cryptobox.class.php#L316'>$box->cryptobox_json_url()</a>; It generates url with your paramenters to gourl.io payment gateway.<br> 
           Using this url you can get bitcoin/altcoin payment box values in JSON format and use it on html page with Jquery/Ajax - <a target='_blank' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/cryptobox.js#L14'>cryptobox_custom(url, paid, path, ext, redirect)</a>. JSON data will allow you to easily customise your bitcoin payment boxes. For example, you can display payment amount and  bitcoin payment address with your own text, you can also accept payments in android/windows and other applications. </p>
        <p>Alternatively, you can receive JSON values through php curl on server side - php function <a target='_blank' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/cryptobox.class.php#L369'>$box->get_json_values()</a>; and use it in your php/other files without using javascript and jquery/ajax.</p>
        <p><a target='_blank' href='<?php echo $box->cryptobox_json_url(); ?>'>JSON data source &#187;</a></p>
        <div class="well well-sm">
        	<div class='gourl_jsondata'></div>
        </div>


	<div style='position:absolute;left:0;'><a target="_blank" href="http://validator.w3.org/check?uri=<?php echo "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>"><img src="https://gourl.io/images/w3c.png" alt="Valid HTML"></a></div>


    </div>

</div> 
  

</body>
</html>