	/**
	* @package     GoUrl Bitcoin/Altcoin Payment Box - show iFrame Payment Box
	* @copyright   2014-2018 Delta Consultants
	* @category    Javascript
	* @website     https://gourl.io
	* @api         https://gourl.io/api.html
	* @version     2.1.3
	*/

	/**
	* Display iFrame payment box
	* Full Instruction - https://gourl.io/bitcoin-payment-gateway-api.html
	*/
	
	function cryptobox_show(boxID, coinName, public_key, amount, amountUSD, period, language, iframeID, userID, userFormat, orderID, cookieName, webdev_key, hash, width, height)
	{
		if (typeof width !== 'number') width = 0;
		if (typeof height !== 'number') height = 0;
	
		var id = public_key.substr(0, public_key.indexOf("AA"));
		if (id == '' || boxID != id || public_key.indexOf("PUB") == -1) alert('Invalid payment box public_key');
		else if ((amount <= 0 && amountUSD <= 0) || (amount > 0 && amountUSD > 0)) alert('You can use in payment box options one of variable only: amount or amountUSD. You cannot place values in that two variables together');
		else if (amount != 0 && ((amount - 0) != amount || amount < 0.0001)) alert('Invalid payment box amount');
		else if (amountUSD != 0 && ((amountUSD - 0) != amountUSD || amountUSD < 0.01)) alert('Invalid payment box amountUSD');
		else if (userFormat != 'COOKIE' && userFormat != 'SESSION' && userFormat != 'IPADDRESS' && userFormat != 'MANUAL') alert('Invalid payment box userFormat value');
		else if (userFormat == 'COOKIE' && cookieName == '') alert('Invalid payment box cookie name');
		else if (userFormat == 'COOKIE' && cryptobox_cookie(cookieName) == '') { if (document.getElementById(iframeID).src != null) document.getElementById(iframeID).src = 'https://gourl.io/images/crypto_cookies.png'; alert('Please enable Cookies in your Browser !'); }
		else if (userFormat == 'COOKIE' && cryptobox_cookie(cookieName) != userID) alert('Invalid cookie value. It may be you are viewing an older copy of the page that is stored in the website cache. Please contact with website owner, need to disable/turn-off caching for current page');
		else if (orderID == '') alert('Invalid orderID');
		else if (period == '') alert('Invalid period');
		else if (public_key.length != 50) alert('Invalid public key');
		else if (webdev_key != '' && (webdev_key.indexOf("DEV") == -1 || webdev_key.length < 20)) alert('Invalid webdev_key, leave it empty');
		else if (hash == '') alert('Invalid payment box hash');
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
			(width>0?'/ws/'+encodeURIComponent(width):'')+
			(height>0?'/hs/'+encodeURIComponent(height):'')+
			'/h/'+encodeURIComponent(hash)+
			'/z/'+Math.random();
			
			var html = document.getElementById(iframeID);
			if (html == null) alert('Cryptobox iframeID HTML with id "' + iframeID + '" not exist!');
			else html.src = url;
		}
		
		return true;
	}

	
	
	
	function cryptobox_cookie(name) 
	{
		var nameEQ = name + "="; var ca = document.cookie.split(';'); for(var i=0;i <  ca.length;i++) { 
		var c = ca[i]; while (c.charAt(0)==' ') c = c.substring(1,c.length); if (c.indexOf(nameEQ) == 0) 
		return c.substring(nameEQ.length,c.length); } return ''; 
	}


	function cryptobox_msghide (id)
	{
		setTimeout(function(){ document.getElementById(id).style.display='none';}, 15000 );
	} 

