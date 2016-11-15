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
 * @copyright   2014-2016 Delta Consultants
 * @category    Libraries
 * @website     https://gourl.io
 * @api         https://gourl.io/api-php.html
 * @example     https://gourl.io/bitcoin-payment-gateway-api.html
 * @gitHub  	https://github.com/cryptoapi/Payment-Gateway
 * @license 	Free GPLv2
 * @version     1.7.8
 *
 *
 *  CLASS CRYPTOBOX - LIST OF METHODS:
 *  --------------------------------------
 *  1. function display_cryptobox(..)			// Show Cryptocoin Payment Box and automatically displays successful payment message. If $submit_btn = true, display user submit button 'Click Here if you have already sent coins' or not
 *  2. function is_paid(..)	 					// If payment received - return true, otherwise return false
 *  3. function is_confirmed()					// Returns true if transaction/payment have 6+ confirmations. Average transaction/payment confirmation time - 10-20min for 6 confirmations (altcoins)
 *  4. function amount_paid()					// Returns the amount of coins received from the user 
 *  5. function amount_paid_usd()				// Returns the approximate amount in USD received from the user using live cryptocurrency exchange rates on the datetime of payment
 *  6. function set_status_processed()			// Optional - if payment received, set payment status to 'processed' and save this status in database
 *  7. function is_processed()					// Optional - if payment status in database is 'processed' - return true, otherwise return false
 *  8. function cryptobox_type()				// Returns cryptobox type - paymentbox or captchabox
 *  9. function payment_id()					// Returns current record id in the table crypto_payments. Crypto_payments table stores all payments from your users
 *  10.function payment_date()					// Returns payment/transaction datetime in GMT format
 *  11.function payment_info()					// Returns object with current user payment details - amount, txID, datetime, usercointry, etc
 *  12.function cryptobox_reset()				// Optional, Delete cookies/sessions and new cryptobox with new payment amount will be displayed. Use this function only if you have not set userID manually.
 *  13.function coin_name()						// Returns coin name (bitcoin, dogecoin, etc)
 *  14.function coin_label()					// Returns coin label (DOGE, BTC, etc)
 *  15.function iframe_id()						// Returns payment box frame id
 *
 *
 *  LIST OF GENERAL FUNCTIONS:
 *  -------------------------------------
 *  A. function payment_history(..) 			// Returns array with history payment details of any of your users / orders / etc.
 *  B. function payment_unrecognised(..) 		// Returns array with unrecognised payments for custom period - $time (users paid wrong amount on your internal wallet address)
 *  C. function display_language_box(..)		// Language selection dropdown list for cryptocoin payment box 
 *  D. function display_currency_box(..)		// Multiple crypto currency selection list. You can accept payments in multiple crypto currencies (for example: bitcoin, litecoin, dogecoin)
 *  E. function cryptobox_selcoin(..)			// Current selected coin by user (bitcoin, dogecoin, etc. - for multiple coin payment boxes) 
 *  F. function get_country_name(..)			// Get country name by country code or reverse
 *  G. function convert_currency_live(..)		// Fiat currency converter using Google Finance live exchange rates
 *  H. function validate_gourlkey(..)			// Validate gourl private/public/affiliate keys 
 *  I. function run_sql(..)						// Run SQL queries and return result in array/object formats
 *
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


define("CRYPTOBOX_VERSION", "1.7.8");

// GoUrl supported crypto currencies
define("CRYPTOBOX_COINS", json_encode(array('bitcoin', 'litecoin', 'paycoin', 'dogecoin', 'dash', 'speedcoin', 'reddcoin', 'potcoin', 'feathercoin', 'vertcoin', 'vericoin', 'peercoin', 'monetaryunit')));


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
	private $language		= "en";		// cryptobox localisation; en - English, es - Spanish, fr - French, de - German, ru - Russian, nl - Dutch, pt - Portuguese, fa - Persian, ko - Korean, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi
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
	private $localisation 	= "";		// localisation; en - English, es - Spanish, fr - French, de - German, ru - Russian, nl - Dutch, pt - Portuguese, fa - Persian, ko - Korean, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi
	
	
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
		
		if (($this->amount <= 0 && $this->amountUSD <= 0) || ($this->amount > 0 && $this->amountUSD > 0)) die("You can use in cryptobox options one of variable only: amount or amountUSD. You cannot place values in that two variables together");
		 
		if ($this->amount && (!is_numeric($this->amount) || $this->amount < 0.0001 || $this->amount > 50000000)) die("Invalid Amount - $this->amount $this->coinLabel. Allowed range: 0.0001 .. 50,000,000");
		if ($this->amountUSD && (!is_numeric($this->amountUSD) || $this->amountUSD < 0.01 || $this->amountUSD > 1000000)) die("Invalid amountUSD - $this->amountUSD USD. Allowed range: 0.01 .. 1,000,000");
		
		$this->period = trim(strtoupper(str_replace(" ", "", $this->period)));
		if (substr($this->period, -1) == "S") $this->period = substr($this->period, 0, -1);
		for ($i=1; $i<=90; $i++) { $arr[] = $i."MINUTE"; $arr[] = $i."HOUR"; $arr[] = $i."DAY"; $arr[] = $i."WEEK"; $arr[] = $i."MONTH"; }
		if ($this->period != "NOEXPIRY" && !in_array($this->period, $arr)) die("Invalid Cryptobox Period - $this->period");
		$this->period = str_replace(array("MINUTE", "HOUR", "DAY", "WEEK", "MONTH"), array(" MINUTE", " HOUR", " DAY", " WEEK", " MONTH"), $this->period);
		
		$id = "gourlcryptolang";
		$this->language = strtolower($this->language);
		$this->localisation = json_decode(CRYPTOBOX_LOCALISATION, true);
		if (isset($_GET[$id]) && in_array($_GET[$id], array_keys($this->localisation))) $this->language = $_GET[$id];
		elseif (isset($_COOKIE[$id]) && in_array($_COOKIE[$id], array_keys($this->localisation))) $this->language = $_COOKIE[$id];
		elseif (!in_array($this->language, array_keys($this->localisation))) $this->language = "en";
		$this->localisation = $this->localisation[$this->language];
		unset($id);
		
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
					if (isset($_COOKIE[$this->cookieName]) && trim($_COOKIE[$this->cookieName]) && strpos($_COOKIE[$this->cookieName], "__")) $this->userID = trim($_COOKIE[$this->cookieName]);
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
					if (isset($_SESSION[$this->cookieName]) && trim($_SESSION[$this->cookieName]) && strpos($_SESSION[$this->cookieName], "--")) $this->userID = trim($_SESSION[$this->cookieName]);
					else
					{	 
						$d = time(); if ($d > 1410000000) $d -= 1410000000;
						$v = trim($d."--".substr(md5(uniqid(mt_rand().mt_rand().mt_rand())), 0, 10));
						$this->userID = $_SESSION[$this->cookieName] = $v; 
					}	
				break;
				
				case "IPADDRESS":
					
					if (session_status() == PHP_SESSION_NONE) session_start();
					if (isset($_SESSION['cryptoUserIP']) && filter_var($_SESSION['cryptoUserIP'], FILTER_VALIDATE_IP))
						 $ip = $_SESSION['cryptoUserIP'];
					else $ip = $_SESSION['cryptoUserIP'] = $this->ip_address();
					$this->userID = trim(md5($ip."*&*".$this->boxID."*&*".$this->coinLabel."*&*".$this->orderID));
					
				break;
				
				default:
					die("Invalid userFormat value - $this->userFormat");
				break;
			}
		}

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
		
		$cryptobox_html = "";
		$val 			= md5($this->iframeID.$this->private_key.$this->userID);
	
		if ($submit_btn && isset($_POST["cryptobox_live_"]) && $_POST["cryptobox_live_"] == $val)
		{
			$id = "id".md5(mt_rand()); 
			if (!$this->paid) $cryptobox_html .= "<a id='c".$this->iframeID."' name='c".$this->iframeID."'></a>";
			$cryptobox_html .= "<br><div id='$id' align='center'>";
			$cryptobox_html .= '<div'.(in_array($this->language, array("ar", "fa"))?' dir="rtl"':'').' style="'.htmlspecialchars($message_style, ENT_COMPAT).'">';
	

			if ($this->paid) $cryptobox_html .= "<span style='color:#339e2e;white-space:nowrap;'>".str_replace(array("%coinName%", "%coinLabel%", "%amountPaid%"), array($this->coinName, $this->coinLabel, $this->amountPaid), $this->localisation[($this->boxType=="paymentbox"?"msg_received":"msg_received2")])."</span>";
			else $cryptobox_html .= "<span style='color:#eb4847'>".str_replace(array("%coinName%", "%coinNames%", "%coinLabel%"), array($this->coinName, ($this->coinLabel=='DASH'?$this->coinName:$this->coinName.'s'), $this->coinLabel), $this->localisation["msg_not_received"])."</span><script type='text/javascript'>cryptobox_msghide('$id')</script>";
			
			$cryptobox_html .= "</div></div><br>";
		}
	
		$hash_str = $this->boxID.$this->coinName.$this->public_key.$this->private_key.$this->webdev_key.$this->amount.$this->period.$this->amountUSD.$this->language.$this->amount.$this->iframeID.$this->amountUSD.$this->userID.$this->userFormat.$this->orderID.$width.$height;
		$hash = md5($hash_str);
		$cryptobox_html .= "<div align='center' style='min-width:".$width."px'><iframe id='$this->iframeID' ".($box_style?'style="'.htmlspecialchars($box_style, ENT_COMPAT).'"':'')." scrolling='no' marginheight='0' marginwidth='0' frameborder='0' width='$width' height='$height'></iframe></div>";
		$cryptobox_html .= "<div><script type='text/javascript'>";
		$cryptobox_html .= "cryptobox_show($this->boxID, '$this->coinName', '$this->public_key', $this->amount, $this->amountUSD, '$this->period', '$this->language', '$this->iframeID', '$this->userID', '$this->userFormat', '$this->orderID', '$this->cookieName', '$this->webdev_key', '$hash', $width, $height);";
		$cryptobox_html .= "</script></div>";
	
		if ($submit_btn && !$this->paid)
		{
			$cryptobox_html .= "<form action='".$_SERVER["REQUEST_URI"]."#".($anchor?$anchor:"c".$this->iframeID)."' method='post'>";
			$cryptobox_html .= "<input type='hidden' id='cryptobox_live_' name='cryptobox_live_' value='$val'>";
			$cryptobox_html .= "<div align='center'>";
			$cryptobox_html .= "<button".(in_array($this->language, array("ar", "fa"))?' dir="rtl"':'')." style='color:#555;border-color:#ccc;background:#f7f7f7;-webkit-box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);box-shadow:inset 0 1px 0 #fff,0 1px 0 rgba(0,0,0,.08);vertical-align:top;display:inline-block;text-decoration:none;font-size:13px;line-height:26px;min-height:28px;margin:20px 0 25px 0;padding:0 10px 1px;cursor:pointer;border-width:1px;border-style:solid;-webkit-appearance:none;-webkit-border-radius:3px;border-radius:3px;white-space:nowrap;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;font-family:\"Open Sans\",sans-serif;font-size: 13px;font-weight: normal;text-transform: none;'>&#160; ".str_replace(array("%coinName%", "%coinNames%", "%coinLabel%"), array($this->coinName, ($this->coinLabel=='DASH'?$this->coinName:$this->coinName.'s'), $this->coinLabel), $this->localisation["button"]).($this->language!="ar"?" &#187;":"")." &#160;</button>";
			$cryptobox_html .= "</div>";
			$cryptobox_html .= "</form>";
		}
	
		$cryptobox_html .= "<br>";
	
		return $cryptobox_html;
	}
	
	
		
	
	
	/* 2. Function is_paid($remotedb = false) -
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
	
	
	


	/* 3. Function is_confirmed() -
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

	
	
	
	
	/* 4. Function amount_paid()
	 * 
	 * Returns the amount of coins received from the user
	 */
	public function amount_paid()
	{
		if ($this->paid) return $this->amountPaid; 
		else return 0;
	}

	
	
	
	
	/* 5. Function amount_paid_usd()
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
	
	
	
	
	
	/* 6. Functions set_status_processed() and is_processed() 
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
				$sql = "UPDATE crypto_payments SET processed = 1, processedDate = '".gmdate("Y-m-d H:i:s")."' WHERE paymentID = $this->paymentID LIMIT 1";
				run_sql($sql);
				$this->processed = true;
			}
			return true;
		}
		else return false;
	}
	
	
	
	
	
	/* 7. Function is_processed() 
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


	
	
	
	/* 8. Function cryptobox_type() 
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
	
	
	
	
	
	/* 9. Function payment_id() 
	 * 
	 * Returns current record id in the table crypto_payments.
	 * Crypto_payments table stores all payments from your users
	*/
	public function payment_id()
	{
		return $this->paymentID;
	}
	
	
	
	
	/* 10. Function payment_date() 
	 * 
	 * Returns payment/transaction datetime in GMT format
	 * Example - 2014-09-26 17:31:58 (is 26 September 2014, 5:31pm GMT) 
	*/
	public function payment_date()
	{
		return $this->paymentDate;
	}
	
	
	
	/* 11. Function payment_info()
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
		$obj = ($this->paymentID) ? run_sql("SELECT * FROM crypto_payments WHERE paymentID = $this->paymentID LIMIT 1") : false;
		if ($obj) $obj->countryName = get_country_name($obj->countryID);
		return $obj;
	}
	
	
	
	
	
	/* 12. Function cryptobox_reset()
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
		
	
	
	
	/* 13. Function coin_name()
	 *
	 * Returns coin name (bitcoin, dogecoin, litecoin, etc)   
	*/
	public function coin_name()
	{
		return $this->coinName;
	}
	
	
	
	
	/* 14. Function coin_label()
	 *
	 * Returns coin label (DOGE, BTC, LTC, etc)   
	*/
	public function coin_label()
	{
		return $this->coinLabel;
	}

	
	
	/* 15. Function iframe_id()
	 *
	 * Returns payment box frame id   
	*/
	public function iframe_id()
	{
		return "box".$this->icrc32($this->boxID."__".$this->orderID."__".$this->userID."__".$this->private_key);
	}
	

	
	
	/*
	 * Other Internal functions   
	*/
	private function check_payment($remotedb = false)
	{
		$this->paymentID = $diff = 0;
		
		$obj = run_sql("SELECT paymentID, amount, amountUSD, txConfirmed, txCheckDate, txDate, processed, boxType FROM crypto_payments WHERE boxID = $this->boxID && orderID = '$this->orderID' && userID = '$this->userID' ".($this->period=="NOEXPIRY"?"":"&& txDate >= DATE_SUB('".gmdate("Y-m-d H:i:s")."', INTERVAL ".$this->period.")")." ORDER BY txDate DESC LIMIT 1");
	
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
		
		if ((!$obj && $remotedb) || ($obj && !$this->confirmed && ($diff > (($this->coinLabel=='BTC'?35:12)*60) || $diff < 0))) // if $diff < 0 - user have incorrect time on local computer
		{
			$this->check_payment_live();
		}
	
		return true;
	}
	private function check_payment_live()
	{
		$ip		= $this->ip_address();
		$hash 	= md5($this->boxID.$this->private_key.$this->userID.$this->orderID.$this->language.$this->period.$ip);
		$box_status = "";
		
		$data = array(
				"r" 	=> $this->private_key,
				"b" 	=> $this->boxID,
				"o"		=> $this->orderID,
				"u"		=> $this->userID,
				"l"		=> $this->language,
				"e"		=> $this->period,
				"i"		=> $ip,
				"h"		=> $hash
		);
	
		$ch = curl_init( "https://coins.gourl.io/result.php" );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
			
		$res = curl_exec( $ch );
	
		if ($res) $res = json_decode($res, true);
		
		if ($res) foreach ($res as $k => $v) if (is_string($v)) $res[$k] = trim($v);

		if (isset($res["status"]) && in_array($res["status"], array("payment_received")) &&
				$res["box"] && is_numeric($res["box"]) && $res["box"] > 0 && $res["amount"] && is_numeric($res["amount"]) && $res["amount"] > 0 &&
				$res["private_key"] && preg_replace('/[^A-Za-z0-9]/', '', $res["private_key"]) == $res["private_key"] && $res["private_key"] == $this->private_key)
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
			$obj = run_sql("select paymentID, processed, txConfirmed from crypto_payments where boxID = ".$res["box"]." && orderID = '".$res["order"]."' && userID = '".$res["user"]."' && txID = '".$res["tx"]."' limit 1"); 

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
						WHERE 		paymentID 			= $this->paymentID 
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
						FROM crypto_payments WHERE unrecognised = 0 ".($boxID?" && boxID = $boxID":"").($orderID?" && orderID = '$orderID'":"").($userID?" && userID='$userID'":"").($countryID?" && countryID='".strtoupper($countryID)."'":"").($period?" && recordCreated > DATE_SUB('".gmdate("Y-m-d H:i:s")."', INTERVAL $period)":"")." ORDER BY txDate DESC LIMIT 10000");
	
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
						FROM crypto_payments WHERE unrecognised = 1 ".($boxID?" && boxID = $boxID":"").($period?" && recordCreated > DATE_SUB('".gmdate("Y-m-d H:i:s")."', INTERVAL $period)":"")." ORDER BY txDate DESC LIMIT 10000");
	
		if ($res && !is_array($res)) $res = array($res);
		
		return $res;
	}
	
	
	
	/* D. Function display_language_box()
	 * 
	 * Language selection dropdown list for cryptocoin payment box
	 */
	function display_language_box($default = "en", $anchor = "gourlcryptolang")
	{
		$default 		= strtolower($default);
		$localisation 	= json_decode(CRYPTOBOX_LOCALISATION, true);
		$id 	 		= "gourlcryptolang";
		$arr 	 		= $_GET;
		
		if (isset($_GET[$id]) && in_array($_GET[$id], array_keys($localisation))) { $lan = $_GET[$id]; unset($arr[$id]); setcookie($id, $lan, time()+7*24*3600, "/"); }
		elseif (isset($_COOKIE[$id]) && in_array($_COOKIE[$id], array_keys($localisation))) $lan = $_COOKIE[$id];
		elseif (in_array($default, array_keys($localisation))) $lan = $default;
		else 	$lan = "en";
		
		$url = $_SERVER["REQUEST_URI"];
		if (mb_strpos($url, "?")) $url = mb_substr($url, 0, mb_strpos($url, "?"));
		$tmp  = "<select name='$id' id='$id' onchange='window.open(\"//".$_SERVER["HTTP_HOST"].$url."?".http_build_query($arr).($arr?"&amp;":"").$id."=\"+this.options[this.selectedIndex].value+\"#".$anchor."\",\"_self\")' style='width:130px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#666;border-radius:5px;-moz-border-radius:5px;border: #ccc 1px solid;margin:0;padding:3px 0 3px 6px;white-space:nowrap;overflow:hidden;'>";
		foreach ($localisation as $k => $v) $tmp .= "<option ".($k==$lan?"selected":"")." value='$k'>".$v["name"]."</option>";
		$tmp .= "</select>";
				
		return $tmp; 
	}
	
	
	
	/* D. Function display_currency_box()
	 *
	* Multiple crypto currency selection list. You can accept payments in multiple crypto currencies
	* For example you can accept payments in bitcoin, litecoin, dogecoin and use the same price in USD
	*/
	function display_currency_box($coins = array(), $defCoin = "", $defLang = "en", $iconWidth = 50, $style = "width:350px; margin: 10px 0 10px 320px", $directory = "images", $anchor = "gourlcryptocoins")
	{
		if (!$coins) return "";
		
		$defCoin 			= strtolower($defCoin);
		$defLang 			= strtolower($defLang);
		$available_payments = json_decode(CRYPTOBOX_COINS, true);
		$arr 	 			= $_GET;
		
		if (!in_array($defCoin, $available_payments)) die("Invalid your default value '$defCoin' in display_currency_box()");
		if (!in_array($defCoin, $coins)) $coins[] = $defCoin; 
		
		
		// Current Coin
		$coinName = cryptobox_selcoin($coins, $defCoin);
		
		// Url for Change Coin
		$coin_url = $_SERVER["REQUEST_URI"];
		if (mb_strpos($coin_url, "?")) $coin_url = mb_substr($coin_url, 0, mb_strpos($coin_url, "?"));
		if (isset($arr["gourlcryptocoin"])) unset($arr["gourlcryptocoin"]);
		$coin_url = "//".$_SERVER["HTTP_HOST"].$coin_url."?".http_build_query($arr).($arr?"&amp;":"")."gourlcryptocoin=";
		
		// Current Language
		$localisation = json_decode(CRYPTOBOX_LOCALISATION, true);
		$id = "gourlcryptolang";
		$keys = array_keys($localisation);
		if (isset($_GET[$id]) && in_array($_GET[$id], $keys)) $lan = $_GET[$id];
		elseif (isset($_COOKIE[$id]) && in_array($_COOKIE[$id], $keys)) $lan = $_COOKIE[$id];
		elseif (in_array($defLang, $keys)) $lan = $defLang;
		else 	$lan = "en";
		$localisation = $localisation[$lan];
						
		$id  = "gourlcryptocoins";
		$tmp = '<div id="'.$id.'" align="center" style="'.htmlspecialchars($style, ENT_COMPAT).'"><div style="margin-bottom:15px"><b>'.$localisation["payment"].' -</b></div>';
		foreach ($coins as $v)
		{
			$v = trim(strtolower($v));
			if (!in_array($v, $available_payments)) die("Invalid your submitted value '$v' in display_currency_box()");
			if (strpos(CRYPTOBOX_PRIVATE_KEYS, ucfirst($v)."77") === false) die("Please add your Private Key for '$v' in variable \$cryptobox_private_keys, file cryptobox.config.php");
			$tmp .= "<a href='".$coin_url.$v."#".$anchor."'><img style='box-shadow:none;margin:".round($iconWidth/10)."px ".round($iconWidth/7)."px;border:0;' width='$iconWidth' title='".str_replace("%coinName%", ucfirst($v), $localisation["pay_in"])."' alt='".str_replace("%coinName%", $v, $localisation["pay_in"])."' src='".$directory."/".$v.($iconWidth>70?"2":"").".png'></a>";
		}
		$tmp .= "</div>";
		
		return $tmp;
	}
		
	
	
	/* E. Function cryptobox_selcoin()
	 *
	 * Current selected coin by user
	 */	
	function cryptobox_selcoin($coins = array(), $defCoin = "")
	{
		if (!$coins) return "";
	
		$defCoin 			= strtolower($defCoin);
		$available_payments = json_decode(CRYPTOBOX_COINS, true); // GoUrl supported crypto currencies
		$id 	 			= "gourlcryptocoin";
	
		if (!in_array($defCoin, $coins)) $coins[] = $defCoin;
	
		// Current Selected Coin
		if (isset($_GET[$id]) && in_array($_GET[$id], $available_payments) && in_array($_GET[$id], $coins)) { $coinName = $_GET[$id]; setcookie($id, $coinName, time()+7*24*3600, "/"); }
		elseif (isset($_COOKIE[$id]) && in_array($_COOKIE[$id], $available_payments) && in_array($_COOKIE[$id], $coins)) $coinName = $_COOKIE[$id];
		else $coinName = $defCoin;
	
		return $coinName;
	}

	
	

	/* F. Function get_country_name()
	 * 
	 * Get country name by country code
	 */
	function get_country_name($countryID, $reverse = false)
	{
		$arr = array("AFG"=>"Afghanistan", "ALA"=>"Aland Islands", "ALB"=>"Albania", "DZA"=>"Algeria", "ASM"=>"American Samoa", "AND"=>"Andorra", "AGO"=>"Angola", "AIA"=>"Anguilla", "ATA"=>"Antarctica", "ATG"=>"Antigua and Barbuda", "ARG"=>"Argentina", "ARM"=>"Armenia", "ABW"=>"Aruba", "AUS"=>"Australia", "AUT"=>"Austria", "AZE"=>"Azerbaijan", "BHS"=>"Bahamas", "BHR"=>"Bahrain", "BGD"=>"Bangladesh", "BRB"=>"Barbados", "BLR"=>"Belarus", "BEL"=>"Belgium", "BLZ"=>"Belize", "BEN"=>"Benin", "BMU"=>"Bermuda", "BTN"=>"Bhutan", "BOL"=>"Bolivia", "BIH"=>"Bosnia and Herzegovina", "BWA"=>"Botswana", "BVT"=>"Bouvet Island", "BRA"=>"Brazil", "IOT"=>"British Indian Ocean Territory", "BRN"=>"Brunei", "BGR"=>"Bulgaria", "BFA"=>"Burkina Faso", "BDI"=>"Burundi", "KHM"=>"Cambodia", "CMR"=>"Cameroon", "CAN"=>"Canada", "CPV"=>"Cape Verde", "BES"=>"Caribbean Netherlands", "CYM"=>"Cayman Islands", "CAF"=>"Central African Republic", "TCD"=>"Chad", "CHL"=>"Chile", "CHN"=>"China", "CXR"=>"Christmas Island", "CCK"=>"Cocos (Keeling) Islands", "COL"=>"Colombia", "COM"=>"Comoros", "COG"=>"Congo", "COD"=>"Congo, Democratic Republic", "COK"=>"Cook Islands", "CRI"=>"Costa Rica", "CIV"=>"Côte d’Ivoire", "HRV"=>"Croatia", "CUB"=>"Cuba", "CUW"=>"Curacao", "CBR"=>"Cyberbunker", "CYP"=>"Cyprus", "CZE"=>"Czech Republic", "DNK"=>"Denmark", "DJI"=>"Djibouti", "DMA"=>"Dominica", "DOM"=>"Dominican Republic", "TMP"=>"East Timor", "ECU"=>"Ecuador", "EGY"=>"Egypt", "SLV"=>"El Salvador", "GNQ"=>"Equatorial Guinea", "ERI"=>"Eritrea", "EST"=>"Estonia", "ETH"=>"Ethiopia", "EUR"=>"European Union", "FLK"=>"Falkland Islands", "FRO"=>"Faroe Islands", "FJI"=>"Fiji Islands", "FIN"=>"Finland", "FRA"=>"France", "GUF"=>"French Guiana", "PYF"=>"French Polynesia", "ATF"=>"French Southern territories", "GAB"=>"Gabon", "GMB"=>"Gambia", "GEO"=>"Georgia", "DEU"=>"Germany", "GHA"=>"Ghana", "GIB"=>"Gibraltar", "GRC"=>"Greece", "GRL"=>"Greenland", "GRD"=>"Grenada", "GLP"=>"Guadeloupe", "GUM"=>"Guam", "GTM"=>"Guatemala", "GGY"=>"Guernsey", "GIN"=>"Guinea", "GNB"=>"Guinea-Bissau", "GUY"=>"Guyana", "HTI"=>"Haiti", "HMD"=>"Heard Island and McDonald Islands", "HND"=>"Honduras", "HKG"=>"Hong Kong", "HUN"=>"Hungary", "ISL"=>"Iceland", "IND"=>"India", "IDN"=>"Indonesia", "IRN"=>"Iran", "IRQ"=>"Iraq", "IRL"=>"Ireland", "IMN"=>"Isle of Man", "ISR"=>"Israel", "ITA"=>"Italy", "JAM"=>"Jamaica", "JPN"=>"Japan", "JEY"=>"Jersey", "JOR"=>"Jordan", "KAZ"=>"Kazakstan", "KEN"=>"Kenya", "KIR"=>"Kiribati", "KWT"=>"Kuwait", "KGZ"=>"Kyrgyzstan", "LAO"=>"Laos", "LVA"=>"Latvia", "LBN"=>"Lebanon", "LSO"=>"Lesotho", "LBR"=>"Liberia", "LBY"=>"Libya", "LIE"=>"Liechtenstein", "LTU"=>"Lithuania", "LUX"=>"Luxembourg", "MAC"=>"Macao", "MKD"=>"Macedonia", "MDG"=>"Madagascar", "MWI"=>"Malawi", "MYS"=>"Malaysia", "MDV"=>"Maldives", "MLI"=>"Mali", "MLT"=>"Malta", "MHL"=>"Marshall Islands", "MTQ"=>"Martinique", "MRT"=>"Mauritania", "MUS"=>"Mauritius", "MYT"=>"Mayotte", "MEX"=>"Mexico", "FSM"=>"Micronesia, Federated States", "MDA"=>"Moldova", "MCO"=>"Monaco", "MNG"=>"Mongolia", "MNE"=>"Montenegro", "MSR"=>"Montserrat", "MAR"=>"Morocco", "MOZ"=>"Mozambique", "MMR"=>"Myanmar", "NAM"=>"Namibia", "NRU"=>"Nauru", "NPL"=>"Nepal", "NLD"=>"Netherlands", "ANT"=>"Netherlands Antilles", "NCL"=>"New Caledonia", "NZL"=>"New Zealand", "NIC"=>"Nicaragua", "NER"=>"Niger", "NGA"=>"Nigeria", "NIU"=>"Niue", "NFK"=>"Norfolk Island", "PRK"=>"North Korea", "MNP"=>"Northern Mariana Islands", "NOR"=>"Norway", "OMN"=>"Oman", "PAK"=>"Pakistan", "PLW"=>"Palau", "PSE"=>"Palestine", "PAN"=>"Panama", "PNG"=>"Papua New Guinea", "PRY"=>"Paraguay", "PER"=>"Peru", "PHL"=>"Philippines", "PCN"=>"Pitcairn", "POL"=>"Poland", "PRT"=>"Portugal", "PRI"=>"Puerto Rico", "QAT"=>"Qatar", "REU"=>"Réunion", "ROM"=>"Romania", "RUS"=>"Russia", "RWA"=>"Rwanda", "BLM"=>"Saint Barthelemy", "SHN"=>"Saint Helena", "KNA"=>"Saint Kitts and Nevis", "LCA"=>"Saint Lucia", "MAF"=>"Saint Martin", "SPM"=>"Saint Pierre and Miquelon", "VCT"=>"Saint Vincent and the Grenadines", "WSM"=>"Samoa", "SMR"=>"San Marino", "STP"=>"Sao Tome and Principe", "SAU"=>"Saudi Arabia", "SEN"=>"Senegal", "SRB"=>"Serbia", "SYC"=>"Seychelles", "SLE"=>"Sierra Leone", "SGP"=>"Singapore", "SXM"=>"Sint Maarten", "SVK"=>"Slovakia", "SVN"=>"Slovenia", "SLB"=>"Solomon Islands", "SOM"=>"Somalia", "ZAF"=>"South Africa", "SGS"=>"South Georgia and the South Sandwich Islands", "KOR"=>"South Korea", "SSD"=>"South Sudan", "ESP"=>"Spain", "LKA"=>"Sri Lanka", "SDN"=>"Sudan", "SUR"=>"Suriname", "SJM"=>"Svalbard and Jan Mayen", "SWZ"=>"Swaziland", "SWE"=>"Sweden", "CHE"=>"Switzerland", "SYR"=>"Syria", "TWN"=>"Taiwan", "TJK"=>"Tajikistan", "TZA"=>"Tanzania", "THA"=>"Thailand", "TGO"=>"Togo", "TKL"=>"Tokelau", "TON"=>"Tonga", "TTO"=>"Trinidad and Tobago", "TUN"=>"Tunisia", "TUR"=>"Turkey", "TKM"=>"Turkmenistan", "TCA"=>"Turks and Caicos Islands", "TUV"=>"Tuvalu", "UGA"=>"Uganda", "UKR"=>"Ukraine", "ARE"=>"United Arab Emirates", "GBR"=>"United Kingdom", "UMI"=>"United States Minor Outlying Islands", "URY"=>"Uruguay", "USA"=>"USA", "UZB"=>"Uzbekistan", "VUT"=>"Vanuatu", "VAT"=>"Vatican (Holy See)", "VEN"=>"Venezuela", "VNM"=>"Vietnam", "VGB"=>"Virgin Islands, British", "VIR"=>"Virgin Islands, U.S.", "WLF"=>"Wallis and Futuna", "ESH"=>"Western Sahara", "XKX"=>"Kosovo", "YEM"=>"Yemen", "ZMB"=>"Zambia", "ZWE"=>"Zimbabwe");
		
		if ($reverse) $result = array_search(ucwords(mb_strtolower($countryID)), $arr);
		elseif (isset($arr[strtoupper($countryID)])) $result = $arr[strtoupper($countryID)];
		
		if (!$result) $result = "";
		
		return $result;
	}
	

	/* G. Function convert_currency_live()
	 * 
	 * Currency Converter using Google Finance live exchange rates
	 * Example - convert_currency_live("EUR", "USD", 22.37) - convert 22.37euro to usd
				 convert_currency_live("EUR", "BTC", 22.37) - convert 22.37euro to bitcoin
	 */
	function convert_currency_live($from_Currency, $to_Currency, $amount)
	{
		$amount = urlencode($amount);
		$from_Currency = trim(strtoupper(urlencode($from_Currency)));
		$to_Currency = trim(strtoupper(urlencode($to_Currency)));

		if ($from_Currency == "TRL")  $from_Currency = "TRY"; // fix for Turkish Lyra
		if ($from_Currency == "ZWD")  $from_Currency = "ZWL"; // fix for Zimbabwe Dollar
		if ($from_Currency == "RIAL") $from_Currency = "IRR"; // fix for Iranian Rial
		
		$url = "https://www.google.com/finance/converter?a=".$amount."&from=".$from_Currency."&to=".$to_Currency;
	
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)");
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$data = explode('bld>', $rawdata);
		$data = explode($to_Currency, $data[1]);
	
		return round($data[0], ($to_Currency=="BTC"?5:2));
	}
	
	
	
	/* H. Function validate_gourlkey()
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
	
	

	/* I. Function run_sql()
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
			if (mysqli_connect_errno())
			{
				echo "<br /><b>Error. Can't connect to your MySQL server.</b> You need to have PHP 5.2+ and MySQL 5.5+ with mysqli extension activated. <a href='http://crybit.com/how-to-enable-mysqli-extension-on-web-server/'>Instruction &#187;</a>\n";
				if (!CRYPTOBOX_WORDPRESS) echo "<br />Also <b>please check DB username/password in file cryptobox.config.php</b>\n";
				die("<br />Server has returned error - <b>".mysqli_connect_error()."</b>");
			}
			$mysqli->query("SET NAMES utf8");
		}
	
		$query = $mysqli->query($sql);
	
		if ($query === FALSE) die("MySQL Error: ".$mysqli->error."; SQL: $sql");
		elseif (is_object($query) && $query->num_rows)
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
	
	
	// en - English, es - Spanish, fr - French, de - German, ru - Russian, nl - Dutch, pt - Portuguese, fa - Persian, ko - Korean, ar - Arabic, cn - Simplified Chinese, zh - Traditional Chinese, hi - Hindi
	$cryptobox_localisation	= array(
							"en" => array("name"		=> "English", 
							/*36*/	"button"			=> "Click Here if you have already sent %coinNames%",
							/*37*/	"msg_not_received" 	=> "<b>%coinNames% have not yet been received.</b><br>If you have already sent %coinNames% (the exact %coinName% sum in one payment as shown in the box below), please wait a few minutes to receive them by %coinName% Payment System. If you send any other sum, Payment System will ignore the transaction and you will need to send the correct sum again, or contact the site owner for assistance.",
							/*38*/	"msg_received" 	 	=> "%coinName% Payment System received %amountPaid% %coinLabel% successfully !",
							/*39*/	"msg_received2" 	=> "%coinName% Captcha received %amountPaid% %coinLabel% successfully !",
							/*40*/	"payment"			=> "Select Payment Method",
							/*42*/	"pay_in"			=> "Payment in %coinName%"),

							"es" => array("name"		=> "Spanish", 
									"button"			=> "Click aqui si ya has mandado %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% no han sido recibidos.</b><br>Si ya has enviado %coinNames% (la cantidad exacta de %coinName% en un s&oacute;lo pago como se muestra abajo), por favor espera unos minutos para recibirlas del %coinName% sistema de pagos. Si has enviado otra cantidad, el sistema de pagos ignorar&aacute; la transacci&oacute;n y necesitar&aacute;s mandar la cantidad correcta de nuevo, o contactar al propietario del sitio para recibir asistencia.",
									"msg_received" 	 	=> "%coinName% Sistema de pago recibido %amountPaid% %coinLabel% satisfactoriamente !",
									"msg_received2" 	=> "%coinName% Captcha recibido %amountPaid% %coinLabel% satisfactoriamente !",
									"payment"			=> "Seleccionar m&eacute;todo de pago",
									"pay_in"			=> "Pago en %coinName%"),
							
							"fr" => array("name"		=> "French",
									"button"			=> "Cliquez ici si vous avez d&eacute;j&agrave; envoy&eacute; vos %coinNames%",
									"msg_not_received" 	=> "<b>Les %coinNames% n'ont pas encore &eacute;t&eacute; re&ccedil;us.</b><br>Si vous avez d&eacute;j&agrave; envoy&eacute; les %coinNames% (la somme exacte en un seul paiement, comme indiqu&eacute; dans le cadre ci-dessous), Veuillez s'il vous pla&icirc;t attendre quelques minutes le temps que le syst&egrave;me re&ccedil;oive votre paiement en %coinName%. Si vous envoyez toute autre somme, le syst&egrave;me de paiement n'identifiera pas la transaction et vous devrez &agrave; nouveau envoyer la somme correcte, ou contacter le propri&eacute;taire du site via l'assistance.",
									"msg_received" 	 	=> "Le syst&egrave;me de paiement %coinName% a re&ccedil;u %amountPaid% %coinLabel% avec succ&egrave;s !",
									"msg_received2" 	=> "Le %coinName% Captcha a re&ccedil;u %amountPaid% %coinLabel% avec succ&egrave;s !",
									"payment"			=> "S&eacute;lectionnez la m&eacute;thode de paiement",
									"pay_in"			=> "Paiement en %coinName%"),

							"de" => array("name"		=> "German", 
									"button"			=> "Klicke hier wenn du schon %coinNames% gesendet hast",
									"msg_not_received" 	=> "<b>%coinNames% wurden bis jetzt noch nicht empfangen.</b><br>Wenn du bereits %coinNames% gesendet hast (der exakte %coinName% Betrag f&uuml;r die Zahlung steht in der Box unten) warte bitte ein paar Minuten bis das %coinName% System die Zahlung erhalten hat. Wenn du einen anderen Betrag sendest ignoriert das System die Transaktion und du musst den korrekten Betrag erneut senden, oder den Besitzer der Website kontaktieren um Hilfe zu erhalten.",
									"msg_received" 	 	=> "%coinName% Bezahlsystem hat %amountPaid% %coinLabel% erfolgreich erhalten !",
									"msg_received2" 	=> "%coinName% Captcha hat %amountPaid% %coinLabel% erfolgreich erhalten !",
									"payment"			=> "Zahlungmethode ausw&auml;hlen",
									"pay_in"			=> "Zahlung in %coinName%"),
									
							"ru" => array("name"		=> "Russian",
									"button"			=> "&#1053;&#1072;&#1078;&#1084;&#1080;&#1090;&#1077; &#1079;&#1076;&#1077;&#1089;&#1100; &#1077;&#1089;&#1083;&#1080; &#1074;&#1099; &#1091;&#1078;&#1077; &#1087;&#1086;&#1089;&#1083;&#1072;&#1083;&#1080; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#1085;&#1077; &#1087;&#1086;&#1083;&#1091;&#1095;&#1077;&#1085;&#1099; &#1077;&#1097;&#1105;.</b><br>&#1045;&#1089;&#1083;&#1080; &#1074;&#1099; &#1091;&#1078;&#1077; &#1087;&#1086;&#1089;&#1083;&#1072;&#1083;&#1080; %coinNames% (&#1090;&#1086;&#1095;&#1085;&#1091;&#1102; &#1089;&#1091;&#1084;&#1084;&#1091; %coinName% &#1086;&#1076;&#1085;&#1080;&#1084; &#1087;&#1083;&#1072;&#1090;&#1077;&#1078;&#1105;&#1084; &#1082;&#1072;&#1082; &#1087;&#1086;&#1082;&#1072;&#1079;&#1072;&#1085;&#1086; &#1085;&#1080;&#1078;&#1077;), &#1087;&#1086;&#1078;&#1072;&#1083;&#1091;&#1081;&#1089;&#1090;&#1072; &#1087;&#1086;&#1076;&#1086;&#1078;&#1076;&#1080;&#1090;&#1077; &#1085;&#1077;&#1089;&#1082;&#1086;&#1083;&#1100;&#1082;&#1086; &#1084;&#1080;&#1085;&#1091;&#1090; &#1076;&#1083;&#1103; &#1087;&#1086;&#1083;&#1091;&#1095;&#1077;&#1085;&#1080;&#1103; &#1080;&#1093; %coinName% &#1087;&#1083;&#1072;&#1090;&#1105;&#1078;&#1085;&#1086;&#1081; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1086;&#1081;. &#1045;&#1089;&#1083;&#1080; &#1074;&#1099; &#1087;&#1086;&#1089;&#1083;&#1072;&#1083;&#1080; &#1083;&#1102;&#1073;&#1091;&#1102; &#1076;&#1088;&#1091;&#1075;&#1091;&#1102; &#1089;&#1091;&#1084;&#1084;&#1091;, &#1087;&#1083;&#1072;&#1090;&#1105;&#1078;&#1085;&#1072;&#1103; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1072; &#1073;&#1091;&#1076;&#1077;&#1090; &#1080;&#1075;&#1085;&#1086;&#1088;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100; &#1101;&#1090;&#1086; &#1080; &#1074;&#1072;&#1084; &#1085;&#1091;&#1078;&#1085;&#1086; &#1073;&#1091;&#1076;&#1077;&#1090; &#1087;&#1086;&#1089;&#1083;&#1072;&#1090;&#1100; &#1087;&#1088;&#1072;&#1074;&#1080;&#1083;&#1100;&#1085;&#1091;&#1102; &#1089;&#1091;&#1084;&#1084;&#1091; &#1086;&#1087;&#1103;&#1090;&#1100;, &#1080;&#1083;&#1080; &#1089;&#1074;&#1103;&#1078;&#1080;&#1090;&#1077;&#1089;&#1100; &#1089; &#1074;&#1083;&#1072;&#1076;&#1077;&#1083;&#1100;&#1094;&#1077;&#1084; &#1089;&#1072;&#1081;&#1090;&#1072; &#1076;&#1083;&#1103; &#1087;&#1086;&#1084;&#1086;&#1097;&#1080;",
									"msg_received" 	 	=> "%coinName% &#1087;&#1083;&#1072;&#1090;&#1105;&#1078;&#1085;&#1072;&#1103; &#1089;&#1080;&#1089;&#1090;&#1077;&#1084;&#1072; &#1087;&#1086;&#1083;&#1091;&#1095;&#1080;&#1083;&#1072; %amountPaid% %coinLabel% &#1091;&#1089;&#1087;&#1077;&#1096;&#1085;&#1086; !",
									"msg_received2" 	=> "%coinName% &#1082;&#1072;&#1087;&#1095;&#1072; &#1087;&#1086;&#1083;&#1091;&#1095;&#1080;&#1083;&#1072; %amountPaid% %coinLabel% &#1091;&#1089;&#1087;&#1077;&#1096;&#1085;&#1086; !",
									"payment"			=> "&#1042;&#1099;&#1073;&#1077;&#1088;&#1080;&#1090;&#1077; &#1089;&#1087;&#1086;&#1089;&#1086;&#1073; &#1086;&#1087;&#1083;&#1072;&#1090;&#1099;",
									"pay_in"			=> "&#1054;&#1087;&#1083;&#1072;&#1090;&#1072; &#1074; %coinName%"),
									
							"pl" => array("name"		=> "Polish", 
									"button"			=> "Kliknij tutaj, je&#347;li ju&#380; wys&#322;ane %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% nie zosta&#322;y jeszcze otrzymane.</b><br>Je&#347;li ju&#380; wys&#322;a&#322;e&#347; %coinNames% (dok&#322;adn&#261; sum&#281; %coinName% w jednej p&#322;atno&#347;ci, jak pokazano w poni&#380;szym polu), prosz&#281; poczeka&#263; kilka minut, aby system p&#322;atno&#347;ci %coinName% m&#243;g&#322; j&#261; otrzyma&#263;. Je&#347;li wy&#347;lesz jak&#261;kolwiek inn&#261; sum&#281;, system p&#322;atno&#347;ci zignoruje transakcje i trzeba b&#281;dzie wys&#322;a&#263; poprawn&#261; sum&#281; ponownie lub skontaktowa&#263; si&#281; z w&#322;a&#347;cicielem witryny w celu uzyskania pomocy.",
									"msg_received" 	 	=> "System p&#322;atno&#347;ci %coinName% otrzyma&#322; %amountPaid% %coinLabel% pomy&#347;lnie !",
									"msg_received2" 	=> "%coinName% Captcha otrzyma&#322; %amountPaid% %coinLabel% pomy&#347;lnie !",
									"payment"			=> "Wybierz metod&#281; p&#322;atno&#347;&#263;i",
									"pay_in"			=> "P&#322;atno&#347;&#263; w %coinName%"),

							"nl" => array("name"		=> "Dutch",
									"button"			=> "Klik hier als je al %coinNames% hebt verstuurd",
									"msg_not_received" 	=> "<b>%coinNames% zijn nog niet ontvangen.</b><br>Als je al %coinNames% verstuurd hebt, (het exacte bedrag in %coinName% staat in het vak hieronder), wacht dan a.u.b. een paar minuten tot ze ontvangen zijn door het %coinName% Betaal Systeem. Als u een ander bedrag verstuurd, zal de transactie worden genegeerd, u zult dan alsnog het correcte bedrag moeten overmaken of contact opnemen met de site beheerder voor verdere assistentie.",
									"msg_received" 	 	=> "%coinName% Betaal Systeem heeft %amountPaid% %coinLabel% succesvol ontvangen !",
									"msg_received2" 	=> "%coinName% Captcha Systeem heeft %amountPaid% %coinLabel% succesvol ontvangen !",
									"payment"			=> "Kies uw betaalmethode",
									"pay_in"			=> "Betaling in %coinName%"),

							"pt" => array("name"		=> "Portuguese",
									"button"			=> "Se ja enviou %coinNames% clique aqui",
									"msg_not_received" 	=> "<b>Os %coinNames% ainda n&#227;o foram recebidos.</b><br>Se j&#225; enviou %coinNames% (a soma exata de %coinName% num s&#243; pagamento, como mostrado na caixa abaixo), por favor, espere alguns minutos para o sistema de pagamentos %coinName% os receber. Se enviar qualquer outro montante, o sistema de pagamentos ir&#225; ignorar a transa&#231;&#227;o e ter&#225; que enviar a soma correta novamente; ou entre em contato com o propriet&#225;rio do site para assist&#234;ncia.",
									"msg_received" 	 	=> "O sistema de pagamentos %coinName% recebeu %amountPaid% %coinLabel% com sucesso !",
									"msg_received2" 	=> "%coinName% Captcha recebeu %amountPaid% %coinLabel% com sucesso !",
									"payment"			=> "Selecione o metodo de pagamento",
									"pay_in"			=> "Pagamento em %coinName%"),
                	         
							"fa" => array("name"		=> "Persian",
									"button"			=> "&#1575;&#1711;&#1585; &#1588;&#1605;&#1575; &#1575;&#1586; &#1602;&#1576;&#1604; &#1575;&#1585;&#1587;&#1575;&#1604; %coinName% &#1575;&#1610;&#1606;&#1580;&#1575; &#1585;&#1575; &#1705;&#1604;&#1610;&#1705; &#1705;&#1606;&#1610;&#1583;",
									"msg_not_received" 	=> "<b>%coinNames% &#1607;&#1606;&#1608;&#1586; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1606;&#1588;&#1583;&#1607; &#1575;&#1587;&#1578; </b><br> &#1575;&#1711;&#1585; &#1588;&#1605;&#1575; &#1602;&#1576;&#1604;&#1575; &#1575;&#1585;&#1587;&#1575;&#1604; &#1705;&#1585;&#1583;&#1610;&#1583; %coinNames% ,&#1576;&#1607; &#1589;&#1608;&#1585;&#1578; &#1583;&#1602;&#1610;&#1602; %coinName% &#1605;&#1580;&#1605;&#1608;&#1593; &#1583;&#1585; &#1610;&#1705; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1607;&#1605;&#1575;&#1606;&#1711;&#1608;&#1606;&#1607; &#1705;&#1607; &#1583;&#1585; &#1705;&#1575;&#1583;&#1585; &#1586;&#1610;&#1585; &#1606;&#1588;&#1575;&#1606; &#1583;&#1575;&#1583;&#1607; &#1588;&#1583;&#1607; &#1575;&#1587;&#1578; , &#1604;&#1591;&#1601;&#1575; &#1670;&#1606;&#1583; &#1583;&#1602;&#1610;&#1602;&#1607; &#1576;&#1585;&#1575;&#1610; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1575;&#1586; &#1591;&#1585;&#1601; %coinName% &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1587;&#1610;&#1587;&#1578;&#1605; &#1589;&#1576;&#1585; &#1705;&#1606;&#1610;&#1583;. &#1575;&#1711;&#1585; &#1588;&#1605;&#1575; &#1607;&#1585; &#1711;&#1608;&#1606;&#1607; &#1605;&#1580;&#1605;&#1608;&#1593; &#1583;&#1610;&#1711;&#1585;&#1610; &#1575;&#1586; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1585;&#1575; &#1601;&#1585;&#1587;&#1578;&#1575;&#1583;&#1607; &#1575;&#1610;&#1583;, &#1587;&#1610;&#1587;&#1578;&#1605; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1605;&#1593;&#1575;&#1605;&#1604;&#1607; &#1585;&#1575; &#1606;&#1575;&#1583;&#1610;&#1583;&#1607; &#1605;&#1610; &#1711;&#1610;&#1585;&#1583; &#1608; &#1588;&#1605;&#1575; &#1606;&#1610;&#1575;&#1586; &#1576;&#1607; &#1575;&#1585;&#1587;&#1575;&#1604; &#1605;&#1580;&#1605;&#1608;&#1593; &#1583;&#1585;&#1587;&#1578;&#1610; &#1705;&#1607; &#1584;&#1705;&#1585; &#1588;&#1583; &#1583;&#1575;&#1585;&#1610;&#1583;, &#1610;&#1575; &#1576;&#1575; &#1583;&#1575;&#1585;&#1606;&#1583;&#1607; &#1587;&#1575;&#1610;&#1578; &#1576;&#1585;&#1575;&#1610; &#1705;&#1605;&#1705; &#1608; &#1578;&#1608;&#1590;&#1610;&#1581;&#1575;&#1578; &#1576;&#1610;&#1588;&#1578;&#1585; &#1578;&#1605;&#1575;&#1587; &#1576;&#1711;&#1610;&#1585;&#1610;&#1583;.",
									"msg_received" 	 	=> "%coinName% &#1587;&#1610;&#1587;&#1578;&#1605; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; %amountPaid% %coinLabel% &#1585;&#1575; &#1576;&#1575; &#1605;&#1608;&#1601;&#1602;&#1610;&#1578; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1705;&#1585;&#1583; !",
									"msg_received2" 	=> "%coinName% &#1705;&#1662;&#1670;&#1575; %amountPaid% %coinLabel% &#1585;&#1575; &#1576;&#1575; &#1605;&#1608;&#1601;&#1602;&#1610;&#1578; &#1583;&#1585;&#1610;&#1575;&#1601;&#1578; &#1705;&#1585;&#1583; !",
									"payment"			=> "&#1585;&#1608;&#1588; &#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1585;&#1575; &#1575;&#1606;&#1578;&#1582;&#1575;&#1576; &#1705;&#1606;&#1610;&#1583;",
									"pay_in"			=> "&#1662;&#1585;&#1583;&#1575;&#1582;&#1578; &#1583;&#1585; %coinName%"),
	    
							"ko" => array("name"		=> "Korean",
									"button"			=> "&#47564;&#50557; %coinName% &#51060;&#48120; &#48372;&#45256;&#45796;&#47732; &#50668;&#44592;&#47484; &#53364;&#47533;&#54616;&#49464;&#50836;",
									"msg_not_received" 	=> "<b>%coinNames% &#50500;&#51649; &#48155;&#51648; &#47803;&#54664;&#49845;&#45768;&#45796;.</b><br>&#47564;&#50557; &#45817;&#49888;&#51060; &#51060;&#48120; %coinNames% &#51012; &#48372;&#45256;&#45796;&#47732; (&#50500;&#47000; &#48149;&#49828;&#50504;&#50640; &#48372;&#50668;&#51648;&#45716; &#54616;&#45208;&#51032; &#44208;&#51228; &#45236;&#50640; &#50668;&#48516;&#51032; %coinName% &#51032; &#54633;&#44228;), &#44208;&#51228; &#49884;&#49828;&#53596;&#51060; &#51652;&#54665;&#46104;&#45716; &#46041;&#50504; &#51104;&#49884;&#47564; &#44592;&#45796;&#47140;&#51452;&#49464;&#50836;. &#47564;&#50557; &#45817;&#49888;&#51060; &#54633;&#44228;&#50640; &#48372;&#50668;&#51648;&#45716; &#44163;&#44284; &#45796;&#47480; &#49688;&#47049;&#51032; &#48708;&#53944;&#53076;&#51064;&#51012; &#48372;&#45256;&#45796;&#47732;, &#44208;&#51228; &#49884;&#49828;&#53596;&#51008; &#54644;&#45817; &#44144;&#47000;&#47484; &#47924;&#49884;&#54616;&#47728;, &#45817;&#49888;&#51008; &#45796;&#49884; &#50732;&#48148;&#47480; &#54633;&#44228;&#47564;&#53372;&#51032; &#48708;&#53944;&#53076;&#51064;&#51012; &#48372;&#45236;&#44144;&#45208; &#46020;&#50880;&#51012; &#51460; &#49688; &#51080;&#45716; &#49324;&#51060;&#53944; &#44288;&#47532;&#51088;&#50640;&#44172; &#50672;&#46973;&#54644;&#50556; &#54633;&#45768;&#45796;.",
									"msg_received" 	 	=> "%coinName% &#44208;&#51228; &#49884;&#49828;&#53596;&#51060; %amountPaid% %coinLabel% &#47484; &#49457;&#44277;&#51201;&#51004;&#47196; &#48155;&#50520;&#49845;&#45768;&#45796; !",
									"msg_received2" 	=> "%coinName% &#52897;&#52320;&#44032; %amountPaid% %coinLabel% &#47484; &#49457;&#44277;&#51201;&#51004;&#47196; &#48155;&#50520;&#49845;&#45768;&#45796; !",
									"payment"			=> "&#44208;&#51228; &#48169;&#48277; &#49440;&#53469;",
									"pay_in"			=> "%coinName% &#51648;&#44553;"),

							"ja" => array("name"		=> "Japanese", 
									"button"			=> "%coinNames%&#12434;&#36865;&#20449;&#28168;&#12398;&#22580;&#21512;&#12399;&#12289;&#12371;&#12385;&#12425;&#12434;&#12463;&#12522;&#12483;&#12463;&#12375;&#12390;&#12367;&#12384;&#12373;&#12356;",
									"msg_not_received" 	=> "<b>%coinNames%&#12398;&#21463;&#21462;&#12399;&#23436;&#20102;&#12375;&#12390;&#12356;&#12414;&#12379;&#12435;&#12290;</b><br>%coinNames%&#65288;&#19979;&#35352;&#12395;&#34920;&#31034;&#12373;&#12428;&#12390;&#12356;&#12427;&#12385;&#12423;&#12358;&#12393;&#12398;%coinNames%&#12434;1&#22238;&#12398;&#12488;&#12521;&#12531;&#12470;&#12463;&#12471;&#12519;&#12531;&#12392;&#12375;&#12390;&#65289;&#12434;&#12377;&#12391;&#12395;&#36865;&#12387;&#12383;&#22580;&#21512;&#12399;&#12289;%coinName%&#27770;&#28168;&#12471;&#12473;&#12486;&#12512;&#12363;&#12425;&#25968;&#20998;&#20197;&#20869;&#12395;&#30906;&#35469;&#12364;&#12354;&#12426;&#12414;&#12377;&#12290;&#25351;&#23450;&#20197;&#19978;&#12398;%coinNames%&#12434;&#36865;&#12387;&#12383;&#22580;&#21512;&#12399;&#12289;&#12471;&#12473;&#12486;&#12512;&#12395;&#21453;&#26144;&#12373;&#12428;&#12414;&#12379;&#12435;&#12398;&#12391;&#12289;&#12418;&#12358;&#19968;&#24230;&#12420;&#12426;&#30452;&#12377;&#12363;&#12289;&#12454;&#12455;&#12502;&#12469;&#12452;&#12488;&#31649;&#29702;&#32773;&#12408;&#12362;&#21839;&#21512;&#12379;&#12367;&#12384;&#12373;&#12356;&#12290;&#19975;&#12364;&#19968;&#12289;&#36865;&#20449;&#28168;&#12415;&#12398;&#22580;&#21512;&#12399;&#12289;%coinName%&#27770;&#28168;&#12471;&#12473;&#12486;&#12512;&#12363;&#12425;&#12398;&#30906;&#35469;&#12434;&#24453;&#12387;&#12390;&#12367;&#12384;&#12373;&#12356;&#12290;",
									"msg_received" 	 	=> "%coinName%&#27770;&#28168;&#12471;&#12473;&#12486;&#12512;&#12391; %amountPaid% %coinLabel% &#12398;&#27770;&#28168;&#12364;&#23436;&#20102;&#12375;&#12414;&#12375;&#12383; !",
									"msg_received2" 	=> "%coinName%&#12461;&#12515;&#12502;&#12481;&#12515;&#12391; %amountPaid% %coinLabel% &#12398;&#27770;&#28168;&#12364;&#23436;&#20102;&#12375;&#12414;&#12375;&#12383; !",
									"payment"			=> "&#27770;&#28168;&#26041;&#27861;&#12434;&#36984;&#25246;",
									"pay_in"			=> "%coinName%&#12391;&#12398;&#27770;&#28168;"),
									
							"id" => array("name"		=> "Indonesian", 
									"button"			=> "Klik disini jika anda telah mengirim %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% belum diterima.</b><br>Jika kamu sudah mengirim %coinNames% (sejumlah %coinNames% dengan jumlah yang tepat seperti pada kotak dibawah),  silahkan tunggu beberapa menit untuk menerima %coinName% lewat sistem pembayaran. Jika anda mengirim sejumlah lain, sistem pembayaran akan mengabaikan transaksinya dan anda perlu mengirim dengan jumlah yang tepat lagi, atau kontak pemilik web untuk membantu.",
									"msg_received" 	 	=> "%coinName% Sistem Pembayaran menerima %amountPaid% %coinLabel% dengan sukses !",
									"msg_received2" 	=> "%coinName% Captcha menerima %amountPaid% %coinLabel% dengan sukses !",
									"payment"			=> "Pilih Metode Pembayaran",
									"pay_in"			=> "Pembayaran dalam bentuk %coinName%"),

							"ar" => array("name"		=> "Arabic",
									"button"			=> "&#1575;&#1590;&#1594;&#1591; &#1607;&#1606;&#1575; &#1601;&#1610; &#1581;&#1575;&#1604;&#1577; &#1602;&#1605;&#1578; &#1601;&#1593;&#1604;&#1575;&#1611; &#1576;&#1575;&#1604;&#1575;&#1585;&#1587;&#1575;&#1604; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#1604;&#1605; &#1610;&#1578;&#1605; &#1575;&#1587;&#1578;&#1604;&#1575;&#1605;&#1607;&#1575; &#1576;&#1593;&#1583;.</b><br> &#1573;&#1584;&#1575; &#1602;&#1605;&#1578; &#1576;&#1573;&#1585;&#1587;&#1575;&#1604;&#1607;&#1575; %coinNames% (&#1576;&#1575;&#1604;&#1592;&#1576;&#1591; %coinName% &#1605;&#1576;&#1604;&#1594; &#1601;&#1610; &#1583;&#1601;&#1593; &#1608;&#1575;&#1581;&#1583;), &#1610;&#1585;&#1580;&#1609; &#1575;&#1604;&#1573;&#1606;&#1578;&#1592;&#1575;&#1585; &#1576;&#1590;&#1593; &#1583;&#1602;&#1575;&#1574;&#1602; &#1604;&#1573;&#1587;&#1578;&#1604;&#1575;&#1605;&#1607;&#1605; &#1605;&#1606; &#1582;&#1604;&#1575;&#1604; %coinName% &#1606;&#1592;&#1575;&#1605; &#1575;&#1604;&#1583;&#1601;&#1593;. &#1573;&#1584;&#1575; &#1602;&#1605;&#1578; &#1576;&#1573;&#1585;&#1587;&#1575;&#1604; &#1605;&#1576;&#1575;&#1604;&#1594; &#1571;&#1582;&#1585;&#1609;, &#1606;&#1592;&#1575;&#1605; &#1575;&#1604;&#1583;&#1601;&#1593; &#1587;&#1608;&#1601; &#1610;&#1580;&#1575;&#1607;&#1604; &#1575;&#1604;&#1589;&#1601;&#1602;&#1577;&#1548; &#1608;&#1587;&#1608;&#1601; &#1578;&#1581;&#1578;&#1575;&#1580; &#1604;&#1573;&#1585;&#1587;&#1575;&#1604; &#1575;&#1604;&#1605;&#1576;&#1604;&#1594; &#1575;&#1604;&#1589;&#1581;&#1610;&#1581; &#1605;&#1585;&#1577; &#1571;&#1582;&#1585;&#1609;",
									"msg_received" 	 	=> "%coinName% &#1578;&#1605; &#1575;&#1587;&#1578;&#1604;&#1575;&#1605; &#1575;&#1604;&#1605;&#1576;&#1604;&#1594; %amountPaid% %coinLabel% &#1576;&#1606;&#1580;&#1575;&#1581; !",
									"msg_received2" 	=> "%coinName% &#1578;&#1605; &#1575;&#1587;&#1578;&#1604;&#1575;&#1605; &#1575;&#1604;&#1603;&#1575;&#1576;&#1578;&#1588;&#1575; %amountPaid% %coinLabel% &#1576;&#1606;&#1580;&#1575;&#1581; !",
									"payment"			=> "&#1575;&#1582;&#1578;&#1585; &#1591;&#1585;&#1610;&#1602;&#1577; &#1575;&#1604;&#1583;&#1601;&#1593;",
									"pay_in"			=> "&#1583;&#1601;&#1593; &#1601;&#1610; %coinName%"),
												
							"cn" => array("name"		=> "Chinese Simplified",
									"button"			=> "&#28857;&#20987;&#27492;,&#22914;&#26524;&#20320;&#24050;&#32463;&#21457;&#36865;&#20102; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#36824;&#27809;&#26377;&#25910;&#21040;&#12290;</b><br>&#22914;&#26524;&#20320;&#24050;&#32463;&#21457;&#36865; %coinNames% (&#20351;&#29992;&#20102;&#31934;&#30830;&#25968;&#37327;,&#22914;&#19979;&#26694;&#20013;&#26174;&#31034;&#30340;&#37027;&#26679;)&#65292;&#35831;&#31561;&#24453; &#20960;&#20998;&#38047;, &#31995;&#32479;&#22312;&#23436;&#25104; %coinName% &#30340;&#25509;&#25910;&#22788;&#29702;&#12290;&#22914;&#26524;&#20320;&#21457;&#36865;&#20854;&#23427;&#25968;&#37327;&#65292;&#25903;&#20184;&#31995;&#32479;&#23558;&#24573;&#30053;&#20320;&#30340;&#20132;&#26131;&#12290;&#20320;&#24517;&#39035;&#20351;&#29992;&#31934;&#30830;&#25968;&#37327;&#12290;",
									"msg_received" 	 	=> "%coinName% &#25903;&#20184;&#31995;&#32479;&#25104;&#21151;&#25509;&#25910;&#20102; %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% &#39564;&#35777;&#30721;&#24050;&#25509;&#25910;&#65292; %amountPaid% %coinLabel% &#25104;&#21151; !",
									"payment"			=> "&#36873;&#25321;&#20184;&#27454;&#26041;&#24335;",
									"pay_in"			=> "&#25903;&#20184; %coinName%"),
									
							"zh" => array("name"		=> "Chinese Traditional",
									"button"			=> "&#40670;&#25802;&#27492;,&#22914;&#26524;&#20320;&#24050;&#32147;&#30332;&#36865;&#20102; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#36996;&#27794;&#26377;&#25910;&#21040;&#12290;</b><br>&#22914;&#26524;&#20320;&#24050;&#32147;&#30332;&#36865; %coinNames% (&#20351;&#29992;&#20102;&#31934;&#30906;&#25976;&#37327;,&#22914;&#19979;&#26694;&#20013;&#39023;&#31034;&#30340;&#37027;&#27171;)&#65292;&#35531;&#31561;&#24453;&#24190;&#20998;&#37758;,&#31995;&#32113;&#22312;&#23436;&#25104; %coinName% &#30340;&#25509;&#25910;&#34389;&#29702;&#12290;&#22914;&#26524;&#20320;&#30332;&#36865;&#20854;&#23427;&#25976;&#37327;&#65292;&#25903;&#20184;&#31995;&#32113;&#23559;&#24573;&#30053;&#20320;&#30340;&#20132;&#26131;&#12290;&#20320;&#24517;&#38920;&#20351;&#29992;&#31934;&#30906;&#25976;&#37327;&#12290;",
									"msg_received" 	 	=> "%coinName% &#25903;&#20184;&#31995;&#32113;&#25104;&#21151;&#25509;&#25910;&#20102; %amountPaid% %coinLabel% !",
									"msg_received2" 	=> "%coinName% &#39511;&#35657;&#30908;&#24050;&#25509;&#25910;&#65292;%amountPaid% %coinLabel% &#25104;&#21151; !",
									"payment"			=> "&#36984;&#25799;&#20184;&#27454;&#26041;&#24335;",
									"pay_in"			=> "&#25903;&#20184; %coinName%"),

							"hi" => array("name"		=> "Hindi",
									"button"			=> "&#2310;&#2346; &#2346;&#2361;&#2354;&#2375; &#2360;&#2375; &#2361;&#2368; &#2349;&#2375;&#2332;&#2375; &#2361;&#2376;&#2306; &#2340;&#2379; &#2351;&#2361;&#2366;&#2306; &#2325;&#2381;&#2354;&#2367;&#2325; &#2325;&#2352;&#2375;&#2306; %coinNames%",
									"msg_not_received" 	=> "<b>%coinNames% &#2325;&#2368; &#2309;&#2349;&#2368; &#2340;&#2325; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; &#2344;&#2361;&#2368;&#2306; &#2325;&#2367;&#2351;&#2366; &#2327;&#2351;&#2366; &#2361;&#2376;.</b><br>&#2344;&#2368;&#2330;&#2375; &#2342;&#2367;&#2319; &#2327;&#2319; &#2348;&#2377;&#2325;&#2381;&#2360; &#2350;&#2375;&#2306; &#2342;&#2367;&#2326;&#2366;&#2351;&#2366; &#2327;&#2351;&#2366; &#2361;&#2376; &#2319;&#2325; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2350;&#2375;&#2306; &#2360;&#2335;&#2368;&#2325; %coinNames% &#2352;&#2366;&#2358;&#2367; &#2351;&#2342;&#2367; &#2310;&#2346; &#2344;&#2375; &#2346;&#2361;&#2354;&#2375; &#2360;&#2375; &#2361;&#2368; %coinName% &#2349;&#2375;&#2332;&#2366; &#2361;&#2376;, &#2340;&#2379; %coinName% &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2346;&#2381;&#2352;&#2339;&#2366;&#2354;&#2368; &#2360;&#2375; &#2313;&#2344;&#2381;&#2361;&#2375;&#2306; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; &#2325;&#2352;&#2344;&#2375; &#2325;&#2375; &#2354;&#2367;&#2319; &#2325;&#2369;&#2331; &#2361;&#2368; &#2350;&#2367;&#2344;&#2335;&#2379;&#2306; &#2325;&#2371;&#2346;&#2351;&#2366; &#2346;&#2381;&#2352;&#2340;&#2368;&#2325;&#2381;&#2359;&#2366; &#2325;&#2352;&#2375;&#2306;. &#2310;&#2346; &#2346;&#2361;&#2354;&#2375; &#2360;&#2375; &#2361;&#2368; &#2325;&#2367;&#2360;&#2368; &#2309;&#2344;&#2381;&#2351; &#2352;&#2366;&#2358;&#2367; &#2349;&#2375;&#2332;&#2344;&#2375; &#2325;&#2368; &#2361;&#2376;, &#2340;&#2379; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2346;&#2381;&#2352;&#2339;&#2366;&#2354;&#2368; &#2354;&#2375;&#2344;-&#2342;&#2375;&#2344; &#2346;&#2352; &#2343;&#2381;&#2351;&#2366;&#2344; &#2344;&#2361;&#2368;&#2306; &#2342;&#2375;&#2327;&#2366; &#2324;&#2352; &#2310;&#2346; &#2347;&#2367;&#2352; &#2360;&#2375; &#2360;&#2361;&#2368; &#2352;&#2366;&#2358;&#2367; &#2349;&#2375;&#2332;&#2344;&#2375; &#2325;&#2368; &#2332;&#2352;&#2370;&#2352;&#2340; &#2361;&#2379;&#2327;&#2368;.",
									"msg_received" 	 	=> "%coinName% &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2346;&#2381;&#2352;&#2339;&#2366;&#2354;&#2368; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; %amountPaid% %coinLabel% &#2360;&#2347;&#2354;&#2340;&#2366;&#2346;&#2370;&#2352;&#2381;&#2357;&#2325; !",
									"msg_received2" 	=> "%coinName% &#2325;&#2376;&#2346;&#2381;&#2330;&#2366; &#2346;&#2381;&#2352;&#2366;&#2346;&#2381;&#2340; %amountPaid% %coinLabel% &#2360;&#2347;&#2354;&#2340;&#2366;&#2346;&#2370;&#2352;&#2381;&#2357;&#2325; !",
									"payment"			=> "&#2330;&#2369;&#2344;&#2375;&#2306; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344; &#2325;&#2366; &#2340;&#2352;&#2368;&#2325;&#2366;",
									"pay_in"			=> "%coinName% &#2350;&#2375;&#2306; &#2349;&#2369;&#2327;&#2340;&#2366;&#2344;")
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