/**
 * Subscribe Pro - Subscriptions Management Extension
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to commercial source code license of SUBSCRIBE PRO INC.
 *
 * @category  SubscribePro
 * @package   SubscribePro_Autoship
 * @author    Subscribe Pro Support <support@subscribepro.com>
 * @copyright 2009-2020 SUBSCRIBE PRO INC. All Rights Reserved.
 * @license   http://www.subscribepro.com/terms-of-service/ Subscribe Pro Terms of Service
 * @link      http://www.subscribepro.com/
 *
 */

$j = jQuery;

$j(document).ready(function(){
    $j(".new-subscription").on('click', '#subscribe', function(e){
        e.preventDefault();
        var form = $j(this);
        $j('.summary-block .messages').html("");
        $j.post(form.attr('href'), {
            "product_id": $j("#product_id").val(),
            "delivery_date": $j("#delivery_date").val(),
            "qty": $j("#delivery_qty").val(),
            "interval": $j("#delivery_interval").val()
        }, function(transport){
            if(transport.match("error")){
                $j('.summary-block .messages').html(transport);
            } else {
                window.location = transport;
            }
        });
    });
});
