<?php
/**
 * ##########################################
 * ###  PLEASE DO NOT MODIFY THIS FILE !  ###
 * ##########################################
 *
 *
 * PHP Cryptocurrency Payment Class
 *
 * @package     GoUrl PHP Bitcoin/Altcoin Payments and Crypto Captcha
 * @copyright   2014-2020 Delta Consultants
 * @category    Libraries
 * @website     https://gourl.io 
 * @api         https://gourl.io/bitcoin-payment-gateway-api.html
 * @example     https://gourl.io/lib/examples/example_customize_box.php    <----
 * @gitHub  	https://github.com/cryptoapi/Payment-Gateway
 * @license 	Free GPLv2
 * @version     2.2.0
 *
 *
 *  CLASS CRYPTOBOX - LIST OF METHODS:
 *  --------------------------------------
 *  1a. function display_cryptobox(..)			// Show Cryptocoin Payment Box and automatically displays successful payment message. If $submit_btn = true, display user submit button 'Click Here if you have already sent coins' or not
 *  1b. function display_cryptobox_bootstrap(..) // Show Customize Mobile Friendly Payment Box and automatically displays successful payment message if use ajax.
 *                                               // optional - FREE WHITE-LABEL PRODUCT - BITCOIN/ALTCOIN PAYMENT BOX WITH YOUT LOGO THROUGH YOUR SERVER.  
 *                                               // This function use bootstrap4 template; you can use your own template without this function
 *  2. function cryptobox_json_url()            // It generates url with your parameters to gourl.io payment gateway. Using this url you can get bitcoin/altcoin payment box values in JSON format and use it on html page with Jquery/Ajax.
 *  3. function get_json_values()               // Alternatively, you can receive JSON values though php curl on server side and use it in your php file without using Javascript and Jquery/Ajax.
 *  4. function cryptobox_hash(..)              // It generates security md5 hash for all values used in payment box 
 *  5. function is_paid(..)	 					// If payment received - return true, otherwise return false
 *  6. function is_confirmed()					// Returns true if transaction/payment have 6+ confirmations. Average transaction/payment confirmation time - 10-20min for 6 confirmations (altcoins)
 *  7. function amount_paid()					// Returns the amount of coins received from the user 
 *  8. function amount_paid_usd()				// Returns the approximate amount in USD received from the user using live cryptocurrency exchange rates on the datetime of payment
 *  9. function set_status_processed()			// Optional - if payment received, set payment status to 'processed' and save this status in database
 *  10. function is_processed()					// Optional - if payment status in database is 'processed' - return true, otherwise return false
 *  11.function cryptobox_type()				// Returns cryptobox type - paymentbox or captchabox
 *  12.function payment_id()					// Returns current record id in the table crypto_payments. Crypto_payments table stores all payments from your users
 *  13.function payment_date()					// Returns payment/transaction datetime in GMT format
 *  14.function payment_info()					// Returns object with current user payment details - amount, txID, datetime, usercointry, etc
 *  15.function payment_status_text()           // Return text message payment received or not; "msg_not_received", "msg_received" or "msg_received2"
 *  16.function cryptobox_reset()				// Optional, Delete cookies/sessions and new cryptobox with new payment amount will be displayed. Use this function only if you have not set userID manually.
 *  17.function coin_name()						// Returns coin name (bitcoin, dogecoin, etc)
 *  18.function coin_label()					// Returns coin label (DOGE, BTC, etc)
 *  19.function iframe_id()						// Returns payment box frame id
 *
 *
 *  LIST OF GENERAL FUNCTIONS:
 *  -------------------------------------
 *  A. function payment_history(..) 			// Returns array with history payment details of any of your users / orders / etc.
 *  B. function payment_unrecognised(..) 		// Returns array with unrecognised payments for custom period - $time (users paid wrong amount on your internal wallet address)
 *  C. function cryptobox_sellanguage(..)       // Get cryptobox current selected language by user (english, spanish, etc)
 *  D. function cryptobox_selcoin(..)			// Get cryptobox current selected coin by user (bitcoin, dogecoin, etc. - for multiple coin payment boxes) 
 *  E. function display_language_box(..)		// Language selection dropdown list for cryptocoin payment box
 *  F. function display_currency_box(..)		// Multiple crypto currency selection list. You can accept payments in multiple crypto currencies (for example: bitcoin, bitcoincash, bitcoinsv, litecoin, dogecoin)
 *  G. function get_country_name(..)			// Get country name by country code or reverse
 *  H. function convert_currency_live(..)		// Fiat currency converter using live exchange rates websites
 *  I. function validate_gourlkey(..)			// Validate gourl private/public/affiliate keys 
 *  J. function run_sql(..)						// Run SQL queries and return result in array/object formats
 *
 *
 *  CONSTANTS
 *  -------------------------------------
 *  CRYPTOBOX_LANGUAGE - cryptobox current selected language
 *  CRYPTOBOX_LOCALISATION - all cryptobox localisations 
 *
 *  Note: Complete Description of the Functions, see on the page below or here - https://gourl.io/api-php.html
 *
 *
 */



if(!defined("CRYPTOBOX_WORDPRESS")) define("CRYPTOBOX_WORDPRESS", false);

if (!CRYPTOBOX_WORDPRESS) { // Pure PHP
    require_once( "cryptobox.config.php" ); 
    require_once( "cryptobox.newpayment.php" );
}
elseif (!defined('ABSPATH')) exit; // Wordpress


define("CRYPTOBOX_VERSION", "2.2.0");

// GoUrl supported crypto currencies
define("CRYPTOBOX_COINS", json_encode(array('bitcoin', 'bitcoincash', 'bitcoinsv', 'litecoin', 'dash', 'dogecoin', 'speedcoin', 'reddcoin', 'potcoin', 'feathercoin', 'vertcoin', 'peercoin', 'monetaryunit', 'universalcurrency')));


class Cryptobox {

	// Custom Variables
	
	private $public_key 	= "";		// value from your gourl.io member page - https://gourl.io/info/memberarea	
	private $private_key 	= "";		// value from your gourl.io member page.  Also you setup cryptocoin name on gourl.io member page
	private $webdev_key 	= "";		// optional, web developer affiliate key
	private $amount 		= 0;		// amount of cryptocoins which will be used in the payment box/captcha, precision is 4 (number of digits after the decimal), example: 0.0001, 2.444, 100, 2455, etc.   
										/* we will use this $amount value of cryptocoins in the payment box with a small fraction after the decimal point to uniquely identify each of your users individually
										 * (for example, if you enter 0.5 BTC, one of your user will see 0.500011 BTC, and another will see  0.500046 BTC, etc) */
	private $amountUSD 		= 0;		/* you can specify your price in USD and cryptobox will automatically convert that USD amount to cryptocoin amount using today live cryptocurrency exchange rates.
										 * Using that functionality (price in USD), you don't need to worry if cryptocurrency prices go down or up. 
										 * User will pay you all times the actual price which is linked on current exchange price in USD on the datetime of purchase.      
										 * You can use in cryptobox options one variable only: amount or amountUSD. You cannot place values of those two variables together. */
	private $period 		= "";		// period after which the payment becomes obsolete and new cryptobox will be shown; allow values: NOEXPIRY, 1 MINUTE..90 MINUTE, 1 HOUR..90 HOURS, 1 DAY..90 DAYS, 1 WEEK..90 WEEKS, 1 MONTH..90 MONTHS  
	private $language		= "en";		// cryptobox localisation; en - English, es - Spanish, fr - French, de - German, nl - Dutch, it - Italian, ru - Russian, pl - Polish, pt - Portuguese, fa - Persian, ko - Korean, ja - Japanese, id - Indonesian, tr - Turkish, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi
	private $iframeID		= "";		// optional, html iframe element id; allow symbols: a..Z0..9_-
	private $orderID 		= "";		// your page name / product name or order name (not unique); allow symbols: a..Z0..9_-@.; max size: 50 symbols
	private $userID 		= "";		// optional, manual setup unique identifier for each of your users; allow symbols: a..Z0..9_-@.; max size: 50 symbols
										/* IMPORTANT - If you use Payment Box/Captcha for registered users on your website, you need to set userID manually with 
										 * an unique value for each of your registered user. It is better than to use cookies by default. Examples: 'user1', 'user2', '3vIh9MjEis' */
	private $userFormat 	= "COOKIE"; // this variable use only if $userID above is empty - it will save random userID in cookies, sessions or use user IP address as userID. Available values: COOKIE, SESSION, IPADDRESS
	  
	/* PLEASE NOTE -
	 * If you use multiple stores/sites online, please create separate GoUrl Payment Box (with unique payment box public/private keys) for each of your stores/websites. 
	 * Do not use the same GoUrl Payment Box with the same public/private keys on your different websites/stores.
	 * if you use the same $public_key, $orderID and $userID in your multiple cryptocoin payment boxes on different website pages and a user has made payment; a successful result for that user will be returned on all those pages (if $period time valid). 
	 * if you change - $public_key or $orderID or $userID - new cryptocoin payment box will be shown for exisiting paid user. (function $this->is_paid() starts to return 'false').
	 * */

	
	// Internal Variables
	
	private $boxID			= 0; 		// cryptobox id, the same as on gourl.io member page. For each your cryptocoin payment boxes you will have unique public / private keys 
	private $coinLabel		= ""; 		// current cryptocoin label (BTC, DOGE, etc.) 
	private $coinName		= ""; 		// current cryptocoin name (Bitcoin, Dogecoin, etc.) 
	private $paid			= false;	// paid or not
	private $confirmed		= false;	// transaction/payment have 6+ confirmations or not
	private $paymentID		= false;	// current record id in the table crypto_payments (table stores all payments from your users)
	private $paymentDate	= "";		// transaction/payment datetime in GMT format
	private $amountPaid 	= 0;		// exact paid amount; for example, $amount = 0.5 BTC and user paid - $amountPaid = 0.50002 BTC
	private $amountPaidUSD 	= 0;		// approximate paid amount in USD; using cryptocurrency exchange rate on datetime of payment
	private $boxType		= "";		// cryptobox type - 'paymentbox' or 'captchabox'
	private $processed		= false;	// optional - set flag to paid & processed	
	private $cookieName 	= "";		// user cookie/session name (if cookies/sessions use)
	private $localisation 	= "";		// localisation; en - English, es - Spanish, fr - French, de - German, nl - Dutch, it - Italian, ru - Russian, pl - Polish, pt - Portuguese, fa - Persian, ko - Korean, ja - Japanese, id - Indonesian, tr - Turkish, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi
	private $ver 		    = "";		// version
	
	
	public function __construct($options = array()) 
	{
		
		// Min requirements
		if (!function_exists( 'mb_stripos' ) || !function_exists( 'mb_strripos' ))  die(sprintf("Error. Please enable <a target='_blank' href='%s'>MBSTRING extension</a> in PHP. <a target='_blank' href='%s'>Read here &#187;</a>", "http://php.net/manual/en/book.mbstring.php", "http://www.knowledgebase-script.com/kb/article/how-to-enable-mbstring-in-php-46.html"));
		if (!function_exists( 'curl_init' )) 										die(sprintf("Error. Please enable <a target='_blank' href='%s'>CURL extension</a> in PHP. <a target='_blank' href='%s'>Read here &#187;</a>", "http://php.net/manual/en/book.curl.php", "http://stackoverflow.com/questions/1347146/how-to-enable-curl-in-php-xampp"));
		if (!function_exists( 'mysqli_connect' )) 									die(sprintf("Error. Please enable <a target='_blank' href='%s'>MySQLi extension</a> in PHP. <a target='_blank' href='%s'>Read here &#187;</a>", "http://php.net/manual/en/book.mysqli.php", "http://crybit.com/how-to-enable-mysqli-extension-on-web-server/"));
		if (version_compare(phpversion(), '5.4.0', '<')) 							die(sprintf("Error. You need PHP 5.4.0 (or greater). Current php version: %s", phpversion()));

		foreach($options as $key => $value) 
			if (in_array($key, array("public_key", "private_key", "webdev_key", "amount", "amountUSD", "period", "language", "iframeID", "orderID", "userID", "userFormat"))) $this->$key = (is_string($value)) ? trim($value) : $value;

		$this->boxID = $this->left($this->public_key, "AA");
		 
		if (preg_replace('/[^A-Za-z0-9]/', '', $this->public_key) != $this->public_key || strlen($this->public_key) != 50 || !strpos($this->public_key, "AA") || !$this->boxID || !is_numeric($this->boxID) || strpos($this->public_key, "77") === false || !strpos($this->public_key, "PUB")) die("Invalid Cryptocoin Payment Box PUBLIC KEY - " . ($this->public_key?$this->public_key:"cannot be empty"));
				
		if (preg_replace('/[^A-Za-z0-9]/', '', $this->private_key) != $this->private_key || strlen($this->private_key) != 50 || !strpos($this->private_key, "AA") || $this->boxID != $this->left($this->private_key, "AA") || !strpos($this->private_key, "PRV") || $this->left($this->private_key, "PRV") != $this->left($this->public_key, "PUB")) die("Invalid Cryptocoin Payment Box PRIVATE KEY".($this->private_key?"":" - cannot be empty"));
		
		if (!defined("CRYPTOBOX_PRIVATE_KEYS") || !in_array($this->private_key, explode("^", CRYPTOBOX_PRIVATE_KEYS))) die("Error. Please add your Cryptobox Private Key ".(CRYPTOBOX_WORDPRESS ? "on your plugin settings page" : "to \$cryptobox_private_keys in file cryptobox.config.php"));

		if ($this->webdev_key && (preg_replace('/[^A-Za-z0-9]/', '', $this->webdev_key) != $this->webdev_key || strpos($this->webdev_key, "DEV") !== 0 || $this->webdev_key != strtoupper($this->webdev_key) || $this->icrc32($this->left($this->webdev_key, "G", false)) != $this->right($this->webdev_key, "G", false))) $this->webdev_key = "";
		
		$c = substr($this->right($this->left($this->public_key, "PUB"), "AA"), 5);
		$this->coinLabel = $this->right($c, "77");
		$this->coinName = $this->left($c, "77");
		
		if ($this->amount 	 && strpos($this->amount, ".")) 	$this->amount = rtrim(rtrim($this->amount, "0"), ".");
		if ($this->amountUSD && strpos($this->amountUSD, ".")) 	$this->amountUSD = rtrim(rtrim($this->amountUSD, "0"), ".");

		if (!$this->amount || $this->amount <= 0) 		$this->amount 	 = 0;
		if (!$this->amountUSD || $this->amountUSD <= 0) 	$this->amountUSD = 0;
		
		if (($this->amount <= 0 && $this->amountUSD <= 0) || ($this->amount > 0 && $this->amountUSD > 0)) die("You can use in cryptobox options one of variable only: amount or amountUSD. You cannot place values in that two variables together (submitted amount = '".$this->amount."' and amountUSD = '".$this->amountUSD."' )");
		 
		if ($this->amount && (!is_numeric($this->amount) || $this->amount < 0.0001 || $this->amount > 500000000)) die("Invalid Amount - ".sprintf('%.8f', $this->amount)." $this->coinLabel. Allowed range: 0.0001 .. 500,000,000");
		if ($this->amountUSD && (!is_numeric($this->amountUSD) || $this->amountUSD < 0.01 || $this->amountUSD > 1000000)) die("Invalid amountUSD - ".sprintf('%.8f', $this->amountUSD)." USD. Allowed range: 0.01 .. 1,000,000");
		
		$this->period = trim(strtoupper(str_replace(" ", "", $this->period)));
		if (substr($this->period, -1) == "S") $this->period = substr($this->period, 0, -1);
		for ($i=1; $i<=90; $i++) { $arr[] = $i."MINUTE"; $arr[] = $i."HOUR"; $arr[] = $i."DAY"; $arr[] = $i."WEEK"; $arr[] = $i."MONTH"; }
		if ($this->period != "NOEXPIRY" && !in_array($this->period, $arr)) die("Invalid Cryptobox Period - $this->period");
		$this->period = str_replace(array("MINUTE", "HOUR", "DAY", "WEEK", "MONTH"), array(" MINUTE", " HOUR", " DAY", " WEEK", " MONTH"), $this->period);
		
		$this->localisation = json_decode(CRYPTOBOX_LOCALISATION, true);
		if (!in_array(strtolower($this->language), array_keys($this->localisation))) $this->language = "en";
		$this->language = cryptobox_sellanguage($this->language);
		$this->localisation = $this->localisation[$this->language];
		
		if ($this->iframeID && preg_replace('/[^A-Za-z0-9\_\-]/', '', $this->iframeID) != $this->iframeID || $this->iframeID == "cryptobox_live_") die("Invalid iframe ID - $this->iframeID. Allowed symbols: a..Z0..9_-");
		
		$this->userID = trim($this->userID);
		if ($this->userID && preg_replace('/[^A-Za-z0-9\.\_\-\@]/', '', $this->userID) != $this->userID) die("Invalid User ID - $this->userID. Allowed symbols: a..Z0..9_-@.");
		if (strlen($this->userID) > 50) die("Invalid User ID - $this->userID. Max: 50 symbols");
		
		$this->orderID = trim($this->orderID);
		if ($this->orderID && preg_replace('/[^A-Za-z0-9\.\_\-\@]/', '', $this->orderID) != $this->orderID) die("Invalid Order ID - $this->orderID. Allowed symbols: a..Z0..9_-@.");
		if (!$this->orderID || strlen($this->orderID) > 50) die("Invalid Order ID - $this->orderID. Max: 50 symbols");
		
		if ($this->userID) 
			$this->userFormat = "MANUAL";
		else 
		{
			switch ($this->userFormat) 
			{
				case "COOKIE":
					$this->cookieName = 'cryptoUsr'.$this->icrc32($this->boxID."*&*".$this->coinLabel."*&*".$this->orderID."*&*".$this->private_key);
					if (isset($_COOKIE[$this->cookieName]) && trim($_COOKIE[$this->cookieName]) && strpos($_COOKIE[$this->cookieName], "__") && preg_replace('/[^A-Za-z0-9\_]/', '', $_COOKIE[$this->cookieName]) == $_COOKIE[$this->cookieName] && strlen($_COOKIE[$this->cookieName]) <= 30) $this->userID = trim($_COOKIE[$this->cookieName]);
					else
					{	 
						$s = trim(strtolower($_SERVER['SERVER_NAME']), " /");
						if (stripos($s, "www.") === 0) $s = substr($s, 4);
						$d = time(); if ($d > 1410000000) $d -= 1410000000;
						$v = trim($d."__".substr(md5(uniqid(mt_rand().mt_rand().mt_rand())), 0, 10));
						setcookie($this->cookieName, $v, time()+(10*365*24*60*60), '/', $s);
						$this->userID = $v;
					}	
				break;
					
				case "SESSION":
					
					if (session_status() == PHP_SESSION_NONE) session_start();
					$this->cookieName = 'cryptoUser'.$this->icrc32($this->private_key."*&*".$this->boxID."*&*".$this->coinLabel."*&*".$this->orderID);
					if (isset($_SESSION[$this->cookieName]) && trim($_SESSION[$this->cookieName]) && strpos($_SESSION[$this->cookieName], "--") && preg_replace('/[^A-Za-z0-9\-]/', '', $_SESSION[$this->cookieName]) == $_SESSION[$this->cookieName] && strlen($_SESSION[$this->cookieName]) <= 30) $this->userID = trim($_SESSION[$this->cookieName]);
					else
					{	 
						$d = time(); if ($d > 1410000000) $d -= 1410000000;
						$v = trim($d."--".substr(md5(uniqid(mt_rand().mt_rand().mt_rand())), 0, 10));
						$this->userID = $_SESSION[$this->cookieName] = $v; 
					}	
				break;
				
				case "IPADDRESS":
					
					if (session_status() == PHP_SESSION_NONE) session_start();
					if (isset($_SESSION['cryptoUserIP']) && filter_var($_SESSION['cryptoUserIP'], FILTER_VALIDATE_IP) && preg_replace('/[^A-Za-z0-9\.\:]/', '', $_SESSION['cryptoUserIP']) == $_SESSION['cryptoUserIP'] && strlen($_SESSION['cryptoUserIP']) <= 50)
						 $ip = $_SESSION['cryptoUserIP'];
					else $ip = $_SESSION['cryptoUserIP'] = $this->ip_address();
					$this->userID = trim(md5($ip."*&*".$this->boxID."*&*".$this->coinLabel."*&*".$this->orderID));
					
				break;
				
				default:
					die("Invalid userFormat value - $this->userFormat");
				break;
			}
		}

		// version string
		$this->ver = "version | gourlphp " . CRYPTOBOX_VERSION;
		if (CRYPTOBOX_WORDPRESS) $this->ver .= " | gourlwordpress" . (defined('GOURL_VERSION') ? " ".GOURL_VERSION : "");
		if (CRYPTOBOX_WORDPRESS && defined('GOURLWC_VERSION') && strpos($this->orderID, "gourlwoocommerce.") === 0)   $this->ver .= " | gourlwoocommerce " . GOURLWC_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLAP_VERSION') && strpos($this->orderID, "gourlappthemes.") === 0)     $this->ver .= " | gourlappthemes " . GOURLAP_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLEDD_VERSION') && strpos($this->orderID, "gourledd.") === 0)          $this->ver .= " | gourledd " . GOURLEDD_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLPMP_VERSION') && strpos($this->orderID, "gourlpmpro.") === 0)        $this->ver .= " | gourlpmpro " . GOURLPMP_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLGV_VERSION') && strpos($this->orderID, "gourlgive.") === 0)          $this->ver .= " | gourlgive " . GOURLGV_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLJI_VERSION') && strpos($this->orderID, "gourljigoshop.") === 0)      $this->ver .= " | gourljigoshop " . GOURLJI_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLWPSC_VERSION') && strpos($this->orderID, "gourlwpecommerce.") === 0) $this->ver .= " | gourlwpecommerce " . GOURLWPSC_VERSION;
		if (CRYPTOBOX_WORDPRESS && defined('GOURLMP_VERSION') && strpos($this->orderID, "gourlmarketpress.") === 0)   $this->ver .= " | gourlmarketpress " . GOURLMP_VERSION;
		
		if (!$this->iframeID) $this->iframeID = $this->iframe_id();
		
		$this->check_payment();
		
		return true;
	}
	
	
	
	

	/* 1. Function display_cryptobox() -
	 * 
	 * Display Cryptocoin Payment Box; the cryptobox will automatically displays successful message if payment has been received
	 * 
	 * Usually user will see on bottom of payment box button 'Click Here if you have already sent coins' (when $submit_btn = true) 
	 * and when they click on that button, script will connect to our remote cryptocoin payment box server
	 * and check user payment.
	 *  
	 * As backup, our server will also inform your server automatically through IPN every time a payment is received
	 * (file cryptobox.callback.php). I.e. if the user does not click on the button or you have not displayed the button, 
	 * your website will receive a notification about a given user anyway and save it to your database. 
	 * Next time your user goes to your website/reloads page they will automatically see the message 
	 * that their payment has been received successfully.
	*/
	public function display_cryptobox($submit_btn = true, $width = "540", $height = "230", $box_style = "", $message_style = "", $anchor = "")
	{
		if (!$box_style) 	 $box_style = "border-radius:15px;box-shadow:0 0 12px #aaa;-moz-box-shadow:0 0 12px #aaa;-webkit-box-shadow:0 0 12px #aaa;padding:3px 6px;margin:10px"; 
		if (!$message_style) $message_style = "display:inline-block;max-width:580px;padding:15px 20px;box-shadow:0 0 10px #aaa;-moz-box-shadow: 0 0 10px #aaa;margin:7px;font-size:13px;font-weight:normal;line-height:21px;font-family: Verdana, Arial, Helvetica, sans-serif;";
		
		$width = intval($width);
		$height = intval($height);

		$box_style = trim($box_style, "; ") .";max-width:".$width."px !important;max-height:".$height."px !important;";
		
		$cryptobox_html = "";
		$val 			= md5($this->iframeID.$this->private_key.$this->userID);
	
		if ($submit_btn && isset($_POST["cryptobox_live_"]) && $_POST["cryptobox_live_"] == $val)
		{
			$id = "id".md5(mt_rand()); 
			if (!$this->paid) $cryptobox_html .= "<a id='c".$this->iframeID."' name='c".$this->iframeID."'></a>";
			$cryptobox_html .= "<br><div id='$id' align='center'>";
			$cryptobox_html .= '<div'.(in_array($this->language, array("ar", "fa"))?' dir="rtl"':'').' style="'.htmlspecialchars($message_style, ENT_COMPAT).'">';
	

			if ($this->paid) $cryptobox_html .= "<span style='color:#339e2e;white-space:nowrap;'>".$this->payment_status_text()."</span>";
			else $cryptobox_html .= "<span style='color:#eb4847'>".$this->payment_status_text()."</span><script type='text/javascript'>cryptobox_msghide('$id')</script>";
			
			$cryptobox_html .= "</div></div><br>";
		}
	
		$hash = $this->cryptobox_hash(false, $width, $height);
		
		$cryptobox_html .= "<div align='center' style='min-width:".$width."px'><iframe id='$this->iframeID' ".($box_style?'style="'.htmlspecialchars($box_style, ENT_COMPAT).'"':'')." scrolling='no' marginheight='0' marginwidth='0' frameborder='0' width='$width' height='$height'></iframe></div>";
		$cryptobox_html .= "<div><script type='text/javascript'>";
		$cryptobox_html .= "cryptobox_show($this->boxID, '$this->coinName', '$this->public_key', $this->amount, $this->amountUSD, '$this->period', '$this->language', '$this->iframeID', '$this->userID', '$this->userFormat', '$this->orderID', '$this->cookieName', '$this->webdev_key', '".base64_encode($this->ver)."', '$hash', $width, $height);";
		$cryptobox_html .= "</script></div>";
	
		if ($submit_btn && !$this->paid)
		{
			$cryptobox_html .= "<form action='".$_SERVER["REQUEST_URI"]."#".($anchor?$anchor:"c".$this->iframeID)."' method='post'>";
			$cryptobox_html .= "<input type='hidden' id='cryptobox_live_' name='cryptobox_live_' value='$val'>";
			$cryptobox_html .= "<div align='center'>";
			$cryptobox_html .= "<button".(in_array($this->language, array("ar", "fa"))?' dir="rtl"':'')." style='color:#555;border-color:#ccc;background:#f7f7f7;-webkit-box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);vertical-align:top;display:inline-block;text-decoration:none;font-size:13px;line-height:26px;min-height:28px;margin:20px 0 25px 0;padding:0 10px 1px;cursor:pointer;border-width:1px;border-style:solid;-webkit-appearance:none;-webkit-border-radius:3px;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:\"Open Sans\",sans-serif;font-size: 13px;font-weight: normal;text-transform: none;'>&#160; ".str_replace(array("%coinName%", "%coinNames%", "%coinLabel%"), array($this->coinName, (in_array($this->coinLabel, array('BCH', 'BSV', 'DASH'))?$this->coinName:$this->coinName.'s'), $this->coinLabel), $this->localisation["button"]).($this->language!="ar"?" &#187;":"")." &#160;</button>";
			$cryptobox_html .= "</div>";
			$cryptobox_html .= "</form>";
		}
	
		$cryptobox_html .= "<br>";
	
		return $cryptobox_html;
	}
	
	

	
	
	/* 2. Function cryptobox_json_url()
	 *
	 * It generates url with your parameters to gourl.io payment gateway.
	 * Using this url you can get bitcoin/altcoin payment box values in JSON format and use it on html page with Jquery/Ajax.
	 * See instruction https://gourl.io/bitcoin-payment-gateway-api.html#p8
	 *  
	 * JSON Values Example -
	 * Payment not received - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/2.21/pe/NOEXPIRY/l/en/o/invoice22/u/83412313__3bccb54769/us/COOKIE/j/1/d/ODIuMTEuOTQuMTIx/h/e889b9a07493ee96a479e471a892ae2e   
	 * Payment received successfully - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/0.1/pe/NOEXPIRY/l/en/o/invoice1/u/demo/us/MANUAL/j/1/d/ODIuMTEuOTQuMTIx/h/ac7733d264421c8410a218548b2d2a2a
	 * 
	 * Alternatively, you can receive JSON values through php curl on server side - function get_json_values() and use it in your php/other files without using javascript and jquery/ajax.
	 * 
	 * By default the user sees bitcoin payment box as iframe in html format - function display_cryptobox().
	 * JSON data will allow you to easily customise your bitcoin payment boxes. For example, you can display payment amount and  
	 * bitcoin payment address with your own text, you can also accept payments in android/windows and other applications. 
	 * You get an array of values - payment amount, bitcoin address, text; and can place them in any position on your webpage/application.   
	 */
	public function cryptobox_json_url()
	{
	    
	    $ip		= $this->ip_address();
	    $hash 	= $this->cryptobox_hash(true);
	    
	    $data = array
	    (
	        "b" 	=> $this->boxID,
	        "c" 	=> $this->coinName,
	        "p" 	=> $this->public_key,
	        "a" 	=> $this->amount,
	        "au" 	=> $this->amountUSD,
	        "pe"	=> str_replace(" ", "_", $this->period),
	        "l" 	=> $this->language,
	        "o" 	=> $this->orderID,
	        "u" 	=> $this->userID,
	        "us"	=> $this->userFormat,
	        "j"     => 1, // json   
	        "d" 	=> base64_encode($ip),
	        "f"     => base64_encode($this->ua(false)),
	        "t" 	=> base64_encode($this->ver),
	        "h" 	=> $hash
	    );
	     
	    if ($this->webdev_key) $data["w"]  = $this->webdev_key;
	    $data["z"] = rand(0,10000000);
	    
	    $url = "https://coins.gourl.io";
	    foreach($data as $k=>$v) $url .= "/".$k."/".rawurlencode($v);
 
	    return $url;
	}
	
	
	
	
	
	/* 3. Function get_json_values()
	 *
	 * Alternatively, you can receive JSON values through php curl on server side and use it in your php/other files without using javascript and jquery/ajax.
	 * Return Array; Examples -
	 * Payment not received - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/2.21/pe/NOEXPIRY/l/en/o/invoice22/u/83412313__3bccb54769/us/COOKIE/j/1/d/ODIuMTEuOTQuMTIx/h/e889b9a07493ee96a479e471a892ae2e   
	 * Payment received successfully - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/0.1/pe/NOEXPIRY/l/en/o/invoice1/u/demo/us/MANUAL/j/1/d/ODIuMTEuOTQuMTIx/h/ac7733d264421c8410a218548b2d2a2a
	 * 
	 * By default the user sees bitcoin payment box as iframe in html format - function display_cryptobox().
	 * JSON data will allow you to easily customise your bitcoin payment boxes. For example, you can display payment amount and  
	 * bitcoin payment address with your own text, you can also accept payments in android/windows and other applications. 
	 * You get an array of values - payment amount, bitcoin address, text; and can place them in any position on your webpage/application.   
	 */
	public function get_json_values()
	{
		$url = $this->cryptobox_json_url();
	    
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->ua());
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		$res = curl_exec( $ch );
		curl_close($ch);

		// security; validate data sent by gourl.io
		$f = false;
		if ($res)
		{
		  $arr = $arr2 = json_decode($res, true);
		  
		  // if error
		  if (!$arr && $res) return array("status" => "error", "err" => substr(strip_tags($res), 0, 250)); 

		  if (isset($arr2["data_hash"]))
		  {
		      unset($arr2["data_hash"]);
		      if (strtolower($arr["data_hash"]) == strtolower(hash("sha512", $this->private_key.json_encode($arr2).$this->private_key))) $f = true;
		  }
		}
		if (!$f) $arr = array();

		if ($arr && $arr["status"] == "payment_received" && !$this->paid) $this->check_payment(true);
		
		return $arr;
	}
	
	
	
	
	
	/* 4. Function cryptobox_hash($json = false, $width = 0, $height = 0)
	 *
	 * It generates security md5 hash for all values used in payment boxes. 
	 * This protects payment box parameters from changes by end user in web browser. 
	 * $json = true - generate md5 hash for json payment data output
	 * or generate hash for iframe html box with sizes $width x $height
	 */
	public function cryptobox_hash($json = false, $width = 0, $height = 0)
	{
	    
	    if ($json) $hash_str = $this->boxID."|".$this->coinName."|".$this->public_key."|".$this->private_key."|".$this->webdev_key."|".$this->amount."|".$this->amountUSD."|".$this->period."|". $this->language."|".$this->orderID."|".$this->userID."|".$this->userFormat."|".$this->ver."|".$this->ip_address();
	    else       $hash_str = $this->boxID."|".$this->coinName."|".$this->public_key."|".$this->private_key."|".$this->webdev_key."|".$this->amount."|".$this->amountUSD."|".$this->period."|". $this->language."|".$this->orderID."|".$this->userID."|".$this->userFormat."|".$this->ver."|".$this->iframeID."|".$width."|".$height;

		
	    $hash = md5($hash_str);

		
	    return $hash; 
	}
	
	
	    
	    
	
	/* 5. Function is_paid($remotedb = false) -
	 * 
	 * This Checks your local database whether payment has been received and is stored on your local database. 
	 * 
	 * If use $remotedb = true, it will check also on the remote cryptocoin payment server (gourl.io),
	 * and if payment is received, it saves it in your local database. Usually user will see on bottom
	 * of payment box button 'Click Here if you have already sent coins' and when they click on that button,
	 * script it will connect to our remote cryptocoin payment box server. Therefore you don't need to use
	 * $remotedb = true, it will make your webpage load slowly if payment on gourl.io is checked during
	 * each of your page loadings.
	 * 
	 * Please note that our server will also inform your server automatically every time when payment is 
	 * received through callback url: cryptobox.callback.php. I.e. if the user does not click on button, 
	 * your website anyway will receive notification about a given user and save it in your database. 
	 * And when your user next time comes on your website/reload page he will automatically will see 
	 * message that his payment has been received successfully.
	 */
	public function is_paid($remotedb = false)
	{
		if (!$this->paymentID && $remotedb) $this->check_payment($remotedb);
		if ($this->paid) return true;
		else return false;
	}
	
	
	


	/* 6. Function is_confirmed() -
	*
	* Function return is true if transaction/payment has 6+ confirmations. 
	* It connects with our payment server and gets the current transaction status (confirmed/unconfirmed). 
	* Some merchants wait until this transaction has been confirmed.  
	* Average transaction confirmation time - 10-20min for 6+ confirmations (altcoins)
	*/
	public function is_confirmed()
	{
		if ($this->confirmed) return true;
		else return false;
	}

	
	
	
	
	/* 7. Function amount_paid()
	 * 
	 * Returns the amount of coins received from the user
	 */
	public function amount_paid()
	{
		if ($this->paid) return $this->amountPaid; 
		else return 0;
	}

	
	
	
	
	/* 8. Function amount_paid_usd()
	 * 
	 * Returns the approximate amount in USD received from the user
	 * using live cryptocurrency exchange rates on the datetime of payment.
	 * Live Exchange Rates obtained from sites poloniex.com and bitstamp.net 
	 * and are updated every 30 minutes!
	 * 
	 * Or you can directly specify your price in USD and submit it in cryptobox using 
	 * variable 'amountUSD'. Cryptobox will automatically convert that USD amount 
	 * to cryptocoin amount using today current live cryptocurrency exchange rates. 
	 * 
	 * Using that functionality, you don't need to worry if cryptocurrency prices go down or up.
	 * User will pay you all times the actual price which is linked on current exchange 
	 * price in USD on the datetime of purchase. 
	 * 
	 * You can accepting cryptocoins on your website with cryptobox variable 'amountUSD'. 
	 * It increase your online sales and also use Poloniex.com AutoSell feature 
	 * (to trade your cryptocoins to USD/BTC during next 30 minutes after payment received).
	 */
	public function amount_paid_usd()
	{
		if ($this->paid) return $this->amountPaidUSD;
		else return 0;
	}
	
	
	
	
	
	/* 9. Functions set_status_processed() and is_processed() 
	 * 
	 * You can use this function when user payment has been received
	 * (function is_paid() returns true) and want to make one time action,
	 * for example  display 'thank you' message to user, etc.
	 * These functions helps you to exclude duplicate processing.
	 * 
	 * Please note that the user will continue to see a successful payment result in 
	 * their crypto Payment box during the period/timeframe you specify in cryptobox option $period
	 */	 
	public function set_status_processed()
	{
		if ($this->paymentID && $this->paid)
		{
			if (!$this->processed)
			{
				$sql = "UPDATE crypto_payments SET processed = 1, processedDate = '".gmdate("Y-m-d H:i:s")."' WHERE paymentID = ".intval($this->paymentID)." LIMIT 1";
				run_sql($sql);
				$this->processed = true;
			}
			return true;
		}
		else return false;
	}
	
	
	
	
	
	/* 10. Function is_processed() 
	 * 
	 * If payment status in database is 'processed' - return true, 
	 * otherwise return false. You need to use it with 
	 * function set_status_processed() together 
	*/
	public function is_processed()
	{
		if ($this->paid && $this->processed) return true;
		else return false;
	}


	
	
	
	/* 11. Function cryptobox_type() 
	 * 
	 * Returns 'paymentbox' or 'captchabox'
	 * 
	 * The Cryptocoin Payment Box and Crypto Captcha are 
	 * absolutely identical technically except for their visual effect.
	 *
	 * It uses the same code to get your user payment, to process that  
	 * payment and to forward received coins to you. They have only two 
	 * visual differences - users will see different logos and different 
	 * text on successful result page.
	 * For example, for dogecoin it will be - 'Dogecoin Payment' or 
	 * 'Dogecoin Captcha' logos and when payment is received we will publish 
	 * 'Payment received successfully' or 'Captcha Passed successfully'.
	 *  
	 * We have made it easier for you to adapt our payment system to your website. 
	 * On signup page you can use 'Bitcoin Captcha' and on sell products page - 'Bitcoin Payment'. 
	*/
	public function cryptobox_type()
	{
		return $this->boxType;
	}
	
	
	
	
	
	/* 12. Function payment_id() 
	 * 
	 * Returns current record id in the table crypto_payments.
	 * Crypto_payments table stores all payments from your users
	*/
	public function payment_id()
	{
		return $this->paymentID;
	}
	
	
	
	
	/* 13. Function payment_date() 
	 * 
	 * Returns payment/transaction datetime in GMT format
	 * Example - 2014-09-26 17:31:58 (is 26 September 2014, 5:31pm GMT) 
	*/
	public function payment_date()
	{
		return $this->paymentDate;
	}
	
	
	
	/* 14. Function payment_info()
	 * 
	 * Returns object with current user payment details -
	 * coinLabel 	 	- cryptocurrency label
	 * countryID 	 	- user location country, 3 letter ISO country code
	 * countryName 	 	- user location country
	 * amount 			- paid cryptocurrency amount
	 * amountUSD 	 	- approximate paid amount in USD with exchange rate on datetime of payment made
	 * addr				- your internal wallet address on gourl.io which received this payment
	 * txID 			- transaction id
	 * txDate 			- transaction date (GMT time)
	 * txConfirmed		- 0 - unconfirmed transaction/payment or 1 - confirmed transaction/payment 
	 * processed		- true/false. True if you called function set_status_processed() for that payment before  
	 * processedDate	- GMT time when you called function set_status_processed()  
	 * recordCreated	- GMT time a payment record was created in your database  
	 * etc.
	*/
	public function payment_info()
	{
		$obj = ($this->paymentID) ? run_sql("SELECT * FROM crypto_payments WHERE paymentID = ".intval($this->paymentID)." LIMIT 1") : false;
		if ($obj) $obj->countryName = get_country_name($obj->countryID);
		return $obj;
	}
	
	
	
	
	
	/* 15. Function cryptobox_reset()
	 *
	 * Optional, It will delete cookies/sessions with userID and new cryptobox with new payment amount
	 * will be displayed after page reload. Cryptobox will recognize user as a new one with new generated userID.
	 * For example, after you have successfully received the cryptocoin payment and had processed it, you can call
	 * one-time cryptobox_reset() in end of your script. Use this function only if you have not set userID manually.
	*/
	public function cryptobox_reset()
	{
		if (in_array($this->userFormat, array("COOKIE", "SESSION")))
		{
			$iframeID = $this->iframe_id();
			
			switch ($this->userFormat)
			{
				case "COOKIE":
					$s = trim(strtolower($_SERVER['SERVER_NAME']), " /");
					if (stripos($s, "www.") === 0) $s = substr($s, 4);
					$d = time(); if ($d > 1410000000) $d -= 1410000000;
					$v = trim($d."__".substr(md5(uniqid(mt_rand().mt_rand().mt_rand())), 0, 10));
					setcookie($this->cookieName, $v, time()+(10*365*24*60*60), '/', $s);
					$this->userID = $v;
					break;
						
				case "SESSION":
					$d = time(); if ($d > 1410000000) $d -= 1410000000;
					$v = trim($d."--".substr(md5(uniqid(mt_rand().mt_rand().mt_rand())), 0, 10));
					$this->userID = $_SESSION[$this->cookieName] = $v;
					break;
			}
			
			if ($this->iframeID == $iframeID) $this->iframeID = $this->iframe_id();
						
			return true;
		}
		else return false;
	}
		
	
	
	
	/* 16. Function coin_name()
	 *
	 * Returns coin name (bitcoin, bitcoincash, bitcoinsv, litecoin, dash, etc)   
	*/
	public function coin_name()
	{
		return $this->coinName;
	}
	
	
	
	
	/* 17. Function coin_label()
	 *
	 * Returns coin label (BTC, BCH, BSV, LTC, DASH, etc)   
	*/
	public function coin_label()
	{
		return $this->coinLabel;
	}

	
	
	/* 18. Function iframe_id()
	 *
	 * Returns payment box frame id   
	*/
	public function iframe_id()
	{
		return "box".$this->icrc32($this->boxID."__".$this->orderID."__".$this->userID."__".$this->private_key);
	}
	

	
	
	/* 19. Function payment_status_text()
	 *
	 * Return message from $cryptobox_localisation on current user language
	 * message payment received or not; "msg_not_received", "msg_received" or "msg_received2"
	 */
	public function payment_status_text()
	{
        if ($this->paid) $txt = str_replace(array("%coinName%", "%coinLabel%", "%amountPaid%"), array($this->coinName, $this->coinLabel, $this->amountPaid), $this->localisation[($this->boxType=="paymentbox"?"msg_received":"msg_received2")]);
        else $txt = str_replace(array("%coinName%", "%coinNames%", "%coinLabel%"), array($this->coinName, (in_array($this->coinLabel, array('BCH', 'BSV', 'DASH'))?$this->coinName:$this->coinName.'s'), $this->coinLabel), $this->localisation["msg_not_received"]);
	             
	    return $txt;        
	}
	
	
	
	/* 20. Function display_cryptobox_bootstrap()
	 *
	 *  Show Customize Mobile Friendly Payment Box and automatically displays successful payment message.
	 *  This function use bootstrap4 template; you can use your own template without this function
	 *  
	 *  FREE WHITE-LABEL BITCOIN/ALTCOIN PAYMENT BOX WITH THIS FUNCTION 
	 *  Simple use this function with 'curl' option and your own logo
	 *
	 *  Live Demo  (awaiting payment)  -     https://gourl.io/lib/examples/example_customize_box.php?boxtype=1
	 *  Live Demo2 (payment received)  -     https://gourl.io/lib/examples/example_customize_box.php?boxtype=2
	 *
	 *  Your html5 file header should have -
	 *  
	 *  <!DOCTYPE html>
	 *  <html lang="en">
	 *  <head>
	 *  <title>...</title>
	 *  <meta charset="utf-8">
	 *  <meta http-equiv="X-UA-Compatible" content="IE=edge">
	 *  <meta name="viewport" content="width=device-width, initial-scale=1">

	 *  A. <!-- Bootstrap CSS - Original Theme -->
	 *  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" crossorigin="anonymous">
	 *  
	 *  B. OR you can use other Themes, for example from https://bootswatch.com/; replace line with bootstrap.min.css above to line below -
	 *  <!-- <link rel="stylesheet" href="https://bootswatch.com/4/darkly/bootstrap.css"> -->

	 *  C. OR isolate Bootstrap CSS to a particular class to avoid css conflicts with your site main css style; use custom isolate css themes from /css folder 
	 *  Bootstrap Isolated CSS (class='bootstrapiso') Original Theme - 
	 *  <!-- <link rel="stylesheet" href="/css/bootstrapcustom.min.css"> -->
	 *  
	 *  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" crossorigin="anonymous"></script>
	 *  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js" crossorigin="anonymous"></script>
	 *  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" crossorigin="anonymous"></script>
	 *  <script defer src="https://use.fontawesome.com/releases/v5.12.0/js/all.js" crossorigin="anonymous"></script>
	 *  script src="<?php echo CRYPTOBOX_JS_FILES_PATH; ?>support.min.js" crossorigin="anonymous"></script>
	 *  <style>
            html { font-size: 14px; }
            @media (min-width: 768px) { html { font-size: 16px; } .tooltip-inner { max-width: 350px; } }
            .mncrpt .container { max-width: 980px; }
            .mncrpt .box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
            img.radioimage-select { padding: 7px; border: solid 2px #ffffff; margin: 7px 1px; cursor: pointer; box-shadow: none; }
            img.radioimage-select:hover { border: solid 2px #a5c1e5; }
            img.radioimage-select.radioimage-checked { border: solid 2px #7db8d9; background-color: #f4f8fb; }
	 *  </style>
	 *  </head>
	 *  <body>
	 *  ....
	 *
	
	* This function has the following parameters -
	* $coins - list of cryptocoins which you accept for payment (bitcoin/litecoin/dash/..)
	* $def_coin - default coin in payment box 
	* $def_language - default language in payment box
	* $custom_text - your own text above payment box 
	* $coinImageSize - coin selection list - image sizes; default 70px 
	* $qrcodeSize - QRCode size; default 200px
	* $show_languages - show or hide language selection menu above payment box
	* $logoimg_path - show or hide (when empty value) logo above payment box. You can use default logo or place path to your own logo
	* $resultimg_path - after payment is received, you can customize successful image in payment box (image with your company text for example)
	* $resultimgSize - result image size; default 250px
	* redirect - redirect to another page after payment is received (3 seconds delay)
	*    
	* method - "ajax" or "curl". 
	*    AJAX - user don't need click payment submit button on form. Payment box show successful paid message automatically
	*    CURL + White Label Payment Box with Your Own Logo (White Label Product - https://www.google.com/search?q=white+label+product), user need to click on button below payment form when payment is sent
	*    with ajax - user browser receive payment data directly from our server and automatically show successful payment notification message on the page (without page reload, any clicks on buttons). 
	*    with curl - User browser receive payment data in json format from your server only; and your server receive json data from our server
	* 
	* debug - show raw payment data from gourl.io on the page also, for debug purposes.  
	*
	* JSON Raw Payment Values Example -
	* Payment not received - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/2.21/pe/NOEXPIRY/l/en/o/invoice22/u/83412313__3bccb54769/us/COOKIE/j/1/d/ODIuMTEuOTQuMTIx/h/e889b9a07493ee96a479e471a892ae2e
	* Payment received successfully - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/0.1/pe/NOEXPIRY/l/en/o/invoice1/u/demo/us/MANUAL/j/1/d/ODIuMTEuOTQuMTIx/h/ac7733d264421c8410a218548b2d2a2a
	 *  
	 */

	public function display_cryptobox_bootstrap ($coins = array(), $def_coin = "", $def_language = "en", $custom_text = "", $coinImageSize = 70, $qrcodeSize = 200, $show_languages = true, $logoimg_path = "default", $resultimg_path = "default", $resultimgSize = 250, $redirect = "", $method = "curl", $debug = false)
	{


	    $logoimg_path = preg_replace('/[^A-Za-z0-9\-\_\=\?\&\.\;\:\/]/', '', $logoimg_path);
	    $resultimg_path = preg_replace('/[^A-Za-z0-9\-\_\=\?\&\.\;\:\/]/', '', $resultimg_path);
	    $redirect = preg_replace('/[^A-Za-z0-9\-\_\=\?\&\.\;\:\/]/', '', $redirect);

	    $custom_text = strip_tags($custom_text, '<p><a><br>');
	    
	    $coinImageSize    = intval($coinImageSize);
	    if ($coinImageSize > 200) $coinImageSize = 70;
	    
	    $qrcodeSize    = intval($qrcodeSize);
	    if ($qrcodeSize > 500) $qrcodeSize = 200;

	    $resultimgSize    = intval($resultimgSize);
	    if ($resultimgSize > 500) $resultimgSize = 250;
	    
	    if (!in_array($method, array("ajax", "curl"))) $method = "curl";
	     
	    
	    $ext           = (defined("CRYPTOBOX_PREFIX_HTMLID")) ? CRYPTOBOX_PREFIX_HTMLID : "acrypto_";      // any prefix for all html elements; default 'acrypto_'
	    $ext2          = "h".trim($ext, " _");
	    
	    $page_url      = "//".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]."#".$ext2; // Current page url
	    $hide       =  "style='display:none'";
	    
	    $phpdir_path   = (defined("CRYPTOBOX_PHP_FILES_PATH")) ? CRYPTOBOX_PHP_FILES_PATH : "";            // path to directory with files cryptobox.class.php/cryptobox.callback.php/cryptobox.newpayment.php; cryptobox.newpayment.php will be automatically call through ajax or php two times - when payment received and when confirmed (6 confirmations) 
	    $imgdir_path   = (defined("CRYPTOBOX_IMG_FILES_PATH")) ? CRYPTOBOX_IMG_FILES_PATH : "images/";     // path to directory with coin image files (directory 'images' by default)
	    $jsdir_path    = (defined("CRYPTOBOX_JS_FILES_PATH"))  ? CRYPTOBOX_JS_FILES_PATH : "";             // path to directory with files ajax.min.js/support.min.js
	     
	    
	    
	    // Language selection list for payment box (html code)
	    if ($show_languages) $languages_list = display_language_box($def_language, $ext2, false);



	
	     
	    // ---------------------------
	    // Bootstrap4 Template Start
	    // ----------------------------
	     
	    // All Payment Box Elements Area Start ...
	    $tmp  = "<div class='bootstrapiso'>";
	    $tmp .= "<div id='".$ext2."' class='".$ext."cryptobox_area mncrpt'>";
	     
	    //JQuery Payment Box Script, see https://github.com/cryptoapi/Payment-Gateway/blob/master/js/source/ajax.js
	    if ($method == "ajax")
	    {
	        $tmp .= "<script>jQuery.getScript('".$jsdir_path."ajax.min.js',  function() { cryptobox_ajax('" . base64_encode($this->cryptobox_json_url()) . "', " . intval($this->is_paid()) . ", " . intval($this->is_confirmed()) . ", '" . base64_encode($phpdir_path) . "', '" . base64_encode($imgdir_path) . "', '" . base64_encode($logoimg_path) . "', '" . base64_encode($ext) . "', '" . base64_encode($redirect) . "'); })</script>";
	    }
	    else
	    {
	        $data = $this->get_json_values();
	        unset($data["public_key"]); unset($data["texts"]["website"]);
	        if (isset($data["private_key"])) unset($data["private_key"]);
	        if (isset($data["private_key_hash"])) unset($data["private_key_hash"]);
	        unset($data["data_hash"]);
	        $data = json_encode($data, JSON_FORCE_OBJECT | JSON_HEX_APOS);
	        $tmp .= '<script>jQuery(document).ready(function(){ cryptobox_update_page("'.base64_encode($data).'", "' . base64_encode($imgdir_path) . '", "' . base64_encode($logoimg_path) . '", "' . base64_encode($ext) . '") })</script>';
	        if ($this->is_paid() && $redirect) $tmp .= '<script>setTimeout(function() { window.location = "'.$redirect.'"; }, 3000);</script>';
	    } 
	     
	     
	    
	    // ----------------------------------
	    // Text - Pay now + Custom User text 
	    // ----------------------------------
	    
	    $tmp .= "<div class='".$ext."header px-3 py-3 pt-md-5 pb-md-4 mx-auto my-4 text-center' style='max-width:700px'>";
	    $tmp .= "<h1 class='display-4'><span class='".$ext."texts_pay_now'>&#160;</span>";
	    $tmp .= "<span class='".$ext."loading_icon mr-3 float-right' " . $hide . "><i style='font-size:50%;' class='fas fa-sync-alt fa-spin'></i></span>";
	    $tmp .= "</h1>";
		


	    $custom_text = trim($custom_text);
	    if ($custom_text)
	    {
    	    $tmp .= "<br>";
    	    if (stripos($custom_text, "<p") === false) $tmp .= "<p class='lead'>" . $custom_text . "</p>"; else $tmp .= $custom_text;
	    }
	    $tmp .= "</div>";



	     // Coin selection list (bitcoin/litecoin/etc)
	     // --------------------
	     $coins_list_html = "";
	     if (!$this->is_paid())
	     {
	        // Coin selection list (html code)
	        $coins_list = display_currency_box($coins, $def_coin, $def_language, $coinImageSize, "margin: 20px 0 80px 0", $imgdir_path, $ext2, true);
	        $coins_list_html = "<div class='container ".$ext."coins_list'><div class='row'><div class='col-12 text-center col-sm-10 offset-sm-1 col-md-8 offset-md-2 text-center'>" . $coins_list . "</div></div></div>";
	     }


	    // ------------------------------
	    // Payment Box Ajax Loading ...
	    // ------------------------------
	    $tmp .= "<div class='".$ext."loader' style='height:700px'>";

	    $tmp .= "<form action='" . $page_url. "' method='post'>";
	    $tmp .= "<div class='container text-center ".$ext."loader_button pt-5 mt-5'><br><br><br><br><br>";
	    $tmp .= "<button type='submit' title='Click to Reload Page' class='btn btn-outline-secondary btn-lg'><i class='fas fa-spinner fa-spin'></i> &#160; " . $this->coin_name() . " " . $this->localisation["loading"] . "</button>";
	    $tmp .= "</div>";

	    $tmp .= "<div class='container'>";
	    $tmp .= "<div class='row'>";
	    $tmp .= "<div class='col-12 text-center col-sm-10 offset-sm-1 col-md-8 offset-md-2'>";
	    $tmp .= "<div class='".$ext."cryptobox_error' " . $hide . ">";
	    $tmp .= $coins_list_html;
	    $tmp .= "<div class='card box-shadow'>";
	    $tmp .= "<div class='card-header'>";
	    $tmp .= "<h4 class='my-0 font-weight-normal'>Error Message";
	    $tmp .= "<span class='".$ext."loading_icon mr-3 float-left' " . $hide . "> <i class='fas fa-laptop'></i></span>";
	    $tmp .= "<span class='".$ext."loading_icon mr-3 float-left' " . $hide . "> <i class='fas fa-sync-alt fa-spin'></i></span>";
	    $tmp .= "</h4>";
	    $tmp .= "</div>";
	    $tmp .= "<div class='card-body'>";
	    $tmp .= "<h1 class='card-title'>" . $this->coin_name() . " " . $this->localisation["loading"] . "</h1>";
	    $tmp .= "<br>";
	    $tmp .= "<div class='lead ".$ext."error_message'></div>";
	    $tmp .= "<br><br>";
	    $tmp .= "<button type='submit' class='".$ext."button_error btn btn-outline-primary btn-block btn-lg'><i class='fas fa-sync'></i> &#160; Reload Page</button>";
	    $tmp .= "<br>";
	    $tmp .= "</div>";
	    $tmp .= "</div>";
	    $tmp .= "<br><br><br><br><br>";
	    $tmp .= "</div>";
	    $tmp .= "</div>";
	    $tmp .= "</div>";
	    $tmp .= "</div>";
	    $tmp .= "</form>";

	    $tmp .= "</div>";
	    
	    // End - Payment Box Ajax Loading ...
	    	    

	    

	    // ----------------------------
	    // Area above Payment Box
	    // ----------------------------
	    $tmp .= "<div class='".$ext."cryptobox_top' " . $hide . ">";
	     
	    
        // A1. Notification payment received or not; when user click 'Refresh' button below payment form
        // --------------------
        if (isset($_POST["".$ext."refresh_"]) || isset($_POST["".$ext."refresh2_"]))
        {    
	            $tmp .= "<div class='row ".$ext."msg mx-2'>";
	            $tmp .= "<div class='container'>";
	            $tmp .= "<div class='row'>";	
	            $tmp .= "<div class='col-12 col-sm-10 offset-sm-1 mb-5 mt-2 text-left'>";
	                        
                if ($this->is_paid(true)) 
                    $tmp .= "<span class='badge badge-success ".$ext."paymentcaptcha_statustext'>Successfully Received</span>"; 
                else 
                    $tmp .= "<span class='badge badge-danger ".$ext."paymentcaptcha_statustext'>Not Received</span>";
                
	            $tmp .= "<div class='jumbotron jumbotron-fluid text-center'>";
	            $tmp .= "<div class='container'>";
                $t = $this->payment_status_text();
	            if (mb_strpos($t, '<br>')) 
	            { 
	                $tmp .= "<h3 class='display-5'>" . $this->left($t, "<br>") . "</h3><br>"; 
	                $t = $this->right($t, '<br>'); 
	            } 
	            $tmp .= "<p class='lead'>" . $t . "</p>";
	            $tmp .= "</div>";
	            $tmp .= "</div>";
	                        
	            $tmp .= "</div>";
	            $tmp .= "</div>";
	            $tmp .= "</div>";
	            $tmp .= "</div>";
	                
	     }  
	    

	     
	     // A2. Coin selection list (bitcoin/litecoin/etc)
	     // --------------------
	     if (!$this->is_paid())
	     {
	         if (!$custom_text) $tmp .= "<br>";
	         $tmp .= $coins_list_html;
	     }


	     
	     
	     // Language / logo Row
	     if ($show_languages || $logoimg_path)
	     {
    	     $tmp .= "<div class='container'>";
    	     $tmp .= "<div class='row'>";
	     }	     

	     
	     // A3. Box Language
	     // --------------------
	     
	     if ($show_languages)
	     {
    	     $offset = ($logoimg_path) ? "mb-2" : "mb-3";
    	     $tmp .= "<div class='".$ext."box_language col-12 ".(CRYPTOBOX_WORDPRESS?"text-left col-sm-2 col-md-3 offset-md-1":"col-sm-4 offset-sm-1 text-sm-left col-md-4 offset-md-2 text-md-left")." mt-sm-4 $offset'>";
    	     $tmp .= "<div class='btn-group'>";
    	     $tmp .= $languages_list;
    	     $tmp .= "</div>";
    	     $tmp .= "</div>";
	     }
	     // End - A3. Box Language
	     

	     // A4. Logo
	     // --------------------
	     if ($logoimg_path)
	     {
	         $offset = ($show_languages) ? "" : "offset-sm-5 offset-md-6";
	         $tmp .= "<div class='".$ext."box_logo col-12 ".(CRYPTOBOX_WORDPRESS?"col-sm-10 col-md-7":"col-sm-6 col-md-4")." mt-4 $offset'>";
    	     $tmp .= "<div class='text-right'><img style='max-width:200px;max-height:40px;' class='".$ext."logo_image' alt='logo' src='#'></div>";
    	     $tmp .= "<br>";
    	     $tmp .= "</div>";
	     }
    	 // End - A4. Logo

	     
	     if ($show_languages || $logoimg_path)
	     {
    	     $tmp .= "</div>";
    	     $tmp .= "</div>";
	     }
	     else $tmp .= "<br><br>";
	     	     
	     
	     $tmp .= "</div>";   
	     // --------------------
         // End - Area above Payment Box
	     
	     
	     
	     
	     
	     

	     // -----------------------------------------------------------------------------------------------
	     // Two visual types of payment box - payment not received (type1) and payment received (type2)
	     // -----------------------------------------------------------------------------------------------
	     
	     
	     // Type1 - Crypto Payment Box - Payment Not Received
	     
	     
	     $tmp .= "<div class='container ".$ext."cryptobox_unpaid' " . $hide . ">";
	     $tmp .= "<div class='row'>";	
	           
	     $tmp .= "<div class='col-12 ".(CRYPTOBOX_WORDPRESS?"col-md-10 offset-md-1":"text-center col-sm-10 offset-sm-1 col-md-8 offset-md-2")."'>";
	           
	     $tmp .= "<form action='" . $page_url . "' method='post'>";
	     $tmp .= "<div class='card box-shadow'>";
	     $tmp .= "<div class='card-header'>";
	                 
	     $tmp .= "<h4 class='my-0 font-weight-normal ".$ext."addr_title'><span class='".$ext."texts_coin_address'>&#160;</span>";
	     $tmp .= "<button type='submit' class='".$ext."refresh btn btn-sm btn-outline-secondary float-right'><i class='fas fa-sync-alt'></i></button>";
	     $tmp .= "<span class='".$ext."loading_icon mr-3 float-left' " . $hide . "> <i class='fas fa-laptop'></i></span>";
	     $tmp .= "<span class='".$ext."loading_icon mr-3 float-left' " . $hide . "> <i class='fas fa-sync-alt fa-spin'></i></span>";
	     $tmp .= "</h4>";

	     $tmp .= "</div>";
	                 
	     $tmp .= "<div class='card-body'>";

	     if ($qrcodeSize) $tmp .= "<div class='".$ext."copy_address'><a href='#a'><img class='".$ext."qrcode_image' style='max-width:".intval($qrcodeSize)."px; height:auto; width:auto\9;' alt='qrcode' data-size='".intval($qrcodeSize)."' src='#'></a></div>";
	                     
	     $tmp .= "<h1 class='mt-3 mb-4 pb-1 card-title ".$ext."copy_amount'><span class='".$ext."amount'>&#160;</span> <small class='text-muted'><span class='".$ext."coinlabel'></span></small></h1>";
	     $tmp .= "<div class='lead ".$ext."copy_amount ".$ext."texts_send'></div>";
	     $tmp .= "<div class='lead ".$ext."texts_no_include_fee'></div>";
	     $tmp .= "<br>";
	     $tmp .= "<h4 class='card-title'>";
	     $tmp .= "<a class='".$ext."wallet_address' style='line-height:1.5;' href='#a'></a> &#160;&#160;"; 
	     $tmp .= "<a class='".$ext."copy_address' href='#a'><i class='fas fa-copy'></i></a> &#160;&#160;";  
	     $tmp .= "<a class='".$ext."wallet_open' href='#a'><i class='fas fa-external-link-alt'></i></a>";
	     $tmp .= "</h4>";
	     $tmp .= "<br>";
	     $tmp .= "<button type='submit' class='".$ext."button_wait btn btn-lg btn-block btn-outline-primary' style='white-space:normal'></button>";
	     $tmp .= "<br>";
	     $tmp .= "<p class='lead ".$ext."texts_intro3'></p>";
	                     
	     $tmp .= "</div>";
	                 
	     $tmp .= "</div>";
	               
	     $tmp .= "<input type='hidden' id='".$ext."refresh_' name='".$ext."refresh_' value='1'>";
	             
	     $tmp .= "<button type='submit' class='".$ext."button_refresh btn btn-lg btn-block btn-primary mt-3' " . $hide . "></button>";
	               
	     // $tmp .= "<div class='lead ".$ext."texts_btn_wait_hint mt-5'></div>"; // additional hint
	     
	     $tmp .= "</form>";
	     
	     $tmp .= "</div>";

	     if ($method != "ajax" && !$this->is_paid())
	     {
	         $tmp .= "<div class='col-12 text-center ".(CRYPTOBOX_WORDPRESS?"col-md-10 offset-md-1":"col-sm-10 offset-sm-1 col-md-8 offset-md-2")."'>";
	         $tmp .= "<form action='" . $page_url . "' method='post'>";
	         $tmp .= "<input type='hidden' id='".$ext."refresh2_' name='".$ext."refresh2_' value='1'>";
	         $tmp .= "<br><button type='submit' class='".$ext."button_confirm btn btn-lg btn-block btn-primary my-2' style='white-space:normal'><i class='fas fa-angle-double-right'></i> &#160; ".str_replace(array("%coinName%", "%coinNames%", "%coinLabel%"), array($this->coinName, (in_array($this->coinLabel, array('BCH', 'BSV', 'DASH'))?$this->coinName:$this->coinName.'s'), $this->coinLabel), $this->localisation["button"])." &#160; <i class='fas fa-angle-double-right'></i></button>";
	         $tmp .= "</form>";
	         $tmp .= "</div>";
	     }
	     
	     
	     $tmp .= "</div>";
	     $tmp .= "</div>";
	     
	     // -----------------------------------------------
	     // End Type1 - Payment Box - Payment Not Received
	     // -----------------------------------------------
	     
	     
	     
	     
	     
	     
	     
	     // -------------------------------------------------------------------------
	     // Type2 - Crypto Payment Box - Payment Received/Successful Result 
	     // -------------------------------------------------------------------------
	     
	     
	     $tmp .= "<div class='container ".$ext."cryptobox_paid' " . $hide . ">";
	     $tmp .= "<div class='row'>";
	     $tmp .= "<div class='col-12 ".(CRYPTOBOX_WORDPRESS?"col-md-10 offset-md-1":"col-sm-10 offset-sm-1 col-md-8 offset-md-2 text-center")."'>";
	           
	     $tmp .= "<div class='card box-shadow'>";
	     $tmp .= "<div class='card-header'>";
	                 
	     $tmp .= "<h4 class='my-0 font-weight-normal ".$ext."addr_title'><span class='".$ext."texts_title'>&#160;</span>";
	     $tmp .= "<span class='".$ext."loading_icon mr-3 float-left' " . $hide . "> <i class='fas fa-laptop'></i></span>";
	     $tmp .= "<span class='".$ext."loading_icon mr-3 float-left' " . $hide . "> <i class='fas fa-sync-alt fa-spin'></i></span>";
	     $tmp .= "</h4>";
	     
	     $tmp .= "</div>";
	                 
	     $tmp .= "<div class='card-body'>";
	                 
	     $tmp .= "<div class='".$ext."paid_total'>";
	     $tmp .= "<h1 style='margin-top:10px' class='card-title ".$ext."copy_amount'><span class='".$ext."amount'>&#160;</span> <small class='text-muted'><span class='".$ext."coinlabel'></span></small></h1>";
	     $tmp .= "</div>";
	     $tmp .= "<br>";
	     if (!$resultimg_path || $resultimg_path == "default") $resultimg_path = $imgdir_path."paid.png";
	     if ($resultimgSize) $tmp .= "<div class='".$ext."copy_transaction'><img class='".$ext."paidimg' style='max-width: 100%; width: ".$resultimgSize."px; height: auto;' src='".$resultimg_path."' alt='Paid'></div><br><br>";
	     $tmp .= "<h1 class='display-4 ".$ext."paymentcaptcha_successful' style='line-height:1.5;'>.</h1>";
	     $tmp .= "<br>";
	     $tmp .= "<div class='lead ".$ext."paymentcaptcha_date'></div>";
	     $tmp .= "<br>";
	     $tmp .= "<br>";
	     $tmp .= "<a href='#a' class='".$ext."button_details btn btn-lg btn-block btn-outline-primary' style='white-space:normal'></a>";
	     $tmp .= "</div>";
	                 
	     $tmp .= "</div>";
	             
	     $tmp .= "</div>";
	     $tmp .= "</div>";
	     $tmp .= "</div>";
	     $tmp .= "<br><br><br>";
	     
	     // -----------------------------------------------
	     // End Type2 - Payment Received/Successful Result 
	     // -----------------------------------------------
	     
	     
	     
	     
	     
	     
	     

	    // -------------------------------------------------------------------------------------------
	    // Debug Raw JSON Payment Data from gourl.io
	    // -------------------------------------------------------------------------------------------
	    
	     if ($debug)
	     {    

	     $tmp .= "<div class='mncrpt_debug container ".$ext."cryptobox_rawdata px-4 py-3' style='overflow-wrap: break-word; display:none;'>";
	     $tmp .= "<div class='row'>";
	     $tmp .= "<div class='col-12'>";
	     $tmp .= "<br><br><br><br><br>";
	     
	     $tmp .= "<h1 class='display-4'>Raw JSON Data (from GoUrl.io payment gateway) -</h1>";
	     $tmp .= "<br>";
	     $tmp .= "<p class='lead'><b>PHP Language</b> - Please use function <a target='_blank' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/lib/cryptobox.class.php#L754'>\$box->display_cryptobox_bootstrap (...)</a>; it generate customize mobile friendly bitcoin/altcoin payment box and automatically displays successful payment message (bootstrap4, json, your own logo, white label product, etc)</p>";
	     $tmp .= "<p class='lead'><b>ASP/Other Languages</b> - You can use function <a target='_blank' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/lib/cryptobox.class.php#L320'>\$box->cryptobox_json_url()</a>; It generates url with your parameters to gourl.io payment gateway. ";
	     $tmp .= "Using this url you can get bitcoin/altcoin payment box values in JSON format and use it on html page with Jquery/Ajax (on the user side). ";
	     $tmp .= "Or your server can receive JSON values through curl - function <a target='_blank' href='https://github.com/cryptoapi/Payment-Gateway/blob/master/lib/cryptobox.class.php#L374'>\$box->get_json_values()</a>; and use it in your files/scripts directly without javascript when generating the webpage (on the server side).</p>";
	     $tmp .= "<p class='lead'><a target='_blank' href='" . $this->cryptobox_json_url() . "'>JSON data source &#187;</a></p>";
	     
	     $tmp .= "<div class='card card-body bg-light'>";
	     $tmp .= "<div class='".$ext."jsondata'></div>";
	     $tmp .= "</div>";
	     
	     $tmp .= "</div>";
	     $tmp .= "</div>";
	     $tmp .= "</div>";
	     $tmp .= "<br><br><br>";
	     
	     }
	     // ------------------
	     // End Debug
	     // ------------------
	     
	     
	     
	     
	     $tmp .= "</div>"; 
	     $tmp .= "</div>";
	     // End - <div class='bootstrapiso'>

	     
	     
	     // ---------------------------
	     // Bootstrap4 Template End
	     // ----------------------------
	     
	     
	     return $tmp;
	     
	}
	
	
	
	
	
	
	

	
	
	
	
	
	
	/*
	 * Other Internal functions   
	*/
	private function check_payment($remotedb = false)
	{
		static $already_checked = false;
	    
		$this->paymentID = $diff = 0;
		
		$obj = run_sql("SELECT paymentID, amount, amountUSD, txConfirmed, txCheckDate, txDate, processed, boxType FROM crypto_payments WHERE boxID = ".intval($this->boxID)." && orderID = '".addslashes($this->orderID)."' && userID = '".addslashes($this->userID)."' ".($this->period=="NOEXPIRY"?"":"&& txDate >= DATE_SUB('".gmdate("Y-m-d H:i:s")."', INTERVAL ".addslashes($this->period).")")." ORDER BY txDate DESC LIMIT 1");
	
		if ($obj)
		{
			$this->paymentID 		= $obj->paymentID;
			$this->paymentDate 		= $obj->txDate;
			$this->amountPaid 		= $obj->amount;
			$this->amountPaidUSD 	= $obj->amountUSD;
			$this->paid 			= true;
			$this->confirmed 		= $obj->txConfirmed;
			$this->boxType 	= $obj->boxType;
			$this->processed 		= ($obj->processed) ? true : false;
			$diff					=  strtotime(gmdate('Y-m-d H:i:s')) - strtotime($obj->txCheckDate);
		}
		
		if (!$obj && isset($_POST["cryptobox_live_"]) && $_POST["cryptobox_live_"] == md5($this->iframeID.$this->private_key.$this->userID)) $remotedb = true;
		
		if (!$already_checked && ((!$obj && $remotedb) || ($obj && !$this->confirmed && ($diff > (10*60) || $diff < 0)))) // if $diff < 0 - user have incorrect time on local computer
		{
			$this->check_payment_live();
			$already_checked = true;
		}
	
		return true;
	}
	
	private function check_payment_live()
	{
		$ip		= $this->ip_address();
		$private_key_hash = strtolower(hash("sha512", $this->private_key));
		$hash 	= md5($this->boxID.$private_key_hash.$this->userID.$this->orderID.$this->language.$this->period.$this->ver.$ip);
		$box_status = "";
		
		$data = array(
				"g" 	=> $private_key_hash,
				"b" 	=> $this->boxID,
				"o"		=> $this->orderID,
				"u"		=> $this->userID,
				"l"		=> $this->language,
				"e"		=> $this->period,
				"t"		=> $this->ver, 
				"i"		=> $ip,
				"h"		=> $hash
		);
	
		$ch = curl_init( "https://coins.gourl.io/result.php" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->ua());
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		$res = curl_exec( $ch );
		curl_close($ch);
	
		if ($res) $res = json_decode($res, true);
		
		if ($res) foreach ($res as $k => $v) if (is_string($v)) $res[$k] = trim($v);

		
		if (isset($res["status"]) && in_array($res["status"], array("payment_received")) &&
				$res["box"] && is_numeric($res["box"]) && $res["box"] > 0 && $res["amount"] && is_numeric($res["amount"]) && $res["amount"] > 0 &&
				isset($res["private_key_hash"]) && strlen($res["private_key_hash"]) == 128 && preg_replace('/[^A-Za-z0-9]/', '', $res["private_key_hash"]) == $res["private_key_hash"] && strtolower($res["private_key_hash"]) == $private_key_hash)
		{
			
			foreach ($res as $k => $v)
			{
				if ($k == "datetime") 							$mask = '/[^0-9\ \-\:]/';
				elseif (in_array($k, array("err", "date")))		$mask = '/[^A-Za-z0-9\.\_\-\@\ ]/';
				else											$mask = '/[^A-Za-z0-9\.\_\-\@]/';
				if ($v && preg_replace($mask, '', $v) != $v) 	$res[$k] = "";
			}
			
			if (!$res["amountusd"] || !is_numeric($res["amountusd"]))	$res["amountusd"] = 0;
			if (!$res["confirmed"] || !is_numeric($res["confirmed"]))	$res["confirmed"] = 0;
				

			
			$dt  = gmdate('Y-m-d H:i:s');
			$obj = run_sql("select paymentID, processed, txConfirmed from crypto_payments where boxID = ".intval($res["box"])." && orderID = '".addslashes($res["order"])."' && userID = '".addslashes($res["user"])."' && txID = '".addslashes($res["tx"])."' && amount = ".floatval($res["amount"])." && addr = '".addslashes($res["addr"])."' limit 1"); 

			if ($obj)
			{ 
				$this->paymentID 	= $obj->paymentID; 
				$this->processed 	= ($obj->processed) ? true : false;
				$this->confirmed 	= $obj->txConfirmed;
				
				// refresh
				$sql = "UPDATE 		crypto_payments 
						SET 		boxType 			= '".$res["boxtype"]."',
									amount 				= ".$res["amount"].",
									amountUSD			= ".$res["amountusd"].",
									coinLabel			= '".$res["coinlabel"]."',
						 			unrecognised		= 0,
						 			addr				= '".$res["addr"]."',
						 			txDate				= '".$res["datetime"]."',
						 			txConfirmed			= ".$res["confirmed"].",
						 			txCheckDate			= '".$dt."'
						WHERE 		paymentID 			= ".intval($this->paymentID)."
						LIMIT 		1";
				
				run_sql($sql);
				
				if ($res["confirmed"] && !$this->confirmed) $box_status = "cryptobox_updated";
			}
			else 
			{	
				// Save new payment details in local database
				$sql = "INSERT INTO crypto_payments (boxID, boxType, orderID, userID, countryID, coinLabel, amount, amountUSD, unrecognised, addr, txID, txDate, txConfirmed, txCheckDate, recordCreated)
						VALUES (".$res["box"].", '".$res["boxtype"]."', '".$res["order"]."', '".$res["user"]."', '".$res["usercountry"]."', '".$res["coinlabel"]."', ".$res["amount"].", ".$res["amountusd"].", 0, '".$res["addr"]."', '".$res["tx"]."', '".$res["datetime"]."', ".$res["confirmed"].", '$dt', '$dt')";
	
				$this->paymentID = run_sql($sql);
				
				$box_status = "cryptobox_newrecord"; 
			}

			
			$this->paymentDate 		= $res["datetime"];
			$this->amountPaid 		= $res["amount"];
			$this->amountPaidUSD 	= $res["amountusd"];
			$this->paid 			= true;
			$this->boxType 			= $res["boxtype"];
			$this->confirmed 		= $res["confirmed"];
			
			
			/**
			 *  User-defined function for new payment - cryptobox_new_payment(...)
			 *  For example, send confirmation email, update database, update user membership, etc.
			 *  You need to modify file - cryptobox.newpayment.php
			 *  Read more - https://gourl.io/api-php.html#ipn
			 */

			if (in_array($box_status, array("cryptobox_newrecord", "cryptobox_updated")) && function_exists('cryptobox_new_payment')) cryptobox_new_payment($this->paymentID, $res, $box_status);
				
			
			return true;
		}
		return false;
	}
	public function left($str, $findme, $firstpos = true)
	{
		$pos = ($firstpos)? stripos($str, $findme) : strripos($str, $findme);
	
		if ($pos === false) return $str;
		else return substr($str, 0, $pos);
	}
	public function right($str, $findme, $firstpos = true)
	{
		$pos = ($firstpos)? stripos($str, $findme) : strripos($str, $findme);
	
		if ($pos === false) return $str;
		else return substr($str, $pos + strlen($findme));
	}
	public function icrc32($str)
	{
		$in = crc32($str);
		$int_max = pow(2, 31)-1;
		if ($in > $int_max) $out = $in - $int_max * 2 - 2;
		else $out = $in;
		$out = abs($out);
		 
		return $out;
	}
	private function ua($agent = true)
	{
	    return (isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'http') . '://' . $_SERVER["SERVER_NAME"] . (isset($_SERVER["REDIRECT_URL"])?$_SERVER["REDIRECT_URL"]:$_SERVER["PHP_SELF"]) . ' | GU ' . (CRYPTOBOX_WORDPRESS?'WORDPRESS':'PHP') . ' ' . CRYPTOBOX_VERSION . ($agent && isset($_SERVER["HTTP_USER_AGENT"])?' | '.$_SERVER["HTTP_USER_AGENT"]:'');
	}
	public function ip_address()
	{
		static $ip_address;
	
		if ($ip_address) return $ip_address;
	
		$ip_address         = "";
		$proxy_ips          = (defined("PROXY_IPS")) ? unserialize(PROXY_IPS) : array();  // your server internal proxy ip
		$internal_ips       = array('127.0.0.0', '127.0.0.1', '127.0.0.2', '192.0.0.0', '192.0.0.1', '192.168.0.0', '192.168.0.1', '192.168.0.253', '192.168.0.254', '192.168.0.255', '192.168.1.0', '192.168.1.1', '192.168.1.253', '192.168.1.254', '192.168.1.255', '192.168.2.0', '192.168.2.1', '192.168.2.253', '192.168.2.254', '192.168.2.255', '10.0.0.0', '10.0.0.1', '11.0.0.0', '11.0.0.1', '1.0.0.0', '1.0.1.0', '1.1.1.1', '255.0.0.0', '255.0.0.1', '255.255.255.0', '255.255.255.254', '255.255.255.255', '0.0.0.0', '::', '0::', '0:0:0:0:0:0:0:0');
	
		for ($i = 1; $i <= 2; $i++)
			if (!$ip_address)
			{
				foreach (array('HTTP_CLIENT_IP', 'HTTP_X_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP', 'X-Forwarded-For', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_REAL_IP', 'REMOTE_ADDR') as $header)
					if (!$ip_address && isset($_SERVER[$header]) && $_SERVER[$header])
					{
						$ip  = trim($_SERVER[$header]);
						$ip2 = "";
						if (strpos($ip, ',') !== FALSE)
						{
							list($ip, $ip2) = explode(',', $ip, 2);
							$ip = trim($ip);
							$ip2 = trim($ip2);
						}
							
						if ($ip && filter_var($ip, FILTER_VALIDATE_IP) && !in_array($ip, $proxy_ips) && ($i==2 || !in_array($ip, $internal_ips))) 				$ip_address = $ip;
						elseif ($ip2 && filter_var($ip2, FILTER_VALIDATE_IP) && !in_array($ip2, $proxy_ips) && ($i==2 || !in_array($ip2, $internal_ips))) 		$ip_address = $ip2;
					}
			}
	
			if (!$ip_address || !filter_var($ip_address, FILTER_VALIDATE_IP)) $ip_address = '0.0.0.0';
	
			return $ip_address;
	}

}	
// end class





	/* A. Function payment_history()
	*
	* Returns array with history payment details of any of your users / orders / etc. (except unrecognised payments) for custom period - $period
	* It includes -
	* paymentID 	 	- current record id in the table crypto_payments.
	* boxID 	 		- your cryptobox id, the same as on gourl.io member page
	* boxType			- 'paymentbox' or 'captchabox'
	* orderID			- your order id / page name / etc.
	* userID 	 		- your user identifier
	* countryID 	 	- your user's location (country) , 3 letter ISO country code
	* coinLabel 	 	- cryptocurrency label
	* amount 		 	- paid cryptocurrency amount
	* amountUSD 	 	- approximate paid amount in USD with exchange rate on datetime of payment made
	* addr			 	- your internal wallet address on gourl.io which received this payment
	* txID 				- transaction id
	* txDate 			- transaction date (GMT time)
	* txConfirmed		- 0 - unconfirmed transaction/payment or 1 - confirmed transaction/payment with 6+ confirmations
	* 					  you can use function is_confirmed() above, it will connect with payment server and get transaction status (confirmed/unconfirmed)
	* processed			- true/false. True if you called function set_status_processed() before
	* processedDate		- GMT time when you called function set_status_processed()
	* recordCreated		- GMT time a payment record was created in your database
	*/
	function payment_history($boxID = "", $orderID = "", $userID = "", $countryID = "", $boxType = "", $period = "7 DAY")
	{
		if ($boxID 		&& (!is_numeric($boxID) || $boxID < 1 || round($boxID) != $boxID))		return false;
		if ($orderID 	&& preg_replace('/[^A-Za-z0-9\.\_\-\@]/', '', $orderID) != $orderID) 		return false;
		if ($userID  	&& preg_replace('/[^A-Za-z0-9\.\_\-\@]/', '', $userID)  != $userID)  		return false;
		if ($countryID  && (preg_replace('/[^A-Za-z]/', '', $countryID)  != $countryID || strlen($countryID) != 3)) return false;
		if ($boxType 	&& !in_array($boxType, array('paymentbox','captchabox')))  				return false;
		if ($period  	&& preg_replace('/[^A-Za-z0-9\ ]/', '', $period)  	!= $period)  		return false;
		
		$res = run_sql("SELECT paymentID, boxID, boxType, orderID, userID, countryID, coinLabel, amount, amountUSD, addr, txID, txDate, txConfirmed, processed, processedDate, recordCreated       
						FROM crypto_payments WHERE unrecognised = 0 ".($boxID?" && boxID = ".intval($boxID):"").($orderID?" && orderID = '".addslashes($orderID)."'":"").($userID?" && userID='".addslashes($userID)."'":"").($countryID?" && countryID='".addslashes(strtoupper($countryID))."'":"").($period?" && recordCreated > DATE_SUB('".gmdate("Y-m-d H:i:s")."', INTERVAL ".addslashes($period).")":"")." ORDER BY txDate DESC LIMIT 10000");
	
		if ($res && !is_array($res)) $res = array($res);
		
		return $res;
	}
	

	
	
	
	/* B. Function payment_unrecognised()
	*
	* Returns array with unrecognised payments for custom period - $period.
	* (users paid wrong amount to your internal wallet address). 
	* You will need to process unrecognised payments manually.
	*
	* We forward you ALL coins received to your internal wallet address 
	* including all possible incorrect amount/unrecognised payments 
	* automatically every 30 minutes. 
	* 
	* Therefore if your user contacts us, regarding the incorrect sent payment,
	* we will forward your user to you (because our system forwards all received payments
	* to your wallet automatically every 30 minutes). We provide a payment gateway only.
	* You need to deal with your user directly to resolve the situation or return the incorrect
	* payment back to your user. In unrecognised payments statistics table you will see the
	* original payment sum and transaction ID - when you click on that transaction's ID
	* it will open external blockchain explorer website with wallet address/es showing
	* that payment coming in. You can tell your user about your return of that incorrect
	* payment to one of their sending address (which will protect you from bad claims).
	*
	* You will have a copy of the statistics on your gourl.io member page
	* with details of incorrect received payments.
	* 
	* It includes -
	* paymentID 	 	- current record id in the table crypto_payments.
	* boxID 	 		- your cryptobox id, the same as on gourl.io member page
	* boxType			- 'paymentbox' or 'captchabox'
	* coinLabel 	 	- cryptocurrency label
	* amount 		 	- paid cryptocurrency amount
	* amountUSD 	 	- approximate paid amount in USD with exchange rate on datetime of payment made
	* addr			 	- your internal wallet address on gourl.io which received this payment
	* txID 				- transaction id
	* txDate 			- transaction date (GMT time)
	* recordCreated		- GMT time a payment record was created in your database
	*/
	function payment_unrecognised($boxID = "", $period = "7 DAY")
	{
		if ($boxID && (!is_numeric($boxID) || $boxID < 1 || round($boxID) != $boxID))	return false;
		if ($period && preg_replace('/[^A-Za-z0-9\ ]/', '', $period) != $period) return false;
			
		$res = run_sql("SELECT paymentID, boxID, boxType, coinLabel, amount, amountUSD, addr, txID, txDate, recordCreated
						FROM crypto_payments WHERE unrecognised = 1 ".($boxID?" && boxID = ".intval($boxID):"").($period?" && recordCreated > DATE_SUB('".gmdate("Y-m-d H:i:s")."', INTERVAL ".addslashes($period).")":"")." ORDER BY txDate DESC LIMIT 10000");
	
		if ($res && !is_array($res)) $res = array($res);
		
		return $res;
	}

	
	
	
	
	/* C. Function cryptobox_sellanguage($default = "en")
	 *
	 *  Get cryptobox current selected language by user (english, spanish, etc)
	 */
	function cryptobox_sellanguage($default = "en")
	{
		$default 		= strtolower($default);
	    $localisation 	= json_decode(CRYPTOBOX_LOCALISATION, true); // List of available languages
	    $id 	 		= (defined("CRYPTOBOX_LANGUAGE_HTMLID")) ? CRYPTOBOX_LANGUAGE_HTMLID : "gourlcryptolang";
	     
	    if(defined("CRYPTOBOX_LANGUAGE"))
	    {
	        if (!isset($localisation[CRYPTOBOX_LANGUAGE])) die("Invalid lanuage value '".CRYPTOBOX_LANGUAGE."' in CRYPTOBOX_LANGUAGE; function cryptobox_language()");
	        else return CRYPTOBOX_LANGUAGE;
	    }

	    if (isset($_GET[$id]) && in_array($_GET[$id], array_keys($localisation)) && !defined("CRYPTOBOX_LANGUAGE_HTMLID_IGNORE") && preg_replace('/[^A-Za-z0-9]/', '', $_GET[$id]) == $_GET[$id] && strlen($_GET[$id]) <= 5) { $lan = $_GET[$id]; setcookie($id, $lan, time()+7*24*3600, "/"); }
	    elseif (isset($_COOKIE[$id]) && in_array($_COOKIE[$id], array_keys($localisation)) && !defined("CRYPTOBOX_LANGUAGE_HTMLID_IGNORE") && preg_replace('/[^A-Za-z0-9]/', '', $_COOKIE[$id]) == $_COOKIE[$id] && strlen($_COOKIE[$id]) <= 5) $lan = $_COOKIE[$id];
	    elseif (in_array($default, array_keys($localisation))) $lan = $default;
	    else 	$lan = "en";
	    
	    define("CRYPTOBOX_LANGUAGE", $lan);
	    
	    return $lan;
	}
	
	
	
	
	
	/* D. Function cryptobox_selcoin()
	 *
	 * Get cryptobox current selected coin by user (bitcoin, dogecoin, etc. - for multiple coin payment boxes)
	 */
	function cryptobox_selcoin($coins = array(), $default = "")
	{
	    static $current = "";

	    $default 			= strtolower($default);
	    $available_payments = json_decode(CRYPTOBOX_COINS, true); // List of available crypto currencies
	    $id 	 			= (defined("CRYPTOBOX_COINS_HTMLID")) ? CRYPTOBOX_COINS_HTMLID : "gourlcryptocoin";

	    if ($default && !in_array($default, $coins)) $coins[] = $default;
	    if (!$default && $coins) $default = array_values($coins)[0];
	     
	    
	    if($current)
	    {
	        if (!in_array($current, $available_payments)) $current = $default;
	        else return $current;
	    }
	     
	    
	    // Current Selected Coin
	    if (isset($_GET[$id]) && in_array($_GET[$id], $available_payments) && in_array($_GET[$id], $coins) && preg_replace('/[^A-Za-z0-9]/', '', $_GET[$id]) == $_GET[$id] && strlen($_GET[$id]) <= 25) { $coinName = $_GET[$id]; setcookie($id, $coinName, time()+7*24*3600, "/"); }
	    elseif (isset($_COOKIE[$id]) && in_array($_COOKIE[$id], $available_payments) && in_array($_COOKIE[$id], $coins) && preg_replace('/[^A-Za-z0-9]/', '', $_COOKIE[$id]) == $_COOKIE[$id] && strlen($_COOKIE[$id]) <= 25) $coinName = $_COOKIE[$id];
	    else $coinName = $default;
	
	    $current =  $coinName;
	     
	    return $coinName;
	}
	
	
	
	
	    
	/* E. Function display_language_box()
	 * 
	 * Language selection dropdown list for cryptocoin payment box<br>
	 * $no_bootstrap = false - use dropdown list in bootstrap
	 */
	function display_language_box($default = "en", $anchor = "gourlcryptolang", $no_bootstrap = true)
	{
	    
		$default 		= strtolower($default);
		$localisation 	= json_decode(CRYPTOBOX_LOCALISATION, true);
		$id 	 		= (defined("CRYPTOBOX_LANGUAGE_HTMLID")) ? CRYPTOBOX_LANGUAGE_HTMLID : "gourlcryptolang";
		$arr 	 		= $_GET;
		if (isset($arr[$id])) unset($arr[$id]);
		
		$lan = cryptobox_sellanguage($default);
		
		$url = $_SERVER["REQUEST_URI"];
		if (mb_strpos($url, "?")) $url = mb_substr($url, 0, mb_strpos($url, "?"));
		
		//sort
		$l1 = array_slice ($localisation, 0, 8);
		$l2 = array_slice ($localisation, 8);
		asort ($l2);
		$localisation = array_merge($l1, $l2);
		
		// <select> html tag list
		if ($no_bootstrap)
		{
    		$tmp  = "<select name='$id' id='$id' onchange='window.open(\"//".$_SERVER["HTTP_HOST"].$url."?".http_build_query($arr).($arr?"&amp;":"").$id."=\"+this.options[this.selectedIndex].value+\"#".$anchor."\",\"_self\")' style='width:130px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#666;border-radius:5px;-moz-border-radius:5px;border: #ccc 1px solid;margin:0;padding:3px 0 3px 6px;white-space:nowrap;overflow:hidden;display:inline;'>";
    		foreach ($localisation as $k => $v)
    		{
    		    $tmp .= "<option ".($k==$lan?"selected":"")." value='$k'>".$v["name"]."</option>";
    		    if ($k == "sv") $tmp .= "<option value='' disabled>---------</option>";
    		}
    		$tmp .= "</select>";
		}
		else
		// bootstrap4
		{
		    
		    $tmp = "<div class='dropdown'>";
		    $tmp .= "<button type='button' class='btn btn-outline-secondary dropdown-toggle' data-toggle='dropdown' id='dropdownMenuButtonLang' aria-haspopup='true' aria-expanded='false'>";
		    $tmp .= "Language" . " - " . $localisation[$lan]['name'];
		    $tmp .= "</button>";
		    $tmp .= "<div class='dropdown-menu' aria-labelledby='dropdownMenuButtonLang' style='width:21em;margin:1px 0 1px -10px'>";
		    $tmp .= "<div class='dropdown-row'>";
		    
		    $count = 0;
		    foreach ($localisation as $k => $v) 
		    {
		        $count ++;
		        $tmp .= "<a href='//".$_SERVER["HTTP_HOST"].$url."?".http_build_query($arr).($arr?"&amp;":"").$id."=".$k."#".$anchor."' class='dropdown-item".($k==$lan?" disabled":"")."' style='display:inline-block;width:49%;'>".$v["name"]."</a>";
		        if (!($count % 2)) {
		            $tmp .= "</div>";
		            if ($count == 8) $tmp .= "<div class='dropdown-divider'></div>";
		            $tmp .= "<div class='dropdown-row'>";
		        }
		        
            }
            $tmp .= '</div></div></div>';
		}
				
		return $tmp; 
	}
	
	
	

	
	/* F. Function display_currency_box()
	 *
	* Multiple crypto currency selection list. You can accept payments in multiple crypto currencies
	* For example you can accept payments in bitcoin, bitcoincash, bitcoinsv, litecoin, etc and use the same price in USD
	*/
	function display_currency_box($coins = array(), $def_coin = "", $def_language = "en", $iconWidth = 50, $style = "width:350px; margin: 10px 0 10px 320px", $directory = "images", $anchor = "gourlcryptocoins", $jquery = false)
	{
		if (!$coins) return "";

		$directory          = rtrim($directory, "/");
		$def_coin 			= strtolower($def_coin);
		$def_language 			= strtolower($def_language);
		$available_payments = json_decode(CRYPTOBOX_COINS, true);
		$localisation       = json_decode(CRYPTOBOX_LOCALISATION, true);
		$arr 	 			= $_GET;
		$id 	 			= (defined("CRYPTOBOX_COINS_HTMLID")) ? CRYPTOBOX_COINS_HTMLID : "gourlcryptocoin";
		
		if (!in_array($def_coin, $available_payments)) die("Invalid your default value '$def_coin' in display_currency_box()");
		if (!in_array($def_coin, $coins)) $coins[] = $def_coin; 
		
		$coins = array_map('strtolower', $coins);
		$coins = array_unique($coins);
		if (count($coins) <= 1) return "";
		
		
		// Current Coin
		$coinName = cryptobox_selcoin($coins, $def_coin);
		
		
		// Url for Change Coin
		$coin_url = $_SERVER["REQUEST_URI"];
		if (mb_strpos($coin_url, "?")) $coin_url = mb_substr($coin_url, 0, mb_strpos($coin_url, "?"));
		if (isset($arr[$id])) unset($arr[$id]);
		$coin_url = "//".$_SERVER["HTTP_HOST"].$coin_url."?".http_build_query($arr).($arr?"&amp;":"").$id."=";
		
		// Current Language
		$lan = cryptobox_sellanguage($def_language);
		$localisation = $localisation[$lan];
		
		
		$tmp = '<div'.($anchor=="gourlcryptocoins"?" id='$anchor'":"").' style="'.trim(trim(htmlspecialchars($style, ENT_COMPAT), "; ")."; text-align:center", "; ").'"><div style="margin-bottom:15px"><b>'.$localisation["payment"].' -</b></div>';
		foreach ($coins as $v)
		{
			$v = trim(strtolower($v));
			if (!in_array($v, $available_payments)) die("Invalid your submitted value '$v' in display_currency_box()");
			if (strpos(CRYPTOBOX_PRIVATE_KEYS, ucfirst($v)."77") === false) die("Please add your Private Key for '$v' in variable \$cryptobox_private_keys, file cryptobox.config.php");
			$url = $coin_url.$v."#".$anchor;
			
			if ($jquery) $tmp .= "<input type='radio' class='aradioimage' data-title='".str_replace("%coinName%", ucfirst($v), $localisation["pay_in"])."' ".($coinName==$v?"checked":"")." data-url='$url' data-width='$iconWidth' data-alt='".str_replace("%coinName%", $v, $localisation["pay_in"])."' data-image='".$directory."/".$v.($iconWidth>70?"2":"").".png' name='aradioname' value='$v'>&#160; ".($iconWidth>70 || count($coins)<4?"&#160; ":"");
			else $tmp .= "<a href='".$url."' onclick=\"location.href='".$url."';\"><img style='box-shadow:none;margin:".round($iconWidth/10)."px ".round($iconWidth/6)."px;border:0;display:inline;' width='$iconWidth' title='".str_replace("%coinName%", ucfirst($v), $localisation["pay_in"])."' alt='".str_replace("%coinName%", $v, $localisation["pay_in"])."' src='".$directory."/".$v.($iconWidth>70?"2":"").".png'></a>";
		}
		$tmp .= "</div>";
		
		return $tmp;
	}
	

	
	

	/* G. Function get_country_name()
	 * 
	 * Get country name by country code
	 */
	function get_country_name($countryID, $reverse = false)
	{
		$arr = array("AFG"=>"Afghanistan", "ALA"=>"Aland Islands", "ALB"=>"Albania", "DZA"=>"Algeria", "ASM"=>"American Samoa", "AND"=>"Andorra", "AGO"=>"Angola", "AIA"=>"Anguilla", "ATA"=>"Antarctica", "ATG"=>"Antigua and Barbuda", "ARG"=>"Argentina", "ARM"=>"Armenia", "ABW"=>"Aruba", "AUS"=>"Australia", "AUT"=>"Austria", "AZE"=>"Azerbaijan", "BHS"=>"Bahamas", "BHR"=>"Bahrain", "BGD"=>"Bangladesh", "BRB"=>"Barbados", "BLR"=>"Belarus", "BEL"=>"Belgium", "BLZ"=>"Belize", "BEN"=>"Benin", "BMU"=>"Bermuda", "BTN"=>"Bhutan", "BOL"=>"Bolivia", "BIH"=>"Bosnia and Herzegovina", "BWA"=>"Botswana", "BVT"=>"Bouvet Island", "BRA"=>"Brazil", "IOT"=>"British Indian Ocean Territory", "BRN"=>"Brunei", "BGR"=>"Bulgaria", "BFA"=>"Burkina Faso", "BDI"=>"Burundi", "KHM"=>"Cambodia", "CMR"=>"Cameroon", "CAN"=>"Canada", "CPV"=>"Cape Verde", "BES"=>"Caribbean Netherlands", "CYM"=>"Cayman Islands", "CAF"=>"Central African Republic", "TCD"=>"Chad", "CHL"=>"Chile", "CHN"=>"China", "CXR"=>"Christmas Island", "CCK"=>"Cocos (Keeling) Islands", "COL"=>"Colombia", "COM"=>"Comoros", "COG"=>"Congo", "COD"=>"Congo, Democratic Republic", "COK"=>"Cook Islands", "CRI"=>"Costa Rica", "CIV"=>"Cote d'Ivoire", "HRV"=>"Croatia", "CUB"=>"Cuba", "CUW"=>"Curacao", "CBR"=>"Cyberbunker", "CYP"=>"Cyprus", "CZE"=>"Czech Republic", "DNK"=>"Denmark", "DJI"=>"Djibouti", "DMA"=>"Dominica", "DOM"=>"Dominican Republic", "TMP"=>"East Timor", "ECU"=>"Ecuador", "EGY"=>"Egypt", "SLV"=>"El Salvador", "GNQ"=>"Equatorial Guinea", "ERI"=>"Eritrea", "EST"=>"Estonia", "ETH"=>"Ethiopia", "EUR"=>"European Union", "FLK"=>"Falkland Islands", "FRO"=>"Faroe Islands", "FJI"=>"Fiji Islands", "FIN"=>"Finland", "FRA"=>"France", "GUF"=>"French Guiana", "PYF"=>"French Polynesia", "ATF"=>"French Southern territories", "GAB"=>"Gabon", "GMB"=>"Gambia", "GEO"=>"Georgia", "DEU"=>"Germany", "GHA"=>"Ghana", "GIB"=>"Gibraltar", "GRC"=>"Greece", "GRL"=>"Greenland", "GRD"=>"Grenada", "GLP"=>"Guadeloupe", "GUM"=>"Guam", "GTM"=>"Guatemala", "GGY"=>"Guernsey", "GIN"=>"Guinea", "GNB"=>"Guinea-Bissau", "GUY"=>"Guyana", "HTI"=>"Haiti", "HMD"=>"Heard Island and McDonald Islands", "HND"=>"Honduras", "HKG"=>"Hong Kong", "HUN"=>"Hungary", "ISL"=>"Iceland", "IND"=>"India", "IDN"=>"Indonesia", "IRN"=>"Iran", "IRQ"=>"Iraq", "IRL"=>"Ireland", "IMN"=>"Isle of Man", "ISR"=>"Israel", "ITA"=>"Italy", "JAM"=>"Jamaica", "JPN"=>"Japan", "JEY"=>"Jersey", "JOR"=>"Jordan", "KAZ"=>"Kazakstan", "KEN"=>"Kenya", "KIR"=>"Kiribati", "KWT"=>"Kuwait", "KGZ"=>"Kyrgyzstan", "LAO"=>"Laos", "LVA"=>"Latvia", "LBN"=>"Lebanon", "LSO"=>"Lesotho", "LBR"=>"Liberia", "LBY"=>"Libya", "LIE"=>"Liechtenstein", "LTU"=>"Lithuania", "LUX"=>"Luxembourg", "MAC"=>"Macao", "MKD"=>"Macedonia", "MDG"=>"Madagascar", "MWI"=>"Malawi", "MYS"=>"Malaysia", "MDV"=>"Maldives", "MLI"=>"Mali", "MLT"=>"Malta", "MHL"=>"Marshall Islands", "MTQ"=>"Martinique", "MRT"=>"Mauritania", "MUS"=>"Mauritius", "MYT"=>"Mayotte", "MEX"=>"Mexico", "FSM"=>"Micronesia, Federated States", "MDA"=>"Moldova", "MCO"=>"Monaco", "MNG"=>"Mongolia", "MNE"=>"Montenegro", "MSR"=>"Montserrat", "MAR"=>"Morocco", "MOZ"=>"Mozambique", "MMR"=>"Myanmar", "NAM"=>"Namibia", "NRU"=>"Nauru", "NPL"=>"Nepal", "NLD"=>"Netherlands", "ANT"=>"Netherlands Antilles", "NCL"=>"New Caledonia", "NZL"=>"New Zealand", "NIC"=>"Nicaragua", "NER"=>"Niger", "NGA"=>"Nigeria", "NIU"=>"Niue", "NFK"=>"Norfolk Island", "PRK"=>"North Korea", "MNP"=>"Northern Mariana Islands", "NOR"=>"Norway", "OMN"=>"Oman", "PAK"=>"Pakistan", "PLW"=>"Palau", "PSE"=>"Palestine", "PAN"=>"Panama", "PNG"=>"Papua New Guinea", "PRY"=>"Paraguay", "PER"=>"Peru", "PHL"=>"Philippines", "PCN"=>"Pitcairn", "POL"=>"Poland", "PRT"=>"Portugal", "PRI"=>"Puerto Rico", "QAT"=>"Qatar", "REU"=>"Reunion", "ROM"=>"Romania", "RUS"=>"Russia", "RWA"=>"Rwanda", "BLM"=>"Saint Barthelemy", "SHN"=>"Saint Helena", "KNA"=>"Saint Kitts and Nevis", "LCA"=>"Saint Lucia", "MAF"=>"Saint Martin", "SPM"=>"Saint Pierre and Miquelon", "VCT"=>"Saint Vincent and the Grenadines", "WSM"=>"Samoa", "SMR"=>"San Marino", "STP"=>"Sao Tome and Principe", "SAU"=>"Saudi Arabia", "SEN"=>"Senegal", "SRB"=>"Serbia", "SYC"=>"Seychelles", "SLE"=>"Sierra Leone", "SGP"=>"Singapore", "SXM"=>"Sint Maarten", "SVK"=>"Slovakia", "SVN"=>"Slovenia", "SLB"=>"Solomon Islands", "SOM"=>"Somalia", "ZAF"=>"South Africa", "SGS"=>"South Georgia and the South Sandwich Islands", "KOR"=>"South Korea", "SSD"=>"South Sudan", "ESP"=>"Spain", "LKA"=>"Sri Lanka", "SDN"=>"Sudan", "SUR"=>"Suriname", "SJM"=>"Svalbard and Jan Mayen", "SWZ"=>"Swaziland", "SWE"=>"Sweden", "CHE"=>"Switzerland", "SYR"=>"Syria", "TWN"=>"Taiwan", "TJK"=>"Tajikistan", "TZA"=>"Tanzania", "THA"=>"Thailand", "TGO"=>"Togo", "TKL"=>"Tokelau", "TON"=>"Tonga", "TTO"=>"Trinidad and Tobago", "TUN"=>"Tunisia", "TUR"=>"Turkey", "TKM"=>"Turkmenistan", "TCA"=>"Turks and Caicos Islands", "TUV"=>"Tuvalu", "UGA"=>"Uganda", "UKR"=>"Ukraine", "ARE"=>"United Arab Emirates", "GBR"=>"United Kingdom", "UMI"=>"United States Minor Outlying Islands", "URY"=>"Uruguay", "USA"=>"USA", "UZB"=>"Uzbekistan", "VUT"=>"Vanuatu", "VAT"=>"Vatican (Holy See)", "VEN"=>"Venezuela", "VNM"=>"Vietnam", "VGB"=>"Virgin Islands, British", "VIR"=>"Virgin Islands, U.S.", "WLF"=>"Wallis and Futuna", "ESH"=>"Western Sahara", "XKX"=>"Kosovo", "YEM"=>"Yemen", "ZMB"=>"Zambia", "ZWE"=>"Zimbabwe");
		
		if ($reverse) $result = array_search(ucwords(mb_strtolower($countryID)), $arr);
		elseif (isset($arr[strtoupper($countryID)])) $result = $arr[strtoupper($countryID)];
		
		if (!$result) $result = "";
		
		return $result;
	}
	
	
	
	
	
	/* H. Function convert_currency_live()
	 *
	 * Currency Converter using live exchange rates websites
	 * Example - convert_currency_live("EUR", "USD", 22.37) - convert 22.37euro to usd
	             convert_currency_live("EUR", "BTC", 22.37) - convert 22.37euro to bitcoin
	   optional - currencyconverterapi_key you can get on https://free.currencyconverterapi.com/free-api-key
	 */
	function convert_currency_live($from_Currency, $to_Currency, $amount, $currencyconverterapi_key = "")
	{
	    static $arr = array();
	    
	    $from_Currency = trim(strtoupper(urlencode($from_Currency)));
	    $to_Currency   = trim(strtoupper(urlencode($to_Currency)));
	    
	    if ($from_Currency == "TRL") $from_Currency = "TRY"; // fix for Turkish Lyra
	    if ($from_Currency == "ZWD") $from_Currency = "ZWL"; // fix for Zimbabwe Dollar
	    if ($from_Currency == "RM")  $from_Currency = "MYR"; // fix for Malaysian Ringgit
	    if ($from_Currency == "XBT") $from_Currency = "BTC"; // fix for Bitcoin
	    if ($to_Currency   == "XBT") $to_Currency   = "BTC"; // fix for Bitcoin
	    
	    if ($from_Currency == "RIAL") $from_Currency = "IRR"; // fix for Iranian Rial
	    if ($from_Currency == "IRT") { $from_Currency = "IRR"; $amount = $amount * 10; } // fix for Iranian Toman; 1IRT = 10IRR
	    
	    $key  = $from_Currency."_".$to_Currency;
	    
	    
	    
	    // a. restore saved exchange rate
	    // ----------------
	    if (!isset($arr[$key]) && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["exch_".$key]) && is_numeric($_SESSION["exch_".$key]) && $_SESSION["exch_".$key] > 0) $arr[$key] = $_SESSION["exch_".$key];
	    
	    if (isset($arr[$key]))
	    {
	        if ($arr[$key] > 0)
	        {
	            $val = $arr[$key];
	            $total = $val*$amount;
	            if ($to_Currency=="BTC" || $total<0.01) $total = sprintf('%.5f', round($total, 5));
	            else $total = round($total, 2);
	            if ($total == 0) $total = sprintf('%.5f', 0.00001);
	            return $total;
	        }
	        else return -1;
	    }
	    
	    
	    $val = 0;
	    if ($from_Currency == $to_Currency)  $val = 1;
	    
	    
	    
	    // b. get BTC rates
	    // ----------------
	    $bitcoinUSD = 0;
	    if (!$val && ($from_Currency == "BTC" || $to_Currency == "BTC"))
	    {
	        $aval = array ('BTC', 'USD', 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'DKK', 'EUR', 'GBP', 'HKD', 'INR', 'ISK', 'JPY', 'KRW', 'NZD', 'PLN', 'RUB', 'SEK', 'SGD', 'THB', 'TWD');
	        if (in_array($from_Currency, $aval) && in_array($to_Currency, $aval))
	        {
	            $data = json_decode(get_url_contents("https://blockchain.info/ticker"), true);
	            
	            // rates BTC->...
	            $rates = array("BTC" => 1);
	            if ($data) foreach($data as $k => $v) $rates[$k] = ($v["15m"] > 1000) ? round($v["15m"]) : ($v["last"] > 1000 ? round($v["last"]) : 0);
	            // convert BTC/USD, EUR/BTC, etc.
	            if (isset($rates[$to_Currency]) && $rates[$to_Currency] > 0 && isset($rates[$from_Currency]) && $rates[$from_Currency] > 0) $val = $rates[$to_Currency] / $rates[$from_Currency];
	            if (isset($rates["USD"]) && $rates["USD"] > 0) $bitcoinUSD = $rates["USD"];
	        }
	        
	        if (!$val && $bitcoinUSD < 1000)
	        {
	            $data = json_decode(get_url_contents("https://www.bitstamp.net/api/ticker/"), true);
	            if (isset($data["last"]) && isset($data["volume"]) && $data["last"] > 1000) $bitcoinUSD = round($data["last"]);
	        }
	        
	        if ($from_Currency == "BTC" && $to_Currency == "USD" && $bitcoinUSD > 0) $val  =  $bitcoinUSD;
	        if ($from_Currency == "USD" && $to_Currency == "BTC" && $bitcoinUSD > 0) $val  =  1 / $bitcoinUSD;
	    }
	    
	    
	    
	    // c. get rates from European Central Bank https://www.ecb.europa.eu
	    // ----------------
	    $aval = array ('EUR', 'USD', 'JPY', 'BGN', 'CZK', 'DKK', 'GBP', 'HUF', 'PLN', 'RON', 'SEK', 'CHF', 'ISK', 'NOK', 'HRK', 'RUB', 'TRY', 'AUD', 'BRL', 'CAD', 'CNY', 'HKD', 'IDR', 'ILS', 'INR', 'KRW', 'MXN', 'MYR', 'NZD', 'PHP', 'SGD', 'THB', 'ZAR');
	    if ($bitcoinUSD > 0) $aval[] = "BTC";
	    if (!$val && in_array($from_Currency, $aval) && in_array($to_Currency, $aval))
	    {
	        $xml = simplexml_load_string(get_url_contents("https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml"));
	        $json = json_encode($xml);
	        $data = json_decode($json,TRUE);
	        
	        if (isset($data["Cube"]["Cube"]))
	        {
	            $data = $data["Cube"]["Cube"];
	            $time = $data["@attributes"]["time"];
	            
	            // rates EUR->...
	            $rates = array("EUR" => 1);
	            foreach($data["Cube"] as $v) $rates[$v["@attributes"]["currency"]] = floatval($v["@attributes"]["rate"]);
	            if ($bitcoinUSD > 0 && $rates["USD"] > 0) $rates["BTC"] = $rates["USD"] / $bitcoinUSD;
	            
	            // convert USD/JPY, EUR/GBP, etc.
	            if ($rates[$to_Currency] > 0 && $rates[$from_Currency] > 0) $val = $rates[$to_Currency] / $rates[$from_Currency];
	        }
	    }
	    
	    
	    // d. get rates from https://free.currconv.com/api/v7/convert?q=BTC_EUR&compact=y&apiKey=your_free_currencyconverterapi_key
	    // free api key here - https://free.currencyconverterapi.com/free-api-key
	    // ----------------
	    if (!$val)
	    {
	        $data = json_decode(get_url_contents("https://free.currconv.com/api/v7/convert?q=".$key."&compact=ultra&apiKey=".$currencyconverterapi_key, 20, TRUE), TRUE);
	        if (isset($data[$key]) && $data[$key] > 0) $val = $data[$key];
	        elseif(isset($data["error"])) echo "<h1>Error in function convert_currency_live(...)! ". $data["error"] . "</h1>";
	    }
	    
	    
	    // e. result
	    // ------------
	    if ($val > 0)
	    {
	        if (session_status() === PHP_SESSION_ACTIVE) $_SESSION["exch_".$key] = $val;
	        
	        $arr[$key] = $val;
	        $total = $val*$amount;
	        if ($to_Currency=="BTC" || $total<0.01) $total = sprintf('%.5f', round($total, 5));
	        else $total = round($total, 2);
	        if ($total == 0) $total = sprintf('%.5f', 0.00001);
	        return $total;
	    }
	    else
	    {
	        $arr[$key] = -1;
	        return -1;
	    }
	}

	
	
	/*	I. Get URL Data
	*/
	function get_url_contents( $url, $timeout = 20, $ignore_httpcode = false )
	{
	    $ch = curl_init();
	    curl_setopt ($ch, CURLOPT_URL, $url);
	    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt ($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
	    curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko");
	    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	    curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
	    $data 		= curl_exec($ch);
	    $httpcode 	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close($ch);
	
	    return (($httpcode>=200 && $httpcode<300) || $ignore_httpcode) ? $data : false;
	}
	
	
	
	
	/* J. Function validate_gourlkey()
	 *
	* Validate gourl private/public/affiliate keys
	* $key 	 	- gourl payment box key
	* $type 	- public, private, affiliate
	* @return 	- true or false 
	*/
	function validate_gourlkey ( $key, $type )
	{
		if (!$key || !in_array($type, array('public', 'private', 'affiliate'))) return false;
		
		$valid = false;
		if ($type == 'public' && strpos($key, 'AA') && strlen($key) == 50)
		{
			$boxID = substr($key, 0, strpos($key, 'AA'));
			if (preg_replace('/[^A-Za-z0-9]/', '', $key) == $key &&
				$boxID && is_numeric($boxID) &&
				strpos($key, "77") !== false &&
				strpos($key, "PUB")) $valid = true;
		}
		elseif ($type == 'private' && strpos($key, 'AA') && strlen($key) == 50)
		{
			$boxID = substr($key, 0, strpos($key, 'AA'));
			if (preg_replace('/[^A-Za-z0-9]/', '', $key) == $key &&
				$boxID && is_numeric($boxID) &&
				strpos($key, "77") !== false &&
				strpos($key, "PRV")) $valid = true;
		}
		elseif ($type == 'affiliate')
		{
			if (preg_replace('/[^A-Z0-9]/', '', $key) == $key &&
				strpos($key, "DEV") === 0 &&
				strpos($key, "G") &&
				is_numeric(substr($key, -2))) $valid = true;
		}
		
		return $valid;
	}
	
	
	
	
	
	/* K. Function run_sql()
	 *
	 * Run SQL queries and return result in array/object formats
	 */
	function run_sql($sql)
	{
		static $mysqli;
	
		$f = true;
		$g = $x = false;
		$res = array();
	
		if (!$mysqli)
		{
			$dbhost = DB_HOST;
			$port = NULL; $socket = NULL; 
			if (strpos(DB_HOST, ":"))
			{ 
				list($dbhost, $port) = explode(':', DB_HOST);
				if (is_numeric($port)) $port = (int) $port;
				else
				{
					$socket = $port;
					$port = NULL;
				}
			}
			$mysqli = @mysqli_connect($dbhost, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket);			
			$err = (mysqli_connect_errno()) ? mysqli_connect_error() : "";
			if ($err)
			{
				// try SSL connection
				$mysqli = mysqli_init();
				$mysqli->real_connect ($dbhost, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket, MYSQLI_CLIENT_SSL);
			}
			if (mysqli_connect_errno())
			{
				echo "<br /><b>Error. Can't connect to your MySQL server.</b> You need to have PHP 5.2+ and MySQL 5.5+ with mysqli extension activated. <a href='http://crybit.com/how-to-enable-mysqli-extension-on-web-server/'>Instruction &#187;</a>\n";
				if (!CRYPTOBOX_WORDPRESS) echo "<br />Also <b>please check DB username/password in file cryptobox.config.php</b>\n";
				die("<br />Server has returned error - <b>".$err."</b>");
			}
			$mysqli->query("SET NAMES utf8");
		}

		$query = $mysqli->query($sql);

		if ($query === FALSE)
        {
            if (!CRYPTOBOX_WORDPRESS && stripos(str_replace('"', '', str_replace("'", "", $mysqli->error)), "crypto_payments doesnt exist"))
            {
                // Try to create new table - https://github.com/cryptoapi/Payment-Gateway#mysql-table
                $mysqli->query("CREATE TABLE `crypto_payments` (
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
                              `txDate` datetime DEFAULT NULL,
                              `txConfirmed` tinyint(1) unsigned NOT NULL DEFAULT '0',
                              `txCheckDate` datetime DEFAULT NULL,
                              `processed` tinyint(1) unsigned NOT NULL DEFAULT '0',
                              `processedDate` datetime DEFAULT NULL,
                              `recordCreated` datetime DEFAULT NULL,
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
                              UNIQUE KEY `key3` (`boxID`, `orderID`, `userID`, `txID`, `amount`, `addr`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");

                $query = $mysqli->query($sql);  // re-run previous query
            }
            if ($query === FALSE) die("MySQL Error: ".$mysqli->error."; SQL: $sql");
        }

		if (is_object($query) && $query->num_rows)
		{
			while($row = $query->fetch_object())
			{
				if ($f)
				{
					if (property_exists($row, "idx")) $x = true;
					$c = count(get_object_vars($row));
					if ($c > 2 || ($c == 2 && !$x)) $g = true;
					elseif (!property_exists($row, "nme")) die("Error in run_sql() - 'nme' not exists! SQL: $sql");
					$f = false;
				}
	
				if (!$g && $query->num_rows == 1 && property_exists($row, "nme")) return $row->nme;
				elseif ($x) $res[$row->idx] = ($g) ? $row : $row->nme;
				else $res[] = ($g) ? $row : $row->nme;
			}
		}
		elseif (stripos($sql, "insert ") !== false) $res = $mysqli->insert_id;

		if (is_object($query)) $query->close();
		if (is_array($res) && count($res) == 1 && isset($res[0]) && is_object($res[0])) $res = $res[0];

		return $res;
	}
	
	
	// en - English, es - Spanish, fr - French, de - German, nl - Dutch, it - Italian, ru - Russian, pl - Polish, pt - Portuguese, fa - Persian, ko - Korean, ja - Japanese, id - Indonesian, tr - Turkish, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi
	// fi - Finnish, sv - Swedish, el - Greek, 	cs - Czech, sl - Slovenian, sr - Serbian, et - Estonian, sq - Albanian
	
	
	$cryptobox_localisation	= array(
							"en" => array("name"		=> "English", 
							/*36*/	"button"			=> "Click Here if you have already sent %coinNames%",
							/*37*/	"msg_not_received" 	=> "<b>%coinNames% have not yet been received.</b><br>If you have already sent %coinNames% (the exact %coinName% sum in one payment as shown in the box below), please wait a few minutes to receive them by %coinName% Payment System. If you send any other sum, Payment System will ignore the transaction and you will need to send the correct sum again, or contact the site owner for assistance.",
							/*38*/	"msg_received" 	 	=> "%coinName% Payment System received %amountPaid% %coinLabel% successfully !",
							/*39*/	"msg_received2" 	=> "%coinName% Captcha received %amountPaid% %coinLabel% successfully !",
							/*40*/	"payment"			=> "Select Payment Method",
							/*42*/	"pay_in"			=> "Payment in %coinName%",
							/*55*/	"loading"			=> "Loading ..."),
	    
							"es" => array("name"		=> "Spanish", 
									"button"			=> "Click aqui si ya has mandado %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% no han sido recibidos.</b><br>Si ya has enviado %coinNames% (la cantidad exacta de %coinName% en un s&oacute;lo pago como se muestra abajo), por favor espera unos minutos para recibirlas del %coinName% sistema de pagos. Si has enviado otra cantidad, el sistema de pagos ignorar&aacute; la transacci&oacute;n y necesitar&aacute;s mandar la cantidad correcta de nuevo, o contactar al propietario del sitio para recibir asistencia.",
									"msg_received" 	 	=> "%coinName% Sistema de pago recibido %amountPaid% %coinLabel% satisfactoriamente !",
									"msg_received2" 	=> "%coinName% Captcha recibido %amountPaid% %coinLabel% satisfactoriamente !",
									"payment"			=> "Seleccionar m&eacute;todo de pago",
									"pay_in"			=> "Pago en %coinName%",
									"loading"			=> "Cargando ..."),
							    	
							"fr" => array("name"		=> "French",
									"button"			=> "Cliquez ici si vous avez d&eacute;j&agrave; envoy&eacute; vos %coinNames%",
									"msg_not_received" 	=> "<b>Les %coinNames% n'ont pas encore &eacute;t&eacute; re&ccedil;us.</b><br>Si vous avez d&eacute;j&agrave; envoy&eacute; les %coinNames% (la somme exacte en un seul paiement, comme indiqu&eacute; dans le cadre ci-dessous), Veuillez s'il vous pla&icirc;t attendre quelques minutes le temps que le syst&egrave;me re&ccedil;oive votre paiement en %coinName%. Si vous envoyez toute autre somme, le syst&egrave;me de paiement n'identifiera pas la transaction et vous devrez &agrave; nouveau envoyer la somme correcte, ou contacter le propri&eacute;taire du site via l'assistance.",
									"msg_received" 	 	=> "Le syst&egrave;me de paiement %coinName% a re&ccedil;u %amountPaid% %coinLabel% avec succ&egrave;s !",
									"msg_received2" 	=> "Le %coinName% Captcha a re&ccedil;u %amountPaid% %coinLabel% avec succ&egrave;s !",
									"payment"			=> "S&eacute;lectionnez la m&eacute;thode de paiement",
									"pay_in"			=> "Paiement en %coinName%",
									"loading"			=> "Chargement ..."),
	     
							"de" => array("name"		=> "German", 
									"button"			=> "Klicke hier wenn du schon %coinNames% gesendet hast",
									"msg_not_received" 	=> "<b>%coinNames% wurden bis jetzt noch nicht empfangen.</b><br>Wenn du bereits %coinNames% gesendet hast (der exakte %coinName% Betrag f&uuml;r die Zahlung steht in der Box unten) warte bitte ein paar Minuten bis das %coinName% System die Zahlung erhalten hat. Wenn du einen anderen Betrag sendest ignoriert das System die Transaktion und du musst den korrekten Betrag erneut senden, oder den Besitzer der Website kontaktieren um Hilfe zu erhalten.",
									"msg_received" 	 	=> "%coinName% Bezahlsystem hat %amountPaid% %coinLabel% erfolgreich erhalten !",
									"msg_received2" 	=> "%coinName% Captcha hat %amountPaid% %coinLabel% erfolgreich erhalten !",
									"payment"			=> "Zahlungmethode ausw&auml;hlen",
									"pay_in"			=> "Zahlung in %coinName%",
									"loading"			=> "Wird geladen ..."),
	    
							"it" => array("name"		=> "Italian",
									"button"			=> "Clicca qui se hai gi&#224; inviato i %coinNames%",
									"msg_not_received"  => "<b>%coinNames% non sono ancora stati ricevuti.</b><br>Se hai gi&#224; inviato i %coinNames% (l&#8217;esatta somma di %coinName% in un unico pagamento, come mostrato nel riquadro sottostante), si prega di attendere qualche minuto perch&#233; il sistema di pagamaneto di riceva. Se si invia qualsiasi altra somma, il sistema di pagamento ignorer&#224; la transazione e sar&#224; necessario inviare di nuovo la somma corretta, oppure contattare il supporto del sito.",
									"msg_received"      => "Il sistema di pagamento %coinName% ha ricevuto %amountPaid% %coinLabel% con successo !",
									"msg_received2"     => "Il %coinName% Captcha ha ricevuto %amountPaid% %coinLabel% con successo !",
									"payment"           => "Seleziona metodo di pagamento",
									"pay_in"            => "Pagamento in %coinName%",
									"loading"			=> "Caricamento in corso ..."),
	    
							"nl" => array("name"		=> "Dutch",
									"button"			=> "Klik hier als je al %coinNames% hebt verstuurd",
									"msg_not_received" 	=> "<b>%coinNames% zijn nog niet ontvangen.</b><br>Als je al %coinNames% verstuurd hebt, (het exacte bedrag in %coinName% staat in het vak hieronder), wacht dan a.u.b. een paar minuten tot ze ontvangen zijn door het %coinName% Betaal Systeem. Als u een ander bedrag verstuurd, zal de transactie worden genegeerd, u zult dan alsnog het correcte bedrag moeten overmaken of contact opnemen met de site beheerder voor verdere assistentie.",
									"msg_received"      => "%coinName% Betaal Systeem heeft %amountPaid% %coinLabel% succesvol ontvangen !",
									"msg_received2"     => "%coinName% Captcha Systeem heeft %amountPaid% %coinLabel% succesvol ontvangen !",
									"payment"           => "Kies uw betaalmethode",
									"pay_in"            => "Betaling in %coinName%",
									"loading"			=> "Bezig met laden ..."),
	     
							"ru" => array("name"		=> "Russian",
									"button"			=> "&#1053;&#1072;&#1078;&#1084;&#1080;&#1090;&#1077; &#1079;&#1076;&#1077;&#1089;&#1100; &#1077;&#1089;&#1083;&#1080; &#1074;&#1099; &#1091;&#1078;&#1077; &#1087;&#1086;&#1089;&#1083;&#1072;&#1083;&#1080; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#1085;&#1077; &#1087;&#1086;&#1083;&#1091;&#1095;&#1077;&#1085;&#1099; &#1077;&#1097;&#1105;.</b><br>&#1045;&#1089;&#1083;&#1080; &#1074;&#1099; &#1091;&#1078;&#1077; &#1087;&#1086;&#1089;&#1083;&#1072;&#1083;&#1080; %coinNames% (&#1090;&#1086;&#1095;&#1085;&#1091;&#1102; &#1089;&#1091;&#1084;&#1084;&#1091; %coinName% &#1086;&#1076;&#1085;&#1080;&#1084; &#1087;&#1083;&#1072;&#1090;&#1077;&#1078;&#1105;&#1084; &#1082;&#1072;&#1082; &#1087;&#1086;&#1082;&#1072;&#1079;&#1072;&#1085;&#1086; &#1085;&#1080;&#1078;&#1077;), &#1087;&#1086;&#1078;&#1072;&#1083;&#1091;&#1081;&#1089;&#1090;&#1072; &#1087;&#1086;&#1076;&#1086;&#1078;&#1076;&#1080;&#1090;&#1077; &#1085;&#1077;&#1089;&#1082;&#1086;&#1083;&#1100;&#1082;&#1086; &#1084;&#1080;&#1085;&#1091;&#1090; &#1076;&#1083;&#1103; &#1087;&#1086;&#1083;&#1091;&#1095;&#1077;&#1085;&#1080;&#1103; &#1080;&#1093; %coinName% &#1087;&#1083;&#1072;&#1090;&#1105;&#1078;&#1085;&#1086;&#1081; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1086;&#1081;. &#1045;&#1089;&#1083;&#1080; &#1074;&#1099; &#1087;&#1086;&#1089;&#1083;&#1072;&#1083;&#1080; &#1083;&#1102;&#1073;&#1091;&#1102; &#1076;&#1088;&#1091;&#1075;&#1091;&#1102; &#1089;&#1091;&#1084;&#1084;&#1091;, &#1087;&#1083;&#1072;&#1090;&#1105;&#1078;&#1085;&#1072;&#1103; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1072; &#1073;&#1091;&#1076;&#1077;&#1090; &#1080;&#1075;&#1085;&#1086;&#1088;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100; &#1101;&#1090;&#1086; &#1080; &#1074;&#1072;&#1084; &#1085;&#1091;&#1078;&#1085;&#1086; &#1073;&#1091;&#1076;&#1077;&#1090; &#1087;&#1086;&#1089;&#1083;&#1072;&#1090;&#1100; &#1087;&#1088;&#1072;&#1074;&#1080;&#1083;&#1100;&#1085;&#1091;&#1102; &#1089;&#1091;&#1084;&#1084;&#1091; &#1086;&#1087;&#1103;&#1090;&#1100;, &#1080;&#1083;&#1080; &#1089;&#1074;&#1103;&#1078;&#1080;&#1090;&#1077;&#1089;&#1100; &#1089; &#1074;&#1083;&#1072;&#1076;&#1077;&#1083;&#1100;&#1094;&#1077;&#1084; &#1089;&#1072;&#1081;&#1090;&#1072; &#1076;&#1083;&#1103; &#1087;&#1086;&#1084;&#1086;&#1097;&#1080;",
									"msg_received" 	 	=> "%coinName% &#1087;&#1083;&#1072;&#1090;&#1105;&#1078;&#1085;&#1072;&#1103; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1072; &#1087;&#1086;&#1083;&#1091;&#1095;&#1080;&#1083;&#1072; %amountPaid% %coinLabel% &#1091;&#1089;&#1087;&#1077;&#1096;&#1085;&#1086; !",
									"msg_received2" 	=> "%coinName% &#1082;&#1072;&#1087;&#1095;&#1072; &#1087;&#1086;&#1083;&#1091;&#1095;&#1080;&#1083;&#1072; %amountPaid% %coinLabel% &#1091;&#1089;&#1087;&#1077;&#1096;&#1085;&#1086; !",
									"payment"			=> "&#1042;&#1099;&#1073;&#1077;&#1088;&#1080;&#1090;&#1077; &#1089;&#1087;&#1086;&#1089;&#1086;&#1073; &#1086;&#1087;&#1083;&#1072;&#1090;&#1099;",
									"pay_in"			=> "&#1054;&#1087;&#1083;&#1072;&#1090;&#1072; &#1074; %coinName%",
									"loading"			=> "&#1047;&#1072;&#1075;&#1088;&#1091;&#1078;&#1072;&#1077;&#1090;&#1089;&#1103; ..."),
	    
							"sv" => array("name"		=> "Swedish",
									"button"			=> "Klicka h&auml;r om du redan har skickat %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% har inte tagits emot &auml;n.</b><br>Om du redan har skickat Bticoins (den exakta summan %coinName% i en betalning som visat i rutan under), var v&auml;nlig v&auml;nta n&aring;gra minuter p&aring; att de ska tas emot av %coinName% Betalnings Systemet. Om du har skickat n&aring;gon annan summa kommer din betalning bli ignorerad, och du beh&ouml;ver skicka den korrekta summan igen, eller kontakta webbplats &auml;garen f&ouml;r assistans.",
									"msg_received" 	 	=> "%coinName% Betalnings Systemet har tagit emot %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% Captcha har tagit emot %amountPaid% %coinLabel% !",
									"payment"			=> "V&auml;lj Betalnings Method",
									"pay_in"			=> "Betalning i %coinName%",
									"loading"			=> "L&auml;ser in ..."),
	    
							"pl" => array("name"		=> "Polish", 
									"button"			=> "Kliknij tutaj, je&#347;li ju&#380; wys&#322;ane %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% nie zosta&#322;y jeszcze otrzymane.</b><br>Je&#347;li ju&#380; wys&#322;a&#322;e&#347; %coinNames% (dok&#322;adn&#261; sum&#281; %coinName% w jednej p&#322;atno&#347;ci, jak pokazano w poni&#380;szym polu), prosz&#281; poczeka&#263; kilka minut, aby system p&#322;atno&#347;ci %coinName% m&#243;g&#322; j&#261; otrzyma&#263;. Je&#347;li wy&#347;lesz jak&#261;kolwiek inn&#261; sum&#281;, system p&#322;atno&#347;ci zignoruje transakcje i trzeba b&#281;dzie wys&#322;a&#263; poprawn&#261; sum&#281; ponownie lub skontaktowa&#263; si&#281; z w&#322;a&#347;cicielem witryny w celu uzyskania pomocy.",
									"msg_received" 	 	=> "System p&#322;atno&#347;ci %coinName% otrzyma&#322; %amountPaid% %coinLabel% pomy&#347;lnie !",
									"msg_received2" 	=> "%coinName% Captcha otrzyma&#322; %amountPaid% %coinLabel% pomy&#347;lnie !",
									"payment"			=> "Wybierz metod&#281; p&#322;atno&#347;&#263;i",
									"pay_in"			=> "P&#322;atno&#347;&#263; w %coinName%",
									"loading"			=> "&#321;aduj&#281; ..."),
	     
							"pt" => array("name"		=> "Portuguese",
									"button"			=> "Se ja enviou %coinNames% clique aqui",
									"msg_not_received" 	=> "<b>Os %coinNames% ainda n&#227;o foram recebidos.</b><br>Se j&#225; enviou %coinNames% (a soma exata de %coinName% num s&#243; pagamento, como mostrado na caixa abaixo), por favor, espere alguns minutos para o sistema de pagamentos %coinName% os receber. Se enviar qualquer outro montante, o sistema de pagamentos ir&#225; ignorar a transa&#231;&#227;o e ter&#225; que enviar a soma correta novamente; ou entre em contato com o propriet&#225;rio do site para assist&#234;ncia.",
									"msg_received" 	 	=> "O sistema de pagamentos %coinName% recebeu %amountPaid% %coinLabel% com sucesso !",
									"msg_received2" 	=> "%coinName% Captcha recebeu %amountPaid% %coinLabel% com sucesso !",
									"payment"			=> "Selecione o metodo de pagamento",
									"pay_in"			=> "Pagamento em %coinName%",
									"loading"			=> "Carregando ..."),
	     
							"fa" => array("name"		=> "Persian",
									"button"			=> "&#1575;&#1711;&#1585; &#1588;&#1605;&#1575; &#1575;&#1586; &#1602;&#1576;&#1604; &#1575;&#1585;&#1587;&#1575;&#1604; %coinName% &#1575;&#1610;&#1606;&#1580;&#1575; &#1585;&#1575; &#1705;&#1604;&#1610;&#1705; &#1705;&#1606;&#1610;&#1583;",
									"msg_not_received" 	=> "<b>%coinNames% &#1607;&#1606;&#1608;&#1586; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1606;&#1588;&#1583;&#1607; &#1575;&#1587;&#1578; </b><br> &#1575;&#1711;&#1585; &#1588;&#1605;&#1575; &#1602;&#1576;&#1604;&#1575; &#1575;&#1585;&#1587;&#1575;&#1604; &#1705;&#1585;&#1583;&#1610;&#1583; %coinNames% ,&#1576;&#1607; &#1589;&#1608;&#1585;&#1578; &#1583;&#1602;&#1610;&#1602; %coinName% &#1605;&#1580;&#1605;&#1608;&#1593; &#1583;&#1585; &#1610;&#1705; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1607;&#1605;&#1575;&#1606;&#1711;&#1608;&#1606;&#1607; &#1705;&#1607; &#1583;&#1585; &#1705;&#1575;&#1583;&#1585; &#1586;&#1610;&#1585; &#1606;&#1588;&#1575;&#1606; &#1583;&#1575;&#1583;&#1607; &#1588;&#1583;&#1607; &#1575;&#1587;&#1578; , &#1604;&#1591;&#1601;&#1575; &#1670;&#1606;&#1583; &#1583;&#1602;&#1610;&#1602;&#1607; &#1576;&#1585;&#1575;&#1610; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1575;&#1586; &#1591;&#1585;&#1601; %coinName% &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1587;&#1610;&#1587;&#1578;&#1605; &#1589;&#1576;&#1585; &#1705;&#1606;&#1610;&#1583;. &#1575;&#1711;&#1585; &#1588;&#1605;&#1575; &#1607;&#1585; &#1711;&#1608;&#1606;&#1607; &#1605;&#1580;&#1605;&#1608;&#1593; &#1583;&#1610;&#1711;&#1585;&#1610; &#1575;&#1586; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1585;&#1575; &#1601;&#1585;&#1587;&#1578;&#1575;&#1583;&#1607; &#1575;&#1610;&#1583;, &#1587;&#1610;&#1587;&#1578;&#1605; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1605;&#1593;&#1575;&#1605;&#1604;&#1607; &#1585;&#1575; &#1606;&#1575;&#1583;&#1610;&#1583;&#1607; &#1605;&#1610; &#1711;&#1610;&#1585;&#1583; &#1608; &#1588;&#1605;&#1575; &#1606;&#1610;&#1575;&#1586; &#1576;&#1607; &#1575;&#1585;&#1587;&#1575;&#1604; &#1605;&#1580;&#1605;&#1608;&#1593; &#1583;&#1585;&#1587;&#1578;&#1610; &#1705;&#1607; &#1584;&#1705;&#1585; &#1588;&#1583; &#1583;&#1575;&#1585;&#1610;&#1583;, &#1610;&#1575; &#1576;&#1575; &#1583;&#1575;&#1585;&#1606;&#1583;&#1607; &#1587;&#1575;&#1610;&#1578; &#1576;&#1585;&#1575;&#1610; &#1705;&#1605;&#1705; &#1608; &#1578;&#1608;&#1590;&#1610;&#1581;&#1575;&#1578; &#1576;&#1610;&#1588;&#1578;&#1585; &#1578;&#1605;&#1575;&#1587; &#1576;&#1711;&#1610;&#1585;&#1610;&#1583;.",
									"msg_received" 	 	=> "%coinName% &#1587;&#1610;&#1587;&#1578;&#1605; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; %amountPaid% %coinLabel% &#1585;&#1575; &#1576;&#1575; &#1605;&#1608;&#1601;&#1602;&#1610;&#1578; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1705;&#1585;&#1583; !",
									"msg_received2" 	=> "%coinName% &#1705;&#1662;&#1670;&#1575; %amountPaid% %coinLabel% &#1585;&#1575; &#1576;&#1575; &#1605;&#1608;&#1601;&#1602;&#1610;&#1578; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1705;&#1585;&#1583; !",
									"payment"			=> "&#1585;&#1608;&#1588; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1585;&#1575; &#1575;&#1606;&#1578;&#1582;&#1575;&#1576; &#1705;&#1606;&#1610;&#1583;",
									"pay_in"			=> "&#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1583;&#1585; %coinName%",
									"loading"			=> "&#1576;&#1575;&#1585;&#1711;&#1584;&#1575;&#1585;&#1740; ..."),
	     
							"ko" => array("name"		=> "Korean",
									"button"			=> "&#47564;&#50557; %coinName% &#51060;&#48120; &#48372;&#45256;&#45796;&#47732; &#50668;&#44592;&#47484; &#53364;&#47533;&#54616;&#49464;&#50836;",
									"msg_not_received" 	=> "<b>%coinNames% &#50500;&#51649; &#48155;&#51648; &#47803;&#54664;&#49845;&#45768;&#45796;.</b><br>&#47564;&#50557; &#45817;&#49888;&#51060; &#51060;&#48120; %coinNames% &#51012; &#48372;&#45256;&#45796;&#47732; (&#50500;&#47000; &#48149;&#49828;&#50504;&#50640; &#48372;&#50668;&#51648;&#45716; &#54616;&#45208;&#51032; &#44208;&#51228; &#45236;&#50640; &#50668;&#48516;&#51032; %coinName% &#51032; &#54633;&#44228;), &#44208;&#51228; &#49884;&#49828;&#53596;&#51060; &#51652;&#54665;&#46104;&#45716; &#46041;&#50504; &#51104;&#49884;&#47564; &#44592;&#45796;&#47140;&#51452;&#49464;&#50836;. &#47564;&#50557; &#45817;&#49888;&#51060; &#54633;&#44228;&#50640; &#48372;&#50668;&#51648;&#45716; &#44163;&#44284; &#45796;&#47480; &#49688;&#47049;&#51032; &#48708;&#53944;&#53076;&#51064;&#51012; &#48372;&#45256;&#45796;&#47732;, &#44208;&#51228; &#49884;&#49828;&#53596;&#51008; &#54644;&#45817; &#44144;&#47000;&#47484; &#47924;&#49884;&#54616;&#47728;, &#45817;&#49888;&#51008; &#45796;&#49884; &#50732;&#48148;&#47480; &#54633;&#44228;&#47564;&#53372;&#51032; &#48708;&#53944;&#53076;&#51064;&#51012; &#48372;&#45236;&#44144;&#45208; &#46020;&#50880;&#51012; &#51460; &#49688; &#51080;&#45716; &#49324;&#51060;&#53944; &#44288;&#47532;&#51088;&#50640;&#44172; &#50672;&#46973;&#54644;&#50556; &#54633;&#45768;&#45796;.",
									"msg_received" 	 	=> "%coinName% &#44208;&#51228; &#49884;&#49828;&#53596;&#51060; %amountPaid% %coinLabel% &#47484; &#49457;&#44277;&#51201;&#51004;&#47196; &#48155;&#50520;&#49845;&#45768;&#45796; !",
									"msg_received2" 	=> "%coinName% &#52897;&#52320;&#44032; %amountPaid% %coinLabel% &#47484; &#49457;&#44277;&#51201;&#51004;&#47196; &#48155;&#50520;&#49845;&#45768;&#45796; !",
									"payment"			=> "&#44208;&#51228; &#48169;&#48277; &#49440;&#53469;",
									"pay_in"			=> "%coinName% &#51648;&#44553;",
									"loading"			=> "&#47196;&#46300; &#51473; ..."),
	     
							"ja" => array("name"		=> "Japanese", 
									"button"			=> "%coinNames%&#12434;&#36865;&#20449;&#28168;&#12398;&#22580;&#21512;&#12399;&#12289;&#12371;&#12385;&#12425;&#12434;&#12463;&#12522;&#12483;&#12463;&#12375;&#12390;&#12367;&#12384;&#12373;&#12356;",
									"msg_not_received" 	=> "<b>%coinNames%&#12398;&#21463;&#21462;&#12399;&#23436;&#20102;&#12375;&#12390;&#12356;&#12414;&#12379;&#12435;&#12290;</b><br>%coinNames%&#65288;&#19979;&#35352;&#12395;&#34920;&#31034;&#12373;&#12428;&#12390;&#12356;&#12427;&#12385;&#12423;&#12358;&#12393;&#12398;%coinNames%&#12434;1&#22238;&#12398;&#12488;&#12521;&#12531;&#12470;&#12463;&#12471;&#12519;&#12531;&#12392;&#12375;&#12390;&#65289;&#12434;&#12377;&#12391;&#12395;&#36865;&#12387;&#12383;&#22580;&#21512;&#12399;&#12289;%coinName%&#27770;&#28168;&#12471;&#12473;&#12486;&#12512;&#12363;&#12425;&#25968;&#20998;&#20197;&#20869;&#12395;&#30906;&#35469;&#12364;&#12354;&#12426;&#12414;&#12377;&#12290;&#25351;&#23450;&#20197;&#19978;&#12398;%coinNames%&#12434;&#36865;&#12387;&#12383;&#22580;&#21512;&#12399;&#12289;&#12471;&#12473;&#12486;&#12512;&#12395;&#21453;&#26144;&#12373;&#12428;&#12414;&#12379;&#12435;&#12398;&#12391;&#12289;&#12418;&#12358;&#19968;&#24230;&#12420;&#12426;&#30452;&#12377;&#12363;&#12289;&#12454;&#12455;&#12502;&#12469;&#12452;&#12488;&#31649;&#29702;&#32773;&#12408;&#12362;&#21839;&#21512;&#12379;&#12367;&#12384;&#12373;&#12356;&#12290;&#19975;&#12364;&#19968;&#12289;&#36865;&#20449;&#28168;&#12415;&#12398;&#22580;&#21512;&#12399;&#12289;%coinName%&#27770;&#28168;&#12471;&#12473;&#12486;&#12512;&#12363;&#12425;&#12398;&#30906;&#35469;&#12434;&#24453;&#12387;&#12390;&#12367;&#12384;&#12373;&#12356;&#12290;",
									"msg_received" 	 	=> "%coinName%&#27770;&#28168;&#12471;&#12473;&#12486;&#12512;&#12391; %amountPaid% %coinLabel% &#12398;&#27770;&#28168;&#12364;&#23436;&#20102;&#12375;&#12414;&#12375;&#12383; !",
									"msg_received2" 	=> "%coinName%&#12461;&#12515;&#12502;&#12481;&#12515;&#12391; %amountPaid% %coinLabel% &#12398;&#27770;&#28168;&#12364;&#23436;&#20102;&#12375;&#12414;&#12375;&#12383; !",
									"payment"			=> "&#27770;&#28168;&#26041;&#27861;&#12434;&#36984;&#25246;",
									"pay_in"			=> "%coinName%&#12391;&#12398;&#27770;&#28168;",
									"loading"			=> "&#35501;&#12415;&#36796;&#12435;&#12391;&#12356;&#12414;&#12377;..."),
	     
							"id" => array("name"		=> "Indonesian", 
									"button"			=> "Klik disini jika anda telah mengirim %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% belum diterima.</b><br>Jika kamu sudah mengirim %coinNames% (sejumlah %coinNames% dengan jumlah yang tepat seperti pada kotak dibawah),  silahkan tunggu beberapa menit untuk menerima %coinName% lewat sistem pembayaran. Jika anda mengirim sejumlah lain, sistem pembayaran akan mengabaikan transaksinya dan anda perlu mengirim dengan jumlah yang tepat lagi, atau kontak pemilik web untuk membantu.",
									"msg_received" 	 	=> "%coinName% Sistem Pembayaran menerima %amountPaid% %coinLabel% dengan sukses !",
									"msg_received2" 	=> "%coinName% Captcha menerima %amountPaid% %coinLabel% dengan sukses !",
									"payment"			=> "Pilih Metode Pembayaran",
									"pay_in"			=> "Pembayaran dalam bentuk %coinName%",
									"loading"			=> "Pemuatan ..."),
	     
							"tr" => array("name"		=> "Turkish",
									"button"			=> "%coinName% g&#246;nderdiyseniz, buraya t&#305;klay&#305;n",
									"msg_not_received" 	=> "<b>%coinNames% hen&#252;z al&#305;namad&#305;.</b><br> De&#287;i&#351;ik yada yanl&#305;&#351; bir mebl&#226; verdiyseniz, sistem kabul etmemi&#351; olabilir. Bu durumda g&#246;derme i&#351;leminizi birka&#231; dakika bekleyerek tekrarlay&#305;n. Veya site sahibinden yard&#305;m isteyin.",
									"msg_received" 	 	=> "%coinName% &#246;deme sistemine %amountPaid% %coinLabel% ba&#351;ar&#305;yla gelmi&#351;tir !",
									"msg_received2" 	=> "%coinName% Capcha`ya %amountPaid% %coinLabel% ba&#351;ar&#305;yla gelmi&#351;tir !",
									"payment"			=> "&#214;deme metodunu se&#231;iniz",
									"pay_in"			=> "%coinName% ile &#246;deme",
									"loading"			=> "Y&#252;kleniyor ..."),
	     
							"ar" => array("name"		=> "Arabic",
									"button"			=> "&#1575;&#1590;&#1594;&#1591; &#1607;&#1606;&#1575; &#1601;&#1610; &#1581;&#1575;&#1604;&#1577; &#1602;&#1605;&#1578; &#1601;&#1593;&#1604;&#1575;&#1611; &#1576;&#1575;&#1604;&#1575;&#1585;&#1587;&#1575;&#1604; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#1604;&#1605; &#1610;&#1578;&#1605; &#1575;&#1587;&#1578;&#1604;&#1575;&#1605;&#1607;&#1575; &#1576;&#1593;&#1583;.</b><br> &#1573;&#1584;&#1575; &#1602;&#1605;&#1578; &#1576;&#1573;&#1585;&#1587;&#1575;&#1604;&#1607;&#1575; %coinNames% (&#1576;&#1575;&#1604;&#1592;&#1576;&#1591; %coinName% &#1605;&#1576;&#1604;&#1594; &#1601;&#1610; &#1583;&#1601;&#1593; &#1608;&#1575;&#1581;&#1583;), &#1610;&#1585;&#1580;&#1609; &#1575;&#1604;&#1573;&#1606;&#1578;&#1592;&#1575;&#1585; &#1576;&#1590;&#1593; &#1583;&#1602;&#1575;&#1574;&#1602; &#1604;&#1573;&#1587;&#1578;&#1604;&#1575;&#1605;&#1607;&#1605; &#1605;&#1606; &#1582;&#1604;&#1575;&#1604; %coinName% &#1606;&#1592;&#1575;&#1605; &#1575;&#1604;&#1583;&#1601;&#1593;. &#1573;&#1584;&#1575; &#1602;&#1605;&#1578; &#1576;&#1573;&#1585;&#1587;&#1575;&#1604; &#1605;&#1576;&#1575;&#1604;&#1594; &#1571;&#1582;&#1585;&#1609;, &#1606;&#1592;&#1575;&#1605; &#1575;&#1604;&#1583;&#1601;&#1593; &#1587;&#1608;&#1601; &#1610;&#1580;&#1575;&#1607;&#1604; &#1575;&#1604;&#1589;&#1601;&#1602;&#1577;&#1548; &#1608;&#1587;&#1608;&#1601; &#1578;&#1581;&#1578;&#1575;&#1580; &#1604;&#1573;&#1585;&#1587;&#1575;&#1604; &#1575;&#1604;&#1605;&#1576;&#1604;&#1594; &#1575;&#1604;&#1589;&#1581;&#1610;&#1581; &#1605;&#1585;&#1577; &#1571;&#1582;&#1585;&#1609;",
									"msg_received" 	 	=> "%coinName% &#1578;&#1605; &#1575;&#1587;&#1578;&#1604;&#1575;&#1605; &#1575;&#1604;&#1605;&#1576;&#1604;&#1594; %amountPaid% %coinLabel% &#1576;&#1606;&#1580;&#1575;&#1581; !",
									"msg_received2" 	=> "%coinName% &#1578;&#1605; &#1575;&#1587;&#1578;&#1604;&#1575;&#1605; &#1575;&#1604;&#1603;&#1575;&#1576;&#1578;&#1588;&#1575; %amountPaid% %coinLabel% &#1576;&#1606;&#1580;&#1575;&#1581; !",
									"payment"			=> "&#1575;&#1582;&#1578;&#1585; &#1591;&#1585;&#1610;&#1602;&#1577; &#1575;&#1604;&#1583;&#1601;&#1593;",
									"pay_in"			=> "&#1583;&#1601;&#1593; &#1601;&#1610; %coinName%",
									"loading"			=> "&#1580;&#1575;&#1585; &#1575;&#1604;&#1578;&#1581;&#1605;&#1610;&#1604; ..."),
	     
							"cn" => array("name"		=> "Chinese Simplified",
									"button"			=> "&#28857;&#20987;&#27492;,&#22914;&#26524;&#20320;&#24050;&#32463;&#21457;&#36865;&#20102; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#36824;&#27809;&#26377;&#25910;&#21040;&#12290;</b><br>&#22914;&#26524;&#20320;&#24050;&#32463;&#21457;&#36865; %coinNames% (&#20351;&#29992;&#20102;&#31934;&#30830;&#25968;&#37327;,&#22914;&#19979;&#26694;&#20013;&#26174;&#31034;&#30340;&#37027;&#26679;)&#65292;&#35831;&#31561;&#24453; &#20960;&#20998;&#38047;, &#31995;&#32479;&#22312;&#23436;&#25104; %coinName% &#30340;&#25509;&#25910;&#22788;&#29702;&#12290;&#22914;&#26524;&#20320;&#21457;&#36865;&#20854;&#23427;&#25968;&#37327;&#65292;&#25903;&#20184;&#31995;&#32479;&#23558;&#24573;&#30053;&#20320;&#30340;&#20132;&#26131;&#12290;&#20320;&#24517;&#39035;&#20351;&#29992;&#31934;&#30830;&#25968;&#37327;&#12290;",
									"msg_received" 	 	=> "%coinName% &#25903;&#20184;&#31995;&#32479;&#25104;&#21151;&#25509;&#25910;&#20102; %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% &#39564;&#35777;&#30721;&#24050;&#25509;&#25910;&#65292; %amountPaid% %coinLabel% &#25104;&#21151; !",
									"payment"			=> "&#36873;&#25321;&#20184;&#27454;&#26041;&#24335;",
									"pay_in"			=> "&#25903;&#20184; %coinName%",
									"loading"			=> "&#21152;&#36733;&#20013;..."),
	     
							"zh" => array("name"		=> "Chinese Traditional",
									"button"			=> "&#40670;&#25802;&#27492;,&#22914;&#26524;&#20320;&#24050;&#32147;&#30332;&#36865;&#20102; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#36996;&#27794;&#26377;&#25910;&#21040;&#12290;</b><br>&#22914;&#26524;&#20320;&#24050;&#32147;&#30332;&#36865; %coinNames% (&#20351;&#29992;&#20102;&#31934;&#30906;&#25976;&#37327;,&#22914;&#19979;&#26694;&#20013;&#39023;&#31034;&#30340;&#37027;&#27171;)&#65292;&#35531;&#31561;&#24453;&#24190;&#20998;&#37758;,&#31995;&#32113;&#22312;&#23436;&#25104; %coinName% &#30340;&#25509;&#25910;&#34389;&#29702;&#12290;&#22914;&#26524;&#20320;&#30332;&#36865;&#20854;&#23427;&#25976;&#37327;&#65292;&#25903;&#20184;&#31995;&#32113;&#23559;&#24573;&#30053;&#20320;&#30340;&#20132;&#26131;&#12290;&#20320;&#24517;&#38920;&#20351;&#29992;&#31934;&#30906;&#25976;&#37327;&#12290;",
									"msg_received" 	 	=> "%coinName% &#25903;&#20184;&#31995;&#32113;&#25104;&#21151;&#25509;&#25910;&#20102; %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% &#39511;&#35657;&#30908;&#24050;&#25509;&#25910;&#65292;%amountPaid% %coinLabel% &#25104;&#21151; !",
									"payment"			=> "&#36984;&#25799;&#20184;&#27454;&#26041;&#24335;",
									"pay_in"			=> "&#25903;&#20184; %coinName%",
									"loading"			=> "&#21152;&#36617;&#20013;..."),
	     
							"hi" => array("name"		=> "Hindi",
									"button"			=> "&#2310;&#2346; &#2346;&#2361;&#2354;&#2375; &#2360;&#2375; &#2361;&#2368; &#2349;&#2375;&#2332;&#2375; &#2361;&#2376;&#2306; &#2340;&#2379; &#2351;&#2361;&#2366;&#2306; &#2325;&#2381;&#2354;&#2367;&#2325; &#2325;&#2352;&#2375;&#2306; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#2325;&#2368; &#2309;&#2349;&#2368; &#2340;&#2325; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; &#2344;&#2361;&#2368;&#2306; &#2325;&#2367;&#2351;&#2366; &#2327;&#2351;&#2366; &#2361;&#2376;.</b><br>&#2344;&#2368;&#2330;&#2375; &#2342;&#2367;&#2319; &#2327;&#2319; &#2348;&#2377;&#2325;&#2381;&#2360; &#2350;&#2375;&#2306; &#2342;&#2367;&#2326;&#2366;&#2351;&#2366; &#2327;&#2351;&#2366; &#2361;&#2376; &#2319;&#2325; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2350;&#2375;&#2306; &#2360;&#2335;&#2368;&#2325; %coinNames% &#2352;&#2366;&#2358;&#2367; &#2351;&#2342;&#2367; &#2310;&#2346; &#2344;&#2375; &#2346;&#2361;&#2354;&#2375; &#2360;&#2375; &#2361;&#2368; %coinName% &#2349;&#2375;&#2332;&#2366; &#2361;&#2376;, &#2340;&#2379; %coinName% &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2346;&#2381;&#2352;&#2339;&#2366;&#2354;&#2368; &#2360;&#2375; &#2313;&#2344;&#2381;&#2361;&#2375;&#2306; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; &#2325;&#2352;&#2344;&#2375; &#2325;&#2375; &#2354;&#2367;&#2319; &#2325;&#2369;&#2331; &#2361;&#2368; &#2350;&#2367;&#2344;&#2335;&#2379;&#2306; &#2325;&#2371;&#2346;&#2351;&#2366; &#2346;&#2381;&#2352;&#2340;&#2368;&#2325;&#2381;&#2359;&#2366; &#2325;&#2352;&#2375;&#2306;. &#2310;&#2346; &#2346;&#2361;&#2354;&#2375; &#2360;&#2375; &#2361;&#2368; &#2325;&#2367;&#2360;&#2368; &#2309;&#2344;&#2381;&#2351; &#2352;&#2366;&#2358;&#2367; &#2349;&#2375;&#2332;&#2344;&#2375; &#2325;&#2368; &#2361;&#2376;, &#2340;&#2379; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2346;&#2381;&#2352;&#2339;&#2366;&#2354;&#2368; &#2354;&#2375;&#2344;-&#2342;&#2375;&#2344; &#2346;&#2352; &#2343;&#2381;&#2351;&#2366;&#2344; &#2344;&#2361;&#2368;&#2306; &#2342;&#2375;&#2327;&#2366; &#2324;&#2352; &#2310;&#2346; &#2347;&#2367;&#2352; &#2360;&#2375; &#2360;&#2361;&#2368; &#2352;&#2366;&#2358;&#2367; &#2349;&#2375;&#2332;&#2344;&#2375; &#2325;&#2368; &#2332;&#2352;&#2370;&#2352;&#2340; &#2361;&#2379;&#2327;&#2368;.",
									"msg_received" 	 	=> "%coinName% &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2346;&#2381;&#2352;&#2339;&#2366;&#2354;&#2368; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; %amountPaid% %coinLabel% &#2360;&#2347;&#2354;&#2340;&#2366;&#2346;&#2370;&#2352;&#2381;&#2357;&#2325; !",
									"msg_received2" 	=> "%coinName% &#2325;&#2376;&#2346;&#2381;&#2330;&#2366; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; %amountPaid% %coinLabel% &#2360;&#2347;&#2354;&#2340;&#2366;&#2346;&#2370;&#2352;&#2381;&#2357;&#2325; !",
									"payment"			=> "&#2330;&#2369;&#2344;&#2375;&#2306; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2325;&#2366; &#2340;&#2352;&#2368;&#2325;&#2366;",
									"pay_in"			=> "%coinName% &#2350;&#2375;&#2306; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344;",
									"loading"			=> "&#2354;&#2379;&#2337; &#2361;&#2379; &#2352;&#2361;&#2366; &#2361;&#2376; ..."),
                    	    
							"fi" => array("name"		=> "Finnish",
									"button"			=> "Klikkaa t&auml;st&auml; jos olet jo l&auml;hett&auml;nyt %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% ei ole viel&auml; vastaanotettu.</b><br>Jos olet jo l&auml;hett&auml;nyt %coinNames% (t&auml;sm&auml;llisen %coinName% -summan yhten&auml; maksuna, kuten maksulaatikossa n&auml;ytet&auml;&auml;n alapuolella), ole hyv&auml; ja odota pari minuutta ett&auml; %coinName% -maksuj&auml;rjestelm&auml; k&auml;sittelee ne. Jos l&auml;hetit mink&auml; tahansa muun summan, maksuj&auml;rjestelm&auml; ei k&auml;sittele maksua ja sinun pit&auml;&auml; l&auml;hett&auml;&auml; oikea summa uudestaan, tai olla yhteydess&auml; sivuston omistajaan.",
									"msg_received" 	 	=> "%coinName% -maksuj&auml;rjestelm&auml; vastaanotti %amountPaid% %coinLabel% onnistuneesti !",
									"msg_received2" 	=> "%coinName% Captcha vastaanotti %amountPaid% %coinLabel% onnistuneesti !",
									"payment"			=> "Valitse maksutapa",
									"pay_in"			=> "Maksu valuutassa %coinName%",
									"loading"			=> "Ladataan ..."),
                    	    
							"el" => array("name"		=> "Greek",
									"button"			=> "&Pi;&alpha;&tau;&#942;&sigma;&tau;&epsilon; &epsilon;&delta;&#974; &alpha;&nu; &#941;&chi;&epsilon;&tau;&epsilon; &#942;&delta;&eta; &sigma;&tau;&epsilon;&#943;&lambda;&epsilon;&iota; &tau;&alpha; %coinNames%",
									"msg_not_received" 	=> "<b>&Tau;&alpha; %coinNames% &delta;&epsilon;&nu; &#941;&chi;&omicron;&upsilon;&nu; &pi;&alpha;&rho;&alpha;&lambda;&eta;&phi;&theta;&epsilon;&#943; &alpha;&kappa;&#972;&mu;&alpha;.</b><br>&Alpha;&nu; &#941;&chi;&epsilon;&tau;&epsilon; &#942;&delta;&eta; &sigma;&tau;&epsilon;&#943;&lambda;&epsilon;&iota; &tau;&alpha; %coinNames% (&alpha;&kappa;&rho;&iota;&beta;&#974;&sigmaf; &tau;&omicron; %coinName% &pi;&omicron;&sigma;&#972; &sigma;&epsilon; &mu;&#943;&alpha; &pi;&lambda;&eta;&rho;&omega;&mu;&#942; &#972;&pi;&omega;&sigmaf; &phi;&alpha;&#943;&nu;&epsilon;&tau;&alpha;&iota; &sigma;&tau;&omicron; &pi;&alpha;&rho;&alpha;&kappa;&#940;&tau;&omega; &kappa;&omicron;&upsilon;&tau;&#943;), &pi;&alpha;&rho;&alpha;&kappa;&alpha;&lambda;&omicron;&#973;&mu;&epsilon; &pi;&epsilon;&rho;&iota;&mu;&#941;&nu;&epsilon;&tau;&epsilon; &mu;&epsilon;&rho;&iota;&kappa;&#940; &lambda;&epsilon;&pi;&tau;&#940; &mu;&#941;&chi;&rho;&iota; &nu;&alpha; &pi;&alpha;&rho;&alpha;&lambda;&eta;&phi;&theta;&omicron;&#973;&nu; &alpha;&pi;&#972; &tau;&omicron; &Sigma;&#973;&sigma;&tau;&eta;&mu;&alpha; &Pi;&lambda;&eta;&rho;&omega;&mu;&#942;&sigmaf; %coinNames%. &Alpha;&nu; &sigma;&tau;&epsilon;&#943;&lambda;&epsilon;&tau;&epsilon; &omicron;&pi;&omicron;&iota;&omicron;&delta;&#942;&pi;&omicron;&tau;&epsilon; &#940;&lambda;&lambda;&omicron; &pi;&omicron;&sigma;&#972;, &tau;&omicron; &Sigma;&#973;&sigma;&tau;&eta;&mu;&alpha; &Pi;&lambda;&eta;&rho;&omega;&mu;&#942;&sigmaf; &theta;&alpha; &alpha;&gamma;&nu;&omicron;&#942;&sigma;&epsilon;&iota; &tau;&eta;&nu; &sigma;&upsilon;&nu;&alpha;&lambda;&lambda;&alpha;&gamma;&#942; &kappa;&alpha;&iota; &theta;&alpha; &pi;&rho;&#941;&pi;&epsilon;&iota; &nu;&alpha; &xi;&alpha;&nu;&alpha;&sigma;&tau;&epsilon;&#943;&lambda;&epsilon;&tau;&epsilon; &tau;&omicron; &sigma;&omega;&sigma;&tau;&#972; &pi;&omicron;&sigma;&#972; &#942; &nu;&alpha; &epsilon;&pi;&iota;&kappa;&omicron;&iota;&nu;&omega;&nu;&#942;&sigma;&epsilon;&tau;&epsilon; &mu;&epsilon; &tau;&omicron;&nu; &delta;&iota;&alpha;&chi;&epsilon;&iota;&rho;&iota;&sigma;&tau;&#942; &tau;&eta;&sigmaf; &iota;&sigma;&tau;&omicron;&sigma;&epsilon;&lambda;&#943;&delta;&alpha;&sigmaf;.",
									"msg_received" 	 	=> "&Tau;&omicron; &Sigma;&#973;&sigma;&tau;&eta;&mu;&alpha; &Pi;&lambda;&eta;&rho;&omega;&mu;&#942;&sigmaf; %coinName% &#941;&lambda;&alpha;&beta;&epsilon; &epsilon;&pi;&iota;&tau;&upsilon;&chi;&#974;&sigmaf; %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "&Kappa;&omega;&delta;&iota;&kappa;&#972;&sigmaf; &Epsilon;&lambda;&#941;&gamma;&chi;&omicron;&upsilon; %coinName% &#941;&lambda;&alpha;&beta;&epsilon; &epsilon;&pi;&iota;&tau;&upsilon;&chi;&#974;&sigmaf; %amountPaid% %coinLabel% !",
									"payment"			=> "&Epsilon;&pi;&iota;&lambda;&#941;&xi;&tau;&epsilon; &Mu;&#941;&theta;&omicron;&delta;&omicron; &Pi;&lambda;&eta;&rho;&omega;&mu;&#942;&sigmaf;",
									"pay_in"			=> "&Pi;&lambda;&eta;&rho;&omega;&mu;&#942; &sigma;&epsilon; %coinName%",
									"loading"			=> "&Phi;&#972;&rho;&tau;&omega;&sigma;&eta; ..."),
                    	    
							"cs" => array("name"		=> "Czech",
									"button"			=> "Klikn&#283;te zde, pokud jste ji&#382; %coinNames% odeslali",
									"msg_not_received" 	=> "<b>%coinNames% je&scaron;t&#283; nebyly obdr&#382;eny.</b><br>Pokud jste  u&#382; %coinNames% odeslali ( p&#345;esn&aacute; suma %coinName% v jedn&eacute; platb&#283; se zobrazuje boxu pod t&iacute;mto textem), pros&iacute;m po&#269;kejte n&#283;kolik minut dokud nebudou obdr&#382;eny %coinName% platebn&iacute;m syst&eacute;mem. Pokud jste odeslali jakoukoliv jinou sumu, platebn&iacute; syst&eacute;m bude transakci ignorovat a Vy budete muset odeslat spr&aacute;vnou &#269;&aacute;stku znovu, nebo kontaktujte administr&aacute;tora webu pro asistenci.",
									"msg_received" 	 	=> "%coinName% platebn&iacute; syst&eacute;m obdr&#382;el %amountPaid% %coinLabel% &uacute;sp&#283;&scaron;n&#283; !",
									"msg_received2" 	=> "%coinName% Captcha obdr&#382;el %amountPaid% %coinLabel% &uacute;sp&#283;&scaron;n&#283; !",
									"payment"			=> "Zvolte Platebn&iacute; Metodu",
									"pay_in"			=> "Platba v %coinName%",
									"loading"			=> "Na&#269;&iacute;t&aacute;n&iacute; ..."),
                    	    
							"sl" => array("name"		=> "Slovenian",
									"button"			=> "Klikni Tu, v primeru da, ste %coinNames% kovance &#382;e poslali",
									"msg_not_received" 	=> "<b>%coinNames% kovancev &scaron;e nismo prejeli.</b><br>V primeru, da ste %coinName% kovance &#382;e poslali (v to&#269;nem znesku kot ga vidite v okvirju spodaj), prosimo po&#269;akajte &scaron;e nekaj minut, da jih %coinName% pla&#269;ilni sistem zabele&#382;i. V primeru, da ste poslali napa&#269;en znesek ba bo pla&#269;ilni sistem transakcijo ignoriral in boste morali to&#269;en znesek poslati ponovno ali pa kontaktirati lastnika trgovine za pomo&#269;.",
									"msg_received" 	 	=> "%coinName% pla&#269;ilni sistem je uspe&scaron;no prejel %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% Captcha je uspe&scaron;no prejela %amountPaid% %coinLabel% !",
									"payment"			=> "Izberite na&#269;in pla&#269;ila",
									"pay_in"			=> "Pla&scaron;ilo v %coinName%",
									"loading"			=> "Nalaganje ..."),
                    	    
							"sr" => array("name"		=> "Serbian",
									"button"			=> "Klikni ovde ako si vec poslao %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% jo&scaron; uvek nisu primljeni.</b><br>Ako si vec poslao/la %coinNames% (tacan iznos jednim placanjem, kako je navedeno ispod), molim te sacekaj koji minut da transakcija bude registrovana od strane %coinName% mre&#382;e i 'legne' u novcanik. Ako si poslao/la bilo koji drugi iznos od navedenog sistem placanja ce ignorisati transakciju i morace&scaron; ponovo da po&scaron;alje&scaron; tacnu sumu ili da nas kontaktira&scaron;.",
									"msg_received" 	 	=> "%coinName% sistem placanja je primio %amountPaid% %coinLabel% uspe&scaron;no !",
									"msg_received2" 	=> "%coinName% Captcha je primio %amountPaid% %coinLabel% uspe&scaron;no !",
									"payment"			=> "Odaberi nacin placanja",
									"pay_in"			=> "Placanje %coinName%",
									"loading"			=> "U&#269;itavanje ..."),
                    	    
							"et" => array("name"		=> "Estonian",
									"button"			=> "Vajuta Siia, kui Sa juba saatsid %coinNames% meile",
									"msg_not_received" 	=> "<b>%coinNames% ei ole veel saabunud.</b><br>Kui Sa juba saatsid %coinNames% (t&auml;pselt sama summa mis n&auml;idatud), siis palun oota m&otilde;ned minutid veel, et need %coinNames% s&uuml;steemis kohale j&otilde;uaks. Kui saatsite m&otilde;ne Teise summa, siis makses&uuml;steem ignoreerib seda ja makse tuleb teha uuesti v&otilde;i kontakteeruda lehe haldajaga, et probleem lahendada.",
									"msg_received" 	 	=> "%coinName% makses&uuml;steem sai edukalt %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% Captcha sai edukalt %amountPaid% %coinLabel% !",
									"payment"			=> "Vali maksevahend",
									"pay_in"			=> "Maksmine %coinName%",
									"loading"			=> "Laadimine ..."),
                    	    
							"sq" => array("name"		=> "Albanian",
									"button"			=> "Kliko k&euml;tu n&euml;se ju keni d&euml;rguar tashm&euml; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% ende nuk jan&euml; marr&euml;.</b><br>N&euml;se ju keni d&euml;rguar tashm&euml; %coinNames% (shuma e sakt&euml; %coinName% n&euml; nj&euml; pages&euml; si&ccedil; tregohet n&euml; kutin&euml; m&euml; posht&euml;), ju lutem prisni disa minuta p&euml;r t&euml; marr&euml; ato nga %coinName% Payment System. N&euml;se d&euml;rgoni ndonj&euml; shum&euml; tjet&euml;r, Sistemi i Pagesave do t&euml; injoroj&euml; transaksionin dhe do t'ju duhet t&euml; d&euml;rgoni p&euml;rs&euml;ri shum&euml;n e sakt&euml;, ose kontaktoni pronarin e faqes p&euml;r ndihm&euml;.",
									"msg_received" 	 	=> "Sistemi i Pagesave %coinName% mori %amountPaid% %coinLabel% me sukses!",
									"msg_received2" 	=> "%coinName% Captcha mori %amountPaid% %coinLabel% me sukses!",
									"payment"			=> "Zgjidh Metod&euml;n e Pages&euml;s",
									"pay_in"			=> "Pagesa n&euml; %coinName%",
									"loading"			=> "Po ngarkohet ...")
                    	    
							);

	if(!defined("CRYPTOBOX_LOCALISATION")) define("CRYPTOBOX_LOCALISATION", json_encode($cryptobox_localisation));
	unset($cryptobox_localisation);  
	
	if (!CRYPTOBOX_WORDPRESS || defined("CRYPTOBOX_PRIVATE_KEYS"))
	{
		$cryptobox_private_keys = explode("^", CRYPTOBOX_PRIVATE_KEYS);
		foreach ($cryptobox_private_keys as $v)
			if (strpos($v, " ") !== false || strpos($v, "PRV") === false || strpos($v, "AA") === false || strpos($v, "77") === false) die("Invalid Private Key - ". (CRYPTOBOX_WORDPRESS ? "please setup it on your plugin settings page" : "$v in variable \$cryptobox_private_keys, file cryptobox.config.php."));

		unset($v); unset($cryptobox_private_keys);
	}
?>