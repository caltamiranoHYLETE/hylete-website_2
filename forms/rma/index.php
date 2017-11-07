<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
    <head>
        <title>Newgistics Test</title>
        <script type="text/javascript" src="/forms/js/jquery.js"></script>
        <script type="text/javascript">
            $().ready(function()
            {
                $( '#MakeARequest' ).submit( function()
                {
                    var apiKey = '1680b6d0e99f43649c03f34919cd5b91';
                    var NgsMid = '2262';
                    var orderID = $('#OrderID').val();

                    alert('<?xml version="1.0" encoding="utf-16"?><ReturnCenterXmlFormData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="Newgistics.ReturnCenter.3.0"><FormData><OrderSelection><FormID>21</FormID><NGSMerchantID>' + NgsMid + '</NGSMerchantID><SecurityIdentity><IdentityType>Consumer</IdentityType></SecurityIdentity><Order><Identifier Qualifier="AtLastAPIKey" Value="' + apiKey + '" Default="true" xmlns="urn:Newgistics.Document.2.0" /><OrderID Qualifier="OrderID" Value="' + orderID + '" Default="true" xmlns="urn:Newgistics.Document.2.0" /></Order></OrderSelection></FormData></ReturnCenterXmlFormData>' );

                    $( '#SLORequest' ).val( '<?xml version="1.0" encoding="utf-16"?><ReturnCenterXmlFormData xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="Newgistics.ReturnCenter.3.0"><FormData><OrderSelection><FormID>21</FormID><NGSMerchantID>' + NgsMid + '</NGSMerchantID><SecurityIdentity><IdentityType>Consumer</IdentityType></SecurityIdentity><Order><Identifier Qualifier="AtLastAPIKey" Value="' + apiKey + '" Default="true" xmlns="urn:Newgistics.Document.2.0" /><OrderID Qualifier="OrderID" Value="' + orderID + '" Default="true" xmlns="urn:Newgistics.Document.2.0" /></Order></OrderSelection></FormData></ReturnCenterXmlFormData>' );

                } );
            } );
        </script>
    </head>
    <body>
        <form id="MakeARequest" name="MakeARequest" method="post" action="https://www.shipmentmanager.com/portal.aspx">
            <input type="hidden" id="SLORequest" name="SLORequest" />
            Order ID: <input type="text" id="OrderID" />
            <input type="submit" value="Search" />
        </form>
    </body>
</html>