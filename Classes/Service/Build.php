<?php
namespace CM\Neos\ThemeModule\Service;

/*
 * This file is part of the CM.Neos.ThemeModule package.
 *
 * (c) 2017, Alexander Kappler
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use CM\Neos\ThemeModule\Domain\Model\Font;
use CM\Neos\ThemeModule\Domain\Model\Settings;
use CM\Neos\ThemeModule\Domain\Repository\SettingsRepository;
use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Property\TypeConverter\ArrayConverter;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Utility\Arrays;

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
     * @Flow\InjectConfiguration(path="http.baseUri", package="Neos.Flow")
     * @var string
     */
    protected $baseUri;

    /**
     * @Flow\Inject
     * @var SettingsRepository
     */
    protected $settingsRepository;

    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

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
     * Merging scss with site-specific settings (if any).
     *
     * @param Settings $settings
     * @return array
     */
    public function buildThemeSettings(Settings $settings): array
    {
        $packageKey = $settings->getPackageKey();
        $globalScssConfiguration = $this->configuration['scss'];
        $siteScssConfiguration = [];

        if (isset($this->configuration['sites'], $this->configuration['sites'][$packageKey], $this->configuration['sites'][$packageKey]['scss'])) {
            $siteScssConfiguration = $this->configuration['sites'][$packageKey]['scss'];
        }

        $mergedScssConfiguration = Arrays::arrayMergeRecursiveOverrule($globalScssConfiguration, $siteScssConfiguration);
        $mergedScssConfiguration['presetVariables'] = Arrays::arrayMergeRecursiveOverrule($mergedScssConfiguration['presetVariables'], $settings->getCustomSettings());

        $mergedScssConfiguration['importPaths'] = str_replace('{packageKey}', $packageKey, $mergedScssConfiguration['importPaths']);
        $mergedScssConfiguration['outputPath'] = str_replace('{packageKey}', $packageKey, $mergedScssConfiguration['outputPath']);

        return $mergedScssConfiguration;
    }

    /**
     * Build the font array with select option list and array with font details like variants, subsets
     *
     * @param string $packageKey
     * @return array
     */
    public function buildFontOptions($packageKey): array
    {
        $globalFontOptions = $this->configuration['fontOptions'];
        $siteFontOptions = [];
        $googleFonts = [];

        if (isset($this->configuration['sites'], $this->configuration['sites'][$packageKey], $this->configuration['sites'][$packageKey]['fontOptions'])) {
            $siteFontOptions = $this->configuration['sites'][$packageKey]['fontOptions'];
        }

        $mergedFontOptions = $this->parseFonts(Arrays::arrayMergeRecursiveOverrule($globalFontOptions, $siteFontOptions));

        if ($this->configuration['addGoogleFonts'] === true) {
            $googleFonts = $this->parseFonts($this->getGoogleWebfonts());
        }

        return Arrays::arrayMergeRecursiveOverrule($mergedFontOptions, $googleFonts);
    }

    /**
     * Request for google webfonts, if cache is outdated, update cache
     *
     * @return array
     */
    protected function getGoogleWebfonts(): array
    {
        if ($this->cacheFrontend->has(self::CACHE_IDENTIFIER)) {
            $cachedResponse = $this->cacheFrontend->get(self::CACHE_IDENTIFIER);

            return $cachedResponse;
        } else {
            $requestUrl = self::GWF_SERVER . '?token=' . self::TOKEN;
            $this->browser->setRequestEngine($this->browserRequestEngine);
            $this->browser->addAutomaticRequestHeader('Cntm', $this->getHost());
            $response = $this->browser->request($requestUrl);

            if ($response->getStatusCode() == 200) {
                $fontsArray = json_decode($response->getContent(), true);
                $this->cacheFrontend->set(self::CACHE_IDENTIFIER, $fontsArray);

                return $fontsArray;
            }
        }

        return [];
    }

    /**
     * Create array for f:form.select viewhelper options
     *
     * @param string $jsonFonts
     * @return array
     */
    protected function getFontsArray($jsonFonts): array
    {
        $fontsArray = $this->arrayConverter->convertFrom($jsonFonts, 'array');

        return $fontsArray;
    }

    /**
     * Get domain of page
     *
     * @return string
     */
    protected function getHost(): string
    {
        if ($this->baseUri) {
            return $this->baseUri;
        }

        $hostname = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';

        $domain = $this->domainRepository->findOneByActiveRequest();
        if ($domain === null) {
            $site = $this->siteRepository->findDefault();
            if ($site !== null) {
                $primaryDomain = $site->getPrimaryDomain();
                $hostname = $primaryDomain ? $primaryDomain->getHostname() : '';
            }
        }

        return $hostname;
    }

    /**
     * Parse the array to a valid output
     *
     * @param $fontOptions array The defined fonts with all details
     * @return array
     */
    protected function parseFonts($fontOptions): array
    {
        $fonts = [];
        if (isset($fontOptions['items'])) {
            foreach ($fontOptions['items'] as $fontItem) {

                $fonts['options'][$fontItem['category']][] = new Font(
                    $fontItem['family'],
                    isset($fontItem['category']) ? $fontItem['category'] : '',
                    isset($fontItem['variants']) ? $fontItem['variants'] : [],
                    isset($fontItem['subsets']) ? $fontItem['subsets'] : [],
                    isset($fontItem['files']) ? $fontItem['files'] : [],
                    isset($fontItem['fontSource']) ? $fontItem['fontSource'] : 'FONT_SOURCE_GOOGLE'
                );
            }
        }

        return $fonts;
    }
}
