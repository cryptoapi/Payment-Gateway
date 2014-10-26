
GoUrl.io Cryptocoin Payment Gateway API
-----------------------------------------

Version 1.1

**Accept Bitcoin, Litecoin, Dogecoin, Speedcoin, Darkcoin, Vertcoin, Reddcoin, Feathercoin, Vericoin, Potcoin Payments Online on your website**

# ![Payment-Gateway](https://gourl.io/images/gateway.jpg)


Our Payment Gateway with Instant Checkout allows you to easily organise your website -

* 100% Free Open Source on Github.com
* No Monthly Fee, Transaction Fee from 0%
* [Pay-Per-Product](https://gourl.io/lib/examples/pay-per-product-multi.php) - sell your products for bitcoin, dogecoin, litecoin, etc. online on your website. It is easy!
* [Pay-Per-Download](https://gourl.io/lib/examples/pay-per-download-multi.php) -  make money on file downloads/other digital content from your website online
* [Pay-Per-Post](https://gourl.io/lib/examples/pay-per-post-multi.php) - get separate payments for each post/article published on your website
* [Pay-Per-Registration](https://gourl.io/lib/examples/pay-per-registration-multi.php) - earn money on user registration on your website; stop spam
* [Pay-Per-Page-Access](https://gourl.io/lib/examples/pay-per-page-multi.php) - sell paid access to selected web page(es) to unregistered visitors online
* [Pay-Per-Membership](https://gourl.io/lib/examples/pay-per-membership-multi.php) - sell monthly/daily membership of your website to members online
* Set your own Prices in USD. It will automatically convert usd to cryptocoins using Live [exchange rates](https://cryptsy.com/)
* Direct Integration on your website (iframe), no external payment pages opens (as other payment gateways offer)
* User will see successful payment result typically within 5 seconds after the payment has been sent
* Your website users and visitors will see GoUrl payment box on your website in their own native languages
* Our Payment Gateway supports the following interface languages: [English](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=en#gourlcryptolang), [French](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=fr#gourlcryptolang), [Russian](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=ru#gourlcryptolang), [Arabic](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=ar#gourlcryptolang), [Simplified Chinese](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=cn#gourlcryptolang), [Traditional Chinese](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=zh#gourlcryptolang), [Hindi](https://gourl.io/bitcoin-payment-gateway-api.html?gourlcryptolang=hi#gourlcryptolang). We can also add any new language to payment system on [request](http://gourl.local/cryptocoin_payment_api.html#lan)
* Global, Anonymous, Secure, Zero Risk, No Chargebacks, No visitor registration is needed.



Information
------------------------------------

Source: [https://github.com/cryptoapi/Payment-Gateway](https://github.com/cryptoapi/Payment-Gateway)

Copyright &copy; 2014 [Delta Consultants](https://gourl.io)

Website: [https://gourl.io](https://gourl.io)

API: [https://gourl.io/cryptocoin_payment_api.html](https://gourl.io/cryptocoin_payment_api.html)

Demo: [https://gourl.io/bitcoin-payment-gateway-api.html](https://gourl.io/bitcoin-payment-gateway-api.html)




Introduction
----------------

PHP Cryptocoin Payment Gateway is a simple PHP/MySQL script which you can easily integrate into your own website in minutes.

Start accepting payments on your website, including all major cryptocoins, and start selling online in minutes. No application process.

The big benefit of Cryptocoin Payment Box is that it fully integrated on your website, no external payment pages opens (as other payment gateways offer). 

Your website will receive full user payment information immediately after cryptocoin payment is made and you can process it in automatic mode. 


# ![Payment-Box](https://gourl.io/images/paymentbox.png)


How It Works
----------------

Usually there will be the following -

* You install payment box directly on your website and dynamically configure order id, currency, amount to pay, etc.
* All your users will see coin payment box on your webpage, and some users will use their coin wallets and make payments to you.
* In around 5 seconds after cryptocoin payment is made, user will see confirmation on your website page that payment is received (i.e. very fast).
* Your website will automatically immediately receive current user id with full payment information from our payment server.
* The user will still be on your webpage and see that successful payment result, your script can automatically process payment and give user confirmation (for example, upgrading user membership or giving download link on your products, etc). All in automatic mode - no manual actions are needed.
* For user that payment procedure on your website will be looking very similar visually and compare with normal credit cards for its speed.
* During the next 30 minutes (after transaction is verified) payment will be automatically forwarded to your own wallet address.

No paperwork, no chargebacks, no monthly fee and low transaction fee (from 0%). 




Installation
----------------------------
* [Free Registration](https://gourl.io/view/registration/New_User_Registration.html) on gourl.io and get private/public keys
* Edit file [cryptobox_config.php](https://gourl.io/images/instruction-config1.png), add your db details and your private key
* Create mysql table cryptobox_payments (mysql query below - table `crypto_payments`)
* Place your [public/private keys](https://gourl.io/images/instruction-config2.png) in any file from Examples directory and run it

THAT'S IT! CRYPTOCOIN PAYMENT BOX SHOULD NOW BE WORKING ON YOUR SITE.

Read more - [https://gourl.io/cryptocoin_payment_api.html](https://gourl.io/cryptocoin_payment_api.html)





MySQL Table
-----------------

Please also run MySQL query below which will create MySQL
table where all the cryptocoin payments made to you will 
be stored.
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


.

	
Payment API List :
---------------------

* [Bitcoin Payment API](https://gourl.io/bitcoin-payment-gateway-api.html)
* [Litecoin Payment API](https://gourl.io/litecoin-payment-gateway-api.html)
* [Dogecoin Payment API](https://gourl.io/dogecoin-payment-gateway-api.html)
* [Speedcoin Payment API](https://gourl.io/speedcoin-payment-gateway-api.html)
* [Darkcoin Payment API](https://gourl.io/darkcoin-payment-gateway-api.html)
* [Vertcoin Payment API](https://gourl.io/vertcoin-payment-gateway-api.html)
* [Reddcoin Payment API](https://gourl.io/reddcoin-payment-gateway-api.html)
* [Feathercoin Payment API](https://gourl.io/feathercoin-payment-gateway-api.html)
* [Vericoin Payment API](https://gourl.io/vericoin-payment-gateway-api.html)


.


PHP Examples / Live Demo : 
-----------------------------

* **Pay-Per-Product**: Example1 - [multiple crypto](https://gourl.io/lib/examples/pay-per-product-multi.php), Example2 - [bitcoin](https://gourl.io/lib/examples/pay-per-product.php)
* **Pay-Per-Download**: Example3 - [multiple crypto](https://gourl.io/lib/examples/pay-per-download-multi.php), Example4 - [bitcoin](https://gourl.io/lib/examples/pay-per-download.php)
* **Pay-Per-Post**: Example5 - [multiple crypto](https://gourl.io/lib/examples/pay-per-post-multi.php), Example6 - [bitcoin](https://gourl.io/lib/examples/pay-per-post.php)
* **Pay-Per-Registration**: Example7 - [multiple crypto](https://gourl.io/lib/examples/pay-per-registration-multi.php), Example8 - [bitcoin](https://gourl.io/lib/examples/pay-per-registration.php)
* **Pay-Per-Page-Access**: Example19 - [multiple crypto](https://gourl.io/lib/examples/pay-per-page-multi.php), Example10 - [bitcoin](https://gourl.io/lib/examples/pay-per-page.php)
* **Pay-Per-Membership**: Example11 - [multiple crypto](https://gourl.io/lib/examples/pay-per-membership-multi.php), Example12 - [bitcoin](https://gourl.io/lib/examples/pay-per-membership.php)


.