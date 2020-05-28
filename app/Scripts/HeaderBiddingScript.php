<?php

namespace App\Scripts;

class HeaderBiddingScript
{
	protected $adsApi;

	public function setCredentials($credentials)
	{
		$this->adsApi = new \App\Scripts\AdsApiGenerator;
		$this->adsApi->setCredentials($credentials)
			->generateAdsApi();
		return $this;
	}

	public function clearCredentials()
	{
		
		$this->adsApi->deleteAdsApi();
	}

	public function createAdUnits($params)
	{
		

		if(!isset($params['type'])){
			die('Type must be set');
		}
		if(!in_array($params['type'],['display', 'video'])){
			die("Type must be either \"display\" or \"video\"");
		}

		if($params['type'] == 'video'){
			$params['sizes'] = [[640, 360]];
		}
		foreach ($params['ssp'] as $ssp) {
			$param = [
				'type' => $params['type'],
				'orderName' => $params['type'] == 'display' ? $params['orderPrefix'].ucfirst($ssp) : $params['orderPrefix'].ucfirst($ssp). " - Video",
				'advertiserName' => $params['orderPrefix'].ucfirst($ssp),
				'priceGranularity' => $params['priceGranularity'],
				'sizes' => $params['sizes'],
				'priceKeyName' => substr("hb_pb_$ssp", 0, 20),
				'adidKeyName' => substr("hb_adid_$ssp", 0, 20),
				'sizeKeyName' => substr("hb_size_$ssp", 0, 20),
				'currency' => $params['currency'],
				'ssp' => $ssp,

			];
			if(isset($params['geoTargetingList'])){
				$param['geoTargetingList'] = $params['geoTargetingList'];
			}
			if(isset($params['geoTargetingList'])){
				$param['geoTargetingList'] = $params['geoTargetingList'];
			}
			if(isset($params['isOopActive'])){
				$param['isOopActive'] = $params['isOopActive'];
			}
			$script = new SSPScript($param);

			$script->createAdUnits();
		}
		return $this;
	}

	public function updateCreatives($params, $type)
	{
		if(!isset($params['type'])){
			$params['type'] = 'display';
		}
		foreach ($params['ssp'] as $ssp) {
			$param = [
				'type' => $params['type'],
				'orderName' => $params['type'] == 'display' ? $params['orderPrefix'].ucfirst($ssp) : $params['orderPrefix'].ucfirst($ssp). " - Video",
				'advertiserName' => $params['orderPrefix'].ucfirst($ssp),
				'priceGranularity' => $params['priceGranularity'],
				'sizes' => $params['sizes'],
				'priceKeyName' => substr("hb_pb_$ssp", 0, 20),
				'adidKeyName' => substr("hb_adid_$ssp", 0, 20),
				'sizeKeyName' => substr("hb_size_$ssp", 0, 20),
				'currency' => $params['currency'],
				'ssp' => $ssp,
			];
			$script = new SSPScript($param);

			$script->updateCreatives($type);
		}
		return $this;
	}

	public function createGlobalAdunits($params)
	{
		if(!isset($params['type'])){
			$params['type'] = 'display';
		}
		$params = [
			'type' => $params['type'],
			'orderName' => $params['type'] == 'display' ? $params['orderPrefix'] : $params['orderPrefix']." - Video",
			'advertiserName' => $params['orderPrefix'],
			'priceGranularity' => $params['priceGranularity'],
			'sizes' => $params['sizes'],
			'priceKeyName' => substr('hb_pb', 0, 20),
			'adidKeyName' => substr('hb_adid', 0, 20),
			'sizeKeyName' => substr('hb_size', 0, 20),
			'currency' => $params['currency'],
			'ssp' => '',
		];
		$script = new SSPScript($params);

		$script->createAdUnits();
	}
}
