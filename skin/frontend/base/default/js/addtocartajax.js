
var AddToCartAjax = Class.create({

	initialize: function(options){

		var defaults = {
			addUrl: '',
			addFromWishlistUrl: '',
			removeUrl: '',
			redirectUrl: '',
			overlayHexColorCode: '',
			overlayOpacity: '0.5',
			popupTimeout: '10',
			popupFadeoutDuration: '0.5',
			defaultQty: '1',
			confirmDeleteMessage: '',
			popupWidth: '255',
			afterCartUpdateFunc: null, 
			showPopupWhenAdding: true,
            showPopupWhenDeleting: true,
			beforeAddFunc: null
		};

		this.options = Object.extend(defaults, options);

		this.options.popupTimeout = parseInt(parseFloat(this.options.popupTimeout) * 1000);
	},

	add: function(clickedNode, productId, buyRelated){
	
		/* BeforeAddFunc is where we can add our custom validation...
		... or any other feature we like..
		*/
		if( this.options.beforeAddFunc && !this.options.beforeAddFunc() ){
			return false;
		}
	
		this.buyRelated = (typeof buyRelated == 'undefined') ? false : buyRelated;
	
		if(!this.isLoading){
	
			var qty = this.options.defaultQty;
	
			if ($('qty_' + productId) != null) {
				qty = $F('qty_' + productId);
			}
	
			var params = '';
			var formNode = $(clickedNode).up('form');
	
			this.parent = $(clickedNode).up('.item');
	
			if(typeof(formNode) == 'undefined'){
				params = 'product=' + productId + '&qty=' + qty;
			}
			else {
	
				var varienForm = new VarienForm($(formNode).readAttribute('id'));
	
				if (!varienForm.validator.validate()) {
					return false;
				}
				params = $(formNode).serialize();
				params += '&product=' + productId;
			}
			if(this.buyRelated){
				params += '&buyRelated=' + this.buyRelated;
				this.showRelated();
				this.isLoading = true;
			}
			else{
				params += '&buyRelated=' + this.buyRelated;
	
				this.isLoading = true;
	
				this.resetPopup();
	
				if(this.options.showPopupWhenAdding == '1'){
					this.showPopup();
				}
				
			}
	
			var r = new Ajax.Request(this.options.addUrl,
				{
					method: 'post',
					parameters: params,
					onSuccess: this.onSuccess.bind(this),
					onFailure: this.onFailure.bind(this)
				});
		}
		else {
			return false;
		}
	},
    addByString: function(params, buyRelated){

   		this.buyRelated = (typeof buyRelated == 'undefined') ? false : buyRelated;

   		if(!this.isLoading){
   			if(this.buyRelated){
   				params += '&buyRelated=' + this.buyRelated;
   				this.showRelated();
   				this.isLoading = true;
   			}
   			else{
   				params += '&buyRelated=' + this.buyRelated;

   				this.isLoading = true;

   				this.resetPopup();
   				this.showPopup();
   			}

   			var r = new Ajax.Request(this.options.addUrl,
   				{
   					method: 'post',
   					parameters: params,
   					onSuccess: this.onSuccess.bind(this),
   					onFailure: this.onFailure.bind(this)
   				});
   		}
   		else {
   			return false;
   		}
   	},
	addFromWishList: function(itemId){

		if(!this.isLoading){

			var params = '',
				qty = 1;

	        var form = $('wishlist-view-form');
	        if (form) {
	            var input = form['qty[' + itemId + ']'];
	            if (input) {
					qty = input.value;
	            }
	        }

			params = 'item=' + itemId + '&qty=' + qty;

			this.isLoading = true;

			this.resetPopup();
			this.showPopup();

			var r = new Ajax.Request(this.options.addFromWishlistUrl,
				{
					method: 'post',
					parameters: params,
					onSuccess: this.onSuccess.bind(this),
					onFailure: this.onFailure.bind(this)
				});
		}
		else {
			return false;
		}
	},

	remove: function(clickedNode, productId){

		if(!this.isLoading){
            this.productId = productId;

            if(this.options.showPopupWhenDeleting) {
                this.resetPopup();
                this.showPopup();

                $('atca-button-container').show();
                $('atca-please-wait').hide();

                // Hide contine shop buttons
                $('atca-continue-btn').hide();
                $('atca-redirect-btn').hide();

                // Show atca yes no buttons
                $('atca-no-btn').show();
                $('atca-yes-btn').show();

                $('atca-message-text').update(this.options.confirmDeleteMessage);
                $('atca-message-text').addClassName('atca-notice');
                $('atca-message-text').show();
            } else {
                this.continueRemove();
            }
		}
		else {
			return false;
		}
	},

	dontRemove: function(){
		this.closePopup();
	},

	continueRemove:function(){

		var url = this.options.removeUrl;

		var params = '';
		params += 'id/' + this.productId;

		url += params;

		this.isLoading = true;

		this.resetPopup();
		$('atca-message-text').hide();
		$('atca-please-wait-remove').show();

		this.isRemoving = true;

		var r = new Ajax.Request(url,
			{
				method: 'get',
				onSuccess: this.onSuccess.bind(this),
				onFailure: this.onFailure.bind(this)
			});
	},

	onSuccess: function(transport){

        if(transport && transport.responseText){

            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch(e){
                response = {};
            }

			$('atca-please-wait').hide();
			$('atca-please-wait-remove').hide();

			if($('atca-please-wait-related')){
				this.pleaseWaitContainer.hide();
				this.pleaseWaitImageContainer.hide();
			}
			if($('atca-please-wait-related') && !response.redirectRelated){
				this.relatedtimeout = setTimeout(function(){
					this.relatedContainer.hide();
					this.relatedText.hide();
					this.relatedImage.hide();
				}.bind(this),2000);
			}

			/*
				Redirect to product page if product has mandatory options or if
				standing on cart/checkout-page and cart has been emptied.
			*/
			if(response.redirect){

				// Show notice message
				$('atca-message-text').update(response.redirect.message);
				$('atca-message-text').addClassName('atca-notice');
				$('atca-message-text').show();

				// Redirect
				window.location = response.redirect.url;
				return false;
			}

			/*
				Redirect to product page if product has mandatory options or if
				standing on cart/checkout-page and cart has been emptied.
			*/
			if(response.redirectRelated){

				$('atca-message-text').hide();
				this.relatedText.show();
				this.relatedImage.show();

				// Show notice message
				this.relatedText.update(response.redirectRelated.message);
				this.relatedText.addClassName('atca-notice');
				this.relatedImage.addClassName('atca-notice');

				// Redirect
				window.location = response.redirectRelated.url;
				return false;
			}

			// Something went wrong...
			if(response.error){
				$('atca-message-text').update(response.error.message);
				$('atca-message-text').addClassName('atca-error');
				if(response.error.html && $('atca-cart')){
					$('atca-cart').update(response.error.html);

					if( this.options.afterCartUpdateFunc ){
						this.options.afterCartUpdateFunc();
					}
				}
			}

			// Something went wrong...
			if(response.errorRelated){

				$('atca-message-text').hide();
				this.relatedText.show();
				this.relatedImage.show();

				this.relatedText.update(response.errorRelated.message);
				this.relatedText.addClassName('atca-error');
				this.relatedImage.addClassName('atca-error');

				if(response.errorRelated.html && $('atca-cart')){
					$('atca-cart').update(response.errorRelated.html);

					if( this.options.afterCartUpdateFunc ){
						this.options.afterCartUpdateFunc();
					}
				}

			}

			// Yeah, added/removed successfully!
			if(response.success){

				$('atca-message-text').update(response.success.message);
				$('atca-related-container').update(response.success.buyRelated);
				$('atca-message-text').addClassName('atca-success');

				// Update cart
				if($('atca-cart')){
					$('atca-cart').update(response.success.html);

					if(response.success.html_quickcheckoutcart){
						if($('quickcheckoutcart-cart-container')){
							$('quickcheckoutcart-cart-container').update(response.success.html_quickcheckoutcart);
							$('quickcheckoutcart-cart-container').fire('quickcheckoutcart:aftercartupdate');
						}
					}

					if(response.success.wishlist_html){
						$$('.my-wishlist').each(function(node){
							node.replace(response.success.wishlist_html);
						}.bind(this));
					}

					var itemCountNumber = (typeof(response.successRelated) === 'object') ? response.successRelated.itemCount : response.success.itemCount,
					itemQtyNumber = (typeof(response.successRelated) === 'object') ? response.successRelated.itemQty : response.success.itemQty;

					// JS hooks
					$('atca-cart').fire('addtocartajax:update', { itemCount: itemCountNumber, itemQty: itemQtyNumber });
					$('atca-cart').fire('quickcheckout:reload');

			        if( this.options.afterCartUpdateFunc ){
			            this.options.afterCartUpdateFunc();
			        }
				}

				if(response.cart_is_empty){
					$('atca-redirect-btn').hide();

					$('atca-continue-btn').setStyle({
						'float': 'none'
					});
				}
			}

			// Yeah, added/removed successfully!
			if(response.successRelated){

				$('atca-message-text').hide();
				this.relatedText.show();
				this.relatedImage.show();
				this.relatedText.update(response.successRelated.message);
				this.relatedText.addClassName('atca-success');
				this.relatedImage.addClassName('atca-success');

				// Update cart
				if($('atca-cart')){
					$('atca-cart').update(response.successRelated.html);

					if(response.successRelated.html_quickcheckoutcart){
						if($('quickcheckoutcart-cart-container')){
							$('quickcheckoutcart-cart-container').update(response.successRelated.html_quickcheckoutcart);
							$('quickcheckoutcart-cart-container').fire('quickcheckoutcart:aftercartupdate');
						}
					}

					if(response.successRelated.wishlist_html){
						$$('.my-wishlist').each(function(node){
							node.replace(response.successRelated.wishlist_html);
						}.bind(this));
					}

					// JS hooks
					$('atca-cart').fire('addtocartajax:update');
					$('atca-cart').fire('quickcheckout:reload');

			        if( this.options.afterCartUpdateFunc ){
			            this.options.afterCartUpdateFunc();
			        }
				}

				if(response.cart_is_empty){
					$('atca-redirect-btn').hide();

					$('atca-continue-btn').setStyle({
						'float': 'none'
					});
				}

			}
			if(!response.successRelated){
				$('atca-message-text').show();
				$('atca-related-container').show();
				$('atca-button-container').show();


				// Show checkout button on product page
				if(response.success && $('atca-checkoutbutton')){
					$('atca-checkoutbutton').setStyle({
						display: 'block'
					});
				}
				if(!response.success.buyRelated){
					this.timeout = setTimeout(function(){
						this.closePopup();
					}.bind(this),this.options.popupTimeout);
				}

			}
			this.isLoading = false;
			this.isRemoving = false;
        }
	},

	onFailure: function(){
		this.closePopup();
	},

	resetPopup: function(){

		if($('atca-continue-btn')){
			$('atca-continue-btn').setStyle({
				'float': 'left'
			});
		}

		if($('atca-no-btn')){
			$('atca-no-btn').hide();
		}

		if($('atca-yes-btn')){
			$('atca-yes-btn').hide();
		}

		if($('atca-continue-btn')){
			$('atca-continue-btn').show();
		}

		if($('atca-redirect-btn')){
			$('atca-redirect-btn').show();
		}

		if($('atca-message-text')){
			$('atca-message-text').hide();
		}

		if($('atca-related-container')){
			$('atca-related-container').hide();
		}

		if($('atca-button-container')){
			$('atca-button-container').hide();	
		}

		if($('atca-message-text')){
			$('atca-message-text').removeAttribute('class');
		}

	},

    hexToRgb: function(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    },

	showPopup: function(){

		// Overlay functionality
		if(this.options.overlayHexColorCode != ''){

			var viewport = document.viewport.getDimensions();
			var windowHeight = viewport.height;

			var bodyNode = $$('body')[0];
			var bodyHeight = bodyNode.getHeight();

			if(bodyHeight > windowHeight){
				windowHeight = bodyHeight;
			}

			if(!this.overlayObserverHasBeenAdded){
				$('atca-overlay').observe('click', function(e){
					if(!this.isLoading){
						this.closePopup();
					}
				}.bind(this));

				this.overlayObserverHasBeenAdded = true;
			}
            var rgbaArray = this.hexToRgb(this.options.overlayHexColorCode);
            var rgba = 'transparent';
            if (rgbaArray != null) {
                rgba = "rgba(" + rgbaArray.r + ", " + rgbaArray.g + ", " + rgbaArray.b + ", " + this.options.overlayOpacity + ")";
            }
			$('atca-overlay').setStyle({
				width: '100%',
				height: windowHeight + 'px',
				background: rgba,
                //background: this.options.overlayOpacity
				//opacity: this.options.overlayOpacity,
				zIndex: '199',
				position: 'absolute',
				top: '0',
				left: '0',
				cursor: 'pointer'
			});

			$('atca-overlay').show();
		}

		$('atca-popup-container').setStyle({
			width: this.options.popupWidth + 'px',
			marginLeft: '-'+(this.options.popupWidth/2)+'px'
		});

		$('atca-popup-container').show();

		if(this.isRemoving){
			$('atca-please-wait-remove').show();
		}
		else {
			$('atca-please-wait').show();
		}


		this.centerElementVertically($('atca-popup-container'));
	},

	showRelated: function(){

		this.pleaseWaitContainer = this.parent.down('#atca-please-wait-related');
		this.pleaseWaitImageContainer = this.parent.down('#atca-please-wait-image-related');
		this.relatedContainer = this.parent.down('#atca-message-text-related-container');
		this.relatedText = this.parent.down('#atca-message-text-related');
		this.relatedImage = this.parent.down('#atca-message-image-related');

		this.relatedText.hide();
		this.relatedImage.hide();

		this.relatedContainer.show();
		this.pleaseWaitContainer.show();
		this.pleaseWaitImageContainer.show();

	},

	closePopup: function(){

		clearTimeout(this.timeout);

		Effect.Fade('atca-popup-container', { duration: this.options.popupFadeoutDuration });

		if($('messages_product_view')){
			Effect.Fade('messages_product_view', { duration: this.options.popupFadeoutDuration });
		}

		if($('atca-overlay')){
			Effect.Fade('atca-overlay', { duration: this.options.popupFadeoutDuration });
		}

		this.isLoading = false;
	},

	centerElementVertically: function(element){

	    if($(element) != null){
			var viewport = document.viewport.getDimensions();
			var windowHeight = viewport.height;

			var scrollOffsets = document.viewport.getScrollOffsets();
			var scrollTop = scrollOffsets.top;

			var yPos = Math.round(windowHeight/2) + scrollTop;
			yPos = yPos - ($(element).getHeight()/2)

			$(element).setStyle({
				top: yPos + 'px'
			});
		}
	},

	redirect: function(url){

		url = typeof(url) != 'undefined' ? url : '';

		if(url == ''){
			url = this.options.redirectUrl;
		}

		window.location = url;
	}
});
