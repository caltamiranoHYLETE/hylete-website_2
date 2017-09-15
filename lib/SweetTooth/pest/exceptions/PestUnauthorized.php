<?php

/**
 * Pest is a REST client for PHP.
 * PestXML adds XML-specific functionality to Pest, automatically converting
 * XML data resturned from REST services into SimpleXML objects.
 * 
 * In other words, while Pest's get/post/put/delete calls return raw strings,
 * PestXML's return SimpleXML objects.
 *
 * PestXML also attempts to derive error messages from the body of erroneous
 * responses, expecting that these too are in XML (i.e. the contents of
 * the first <error></error> tag in the response is assumed to be the error mssage) 
 *
 * See http://github.com/educoder/pest for details.
 *
 * This code is licensed for use, modification, and distribution
 * under the terms of the MIT License (see http://en.wikipedia.org/wiki/MIT_License)
 */

/* 401 */
require_once 'PestClientError.php';
class Pest_Unauthorized extends Pest_ClientError 
{
    
}
