<?php

namespace App\Scripts;

use Google\AdsApi\AdManager\v202002\CustomCriteria;
use Google\AdsApi\AdManager\v202002\CustomCriteriaComparisonOperator;

class SSPScript extends \App\AdManager\Manager
{
	
	

	protected $type;
	protected $orderName;
	protected $advertiserName;
	protected $priceGranularity;
	protected $sizes;
	protected $priceKeyName;
	protected $adidKeyName;
	protected $sizeKeyName;
	protected $ssp;
	protected $currency;
	protected $geoTargetingList;

	public function __construct($params)
	{
		foreach ($params as $key => $value) {
			$this->$key = $value;
		}
	}

	public function createAdUnits()
	{
		$customCriterias = [];
		$geoTargeting = null;
		if($this->geoTargetingList !== null){
			$geoTargeting = (new \App\AdManager\GeoTargetingManager)->setGeoTargeting($this->geoTargetingList);
		}
		if(!empty($this->customTargeting)){
			foreach ($this->customTargeting as $key => $values) {
				$keyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($key);
				$values = explode(",",str_replace(" ", "", $values));
				$values = (new \App\AdManager\ValueManager)->setKeyId($keyId)
					->convertValuesListToDFPValuesList($values);
				$valueIds = [];
				foreach ($values as $value) {
					array_push($valueIds, $value['valueId']);
				}
				$customCriteria = new CustomCriteria();
				$customCriteria->setKeyId($keyId);
				$customCriteria->setOperator(CustomCriteriaComparisonOperator::IS);
				$customCriteria->setValueIds($valueIds);
				array_push($customCriterias, $customCriteria);
			}
		}

		$valuesList = Buckets::createBuckets($this->priceGranularity);

		//Get the Trafficker Id
		$traffickerId = (new \App\AdManager\UserManager())->getUserId();
		echo 'TraffickerId: '.$traffickerId."\n";

		//Get the Advertising Company Id
		$advertiserId = (new \App\AdManager\CompanyManager())->setUpCompany($this->advertiserName);
		echo 'AdvertiserName : '.$this->advertiserName."\tAdvertiserId: ".$advertiserId."\n";

		//Get the OrderId
		$orderId = (new \App\AdManager\OrderManager())->setUpOrder($this->orderName, $advertiserId, $traffickerId);
		echo 'OrderName : '.$this->orderName."\tOrderId: ".$orderId."\n";


		//Create and get KeyIds
		$priceKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->priceKeyName);
		echo 'PriceKeyName : '.$this->priceKeyName."\tPriceKeyId: ".$priceKeyId."\n";
		$adidKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->adidKeyName);
		echo 'AdidKeyName : '.$this->adidKeyName."\tAdidKeyId: ".$adidKeyId."\n";
		$sizeKeyId = (new \App\AdManager\KeyManager())->setUpCustomTargetingKey($this->sizeKeyName);
		echo 'SizeKeyName : '.$this->sizeKeyName."\tSizeKeyId: ".$sizeKeyId."\n";

		//Create and get Values
		$valuesManager = new \App\AdManager\ValueManager();
		$valuesManager->setKeyId($priceKeyId);
		$dfpValuesList = $valuesManager->convertValuesListToDFPValuesList($valuesList);
		echo "Values List Created\n";

		if($this->type == "display"){
			$creativeManager = new \App\AdManager\DisplayCreativeManager();
		}
		if($this->type == "video"){
			$creativeManager = new \App\AdManager\VideoCreativeManager();
		}
		$creativeManager->setSsp($this->ssp)
			->setType($this->type)
			->setAdvertiserId($advertiserId);
		$creativesList = $creativeManager->setUpCreatives();


		echo "\n\n".json_encode($creativesList)."\n\n";
		$rootAdUnitId = (new \App\AdManager\RootAdUnitManager())->setRootAdUnit();
		echo 'rootAdUnitId: '.$rootAdUnitId."\n";

		$i = 0;

		foreach ($dfpValuesList as $dfpValue) {
			if($this->type == "display"){
				$lineItemManager = new \App\AdManager\DisplayLineItemManager();
			}
			if($this->type == "video"){
				$lineItemManager = new \App\AdManager\VideoLineItemManager();
			}

			$lineItemManager->setOrderId($orderId)
				->setSizes($this->sizes)
				->setSsp($this->ssp)
				->setCurrency($this->currency)
				->setKeyId($priceKeyId)
				->setValueId($dfpValue['valueId'])
				->setBucket($dfpValue['valueName'])
				->setCustomCriterias($customCriterias)
				->setRootAdUnitId($rootAdUnitId)
				->setLineItemName();
			if($geoTargeting !== null){
				$lineItemManager->setGeoTargeting($geoTargeting);
			}
			$lineItem = $lineItemManager->setUpLineItem();
			$licaManager = new \App\AdManager\LineItemCreativeAssociationManager();
			$licaManager->setLineItem($lineItem)
				->setCreativeList($creativesList)
				->setType($this->type)
				->setSizeOverride($this->sizes)
				->setUpLica();

			++$i;
			if (empty($this->ssp)) {
				echo "\n\nLine Item Prebid_".$dfpValue['valueName']." created/updated.\n";
			} else {
				echo "\n\nLine Item ".ucfirst($this->ssp).'_Prebid_'.$dfpValue['valueName']." created/updated.\n";
			}

			echo round(($i / count($dfpValuesList)) * 100, 1)."% done\n\n";
		}
		
		(new \App\AdManager\OrderManager())->approveOrder($orderId);
		
	}


	public function updateCreatives($type = "old")
	{
		$advertiserId = (new \App\AdManager\CompanyManager())->setUpCompany($this->advertiserName);
		echo 'AdvertiserName : '.$this->advertiserName."\tAdvertiserId: ".$advertiserId."\n";

		$creativeManager = new \App\AdManager\CreativeManager();
		$creativeManager->setSsp($this->ssp)
			->setAdvertiserId($advertiserId);
		$creativeManager->setUpCreatives($type);
	}

}
