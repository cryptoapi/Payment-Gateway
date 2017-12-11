/**
  * Cryptocoin Payment Box Javascript           
  *
  * @package     GoUrl Bitcoin/Altcoin Payment Box and Crypto Captcha
  * @copyright   2014-2018 Delta Consultants
  * @category    Javascript
  * @website     https://gourl.io
  * @api         https://gourl.io/api.html
  * @version     1.8.3
  *
  */


	/*
	 * This function will receive JSON payment data from Gourl.io Payment Gateway through jquery ajax request and 
	 * will place received json values in all existing html <div class='gourl_...'> elements on webpage.
	 * Also that function checks every 7 seconds if payment has been received and automatically displays received payment data on webpage.
	 * 
	 * This function has the following parameters -
	 * url - payment url to gourl.io. Using this url you can get bitcoin/altcoin payment box values in JSON format.
	 * Please use php function $box->cryptobox_json_url() - which will generate url with your parameters to gourl.io payment gateway. 
	 * paid - 1/0, shows if transaction received already or not. You need to call $this->is_paid() as in example.
	 * path - path to file cryptobox.callback.php; it will be automatically call that file through ajax if payment is received
	 * ext - custom prefix in html class names, by default 'gourl_'. You can use for example - 'mycrypto_' and therefore div class in html template will be <div class='mycrypto_amount'></div>, etc.
	 * redirect - url, redirect to another page after payment is received; i.e. when payment received automatically call cryptobox.callback.php through ajax and after 5 seconds it will redirect to another page. Php class $this->is_paid() start to return TRUE.
	 * 
	 * JSON Values Example -
	 * Payment not received - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/2.21/pe/NOEXPIRY/l/en/o/invoice22/u/83412313__3bccb54769/us/COOKIE/j/1/d/ODIuMTEuOTQuMTIx/h/e889b9a07493ee96a479e471a892ae2e   
	 * Payment received successfully - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/0.1/pe/NOEXPIRY/l/en/o/invoice1/u/demo/us/MANUAL/j/1/d/ODIuMTEuOTQuMTIx/h/ac7733d264421c8410a218548b2d2a2a
	 * 
	 * Alternatively, you can receive JSON values though php curl on server side (php function get_json_values()) and use it in your php file without using Javascript and Jquery/Ajax.
	 * 
	 * Full Instruction - https://gourl.io/bitcoin-payment-gateway-api.html#p8
	 */

	function cryptobox_custom(url, paid, path, ext, redirect)
	{
		var start  = new Date().getTime();
		var st = new Date().getTime();
		var received = false;
		var error = false;
		

		if (typeof paid !== 'number') 		paid = 0;
		if (typeof path !== 'string') 		path = '';
		if (typeof ext !== 'string') 		ext = 'gourl_';
		if (typeof redirect !== 'string') 	redirect = '';
		

		cryptobox_callout = function () 
		{
			$.ajax(
			{
				type: 'GET', 
				url: url,
				cache: false, 
				contentType: 'application/json; charset=utf-8',
				data: { format: 'json' },
				dataType: 'json'
			})

			.fail(function() 
			{
				$('.'+ext+'error_message').html('Error loading data ! &#160; <a target="_blank" href="'+url+'">Raw details here &#187;</a>');
				$('.'+ext+'loader_button' ).fadeOut(400, function(){ $('.'+ext+'loader').show(); $('.'+ext+'cryptobox_error').fadeIn(400);  })
				error = true;
			})

			.done(function( data ) 
			{
				cryptobox_update_page(data, ext);
				if (data.status == "payment_received")
				{
					received = true;
					
					// update record in local db
					if (!paid) $.post( path+"cryptobox.callback.php", data )
								.fail( function() {alert( "Internal Error! Unable to find file cryptobox.callback.php. Please contact the website administrator.") })
								.done(function(txt) { if (txt != "cryptobox_newrecord" && txt != "cryptobox_updated" && txt != "cryptobox_nochanges") alert("Internal Error! "+txt); });
								
					// optional, redirect to another page after payment is received
					if (redirect) setTimeout(function() { window.location = redirect; }, 5000);
				}

				if (!received && !error)
				{	  			  		
					var end = new Date().getTime();
					if ((end - start) > 20*60*1000)
					{
						 $('.'+ext+'button_wait').hide();
						 $('.'+ext+'button_refresh').removeClass('btn-default').addClass('btn-info');
						 $('.'+ext+'cryptobox_unpaid .panel').removeClass('panel-info').removeClass('panel-primary').removeClass('panel-warning').removeClass('panel-success').addClass('panel-default').fadeTo("slow" , 0.4, function() {});
						 $('[data-original-title]').tooltip('disable');
					}
					else
					{
						setTimeout(cryptobox_callout, 7000);
					}
				}
			});
		}
		
		cryptobox_callout();

		return true;
		
	}
	
	
	function cryptobox_update_page(data, ext)
	{

		// Awaiting Payment
		logoext = (data.coinname == 'Bitcoin') ? '_' + data.texts.language : '';   	
		if (data.boxtype == 'paymentbox') $('.'+ext+'boxlogo').attr('src', 'https://coins.gourl.io/images/'+data.coinname.toLowerCase()+'/payment'+logoext+'.png'); else $('.'+ext+'boxlogo').attr('src', 'https://coins.gourl.io/images/'+data.coinname.toLowerCase()+'/captcha'+logoext+'.png');

		var qrcodesize = (typeof $('.'+ext+'qrcode_image').attr('data-size') === 'undefined') ? 110 : $('.'+ext+'qrcode_image').attr('data-size');
		$('.'+ext+'qrcode_image').attr('src', 'https://chart.googleapis.com/chart?chs='+qrcodesize+'x'+qrcodesize+'&chld=M|0&cht=qr&chl='+data.coinname.toLowerCase()+'%3A'+data.addr+'%3Famount%3D'+data.amount+'&choe=UTF-8'); 
		
		if ($.isFunction($.fn.tooltip)) 
		{ 
			$('.'+ext+'wallet_open').attr('data-original-title', data.texts.btn_wallet).attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'qrcode_image').attr('data-original-title', data.texts.qrcode).attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip(); 
			$('.'+ext+'button_wait').attr('data-original-title', data.texts.btn_wait_hint).attr('data-placement', 'top').attr('data-toggle', 'tooltip').tooltip();  
		}
		
		$('.'+ext+'paymentcaptcha_amount').text(((data.boxtype=='paymentbox') ? data.texts.payment_amount : data.texts.captcha_amount));
		$('.'+ext+'paymentcaptcha_status').text(((data.boxtype=='paymentbox') ? data.texts.payment_status : data.texts.captcha_status));

		var txt = '-';
		if (data.status=='payment_not_received')  txt = data.texts.not_received;
		else if (data.status=='payment_received') txt = (data.boxtype=='paymentbox') ? data.texts.payment_successful : data.texts.captcha_successful;

		$('.'+ext+'paymentcaptcha_statustext').text(txt);


		
		// Buttons	
		if (data.status == 'payment_received')
		{
			$('.'+ext+'texts_btn_wait_hint').hide();
			$('.'+ext+'button_wait').html(((data.boxtype=='paymentbox') ? data.texts.payment_successful : data.texts.captcha_successful));
		}
		else
		{
			$('.'+ext+'button_wait').html('<i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i> &#160; ' + ((data.boxtype=='paymentbox') ? data.texts.payment_wait : data.texts.captcha_wait));
		}
		
		$('.'+ext+'button_refresh').html('<i class="fa fa-refresh" aria-hidden="true"></i>&#160; ' + data.texts.refresh);
		
		
		
		// Payment Received
		$('.'+ext+'paymentcaptcha_title').text((data.boxtype=='paymentbox') ? data.texts.title : data.coinname);
		$('.'+ext+'paymentcaptcha_successful').text((data.boxtype=='paymentbox') ? data.texts.payment_successful : data.texts.captcha_successful);
		$('.'+ext+'paymentcaptcha_date').html(((data.boxtype=='paymentbox') ? data.texts.received_on : data.texts.captcha_passed) + ' <b>' + data.date + '</b>');
		$('.'+ext+'button_details').html('<span class="glyphicon glyphicon-'+((data.coinlabel=='BTC') ? 'bitcoin' : 'globe')+'" aria-hidden="true"></span>&#160; ' + data.texts.btn_res);
		$('.'+ext+'button_details').attr('href', data.tx_url).attr('target', '_blank');

		
		
		// Init
		if (data.texts.language == 'fa' || data.texts.language == 'ar') $('.'+ext+'cryptobox_error, .'+ext+'cryptobox_top, .'+ext+'cryptobox_unpaid, .'+ext+'cryptobox_paid, .'+ext+'cryptobox_rawdata').attr('dir', 'rtl');

		$('.'+ext+'loader').fadeOut(400, function()
		{
			$('.'+ext+'cryptobox_top, .'+ext+'cryptobox_rawdata').fadeIn(400);
			if (data.status == 'payment_received') 
			{
				$('.'+ext+'cryptobox_unpaid, .'+ext+'boxlogo_unpaid, .'+ext+'msg').hide(); 
				$('.'+ext+'cryptobox_paid, .'+ext+'boxlogo_paid').fadeIn(400); 
			}
			else 
			{
				$('.'+ext+'cryptobox_paid, .'+ext+'boxlogo_paid').hide();
				$('.'+ext+'cryptobox_unpaid, .'+ext+'boxlogo_unpaid').fadeIn(400); 
			}
			$('.'+ext+'msg').delay(7000).fadeOut(2000);
		}); 		

		
		
		// Raw Data froim Gourl.io 
		var html = "";
		$.each(data, function(key, val)
		{
			if (typeof val === 'object')
			{
				var html2 = '<div style="margin-left:50px">';
				$.each(val, function(key2, val2)
				{
					html2 += "[" + key2 + '] => ' + val2 + "<br>";
					$('.' + ext + key + '_' + key2).html(val2);
				});
				val = html2 + '</div>';
			}
			else 
			{	
				if (key.indexOf("_url") > 0) $('.' + ext + key).attr("href", val);
				else $('.' + ext + key).html(val);
			}
			
			html += "[" + key + '] => ' + val + "<br>";
		});
		

		// Custom exchange text
		if ($('.'+ext+'texts_intro1b').attr('data-site') !== 'undefined' && $('.'+ext+'texts_intro1b').attr('data-url') !== 'undefined')
		{
			var exchange = '<a target="_blank" href="' + $('.'+ext+'texts_intro1b').attr('data-url') + '">' + $('.'+ext+'texts_intro1b').attr('data-site') + '</a>';
			$('.'+ext+'texts_intro1b').html((data.texts.intro1b).replace("___", exchange));
		}
		

		$('.'+ext+'jsondata').html(html);

		
		return true;
	}
	
	
	
	
	
	
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
