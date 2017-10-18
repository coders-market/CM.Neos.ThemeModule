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
use CM\Neos\ThemeModule\Service\Request;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Package\PackageManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Fusion\Core\Cache\ContentCache;
use Neos\Utility\ObjectAccess;

class BackendController extends ActionController
{
    /**
     * @Flow\Inject
     * @var SettingsRepository
     */
    protected $settingsRepository;

    /**
     * @Flow\Inject
     * @var Request
     */
    protected $requestService;

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
     * @Flow\Inject
     * @var PackageManagerInterface
     */
    protected $packageManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * Default index action
     *
     * @param string $targetPackageKey
     * @return void
     */
    public function indexAction($targetPackageKey = '')
    {
        $sitePackageKey = $targetPackageKey ?: $this->requestService->getCurrentSitePackageKey();

        if (($settings = $this->settingsRepository->findByIdentifier('')) !== null) {
            // needed to migrate from Settings without packageKey
            ObjectAccess::setProperty($settings, 'packageKey', $sitePackageKey, true);
            $this->settingsRepository->update($settings);
            $this->persistenceManager->whitelistObject($settings);
        } elseif (($settings = $this->settingsRepository->findByIdentifier($sitePackageKey)) === null) {
            $settings = new Settings($sitePackageKey);
            $this->settingsRepository->add($settings);
            $this->persistenceManager->whitelistObject($settings);
        }

        $themeSettings = $this->buildService->buildThemeSettings($settings->getPackageKey());

        $fonts = $this->buildService->buildFontOptions();

        $this->view->assignMultiple([
            'availableSitePackages' => $this->getAvailableSitePackageKeys(),
            'settings' => $settings,
            'themeSettings' => $themeSettings['presetVariables'],
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

        $this->redirect('index', 'Backend', 'CM.Neos.ThemeModule', ['targetPackageKey' => $settings->getPackageKey()]);
    }

    /**
     * Returns an array of all available site packages.
     *
     * @return array
     */
    protected function getAvailableSitePackageKeys(): array
    {
        $availableSitePackages = $this->packageManager->getFilteredPackages('available', 'Sites', 'neos-site');

        return array_combine(array_keys($availableSitePackages), array_keys($availableSitePackages));
    }
}
