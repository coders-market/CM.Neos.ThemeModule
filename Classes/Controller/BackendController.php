<?php
namespace CM\Neos\ThemeModule\Controller;

use TYPO3\Flow\Cache\Frontend\VariableFrontend;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Log\SystemLoggerInterface;
use TYPO3\Flow\Mvc\Controller\ActionController;
use TYPO3\Flow\Http\Client\Browser;
use TYPO3\Flow\Http\Client\CurlEngine;
use CM\Neos\ThemeModule\Domain\Model\Settings;
use CM\Neos\ThemeModule\Domain\Repository\SettingsRepository;
use CM\Neos\ThemeModule\FileUtility;
use Leafo\ScssPhp\Compiler;
use TYPO3\Flow\Property\TypeConverter\ArrayConverter;
use TYPO3\Neos\Domain\Model\Site;
use TYPO3\Neos\Domain\Repository\SiteRepository;
use TYPO3\Flow\Utility\Unicode\Functions;

class BackendController extends ActionController {

	const CACHE_IDENTIFIER = 'cm-neos-thememodule-fonts-json';

	const GWF_SERVER = 'http://gwf.coders.market/api/v1';
	const TOKEN = '061207df18a4dcaa21e2a893ca160e87fcc46227';

	/**
	 * @Flow\Inject
	 * @var SystemLoggerInterface
	 */
	protected $systemLogger;

	/**
	 * @Flow\Inject
	 * @var FileUtility
	 */
	protected $filesUtilities;

	/**
	 * @Flow\Inject
	 * @var ArrayConverter
	 */
	protected $arrayConverter;

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
	 * @Flow\InjectConfiguration(package="CM.Neos.ThemeModule")
	 * @var array
	 */
	protected $configuration;

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
	 * Default index action
	 */
	public function indexAction() {

		/** @var Settings $dbSettings */
		$activeSettings = $this->settingsRepository->findActive();

		if ( !$activeSettings ) {
			$activeSettings = new Settings();
		}

		$themeSettings = $this->buildThemeSettings();

		$this->view->assignMultiple(array(
			'settings' => $activeSettings,
			'themeSettings' => $themeSettings
		));
	}


	/**
	 * Update theme settings
	 *
	 * @param Settings 	$settings 			Custom theme setting object
	 * @param array 	$customSettings		Custom settings for the theme
	 */
	public function updateAction(Settings $settings, $customSettings = array()){

		$settings->setCustomSettings(json_encode($customSettings));

		if ($settings instanceof Settings && $this->persistenceManager->isNewObject($settings)) {
			$this->settingsRepository->add($settings);
		} elseif ( $settings instanceof Settings ) {
			$this->settingsRepository->update($settings);
		}

		$this->compileScss($settings);

		$this->redirect('index');
	}


	/**
	 * Compile scss to css and add custom scss/css
	 *
	 * @param Settings $settings current settings
	 */
	private function compileScss(Settings $settings){

		$themeSettings = $this->buildThemeSettings();

		$scssVars = array();

		foreach ($themeSettings as $group) {
			foreach ($group['type'] as $typeKey => $typeValue) {
				foreach ($typeValue as $element) {
					if ($typeKey === 'font') {
						$scssVars[$element['scssVariableName']] = '"' . $element['value'] .'", ' . $element['fontFallbackValue'];
					} else {
						$scssVars[$element['scssVariableName']] = $element['value'];
					}

				}
			}
		}

		try {

			// get absolute path to scss folder
			$pathParts = Functions::parse_url($this->configuration['scss']['importPaths']);
			$scssAbsolutePath = FLOW_PATH_ROOT . 'Packages/Sites/'. $pathParts['host'] .'/Resources' . $pathParts['path'];
			$scssAbsolutePath = FileUtility::getUnixStylePath($scssAbsolutePath);

			$scss = new Compiler();
			$scss->setImportPaths($scssAbsolutePath);

			$scss->setFormatter($this->configuration['scss']['formatter']);
			$scss->setVariables($scssVars);

			$mainScssFileAndPath = FileUtility::concatenatePaths(array($this->configuration['scss']['importPaths'],$this->configuration['scss']['mainScssFile']));

			$mainScssContent = FileUtility::getFileContents($mainScssFileAndPath);

			if( $settings->getCustomScss() ) {
				// add custom scss code to the end of the file
				$mainScssContent = $mainScssContent . "\n" . $settings->getCustomScss();
			}

			//$compiledCss = $scss->compile($mainScssContent);
			$compiledCss = $scss->compile($mainScssContent);

			if( $settings->getCustomCss() ) {
				// add custom css code to the end of the file
				$compiledCss = $compiledCss . "\n" . $settings->getCustomCss();
			}

			FileUtility::writeStaticFile($this->configuration['scss']['outputPath'],$this->configuration['scss']['outputFilename'],$compiledCss);

			$this->systemLogger->log('Scss successfully compiled');

		} catch (\Exception $e) {
			$this->systemLogger->logException($e,array('message' => 'Compiling scss was not successful'));
		}
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
	 * Merge google fonts with custom defined yaml fonts
	 *
	 * @return array
	 */
	function addGoogleFonts($themeArray) {
		$googleFonts = $this->getGoogleWebfonts();
		$googleFonts = array_column($googleFonts['items'],'family', 'family');

		foreach($themeArray as $groupKey=>$groupValue)
		{
			foreach ($groupValue['type'] as $typeKey=>$typeValue) {
				if ($typeKey === 'font') {
					foreach ($typeValue as $elementKey=>$elementValue) {
						if (isset($themeArray[$groupKey]['type'][$typeKey][$elementKey]['options'])){
							$themeArray[$groupKey]['type'][$typeKey][$elementKey]['options'] = array_replace_recursive($themeArray[$groupKey]['type'][$typeKey][$elementKey]['options'], $googleFonts);
						} else {
							$themeArray[$groupKey]['type'][$typeKey][$elementKey]['options'] = array();
							$themeArray[$groupKey]['type'][$typeKey][$elementKey]['options'] = $googleFonts;
						}

					}
				}
			}
		}

		return $themeArray;
	}

	/**
	 * Get domain of page
	 *
	 * @return string
	 */
	protected function getHost(){
		$hostname = '';

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
	 * Build Theme Settings based on Settings.yaml and custom values
	 *
	 * @return array
	 */
	protected function buildThemeSettings() {
		// Get all settings from yaml
		$themeYamlSettings = $this->configuration['scss']['presetVariables'];

		/** @var Settings $dbSettings */
		$dbSettings = $this->settingsRepository->findActive();

		if(count($dbSettings) > 0 && $dbSettings->getCustomSettings()) {

			$dbCustomSettings = json_decode($dbSettings->getCustomSettings(), true);
			$themeArray = array_replace_recursive($themeYamlSettings,$dbCustomSettings);
		} else {
			$themeArray = $themeYamlSettings;
		}

		$themeArray = $this->addGoogleFonts($themeArray);

		return $themeArray;
	}

}
