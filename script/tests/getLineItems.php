<?php

putenv('HOME='.dirname(__DIR__)."/../");
require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../customerConfig/GalaxieMedia.php';


$credentials = array(
	"networkCode" => $networkCode,
	"applicationName" => "Prebid",
	"jsonKeyFilePath" => $jsonKeyFilePath,
	"impersonatedEmail" => $impersonatedEmail
);

$foo = new App\Scripts\AdsApiGenerator;
$foo->setCredentials($credentials)
	->generateAdsApi();


$lineItem = (new \App\AdManager\DisplayLineItemManager);
$lineItem->lineItemName = ("Justpremium_Prebid_8.00");
$lineItem->setOrderId(2674817239)
    ->getLineItem();
var_dump($lineItem);

$foo->deleteAdsApi();

if (is_numeric($traffickerId)) {
	echo "\n====Connexion OK====\n\n";
} else {
	echo "\n===Connexion KO====\n\n";
}
