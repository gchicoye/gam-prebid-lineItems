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


$script = new App\Scripts\HeaderBiddingScript;

$script->setCredentials($credentials)
	->createAdUnits($entry)
	->clearCredentials();

