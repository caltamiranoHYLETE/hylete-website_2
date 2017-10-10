<?php
header('Content-type: application/json; charset=utf-8');

$url = $_GET['requrl'];
$json = file_get_contents($url);

$doc = new DOMDocument();
$doc->loadXML($json);

$items = $doc->getElementsByTagName('string');

for ($i = 0; $i < $items->length; $i++) {
    echo $items->item($i)->nodeValue . "\n";
}
	
?>