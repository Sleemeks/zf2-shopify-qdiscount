<?php
namespace QuickProductDiscount\ScriptDiscount;

class Script
{
    public function code($min,$max)
    {	
        $code = "
		jQuery('head').append('<link rel=\'stylesheet\' href=\'http://qdiscount.tentura.eu:8080/css/qdiscount.css\'>');
		jQuery( document ).ready(function() {
			if(jQuery.cookie('qpd') != null) {
				var cookies = jQuery.parseJSON(jQuery.cookie('qpd'));
				for(var i=0;i<cookies['quickprodisc']['length'];i++) {
					if(cookies['quickprodisc'][i]['discount_life_time'] <= jQuery.now()) {
						cookies['quickprodisc'].splice(i, 1);
						i = i - 1;
					}
				}
				jQuery.cookie('qpd', JSON.stringify(cookies), {path: '/'});
			}
		
			showDiscountButton();
			var product_handle = __st['pageurl'].split('/products/');
			if(typeof product_handle[1] !== 'undefined') {
				var product_handle2 = product_handle[1].split('?');
				if(typeof Shopify.money_format !== 'undefined') {
					var money_format = Shopify.money_format.split('{{amount}}');
				}
				if(typeof product_handle2[0] !== 'undefined'){
					jQuery.getJSON('/products/'+product_handle2[0]+'.js', function(product) {
						product_price = product['variants'][0]['price'];
						jQuery( '.selector-wrapper' ).change(function() {
							var variant_length = jQuery('select[name=\'id\'] option').length;
							for(var i=0;i<variant_length;i++) {
								if(product['variants'][i]['id'] == jQuery('select[name=\'id\']').val()) {
									product_price = product['variants'][i]['price'];
									break;
								}
							}
						});
						var product_info = product;
						if(jQuery.cookie('qpd') == null) {
							jQuery('#quick-product-discount').show();
							jQuery('#quick-product-discount').click(function() {
								var percent = Math.floor(Math.random() * ({$max} - {$min} + 1)) + {$min};
								var new_price = product_price-(product_price*percent/100);
								var discount_price = number_format((new_price/100),2,'.',',');
								var discount_life_time = discountLifeTime();
								
								if(product_price != new_price) {
									var product =
									{
										'quickprodisc' :
										[{
											product_id: product_info['id'],
											product_variant_id: parseInt(jQuery('select[name=\'id\']').val()),
											price: discount_price,
											discount_life_time: discount_life_time
										},]
									};
									
									jQuery('#quick-product-discount').css('display','none');
									jQuery('.qdiscount-loader').css('display','block');
									
									jQuery.cookie('qpd', JSON.stringify(product), {path: '/'});
									ajaxCreateProduct(product_info,discount_price); // Add product to shop
								} else {
									var product = 
									{
										'quickprodisc' :
										[{
											product_id: product_info['id'],
											product_variant_id: parseInt(jQuery('select[name=\'id\']').val()),
											price: discount_price,
											discount_life_time: discount_life_time
										},]
									};
									
									jQuery.cookie('qpd', JSON.stringify(product), {path: '/'});
									
									jQuery('#quick-product-discount').css('display','none');
									jQuery('.qdiscount-loader').css('display','block');
									setTimeout(function() {
										jQuery('.qdiscount-loader').css('display','none');
										buttonReplace(discount_price); 					// Replace batton to new price
									}, 1500);
								}
								selectorChange(product_info);					// If select change
							});	
						} else {
							jQuery('#quick-product-discount').show();
							var cookies = jQuery.parseJSON(jQuery.cookie('qpd'));
							for(var i=0; i<cookies['quickprodisc'].length; i++) {
								if(cookies['quickprodisc'][i]['product_variant_id'] == jQuery('select[name=\'id\']').val()) {
									buttonReplace(cookies['quickprodisc'][i]['price']); // Replace batton to new price
									break;
								}
							}
							selectorChange(product_info);		// If select change
						}
						addProductToCart();	// Add to cart created product
					});
				}
			}
			
			function buttonReplace(price) {
				jQuery('body').addClass('qdiscount-body');
				jQuery('#quick-product-discount').replaceWith( jQuery('<span id=\'quick-product-discount-price\' style=\'display:block;\'>'+money_format['0'] + price+'</span>'));
			}
			function priceReplaceButton() {
				jQuery('body').removeClass('qdiscount-body');
				jQuery('#quick-product-discount-price').replaceWith( jQuery('<button type=\'button\' id=\'quick-product-discount\' class=\'btn\' style><span>Get Discount</span></button>'));
			}
			function priceReplace(price) {
				jQuery('#quick-product-discount-price').replaceWith( jQuery('<span id=\'quick-product-discount-price\' style=\'display:block;\'>'+money_format['0'] + price+'</span>'));
			}
			function showDiscountButton() {
				if (jQuery('#hidden-qdiscount').length == 1) {
					jQuery('#quick-product-discount').remove();
				}
			}
			
			function selectorChange(product_info) {
				jQuery( '.selector-wrapper' ).change(function() {
					var percent = Math.floor(Math.random() * ({$max} - {$min} + 1)) + {$min};
					priceReplaceButton();
					var cookies = jQuery.parseJSON(jQuery.cookie('qpd'));
					var cookLength = cookies['quickprodisc'].length;
					for(var i = 0; i < cookLength; i++) {
						if(cookies['quickprodisc'][i]['product_variant_id'] == jQuery('select[name=\'id\']').val()) {
							buttonReplace(cookies['quickprodisc'][i]['price']);			// Replace batton to new price
							break;
						}
					}
					clickGetDiscount(product_info);	
				});
				clickGetDiscount(product_info);
			}
			
			function clickGetDiscount(product_info) {
				jQuery('#quick-product-discount').click(function(){
					var product_variant_length = jQuery('select[name=\'id\'] option').length;
					for(var i=0;i<product_variant_length;i++) {
						if(product_info['variants'][i]['id'] == jQuery('select[name=\'id\']').val()) {
							product_price = product_info['variants'][i]['price'];
							break;
						}
					}
					var percent = Math.floor(Math.random() * ({$max} - {$min} + 1)) + {$min};
					var new_price = product_price-(product_price*percent/100);
					var discount_price = number_format((new_price/100),2,'.',',');
					var cookies = jQuery.parseJSON(jQuery.cookie('qpd'));
					var discount_life_time = discountLifeTime();
					
					if(product_price != new_price) {
						cookies['quickprodisc'].push({
							product_id : product_info['id'],
							product_variant_id: parseInt(jQuery('select[name=\'id\']').val()),
							price: discount_price,
							discount_life_time: discount_life_time
						});
						
						jQuery('#quick-product-discount').css('display','none');
						jQuery('.qdiscount-loader').css('display','block');
						
						jQuery.cookie('qpd', JSON.stringify(cookies), {path: '/'});
						ajaxCreateProduct(product_info,discount_price); // Add product to shop
					} else {
						cookies['quickprodisc'].push({
							product_id : product_info['id'],
							product_variant_id: parseInt(jQuery('select[name=\'id\']').val()),
							price: discount_price,
							discount_life_time: discount_life_time
						});
						jQuery.cookie('qpd', JSON.stringify(cookies), {path: '/'});
						
						jQuery('#quick-product-discount').css('display','none');
						jQuery('.qdiscount-loader').css('display','block');
						setTimeout(function() {
							jQuery('.qdiscount-loader').css('display','none');
							buttonReplace(discount_price); 					// Replace batton to new price
						}, 1500);
					}
				});
			}
			
			function discountLifeTime() {
				var today = new Date();
				var lastExistDay = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 30);
				return lastExistDay.getTime();
			}
	  
			function addProductToCart() {
				//jQuery('form[action=\'/cart/add\']').submit(function (e) {
				jQuery('[name=\'add\']').click(function(){
					if(jQuery.cookie('qpd') != null) {
						var cookies = jQuery.parseJSON(jQuery.cookie('qpd'));
						
						for(var i = 0; i < cookies['quickprodisc'].length; i++) {
							if((cookies['quickprodisc'][i]['product_variant_id'] == jQuery('select[name=\'id\']').val()) && (cookies['quickprodisc'][i]['variant_id'])) {
								var cookies_item = cookies['quickprodisc'][i]; 
								break;
							} else {
								var cookies_item = false;
							}
						}
						if(cookies_item) {
							var interval = setInterval(function(){
								jQuery.getJSON('/cart.js', function(cart_data) {
									for(var n=0; n<cart_data['items'].length; n++) {
										if((cart_data['items'][n]['product_id'] == cookies_item['product_id']) && (cart_data['items'][n]['variant_id'] == cookies_item['product_variant_id'])) {
											jQuery.post('/cart/change.js', {quantity: 0, id: cookies_item['product_variant_id']});
											var interval2 = setInterval(function(){
												jQuery.post('/cart/add.js', {id: cookies_item['variant_id']});
												clearInterval(interval2);
											},30);
											break;
										}
									}
								});
								clearInterval(interval);
							},150);
						}
					}
				});
			}
	  
			function number_format(number, decimals, dec_point, thousands_sep) {
				number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
				var n = !isFinite(+number) ? 0 : +number,
				prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
				sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
				dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
				s = '',
				toFixedFix = function(n, prec) {
					var k = Math.pow(10, prec);
					return '' + (Math.round(n * k) / k).toFixed(prec);
				};
				s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
				if (s[0].length > 3) {
					s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
				}
				if ((s[1] || '').length < prec) {
					s[1] = s[1] || '';
					s[1] += new Array(prec - s[1].length + 1).join('0');
				}
				return s.join(dec);
			}
				
			function ajaxCreateProduct(product_info,discount_price) {
				var product_variant_length = jQuery('select[name=\'id\'] option').length;
				for(var i=0;i<product_variant_length;i++) {
					if(product_info['variants'][i]['id'] == jQuery('select[name=\'id\']').val()) {
						var product_variant = product_info['variants'][i];
						break;
					}
				}				
				jQuery.ajax({
					url: 'http://qdiscount.tentura.eu:8080/product',
					type: 'POST',
					data: {
						product: product_info, shop_id: __st['a'],
						discount_price: discount_price,
						product_variant: product_variant,
						product_url: product_handle2[0]
					},
					crossDomain: true,
					success: function(result){
						var response = jQuery.parseJSON(result);
						var cookies = jQuery.parseJSON(jQuery.cookie('qpd'));
						var array_index = cookies['quickprodisc'].length - 1;
						cookies['quickprodisc'][array_index].variant_id = response['result'];
						jQuery.cookie('qpd', JSON.stringify(cookies), {path: '/'});
						jQuery('.qdiscount-loader').css('display','none');
						buttonReplace(discount_price);						// Replace batton to new price
					},
					error: function (xhr,status,error) {
						//console.log(error);
					}
				});
			}
		});";
        return $code;
    }
}