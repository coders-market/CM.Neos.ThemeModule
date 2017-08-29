<?php
namespace CM\Neos\ThemeModule\Fusion;

use CM\Neos\ThemeModule\Domain\Model\Font;
use CM\Neos\ThemeModule\Service\Build;
use CM\Neos\ThemeModule\Service\Compile;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class FontImplementation extends AbstractFusionObject
{

    const GOOGLE_WEBFONT_API = '//fonts.googleapis.com/css';

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
     * Render link-tag for cdn / google fonts
     *
     * @return mixed
     */
    public function evaluate()
    {

        $settings = $this->buildService->buildThemeSettings();

        if (isset($settings['font']['type']['font']) && is_array($settings['font']['type']['font']) && count($settings['font']['type']['font']) > 0) {
            $fontSettings = $settings['font']['type']['font'];
        } else {
            return null;
        }

        $fonts = $this->buildService->buildFontOptions();

        if (!isset($fonts) || !is_array($fonts) || count($fonts) === 0) {
            return null;
        }

        $externalFonts = [];

        foreach ($fontSettings as $fontSetting) {
            if (!isset($fontSetting['value']['family'])) {
                continue;
            }

            /** @var Font $font */
            $font = $this->compileService->findFontByName($fontSetting['value']['family'], $fonts);

            if (!isset($font) || $font->getFontSource() == Font::FONT_SOURCE_SYSTEM || $font->getFontSource() == Font::FONT_SOURCE_LOCAL) {
                continue;
            }

            // Check if at least one font variant is available
            if (isset($fontSetting['value']['variants']) && is_string($fontSetting['value']['variants'])) {
                $variantsArray = json_decode($fontSetting['value']['variants']);
            }

            if (isset($variantsArray) && is_array($variantsArray) && count($variantsArray) > 0 && isset($font)) {

                switch ($font) {
                    case ($font->getFontSource() === Font::FONT_SOURCE_GOOGLE):
                        $externalFonts['google'][$fontSetting['value']['family']]['settings'] = $fontSetting;
                        break;

                    case ($font->getFontSource() === Font::FONT_SOURCE_CDN):
                        $externalFonts['cdn'][$fontSetting['value']['family']]['font'] = $font;
                        $externalFonts['cdn'][$fontSetting['value']['family']]['settings'] = $fontSetting;
                        break;

                    default:
                }

            }
        }

        $html = $this->getFontLinkTag($externalFonts);

        return $html;
    }


    /**
     * Return the <link> tag for the given font
     *
     * @param array $externalFonts
     *
     * @return string
     */
    private function getFontLinkTag(array $externalFonts)
    {
        $link = '';

        if (isset($externalFonts['cdn']) && count($externalFonts['cdn']) > 0) {
            foreach ($externalFonts['cdn'] as $cdnFontLink) {
                $link .= $this->cdnLinkTag($cdnFontLink['font'], array('regular'));
            }
        }

        if (isset($externalFonts['google']) && count($externalFonts['google']) > 0) {
            $link .= $this->googleWebfontLinkTag($externalFonts['google']);
        }

        return $link;
    }

    /**
     * Build cdn webfont <link> tag
     *
     * @param Font $font The font which should be added
     * @param array $variants cdn Font link can only contain one href url (cdn path)
     *
     * @return string
     */
    private function cdnLinkTag(Font $font, array $variants)
    {

        $link = '<link rel="stylesheet" href="';
        foreach ($font->getFiles() as $fileKey => $file) {
            if ($fileKey === $variants[0]) {
                $link .= $file;
            }
        }
        $link .= '">';

        return $link;
    }

    /**
     * Build google webfont specific <link> tag
     *
     * @param array $googleFonts Array of google fonts which should be combined to one link tag
     * @return string
     */
    private function googleWebfontLinkTag(array $googleFonts)
    {
        $link = '<link rel="stylesheet" href="';
        $link .= self::GOOGLE_WEBFONT_API;
        $link .= '?family=';

        $n = 0;
        foreach ($googleFonts as $googleFont) {
            if ($n > 0) {
                $link .= '|';
            }

            $link .= str_replace(' ', '+', $googleFont['settings']['value']['family']);

            $variants = json_decode($googleFont['settings']['value']['variants']);
            if (count($variants) > 0) {
                $link .= ':' . implode(",", $variants);
            }

            $n++;
        }

        $link .= '">';

        return $link;
    }

}
