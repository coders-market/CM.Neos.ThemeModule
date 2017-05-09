<?php
namespace CM\Neos\ThemeModule\Controller;

use CM\Neos\ThemeModule\Service\Build;
use CM\Neos\ThemeModule\Service\Compile;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cache\CacheManager;
use Neos\Flow\Mvc\Controller\ActionController;
use CM\Neos\ThemeModule\Domain\Model\Settings;
use CM\Neos\ThemeModule\Domain\Repository\SettingsRepository;


class BackendController extends ActionController {


	/**
	 * @Flow\Inject
	 * @var SettingsRepository
	 */
	protected $settingsRepository;


	/**
	 * @Flow\Inject
	 * @var Build
	 */
	protected $buildService;

	/**
	 * @Flow\Inject
	 * @var Compile
	 */
	protected $compile;

	/**
	 * @Flow\Inject
	 * @var CacheManager
	 */
	protected $cacheManager;


	/**
	 * Default index action
	 */
	public function indexAction() {

		/** @var Settings $dbSettings */
		$activeSettings = $this->settingsRepository->findActive();

		if ( !$activeSettings ) {
			$activeSettings = new Settings();
		}

		$themeSettings = $this->buildService->buildThemeSettings();

		$fonts = $this->buildService->buildFontOptions();

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

		$this->compile->compileScss($settings, $customSettings);

		// Make sure all page caches get flushed
		$this->cacheManager->flushCachesByTag('DescendantOf_'.strtr('Neos.Neos:Page', '.:', '_-'), true);
		$this->cacheManager->flushCachesByTag('DescendantOf_'.strtr('Neos.NodeTypes:Page', '.:', '_-'), true);
		$this->cacheManager->flushCachesByTag('DescendantOf_'.strtr('Neos.Neos:Document', '.:', '_-'), true);

		$this->cacheManager->flushCachesByTag('NodeType_'.strtr('Neos.Neos:Page', '.:', '_-'), true);
		$this->cacheManager->flushCachesByTag('NodeType_'.strtr('Neos.NodeTypes:Page', '.:', '_-'), true);
		$this->cacheManager->flushCachesByTag('NodeType_'.strtr('Neos.Neos:Document', '.:', '_-'), true);

		$this->redirect('index');
	}

}
