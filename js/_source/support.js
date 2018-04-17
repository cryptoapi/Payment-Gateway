	/**
	* @package     GoUrl Bitcoin/Altcoin Payment Box - Update HTML Payment Box values
	* @copyright   2014-2018 Delta Consultants
	* @category    Javascript
	* @website     https://gourl.io   
	* @api         https://gourl.io/api.html     
	* @version     2.1.2
	*/
	
	/**
	* This function will place received json raw payment values to all existing html <div class='acrypto_...'> elements on webpage.
	* JSON Values Example -
	* Payment not received - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/2.21/pe/NOEXPIRY/l/en/o/invoice22/u/83412313__3bccb54769/us/COOKIE/j/1/d/ODIuMTEuOTQuMTIx/h/e889b9a07493ee96a479e471a892ae2e  
	* Payment received successfully - https://coins.gourl.io/b/20/c/Bitcoin/p/20AAvZCcgBitcoin77BTCPUB0xyyeKkxMUmeTJRWj7IZrbJ0oL/a/0/au/0.1/pe/NOEXPIRY/l/en/o/invoice1/u/demo/us/MANUAL/j/1/d/ODIuMTEuOTQuMTIx/h/ac7733d264421c8410a218548b2d2a2a
	*
	* Full Instruction - https://gourl.io/bitcoin-payment-gateway-api.html#p8
	*/

	function cryptobox_update_page(data, imgdir_path, logoimg_path, ext)
	{

		if (jQuery.type( data ) === "string") 	data = $.parseJSON(atob(data)); 
		if (typeof imgdir_path !== 'string') 	imgdir_path = 'images/';	else 	imgdir_path = atob(imgdir_path);
		if (typeof logoimg_path !== 'string') 	logoimg_path = 'default';  	else 	logoimg_path = atob(logoimg_path);
		if (typeof ext !== 'string') 			ext = 'acrypto_';			else 	ext = atob(ext);
		
		$('.mncrpt a.dropdown-item, .'+ext+'button_confirm, .'+ext+'button_wait, .'+ext+'button_error, .'+ext+'refresh, .mncrpt img.radioimage-select').click(function() { $('.'+ext+'refresh, .'+ext+'msg').hide(); document.location.href = "#h"+ext.replace(/_\s*$/, ""); $('.'+ext+'loading_icon').show(); });

		
		if (jQuery.type( data.coinname ) !== "string" || jQuery.type( data.texts ) !== "object" || jQuery.type( data.status ) !== "string" || (data.status  != "payment_received" && data.status  != "payment_not_received"))
		{
				if (jQuery.type( data.err ) === "string" && data.err) $('.'+ext+'error_message').html('Error loading data !<br><br><b>'+data.err+'</b>');
				else $('.'+ext+'error_message').html('Error loading data ! Please contact the website administrator.');
				$('.'+ext+'loader_button' ).fadeOut(400, function(){ $('.'+ext+'loader').show(); $('.'+ext+'cryptobox_error').fadeIn(400);  })
				$('.'+ext+'cryptobox_error .'+ext+'coins_list').show();
				return false;
		}
		
		var coinName = data.coinname.toLowerCase();
		
		if (logoimg_path == "default")
		{
			var logoext = (coinName == 'bitcoin') ? '_' + data.texts.language : '';
			var a = [];
			a['dash'] = ['de', 'es', 'nl', 'ru']; 
			a['dogecoin'] = ['cn', 'de', 'es', 'fr', 'hi', 'ru', 'zh']; 
			a['feathercoin'] = ['es', 'ru']; 
			a['litecoin'] = ['cn', 'de', 'es', 'fr', 'hi', 'nl', 'ru', 'zh']; 
			a['peercoin'] = ['es', 'ru']; 
			a['potcoin'] = ['es', 'ru']; 
			a['reddcoin'] = ['cn', 'es', 'fr', 'hi', 'ru', 'zh']; 
			a['speedcoin'] = ['cn', 'es', 'fr', 'hi', 'ru', 'zh']; 
			a['vertcoin'] = ['es', 'ru']; 
			if (a.hasOwnProperty(coinName) && jQuery.inArray(data.texts.language, a[coinName]) != -1) logoext = '_' + data.texts.language;
			var src = (data.boxtype == 'paymentbox') ? imgdir_path+coinName+'/payment'+logoext+'.png' : imgdir_path+coinName+'/captcha'+logoext+'.png'
		}
		else if (!logoimg_path) src = "";
		else src = logoimg_path;
		
		if (src == "") $('.'+ext+'logo_image').hide();
		else $('.'+ext+'logo_image').attr('src', src);

		var qrcodesize = (typeof $('.'+ext+'qrcode_image').attr('data-size') === 'undefined') ? 110 : $('.'+ext+'qrcode_image').attr('data-size');
		$('.'+ext+'qrcode_image').attr('src', 'https://chart.googleapis.com/chart?chs='+qrcodesize+'x'+qrcodesize+'&chld=M|0&cht=qr&chl='+coinName+'%3A'+data.addr+'%3Famount%3D'+data.amount+'&choe=UTF-8'); 
		
		if ($.isFunction($.fn.tooltip)) 
		{ 
			$('.'+ext+'wallet_address').attr('data-original-title', data.texts.btn_wallet).attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'wallet_open').attr('data-original-title', data.texts.btn_wallet_hint.replace("\\n", " ")).attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'qrcode_image').attr('data-original-title', data.texts.qrcode).attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip(); 
			if (!($('.'+ext+'button_confirm').length)) $('.'+ext+'button_wait').attr('data-original-title', data.texts.btn_wait_hint).attr('data-placement', 'top').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'button_confirm').attr('data-original-title', data.texts.btn_wait_hint).attr('data-placement', 'top').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'button_refresh, .'+ext+'refresh').attr('data-original-title', data.texts.refresh).attr('data-placement', 'top').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'copy_address').attr('data-original-title', data.texts.btn_copy).attr('data-placement', 'top').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'copy_amount').css( 'cursor', 'pointer' ).attr('data-original-title', data.texts.copy_amount).attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip();  
			$('.'+ext+'copy_transaction').css( 'cursor', 'pointer' ).attr('data-original-title', 'Copy Transaction ID').attr('data-placement', 'bottom').attr('data-toggle', 'tooltip').tooltip();  
		}
		
		$('.'+ext+'paymentcaptcha_amount').text(((data.boxtype=='paymentbox') ? data.texts.payment_amount : data.texts.captcha_amount));
		$('.'+ext+'paymentcaptcha_status').text(((data.boxtype=='paymentbox') ? data.texts.payment_status : data.texts.captcha_status));
		$('.'+ext+'wallet_open').attr("href", data.wallet_url);  
		$('.'+ext+'wallet_address').html(data.addr).attr("href", data.wallet_url);  

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
			$('.'+ext+'texts_btn_wait_hint').show();
			$('.'+ext+'button_wait').html('<i class="fas fa-circle-notch fa-spin"></i> &#160; ' + ((data.boxtype=='paymentbox') ? data.texts.payment_wait : data.texts.captcha_wait));
		}
		
		$('.'+ext+'button_refresh').html('<i class="fas fa-sync-alt"></i>&#160; ' + data.texts.refresh);
		
		
		
	

		// Payment Received Payment Box
		if (data.status == 'payment_received')
		{
			$('.'+ext+'paymentcaptcha_title').text((data.boxtype=='paymentbox') ? data.texts.title : data.coinname);
			$('.'+ext+'paymentcaptcha_successful').text((data.boxtype=='paymentbox') ? data.texts.payment_successful : data.texts.captcha_successful);
			$('.'+ext+'paymentcaptcha_date').html(((data.boxtype=='paymentbox') ? data.texts.received_on : data.texts.captcha_passed) + ' <b>' + data.date + '</b>');
			$('.'+ext+'button_details').html('<span class="glyphicon glyphicon-'+((data.coinlabel=='BTC') ? 'bitcoin' : 'globe')+'"></span>&#160; ' + data.texts.btn_res);
			$('.'+ext+'button_details').click(function() { newwindow=window.open(data.tx_url,'','height=800,width=1100'); if (window.focus) {newwindow.focus()} return false; });

			$('.'+ext+'texts_pay_now').hide();
			$('.'+ext+'texts_intro1').hide();
			$('.'+ext+'texts_intro2').hide();
			$('.'+ext+'texts_intro3').hide();
			$('.'+ext+'coins_list').hide();
			//$('.'+ext+'box_language').addClass('col-md-offset-3');
		}
		else $('.'+ext+'coins_list').show();
		
		
		// Init
		if (data.texts.language == 'fa' || data.texts.language == 'ar') 
		{
			$('.'+ext+'cryptobox_error, .'+ext+'cryptobox_top, .'+ext+'cryptobox_unpaid, .'+ext+'cryptobox_paid, .'+ext+'cryptobox_rawdata').attr('dir', 'rtl');
			$('.'+ext+'cryptobox_unpaid .row, .'+ext+'cryptobox_paid .row, .'+ext+'coins_list .row').addClass('justify-content-center');
		}

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
			$('.'+ext+'msg').delay(10000).fadeOut(2000);
		}); 		

		
		
		// Raw Payment Data
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
		$('#dmgnpcode').delay(1000).show('slow');
		
		
		// copy amount/wallet address to clipboard icon
		$( '.'+ext+'copy_address, .'+ext+'copy_amount, .'+ext+'copy_transaction' ).click( function()
		{
			var textArea = document.createElement( "textarea" );
			if (($(this).attr("class")).indexOf("copy_amount") > 0) textArea.value = data.amount;
			else if (($(this).attr("class")).indexOf("copy_transaction") > 0) textArea.value = data.tx_url;
			else textArea.value = data.addr;
			document.body.appendChild( textArea );
			textArea.select();
			
			try {
					var text = (document.execCommand( 'copy' )) ? data.texts.copied : 'Oops, unable to copy :(';
			} catch (err) {
					var text = 'Oops, unable to copy :(';
			}
			
			document.body.removeChild( textArea );
			
			if ($.isFunction($.fn.tooltip))
			{
				var el = $(this);
				el.tooltip('dispose');
				el.attr('data-original-title', text).tooltip('show');
			}
			
			return false;
		});

		
		return true;
	}
	
	


	
	jQuery(document).ready(function() 
	{
		// images in radio boxes
		$('input.aradioimage').aradioimage();
	});



	(function($) {
		$.fn.aradioimage = function( options ) {
			var defaults = {
					imgItemClass: 'radioimage-select',
					imgItemCheckedClass: 'radioimage-checked',
					hideLabel: true
				},

					syncClassChecked = function( img ) {
					var radioName = img.prev('input[type="radio"]').attr('name');

					$('input[name="' + radioName + '"]').each(function() {
						var myImg = $(this).next('img');
						if ($(this).prop('checked') && typeof $(this).data('url') !== 'undefined') window.location.href = $(this).data('url');
					});
				};

			options = $.extend( defaults, options );

			return this.each(function() {
				$(this)
					.hide()
					.after('<img src="' + $(this).data('image') + '" width="' + ($(this).data('width')+18) + '" alt="' + $(this).data('alt') + '" title="' + $(this).data('title') + '" />');

				var img = $(this).next('img');
				img.addClass(options.imgItemClass);

				if ( options.hideLabel ) {
					$('label[for=' + $(this).attr('id') + ']').hide();
				}

				if ( $(this).prop('checked') ) {
					img.addClass(options.imgItemCheckedClass);
				}

				img.on('click', function(e) {
					$(this)
						.prev('input[type="radio"]')
						.prop('checked', true)
						.trigger('change');

					syncClassChecked($(this));
				} );
			});
		}
	}) (jQuery);    

