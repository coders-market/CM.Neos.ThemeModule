<?php

namespace CM\Neos\ThemeModule\Service;

use CM\Neos\ThemeModule\Domain\Model\Font;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use CM\Neos\ThemeModule\Domain\Model\Settings;
use CM\Neos\ThemeModule\Domain\Repository\SettingsRepository;

use Neos\Flow\Property\TypeConverter\ArrayConverter;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;

class Build
{
	const CACHE_IDENTIFIER = 'cm-neos-thememodule-fonts-json';
	const GWF_SERVER = 'http://gwf.coders.market/api/v1';
	const TOKEN = '061207df18a4dcaa21e2a893ca160e87fcc46227';

	/**
	 * @Flow\InjectConfiguration(package="CM.Neos.ThemeModule")
	 * @var array
	 */
	protected $configuration;

	/**
	 * @Flow\Inject
	 * @var SettingsRepository
	 */
	protected $settingsRepository;

	/**
	 * @Flow\Inject
	 * @var SiteRepository
	 */
	protected $siteRepository;

	/**
	 * @Flow\Inject
	 * @var ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @Flow\Inject
	 * @var VariableFrontend
	 */
	protected $cacheFrontend;

	/**
	 * @Flow\Inject
	 * @var Browser
	 */
	protected $browser;

	/**
	 * @Flow\Inject
	 * @var CurlEngine
	 */
	protected $browserRequestEngine;

	/**
	 * @Flow\Inject
	 * @var ArrayConverter
	 */
	protected $arrayConverter;

	/**
	 * @Flow\Inject
	 * @var ResourceManager
	 */
	protected $resourceManager;


	/**
	 * Build Theme Settings based on Settings.yaml and custom values
	 *
	 * @return array
	 */
	public function buildThemeSettings() {
		// Get all settings from yaml
		$themeYamlSettings = $this->configuration['scss']['presetVariables'];

		/** @var Settings $dbSettings */
		$dbSettings = $this->settingsRepository->findActive();

		if(count($dbSettings) > 0 && $dbSettings->getCustomSettings()) {

			$dbCustomSettings = json_decode($dbSettings->getCustomSettings(), true);
			$themeArray = isset($themeYamlSettings) && is_array($themeYamlSettings)
							&& isset($dbCustomSettings) && is_array($dbCustomSettings)
								? array_replace_recursive($themeYamlSettings,$dbCustomSettings) : array();
		} else {
			$themeArray = isset($themeYamlSettings) && is_array($themeYamlSettings) ? $themeYamlSettings : array();
		}

		return $themeArray;
	}

	/**
	 * Build the font array with select option list and array with font details like variants, subsets
	 *
	 * @return array
	 */
	public function buildFontOptions() {
		$settingsFont = array();
		$googleFonts = array();

		if(isset($this->configuration['fontOptions']) && count($this->configuration['fontOptions']) > 0) {
			$settingsFont = $this->parseFonts($this->configuration['fontOptions']);
		}

		if(!isset($this->configuration['addGoogleFonts']) || $this->configuration['addGoogleFonts'] === true) {
			$googleFonts = $this->parseFonts($this->getGoogleWebfonts(), 'FONT_SOURCE_GOOGLE');
		}

		return array_merge_recursive($settingsFont, $googleFonts);
	}



	/**
	 * Request for google webfonts, if cache is outdated, update cache
	 *
	 * @return array
	 */
	protected function getGoogleWebfonts() {

		if ($this->cacheFrontend->has(self::CACHE_IDENTIFIER)) {
			$cachedResponse = $this->cacheFrontend->get(self::CACHE_IDENTIFIER);
			return $cachedResponse;
		} else {
			$requestUrl = self::GWF_SERVER.'?token='.self::TOKEN;
			$this->browser->setRequestEngine($this->browserRequestEngine);
			$this->browser->addAutomaticRequestHeader('Cntm',$this->getHost());
			$response = $this->browser->request($requestUrl);

			if ( $response->getStatusCode() == 200 ) {
				$fontsArray = json_decode($response->getContent(),true);
				$this->cacheFrontend->set(self::CACHE_IDENTIFIER, $fontsArray);
				return $fontsArray;
			} else {
				return array();
			}

		}

	}

	/**
	 * Create array for f:form.select viewhelper options
	 *
	 * @param string $jsonFonts
	 *
	 * @return array
	 */
	protected function getFontsArray($jsonFonts){
		$fontsArray = $this->arrayConverter->convertFrom($jsonFonts,'array');
		return $fontsArray;
	}


	/**
	 * Get domain of page
	 *
	 * @return string
	 */
	protected function getHost(){
		$hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

		/** @var Site $site */
		$site = $this->siteRepository->findFirstOnline();
		if ( $site ) {
			$activeDomains = $site->getActiveDomains();
			foreach ($activeDomains as $activeDomain) {
				$hostname = $activeDomain->getHostname();
			}
		}

		$baseUri = $this->configurationManager->getConfiguration('Settings','Neos.Flow.http.baseUri');

		if($baseUri) {
			$hostname = $baseUri;
		}

		return $hostname;
	}

	/**
	 * Parse the array to a valid output
	 *
	 * @param $fontOptions	array	The defined fonts with all details
	 * @param $fontSource string	Force font source (optional) possible values: FONT_SOURCE_LOCAL, FONT_SOURCE_CDN, FONT_SOURCE_GOOGLE
	 *
	 * @return array
	 */
	function parseFonts($fontOptions, $fontSource = ''){
		$font = array();
		if(isset($fontOptions['items'])){
			foreach ($fontOptions['items'] as $fontItem) {

				$font['options'][$fontItem['category']][] = new Font(
					$fontItem['family'],
					isset($fontItem['category']) ? $fontItem['category'] : '',
					isset($fontItem['variants']) ? $fontItem['variants'] : array(),
					isset($fontItem['subsets']) ? $fontItem['subsets'] : array(),
					isset($fontItem['files']) ? $fontItem['files'] : array(),
					isset($fontItem['fontSource']) ? $fontItem['fontSource'] : 'FONT_SOURCE_GOOGLE'
				);
			}
		}

		return $font;
	}
}