<?php

namespace App\AdManager;

use Google\AdsApi\AdManager\v202102\NetworkService;

class RootAdUnitManager extends Manager
{
	public function setRootAdUnit()
	{
		$networkService = $this->serviceFactory->createNetworkService($this->session);
		$rootAdUnitId = $networkService->getCurrentNetwork()
			->getEffectiveRootAdUnitId();

		return $rootAdUnitId;
	}
}
