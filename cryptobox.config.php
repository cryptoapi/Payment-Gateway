<?
/**
 *  YOUR MYSQL DATABASE DETAILS
 */

 define("DB_HOSTNAME", 	"localhost");				// hostname
 define("DB_USERNAME", 	"-Please Enter Username-");		// database username
 define("DB_PASSWORD", 	"-Please Enter Password-");		// database password
 define("DB_DATABASE", 	"-Please Enter Database Name-");	// database name




/**
 *  ARRAY OF ALL YOUR CRYPTOBOX PRIVATE KEYS
 *  Place values from your gourl.io signup page 
 *  array("your_privatekey_for_box1", "your_privatekey_for_box2 (otional), etc...");
 */

 $cryptobox_private_keys = array();	
 


 
 define("CRYPTOBOX_PRIVATE_KEYS", implode("^", $cryptobox_private_keys));
 unset($cryptobox_private_keys);

?>