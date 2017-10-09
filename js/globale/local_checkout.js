var Pointers = new Array();
Pointers['billing'] = [
    "#billing_customer_address",
    "#billing-address-select",
    "#billing\\:country_id"
];
Pointers['shipping'] = [
    "#shipping_customer_address",
    "#shipping-address-select",
    "#shipping\\:country_id"
];

var use_for = new Array();
use_for = [
    "#billing\\:use_for_shipping_yes",
    "#shipping\\:same_as_billing"
];

var countryCode;
var defaultCountryCode;
var inGEProcess = false;
var GEIsInCheckout = true;


function convertAddressToCountryCode(countryCode){

    if(jQuery.isNumeric(countryCode)){
        countryCode = CustomerAddresses[countryCode]['country_id'];
    }
    return countryCode;
}


function applyGEEvent(){

    jQuery( Pointers['billing'].join() ).each(function(){
        jQuery(this).change(function() {
            if(jQuery(use_for.join()).is(':checked')) {
                handleCountry('billing', null, this);
            }
        });
    });

    jQuery(use_for.join()).change(function() {
        handleCountry('billing');
    });

    jQuery( Pointers['shipping'].join() ).each(function(){
        jQuery(this).change(function() {
            handleCountry('shipping', null, this);
        });
    });

}


function handleCountry(type, parentMethod, Pointer){
    if(typeof(Pointer) !== 'undefined'){
        countryCode = jQuery( Pointer ).val();
    }
    else{
        countryCode = jQuery( Pointers[type].join() ).val();
    }
    //defaultCountryCode = countryCode;
    countryCode = convertAddressToCountryCode(countryCode);
    inGEProcess = true;
    if(typeof(Pointer) !== 'undefined'){
        return handleGERedirect(countryCode, jQuery( Pointer ), parentMethod);
    }
    else{
        return handleGERedirect(countryCode, jQuery( Pointers[type].join() ), parentMethod);
    }
}


function handleGERedirect( country_code, Pointer, failCallback ){

    if(jQuery.inArray(country_code, GlobaleOperatedCountries) != -1){
        //call popup
        gle("ShippingSwitcher","show",country_code,function(data){
            if(!inGEProcess){
                return false;
            }
            //country & currency are the new select values
            //Redirect to merchant cart page
            if(data.isOperatedByGlobale == true){
                window.location = MerchantCartUrl;
            }else{
                Pointer.val(defaultCountryCode);
            }
            inGEProcess = false;
            return false;
        });

        return false;
    }
    else{
        if(typeof failCallback == 'function'){
            failCallback();
        }
        return true;
    }
}


document.addEventListener("DOMContentLoaded", function(event) {

    /** //////////////////////// START OnePage Section //////////////////////// */

    defaultCountryCode = jQuery( Pointers['billing'].join() ).val();
    if(jQuery(use_for.join()).is(':checked')){
        handleCountry('billing');
    }
    else{
        handleCountry('shipping');
    }

    applyGEEvent();

    /*  */
    if(typeof(Billing) !== 'undefined'){
        Billing.prototype.save = Billing.prototype.save.wrap(function(parentMethod){
            if(jQuery(use_for.join()).is(':checked')){
                handleCountry('billing', parentMethod);
            }
            else{
                parentMethod();
            }
        });
    }

    /*  */
    if(typeof(Shipping) !== 'undefined'){
        Shipping.prototype.save = Shipping.prototype.save.wrap(function(parentMethod){
            handleCountry('shipping', parentMethod);
        });
    }

    /** //////////////////////// END OnePage Section //////////////////////// */

    /** //////////////////////// START OnePageCheckout Section //////////////////////// */

    Validation.add('validate-select','',function(v){

        var Pointer;
        var Validate;
        if(jQuery(use_for.join()).is(':checked')){
            if(jQuery( Pointers['billing'].join() ).val().trim() == ""){
                Pointer = Pointers['billing'][Pointers['billing'].length-1];
            }
            Validate = handleCountry('billing', null, Pointer);
        }
        else{
            if(jQuery( Pointers['shipping'].join() ).val().trim() == ""){
                Pointer = Pointers['shipping'][Pointers['shipping'].length-1];
            }
            Validate = handleCountry('shipping', null, Pointer);
        }

        return Validate;

    });

    /** //////////////////////// END OnePageCheckout Section //////////////////////// */

});