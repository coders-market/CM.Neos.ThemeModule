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
use CM\Neos\ThemeModule\FileUtility;
use Leafo\ScssPhp\Compiler;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Utility\Unicode\Functions;

class Compile
{
    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\InjectConfiguration(package="CM.Neos.ThemeModule")
     * @var array
     */
    protected $configuration;

    /**
     * @Flow\Inject
     * @var ResourceManager
     */
    protected $resourceManager;

    /**
     * @Flow\Inject
     * @var Build
     */
    protected $buildService;

    /**
     * Compile scss to css and add custom scss/css
     *
     * @param Settings $settings current settings
     * @return void
     */
    public function compileScss(Settings $settings)
    {
        $scssVariables = [];
        $mainScssContent = '';
        $fonts = $this->buildService->buildFontOptions($settings->getPackageKey());
        $fontFaceCss = '';

        foreach ($settings->getCustomSettings() as $group) {
            foreach ($group['type'] as $typeKey => $typeValue) {
                foreach ($typeValue as $element) {
                    if ($typeKey === 'font') {
                        // Render font name for scss variable
                        $scssVariables[$element['scssVariableName']] = '"' . $element['value']['family'] . '", ' . $element['fontFallbackValue'];

                        $font = $this->findFontByName($element['value']['family'], $fonts);

                        $variantsArray = json_decode($element['value']['variants']);

                        // Render only local fonts into css, google/cdn fonts are added via fusion object and
                        // check if at least one font variant is available
                        if ($font->getFontSource() === Font::FONT_SOURCE_LOCAL && isset($variantsArray) && is_array($variantsArray) && count($variantsArray) > 0) {
                            $fontFaceCss .= $this->scssFontFace($font, $variantsArray);
                        }
                    } else {
                        $scssVariables[$element['scssVariableName']] = $element['value'];
                    }
                }
            }
        }

        try {
            $themeSettings = $this->buildService->buildThemeSettings($settings);

            // get absolute path to scss folder
            $pathParts = Functions::parse_url($themeSettings['importPaths']);
            $scssAbsolutePath = FileUtility::concatenatePaths([FLOW_PATH_ROOT, 'Packages/Sites/', $pathParts['host'], '/Resources', $pathParts['path']]);

            $scss = new Compiler();
            $scss->setImportPaths($scssAbsolutePath);

            $scss->setFormatter($themeSettings['formatter']);
            $scss->setVariables($scssVariables);

            $mainScssFileAndPath = FileUtility::concatenatePaths([
                $themeSettings['importPaths'],
                $themeSettings['mainScssFile']
            ]);

            $mainScssContent .= FileUtility::getFileContents($mainScssFileAndPath);

            if ($settings->getCustomScss()) {
                // add custom scss code to the end of the file
                $mainScssContent = $mainScssContent . "\n" . $settings->getCustomScss();
            }

            // add fonts as @import rule to css
            if ($fontFaceCss) {
                $compiledCss = $fontFaceCss . "\n";
            } else {
                $compiledCss = '';
            }

            // compile scss to css
            $compiledCss .= $scss->compile($mainScssContent);

            if ($settings->getCustomCss()) {
                // add custom css code to the end of the file
                $compiledCss = $compiledCss . "\n" . $settings->getCustomCss();
            }

            FileUtility::writeStaticFile(
                $themeSettings['outputPath'],
                $themeSettings['outputFilename'],
                $compiledCss
            );

            $this->systemLogger->log('Scss successfully compiled');
        } catch (\Exception $e) {
            $this->systemLogger->logException($e, ['message' => 'Compiling scss was not successful']);
        }
    }

    /**
     * Find font by given Name
     *
     * @param string $name
     * @param array $fonts
     * @return Font $font
     */
    public function findFontByName($name, $fonts)
    {
        $font = null;

        foreach ($fonts['options'] as $categoryFonts) {
            /** @var Font $categoryFont */
            foreach ($categoryFonts as $categoryFont) {
                if ($categoryFont->getFamily() === $name) {
                    return $categoryFont;
                }
            }
        }

        return $font;
    }

    /**
     * Build css font-face string
     *
     * @param Font $font The font to render a font-face
     * @param array $variants The font variants to render
     * @return string
     */
    protected function scssFontFace(Font $font, $variants)
    {
        $fontFace = '';

        foreach ($variants as $variant) {
            $fontstyle = 'normal';

            switch ($variant) {
                case is_numeric($variant):
                    $fontweight = $variant;
                break;

                case $variant === 'regular':
                    $fontweight = 'normal';
                break;

                case (strpos($variant, 'italic') !== false):
                    $fontweight = substr($variant, 0, 3);
                    $fontstyle = 'italic';
                break;

                default:
                    $fontweight = 400;
            }

            $fontFace .= '@font-face {';
            $fontFace .= "font-family: '" . $font->getFamily() . "';";
            $fontFace .= 'font-style: ' . $fontstyle . ';';
            $fontFace .= 'font-weight: ' . $fontweight . ';';
            $fontFace .= "src: local('" . $font->getFamily() . "'),";

            if (strpos($font->getFamily(), ' ') !== false) {
                $fontFace .= " local('" . str_replace(' ', '', $font->getFamily()) . "'),";
            }

            foreach ($font->getFiles() as $fileKey => $file) {

                if ($fileKey === $variant) {
                    if (is_array($file)) {
                        $i = 0;
                        foreach ($file as $source) {
                            if ($i > 0) {
                                $fontFace .= ',';
                            }
                            $fontFace .= "url(" . $this->resourceManager->getPublicPackageResourceUriByPath($source) . ") format('" . pathinfo($this->resourceManager->getPublicPackageResourceUriByPath($source),
                                    PATHINFO_EXTENSION) . "')";
                            $i++;
                        }
                    } else {
                        $fontFace .= "url(" . $this->resourceManager->getPublicPackageResourceUriByPath($file) . ") format('" . pathinfo($this->resourceManager->getPublicPackageResourceUriByPath($file),
                                PATHINFO_EXTENSION) . "')";
                    }
                }
            }

            $fontFace .= ";}\n";
        }

        return $fontFace;
    }
}
