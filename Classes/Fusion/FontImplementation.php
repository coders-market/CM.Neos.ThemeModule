<?php
namespace CM\Neos\ThemeModule\Fusion;

use CM\Neos\ThemeModule\Domain\Model\Font;
use CM\Neos\ThemeModule\Service\Build;
use CM\Neos\ThemeModule\Service\Compile;
use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class FontImplementation extends AbstractFusionObject {

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
	public function evaluate() {

		$settings = $this->buildService->buildThemeSettings();
		$fontSettings = $settings['font']['type']['font'];
		$fonts = $this->buildService->buildFontOptions();

		$externalFonts = [];

		foreach ($fontSettings as $fontSetting) {
			/** @var Font $font */
			$font = $this->compileService->findFontByName($fontSetting['value']['family'], $fonts);

			// Check if at least one font variant is available
			$variantsArray = json_decode($fontSetting['value']['variants']);
			if (isset($variantsArray) && is_array($variantsArray) && count($variantsArray) > 0) {

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
	 * @param array	$externalFonts
	 *
	 * @return string
	 */
	private function getFontLinkTag(array $externalFonts) {
		$link = '';

		if(isset($externalFonts['cdn']) && count($externalFonts['cdn']) > 0) {
			foreach ($externalFonts['cdn'] as $cdnFontLink) {
				$link .= $this->cdnLinkTag($cdnFontLink['font'], array('regular'));
			}
		}

		if(isset($externalFonts['google']) && count($externalFonts['google']) > 0) {
			$link .= $this->googleWebfontLinkTag($externalFonts['google']);
		}

		return $link;
	}

	/**
	 * Build cdn webfont <link> tag
	 *
	 * @param Font $font	The font which should be added
	 * @param array $variants	cdn Font link can only contain one href url (cdn path)
	 *
	 * @return string
	 */
	private function cdnLinkTag(Font $font, array $variants) {

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
	 * @param array $googleFonts	Array of google fonts which should be combined to one link tag
	 * @return string
	 */
	private function googleWebfontLinkTag(array $googleFonts){
		$link = '<link rel="stylesheet" href="';
		$link .= self::GOOGLE_WEBFONT_API;
		$link .= '?family=';

		$n = 0;
		foreach ($googleFonts as $googleFont){
			if ($n > 0) {
				$link .= '|';
			}

			$link .= str_replace(' ', '+', $googleFont['settings']['value']['family']);

			$variants = json_decode($googleFont['settings']['value']['variants']);
			if (count($variants) > 0) {
				$link .= ':' . implode(",",$variants);
			}

			$n++;
		}

		$link .= '">';

		return $link;
	}

}
