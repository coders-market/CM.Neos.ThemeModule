<?php
namespace CM\Neos\ThemeModule\Controller;

use CM\Neos\ThemeModule\Domain\Model\Font;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use CM\Neos\ThemeModule\Domain\Model\Settings;
use CM\Neos\ThemeModule\Domain\Repository\SettingsRepository;
use CM\Neos\ThemeModule\FileUtility;
use Leafo\ScssPhp\Compiler;
use Neos\Flow\Property\TypeConverter\ArrayConverter;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Utility\Unicode\Functions;

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
	 * @var array
	 */
	protected $fonts = array();

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

		$fonts = $this->buildFontOptions();

		$this->view->assignMultiple(array(
			'settings' => $activeSettings,
			'themeSettings' => $themeSettings,
			'fonts' => $fonts
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
						$scssVars[$element['scssVariableName']] = '"' . $element['value']['family'] .'", ' . $element['fontFallbackValue'];
					} else {
						$scssVars[$element['scssVariableName']] = $element['value'];
					}

				}
			}
		}

		$scssFontFace = $this->buildFontFaceScss();

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

			$mainScssContent = $this->addFontScss();

			$mainScssContent = $mainScssContent . FileUtility::getFileContents($mainScssFileAndPath);

			if( $settings->getCustomScss() ) {
				// add custom scss code to the end of the file
				$mainScssContent = $mainScssContent . "\n" . $settings->getCustomScss();
			}

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
	 * Build FontFace Scss code for selected fonts
	 *
	 * @return string
	 */
	private function buildFontFaceScss() {
		$fontface = '';

		return $fontface;
	}

	/**
	 * Build the font array with select option list and array with font details like variants, subsets
	 *
	 * @return array
	 */
	private function buildFontOptions() {
		$settingsFont = array();
		$googleFonts = array();

		if(isset($this->configuration['fontOptions']) && count($this->configuration['fontOptions']) > 0) {
			$settingsFont = $this->fonts = $this->parseFonts($this->configuration['fontOptions']);
		}

		if(isset($this->configuration['addGoogleFonts']) && $this->configuration['addGoogleFonts'] !== false) {
			$googleFonts = $this->fonts = $this->parseFonts($this->getGoogleWebfonts(), 'FONT_SOURCE_GOOGLE');
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

				if(isset($fontItem['fontSource']) && $fontItem['fontSource'] !== '' && $fontSource === '') {
					$fontSource = $fontItem['fontSource'];
				}

				$font['options'][$fontItem['category']][] = new Font(
					$fontItem['family'],
					isset($fontItem['category']) ? $fontItem['category'] : '',
					isset($fontItem['variants']) ? $fontItem['variants'] : array(),
					isset($fontItem['subsets']) ? $fontItem['subsets'] : array(),
					$fontItem['files'],
					$fontSource
				);
			}
		}

		return $font;
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

		return $themeArray;
	}

}
