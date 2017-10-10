<?php
// Smart GET function
function GET($name=NULL, $value=false, $option="default")
{
    $option=false; // Old version depricated part
    $content=(!empty($_GET[$name]) ? trim($_GET[$name]) : (!empty($value) && !is_array($value) ? trim($value) : false));
    if(is_numeric($content))
        return preg_replace("@([^0-9])@Ui", "", $content);
    else if(is_bool($content))
        return ($content?true:false);
    else if(is_float($content))
        return preg_replace("@([^0-9\,\.\+\-])@Ui", "", $content);
    else if(is_string($content))
    {
        if(filter_var ($content, FILTER_VALIDATE_URL))
            return $content;
        else if(filter_var ($content, FILTER_VALIDATE_EMAIL))
            return $content;
        else if(filter_var ($content, FILTER_VALIDATE_IP))
            return $content;
        else if(filter_var ($content, FILTER_VALIDATE_FLOAT))
            return $content;
        else
            return preg_replace("@([^a-zA-Z0-9\+\-\_\*\@\$\!\;\.\?\#\:\=\%\/\ ]+)@Ui", "", $content);
    }
    else false;
}

function encryptQueryString($qryStr, $key){
	$query = base64_encode(urlencode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $qryStr, MCRYPT_MODE_CBC, md5(md5($key)))));
	return $query;
}

function decryptQueryString($qryStr, $key) {
	$queryString = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), urldecode(base64_decode($qryStr)), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
	
	return $queryString;
}
?>