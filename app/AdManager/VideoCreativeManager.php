<?php

namespace App\AdManager;

use Google\AdsApi\AdManager\Util\v202102\StatementBuilder;
use Google\AdsApi\AdManager\v202102\CreativeService;
use Google\AdsApi\AdManager\v202102\VastRedirectCreative;
//use Google\AdsApi\AdManager\v202102\VideoRedirectAsset;
use Google\AdsApi\AdManager\v202102\Size;
use Google\AdsApi\AdManager\v202102\ApiException;

class VideoCreativeManager extends Manager
{
	protected $ssp;
	protected $advertiserId;
	protected $type;
	protected $sizes;

	public function setSsp($ssp)
	{
		$this->ssp = $ssp;

		return $this;
	}

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function setAdvertiserId($advertiserId)
	{
		$this->advertiserId = $advertiserId;

		return $this;
	}

	public function setUpCreatives()
	{

		$output = [];
		//Create a creativeName List
		$creativeNameList = [];

		$sizes = [[640, 360], [400,300], [640, 480]];
		foreach($sizes as $size){
			if (empty($this->ssp)) {
				array_push($creativeNameList, "Prebid_Creative_Video - ".json_encode($size));
			} else {
				array_push($creativeNameList, ucfirst($this->ssp)."_Prebid_Creative_Video - ".json_encode($size));
			}
		}

		foreach ($creativeNameList as $i => $creativeName) {
			if (empty(($foo = $this->getCreative($creativeName)))) {
				$foo = $this->createCreative($creativeName, $this->createSnippet(), $this->advertiserId, $sizes[$i]);
			} else {
				$foo = $this->updateCreative($creativeName, $this->createSnippet(), $this->advertiserId, $sizes[$i]);
			}
			array_push($output, $foo[0]);
		}

		return $output;
	}

	public function getAllCreatives()
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;
		$statementBuilder = (new StatementBuilder())->orderBy('id ASC')
			->limit($pageSize);

		$totalResultSetSize = 0;
		do {
			$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			if (null == $data->getResults()) {
				return $output;
			}
			foreach ($data->getResults() as $creative) {
				$foo = [
					'creativeId' => $creative->getId(),
					'creativeName' => $creative->getName(),
				];

				array_push($output, $foo);
				$statementBuilder->increaseOffsetBy($pageSize);
			}
		} while ($statementBuilder->getOffset() < $totalResultSetSize);

		return $output;
	}

	public function getCreative($creativeName)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$statementBuilder = (new StatementBuilder())
			->orderBy('id ASC')
			->where('name = :name AND advertiserId = :advertiserId')
			->WithBindVariableValue('name', $creativeName)
			->WithBindVariableValue('advertiserId', $this->advertiserId);
		do{
			try{
				$data = $creativeService->getCreativesByStatement($statementBuilder->toStatement());
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		if (null !== $data->getResults()) {
			foreach ($data->getResults() as $creative) {
				$foo = [
					'creativeId' => $creative->getId(),
					'creativeName' => $creative->getName(),
				];
				array_push($output, $foo);
			}
		}

		return $output;
	}

	public function createCreative($creativeName, $snippet, $advertiserId, $sizeArray)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$size = new Size();
		$size->setWidth($sizeArray[0]);
		$size->setHeight($sizeArray[1]);
		$size->setIsAspectRatio(false);

		$creative = new VastRedirectCreative();

		$creative->setName($creativeName)
			->setAdvertiserId($advertiserId)
			->setVastXmlUrl($snippet)
			//->setAllowDurationOverride(true)
			->setDuration(1000)
			->setSize($size);

		// Create the order on the server.
		do{
			try{
				$results = $creativeService->createCreatives([$creative]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	public function updateCreative($creativeName, $snippet, $advertiserId, $sizeArray)
	{
		$output = [];
		$creativeService = $this->serviceFactory->createCreativeService($this->session);
		$statementBuilder = (new StatementBuilder())->where('name = :name')
            ->orderBy('id ASC')
            ->limit(1)
            ->withBindVariableValue('name', $creativeName);
        // Get the creative.
        $page = $creativeService->getCreativesByStatement(
            $statementBuilder->toStatement()
        );

		$creative = $page->getResults()[0];
		$size = new Size();
		$size->setWidth($sizeArray[0]);
		$size->setHeight($sizeArray[1]);
		$size->setIsAspectRatio(false);

		$creative->setName($creativeName)
			->setAdvertiserId($advertiserId)
			->setVastXmlUrl($snippet)
			//->setAllowDurationOverride(true)
			->setDuration(1000)
			->setSize($size);

		// Create the order on the server.
		do {
			try {
				$results = $creativeService->updateCreatives([$creative]);
			} catch (ApiException $Exception) {
				echo "\n\n======EXCEPTION======\n\n";
				$ApiErrors = $Exception->getErrors();
				foreach ($ApiErrors as $Error) {
					printf(
						"There was an error on the field '%s', caused by an invalid value '%s', with the error message '%s'\n",
					$Error->getFieldPath(),
					$Error->getTrigger(),
					$Error->getErrorString()
					);
				}
				++$attempts;
				sleep(30);
				continue;
			}
			break;
		} while ($attempts < 5);
		
		foreach ($results as $creative) {
			$foo = [
				'creativeId' => $creative->getId(),
				'creativeName' => $creative->getName(),
			];
			array_push($output, $foo);
		}

		return $output;
	}

	/*
	private function createSnippet()
	{
		$snippet = "<script src = 'https://cdn.jsdelivr.net/npm/prebid-universal-creative@latest/dist/creative.js'></script>\n";
		$snippet .= "<script>\n";
		$snippet .= "\tvar ucTagData = {};\n";
		$snippet .= "\tucTagData.adServerDomain = '';\n";
		$snippet .= "\tucTagData.pubUrl = '%%PATTERN:url%%';\n";
		$snippet .= "\tucTagData.targetingMap = %%PATTERN:TARGETINGMAP%%;\n";
		$snippet .= "\ttry {\n";
		$snippet .= "\t\tucTag.renderAd(document, ucTagData);\n";
		$snippet .= "\t} catch (e) {\n";
    	$snippet .= "\t\tconsole.log(e);\n";
    	$snippet .= "\t}\n";
    	$snippet .= "</script>\n";

    	return $snippet;

	}
	*/

	private function createSnippet()
	{
		if (empty($this->ssp)) {
			$key = substr('hb_uuid', 0, 20);
		} else {
			$key = substr('hb_uuid_'.$this->ssp, 0, 20);
		}
		$snippet = "https://prebid.adnxs.com/pbc/v1/cache?uuid=%%PATTERN:".$key."%%";

    	return $snippet;
		
	}

	
}
