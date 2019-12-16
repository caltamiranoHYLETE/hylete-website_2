/**
* © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
* “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
* (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
* Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
* You should read the Agreement carefully before using the code.
*/

function removeTokenChoice() {
    $$('.cyber-payment-token').forEach(function(element) {
        element.checked = false;
    });
    enableCvnInput(999);
}

function enableCvnInput(index) {
    $$('.cyber-payment-token-cvn').forEach(function(element) {
        if (element.readAttribute('data-index') == index) {
            element.enable();
        } else {
            element.value = "";
            element.disable();
            Validation.reset(element);
        }
    });
}

//validation classes for Tokenised Radio buttons and CVNs for Customer Saved Cards(tokens)
Validation.addAllThese([
    ['validate-cyber', 'Please specify a card or add a new card.', function(v, elm) {
        return $$('.cyber-payment-token:checked').length > 0;
    }],
    ['validate-cybercvn', 'Please fill in a CVN Number to continue.', function(v, elm) {
        var index = $(elm).readAttribute('data-index');
        var relatedToken = $('cyber-payment-token' + index);
        return relatedToken && relatedToken.checked && $(elm).value;
    }]
]);

//switches tokens on / off as well as new card details.
function switchNewCard() {
    //makes the card details appear/dissappear to add a new card
    if ($('new_token').value != 1) {
        $('cybersourcesop_cc_type_select_div').setStyle({ 'display': 'block' });
        $('cybersourcesop_cc_type_cc_number_div').setStyle({ 'display': 'block' });
        $('cybersourcesop_cc_type_exp_div').setStyle({ 'display': 'block' });
        $('use_saved_cc').setStyle({ 'display': 'block' });
        //deselect any radio buttons
        //clear validation-passed classes for tokens
        $$('input#token.validate-cyber').className = "validate-cyber";
        //clear validation-passed classes for cvns
        $$('input#cybersourcesop_cc_cid.validate-cvn').className = "validate-cybercvn";
        //hide Tokens
        removeTokenChoice();
        if ($('cybersourcesop_cc_type_cvv_div')) {$('cybersourcesop_cc_type_cvv_div').setStyle({ 'display': 'block' }); }
        $('cybersourcesop_cc_save_div').setStyle({ 'display': 'block' });
        $$('.tokenList')[0].setStyle({ 'display': 'none'});
        $('new_token').value = 1;
        //enable the CVN        
        if ($$('cybersourcesop_cc_cid')) {//PB        
			$$('input[new="1"]')[0].enable();
		}
        return true;
    } else {
        //hide the card details. so user can select token rather.
        $('cybersourcesop_cc_type_select_div').setStyle({ 'display': 'none' });
        $('cybersourcesop_cc_type_cc_number_div').setStyle({ 'display': 'none' });
        $('cybersourcesop_cc_type_exp_div').setStyle({ 'display': 'none' });
        if ($('cybersourcesop_cc_type_cvv_div')) {$('cybersourcesop_cc_type_cvv_div').setStyle({ 'display': 'none' }); }
        $('cybersourcesop_cc_save_div').setStyle({ 'display': 'none' });
        $('use_saved_cc').setStyle({ 'display': 'none' });
        //clear validation-passed classes for tokens
        $$('input#token.validate-cyber').className = "validate-cyber";
        //clear validation-passed classes for cvns
        $$('input#cybersourcesop_cc_cid.validate-cvn').className = "validate-cybercvn";
        //deselect any radio buttons
        removeTokenChoice();
        //show tokens
        $$('.tokenList')[0].setStyle({ 'display': 'block'});
        $('new_token').value = "";
        return false;
    }
}
