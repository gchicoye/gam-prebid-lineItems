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

//$network= (new \App\AdManager\NetworkManager)->makeTestNetwork();

$user = (new \App\AdManager\UserManager)->createUser("John Doe","john.doe@gmail.com");