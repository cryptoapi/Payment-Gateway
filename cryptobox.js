/**
  *
  * Cryptocoin Payment Box Javascript
  *
  * @package     Cryptocoin Payment Box / Cryptocoin Captcha 
  * @copyright   2014 Delta Consultants
  * @category    Javascript
  * @website     https://gourl.io
  * @api         https://gourl.io/cryptocoin_payment_api.html
  * @version     1.3
  *
  */

	function cryptobox_cookie(name) 
	{
		var nameEQ = name + "="; var ca = document.cookie.split(';'); for(var i=0;i <  ca.length;i++) { 
		var c = ca[i]; while (c.charAt(0)==' ') c = c.substring(1,c.length); if (c.indexOf(nameEQ) == 0) 
		return c.substring(nameEQ.length,c.length); } return ''; 
	}
	
	function cryptobox_show(boxID, coinName, public_key, amount, amountUSD, period, language, iframeID, userID, userFormat, orderID, cookieName, webdev_key, hash)
	{
		var id = public_key.substr(0, public_key.indexOf("AA"));
		if (id == '' || boxID != id || public_key.indexOf("PUB") == -1) alert('Invalid cryptobox public_key');
		else if ((amount <= 0 && amountUSD <= 0) || (amount > 0 && amountUSD > 0)) alert('You can use in cryptobox options one of variable only: amount or amountUSD. You cannot place values in that two variables together');
		else if (amount != 0 && ((amount - 0) != amount || amount < 0.001)) alert('Invalid cryptobox amount');
		else if (amountUSD != 0 && ((amountUSD - 0) != amountUSD || amountUSD < 0.01)) alert('Invalid cryptobox amountUSD');
		else if (userFormat != 'COOKIE' && userFormat != 'SESSION' && userFormat != 'IPADDRESS' && userFormat != 'MANUAL') alert('Invalid cryptobox userFormat value');
		else if (userFormat == 'COOKIE' && cookieName == '') alert('Invalid cryptobox cookie name');
		else if (userFormat == 'COOKIE' && cryptobox_cookie(cookieName) != userID) alert('Please enable Cookies in your browser !');
		else if (orderID == '') alert('Invalid orderID');
		else if (period == '') alert('Invalid period');
		else if (webdev_key != '' && (webdev_key.indexOf("DEV") == -1 || webdev_key.length < 20)) alert('Invalid webdev_key, leave it empty');
		else if (hash == '') alert('Invalid cryptobox hash');
		else 
		{
			var url = 'https://coins.gourl.io' + 
			'/b/'+encodeURIComponent(boxID)+'/c/'+encodeURIComponent(coinName)+
			'/p/'+encodeURIComponent(public_key)+
			'/a/'+encodeURIComponent(amount)+'/au/'+encodeURIComponent(amountUSD)+
			'/pe/'+encodeURIComponent(period.replace(' ', '_'))+'/l/'+encodeURIComponent(language)+
			'/i/'+encodeURIComponent(iframeID)+'/u/'+encodeURIComponent(userID)+
			'/us/'+encodeURIComponent(userFormat)+'/o/'+encodeURIComponent(orderID)+
			(webdev_key?'/w/'+encodeURIComponent(webdev_key):'')+
			'/h/'+encodeURIComponent(hash)+'/z/'+Math.random();
			var html = document.getElementById(iframeID);
			if (html == null) alert('Cryptobox iframeID HTML with id "' + iframeID + '" not exist!');
			else html.src = url;
		}
		
		return true;
	}
	
	
	function cryptobox_msghide (id)
	{ 
		setTimeout(function(){ document.getElementById(id).style.display='none';}, 15000 ); 
	}	
 
