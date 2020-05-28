<?php

putenv('HOME='.dirname(__DIR__)."/../");
require __DIR__.'/../../vendor/autoload.php';
require __DIR__.'/../../customerConfig/Cambium.php';


$credentials = array(
	"networkCode" => $networkCode,
	"applicationName" => "Prebid",
	"jsonKeyFilePath" => $jsonKeyFilePath,
	"impersonatedEmail" => $impersonatedEmail
);

$foo = new App\Scripts\AdsApiGenerator;
$foo->setCredentials($credentials)
	->generateAdsApi();


$advertiser = (new \App\AdManager\CompanyManager)->getCompany('Test');
$advertiserId = $advertiser[0]['companyId'];

echo $advertiserId;

$creative = (new \App\AdManager\VideoCreativeManager);
$creative->setAdvertiserId($advertiserId)
	->getCreative("Test-Preroll_AdUnit TH Test-Preroll-AdUnit 640x360v VAST redirect 18 19");

die("OUUUPS");


$creative = (new \App\AdManager\DisplayCreativeManager);
$creative->setAdvertiserId($advertiserId)
    ->getCreative("Justpremium_Prebid_Creative_1");
var_dump($creative);

$foo->deleteAdsApi();

if (is_numeric($traffickerId)) {
	echo "\n====Connexion OK====\n\n";
} else {
	echo "\n===Connexion KO====\n\n";
}
