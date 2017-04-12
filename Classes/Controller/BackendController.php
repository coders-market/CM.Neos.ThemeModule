<?php
namespace CM\Neos\ThemeModule\Controller;

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
		$activeSettings = $this->settingsRepository->findActive();

		$fonts = $this->getGoogleWebfonts();

		if (!$activeSettings) {
			$activeSettings = new Settings();
		}

		$this->view->assignMultiple(array(
			'settings' => $activeSettings,
			'fonts' => array_column($fonts['items'],'family', 'family')
		));
	}


	/**
	 * Update theme settings
	 *
	 * @param Settings $settings Custom theme setting object
	 */
	public function updateAction(Settings $settings){
		xdebug_break();

		if ($settings instanceof Settings && $this->persistenceManager->isNewObject($settings)) {
			$this->settingsRepository->add($settings);
		} elseif ( $settings instanceof Settings ) {
			$this->settingsRepository->update($settings);
		}

		$this->compileScss($settings->getCustomCss(), $settings->getCustomScss());

		$this->redirect('index');
	}


	/**
	 * Compile scss to css and add custom scss/css
	 *
	 * @param string $customCss 	Custom css code to append
	 * @param string $customScss 	Custom scss code to append
	 * @param string $customFont 	The selected font
	 */
	private function compileScss($customCss = '', $customScss = '', $customFont = ''){

		try {

			$scss = new Compiler();
			$scss->setImportPaths($this->configuration['scss']['importPaths']);
			$scss->setFormatter($this->configuration['scss']['formatter']);

			$mainScssFileAndPath = FileUtility::concatenatePaths(array($this->configuration['scss']['importPaths'],$this->configuration['scss']['mainScssFile']));

			$mainScssContent = FileUtility::getFileContents($mainScssFileAndPath);

			if( $customScss ) {
				// add custom scss code to the end of the file
				$mainScssContent = $mainScssContent . "\n" . $customScss;

			}

			$compiledCss = $scss->compile($mainScssContent);

			if( $customCss ) {
				// add custom css code to the end of the file
				$compiledCss = $compiledCss . "\n" . $customCss;
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
}
