<?php
namespace CM\Neos\ThemeModule\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Theme settings
 *
 * @Flow\ValueObject
 */
final class Font {

	const FONT_SOURCE_SYSTEM = 'system';
	const FONT_SOURCE_LOCAL = 'local';
	const FONT_SOURCE_CDN = 'cdn';
	const FONT_SOURCE_GOOGLE = 'google';

	/**
	 * @ORM\Id
	 * @ORM\Column(type="string")
	 * @var string
	 */
	public $family;

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	public $category;

	/**
	 * @ORM\Column
	 * @var string
	 */
	public $fontSource = self::FONT_SOURCE_GOOGLE;

	/**
	 * @ORM\Column
	 * @var array
	 */
	public $variants = array();

	/**
	 * @ORM\Column
	 * @var array
	 */
	public $subsets = array();

	/**
	 * @ORM\Column
	 * @var array
	 */
	public $files = array();


	public function __construct($family, $category = '', $variants = array(), $subsets = array(), $files = array(), $fontSource = 'FONT_SOURCE_GOOGLE') {
		$this->family = $family;
		$this->category = $category;
		$this->variants =$variants;
		$this->subsets = $subsets;
		$this->files = $files;
		$this->fontSource = constant('self::'.strtoupper($fontSource));
	}

	/**
	 * @return string
	 */
	public function getFamily(): string
	{
		return $this->family;
	}

	/**
	 * @return string
	 */
	public function getCategory(): string
	{
		return $this->category;
	}

	/**
	 * @return string
	 */
	public function getFontSource(): string
	{
		return $this->fontSource;
	}

	/**
	 * @return array
	 */
	public function getVariants(): array
	{
		return $this->variants;
	}

	/**
	 * @return array
	 */
	public function getSubsets(): array
	{
		return $this->subsets;
	}

	/**
	 * @return array
	 */
	public function getFiles(): array
	{
		return $this->files;
	}

}