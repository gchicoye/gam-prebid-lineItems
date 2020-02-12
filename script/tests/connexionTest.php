<?php

putenv('HOME='.dirname(__DIR__)."/../");
require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../customerConfigSample.php';

$credentials = array(
	"networkCode" => $networkCode,
	"applicationName" => "Prebid",
	"jsonKeyFilePath" => $jsonKeyFilePath,
	"impersonatedEmail" => $impersonatedEmail
);

$foo = new App\Scripts\AdsApiGenerator;
$foo->setCredentials($credentials)
	->generateAdsApi();


$traffickerId = (new \App\AdManager\UserManager())->getUserId();

$foo->deleteAdsApi();

if (is_numeric($traffickerId)) {
	echo "\n====Connexion OK====\n\n";
} else {
	echo "\n===Connexion KO====\n\n";
}
