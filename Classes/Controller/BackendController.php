<?php
namespace CM\Neos\ThemeModule\Controller;

/*
 * This file is part of the CM.Neos.ThemeModule package.
 *
 * (c) 2017, Alexander Kappler
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use CM\Neos\ThemeModule\Domain\Model\Settings;
use CM\Neos\ThemeModule\Domain\Repository\SettingsRepository;
use CM\Neos\ThemeModule\Service\Build;
use CM\Neos\ThemeModule\Service\Compile;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Fusion\Core\Cache\ContentCache;

class BackendController extends ActionController
{
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
    protected $compileService;

    /**
     * @Flow\Inject
     * @var ContentCache
     */
    protected $contentCache;

    /**
     * Default index action
     *
     * @return void
     */
    public function indexAction()
    {
        /** @var Settings $dbSettings */
        $activeSettings = $this->settingsRepository->findActive();

        if (!$activeSettings) {
            $activeSettings = new Settings();
        }

        $themeSettings = $this->buildService->buildThemeSettings();

        $fonts = $this->buildService->buildFontOptions();

        $this->view->assignMultiple([
            'settings' => $activeSettings,
            'themeSettings' => $themeSettings,
            'fonts' => $fonts
        ]);
    }

    /**
     * Update theme settings
     *
     * @param Settings $settings Custom theme setting object
     * @param array $customSettings Custom settings for the theme
     * @return void
     */
    public function updateAction(Settings $settings, array $customSettings = [])
    {
        $settings->setCustomSettings($customSettings);

        if ($settings instanceof Settings && $this->persistenceManager->isNewObject($settings)) {
            $this->settingsRepository->add($settings);
        } elseif ($settings instanceof Settings) {
            $this->settingsRepository->update($settings);
        }

        $this->compileService->compileScss($settings);

        // Make sure all page caches get flushed, in case font settings were changed and CM.Neos.ThemeModule:Font is in use.
        $this->contentCache->flushByTag('DescendantOf_Neos.Neos:Page');
        $this->contentCache->flushByTag('DescendantOf_Neos.NodeTypes:Page');
        $this->contentCache->flushByTag('DescendantOf_Neos.Neos:Document');

        $this->contentCache->flushByTag('NodeType_Neos.Neos:Page');
        $this->contentCache->flushByTag('NodeType_Neos.NodeTypes:Page');
        $this->contentCache->flushByTag('NodeType_Neos.Neos:Document');

        $this->redirect('index');
    }

}
