	/**
	* @package     GoUrl Bitcoin/Altcoin Payment Box - Receive JSON payment data through Ajax from gourl.io
	* @copyright   2014-2018 Delta Consultants
	* @category    Javascript
	* @website     https://gourl.io
	* @api         https://gourl.io/api.html
	* @version     2.1.3
	*/
	
	/**
	* This function will receive JSON payment data from Gourl.io Payment Gateway through jquery ajax request.
	* Also function checks every 7 seconds if payment has been received and automatically displays received payment data on webpage.
	* 
	* *****	   Live Demo - https://gourl.io/lib/examples/example_customize_box.php    *****
	* 
	* This function has the following parameters -
	* url - payment url to gourl.io. Using this url you can get bitcoin/altcoin payment box values in JSON format.
	* Please use php function $box->cryptobox_json_url() - generate url with your parameters to gourl.io payment gateway. 
	* paid - 1/0, shows if transaction received already or not. php function -  $this->is_paid()
	* confirmed - 1/0, shows if transaction received and have 6+ confirmations already. php function -  $this->is_confirmed()
	* phpdir_path - path to directory with files cryptobox.class.php/cryptobox.callback.php/cryptobox.newpayment.php; cryptobox.newpayment.php will be automatically call two times through ajax - when payment received/and when confirmed.
	* imgdir_path - path to directory with files logo/coin images;
	* logoimg_path - path to file with your own logo;
	* ext - custom prefix in html class names, by default 'acrypto_'. You can use for example - 'mycrypto_' and therefore div class in html template will be <div class='mycrypto_amount'></div>, etc.
	* redirect - url, redirect to another page after payment is received; i.e. when payment received automatically call cryptobox.callback.php through ajax and after 3 seconds it will redirect to another page. Php class $this->is_paid() start to return TRUE.
	* 
	* JSON Values Example -
	* Payment not received - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/2.21/pe/NOEXPIRY/l/en/o/invoice22/u/83412313__3bccb54769/us/COOKIE/j/1/d/ODIuMTEuOTQuMTIx/h/e889b9a07493ee96a479e471a892ae2e
	* Payment received successfully - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/0.1/pe/NOEXPIRY/l/en/o/invoice1/u/demo/us/MANUAL/j/1/d/ODIuMTEuOTQuMTIx/h/ac7733d264421c8410a218548b2d2a2a
	* 
	* Alternatively, you can receive JSON values though php curl on server side (php function get_json_values()) and use it in your php files without using Jquery Ajax.
	* 
	* Full Instruction - https://gourl.io/bitcoin-payment-gateway-api.html#p8
	*/
	 
	function cryptobox_ajax (url, paid, confirmed, phpdir_path, imgdir_path, logoimg_path, ext, redirect)
	{
		var start  = new Date().getTime();
		var st = new Date().getTime();
		var error = false; 
		var received = false;
 
		url = atob(url); 
		if (typeof paid !== 'number') 			paid = 0;
		if (typeof confirmed !== 'number') 		confirmed = 0;
		if (typeof phpdir_path !== 'string') 	phpdir_path = '';			else 	phpdir_path = atob(phpdir_path);
		if (typeof imgdir_path !== 'string') 	imgdir_path = 'images/';	else 	imgdir_path = atob(imgdir_path);
		if (typeof logoimg_path !== 'string') 	logoimg_path = 'default';  	else 	logoimg_path = atob(logoimg_path);
		if (typeof ext !== 'string') 			ext = 'acrypto_';			else 	ext = atob(ext);
		if (typeof redirect !== 'string') 		redirect = '';				else 	redirect = atob(redirect);

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
					error = true;

					var err_message = "";
					$.ajax({
						type: 'GET', 
						url: url,
						cache: false,
						dataType: 'text'
					})
					.done(function( err_data ) {
						if (err_data != '') err_message = '<br><br><b>' + err_data.substr(0, 250) + '</b><br><br>';
					})
					.always(function() 
					{ 
						//$('.'+ext+'error_message').html('Error loading data ! &#160; <a target="_blank" href="'+url+'">Raw details here &#187;</a>');
						$('.'+ext+'error_message').html('Error loading data ! ' + err_message + ' Please contact the website administrator.');
						$('.'+ext+'loader_button' ).fadeOut(400, function(){ $('.'+ext+'loader').show(); $('.'+ext+'cryptobox_error').fadeIn(400);  })
						$('.'+ext+'cryptobox_error .'+ext+'coins_list').show();
						$('.'+ext+'button_error, .mncrpt img.radioimage-select').click(function() { $('.'+ext+'refresh, .'+ext+'msg').hide(); document.location.href = "#h"+ext.replace(/_\s*$/, ""); $('.'+ext+'loading_icon').show(); });
					});
					return false;
			})

			.done(function( data ) 
			{
				
				if (jQuery.type( data.coinname ) !== "string" || jQuery.type( data.texts ) !== "object" || jQuery.type( data.status ) !== "string" || (data.status  != "payment_received" && data.status  != "payment_not_received"))
				{
					if (jQuery.type( data.err ) === "string" && data.err) $('.'+ext+'error_message').html('Error loading data !<br><br><b>'+data.err+'</b>');
					else $('.'+ext+'error_message').html('Error loading data ! Please contact the website administrator.');
					$('.'+ext+'loader_button' ).fadeOut(400, function(){ $('.'+ext+'loader').show(); $('.'+ext+'cryptobox_error').fadeIn(400);  })
					$('.'+ext+'cryptobox_error .'+ext+'coins_list').show();
					$('.'+ext+'button_error, .mncrpt img.radioimage-select').click(function() { $('.'+ext+'refresh, .'+ext+'msg').hide(); document.location.href = "#h"+ext.replace(/_\s*$/, ""); $('.'+ext+'loading_icon').show(); });
					return false;
				}

				cryptobox_update_page(data, btoa(imgdir_path), btoa(logoimg_path), btoa(ext));
				if (data.status == "payment_received")
				{	
					received = true;
					
					// update record in local db
					if (!paid || (paid && !confirmed && data.confirmed)) $.post( phpdir_path+"cryptobox.callback.php", data )
								.fail( function() {alert( "Internal Error! Unable to find callback file. Please contact the website administrator.") })
								.done(function(txt) { if (txt != "cryptobox_newrecord" && txt != "cryptobox_updated" && txt != "cryptobox_nochanges") alert("Internal Error! "+txt); });
								
					// optional, redirect to another page after payment is received
					if (redirect) setTimeout(function() { window.location = redirect; }, 3000);
				}

				if (!received && !error)
				{	  			  		
					var end = new Date().getTime();
					if ((end - start) > 20*60*1000)
					{
						 $('.'+ext+'button_wait').hide();
						 $('.'+ext+'button_refresh').show();
						 $('.'+ext+'cryptobox_unpaid .card, .'+ext+'cryptobox_top').fadeTo("slow" , 0.3, function() {});
						 if ($.isFunction($.fn.tooltip)) $('[data-original-title]').tooltip('disable');
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
	
	  