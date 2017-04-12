<?php
namespace CM\Neos\ThemeModule\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Theme settings
 *
 * @Flow\Entity
 */
class Settings {

	/**
	 * @ORM\Column(type="text", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $customScss;

	/**
	 * @ORM\Column(type="text", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $customCss;

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $customFont = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $graybase = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $bgColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $primaryColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $successColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $infoColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $warningColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $dangerColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $fontSizeBase = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnDefaultBgColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnDefaultBorder = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnDefaultColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnPrimaryColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnSuccessColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnInfoColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnWarningColor = '';

	/**
	 * @ORM\Column(type="string", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $btnDangerColor = '';


	public function __construct() {}

	/**
	 * @return string
	 */
	public function getCustomScss()
	{
		return $this->customScss;
	}

	/**
	 * @param string $customScss
	 */
	public function setCustomScss($customScss)
	{
		$this->customScss = $customScss;
	}

	/**
	 * @return string
	 */
	public function getCustomCss()
	{
		return $this->customCss;
	}

	/**
	 * @param string $customCss
	 */
	public function setCustomCss($customCss)
	{
		$this->customCss = $customCss;
	}

	/**
	 * @return string
	 */
	public function getCustomFont(): string
	{
		return $this->customFont;
	}

	/**
	 * @param string $customFont
	 */
	public function setCustomFont(string $customFont)
	{
		$this->customFont = $customFont;
	}

	/**
	 * @return string
	 */
	public function getGraybase(): string
	{
		return $this->graybase;
	}

	/**
	 * @param string $graybase
	 */
	public function setGraybase(string $graybase)
	{
		$this->graybase = $graybase;
	}

	/**
	 * @return string
	 */
	public function getBgColor(): string
	{
		return $this->bgColor;
	}

	/**
	 * @param string $bgColor
	 */
	public function setBgColor(string $bgColor)
	{
		$this->bgColor = $bgColor;
	}

	/**
	 * @return string
	 */
	public function getPrimaryColor(): string
	{
		return $this->primaryColor;
	}

	/**
	 * @param string $primaryColor
	 */
	public function setPrimaryColor(string $primaryColor)
	{
		$this->primaryColor = $primaryColor;
	}

	/**
	 * @return string
	 */
	public function getSuccessColor(): string
	{
		return $this->successColor;
	}

	/**
	 * @param string $successColor
	 */
	public function setSuccessColor(string $successColor)
	{
		$this->successColor = $successColor;
	}

	/**
	 * @return string
	 */
	public function getInfoColor(): string
	{
		return $this->infoColor;
	}

	/**
	 * @param string $infoColor
	 */
	public function setInfoColor(string $infoColor)
	{
		$this->infoColor = $infoColor;
	}

	/**
	 * @return string
	 */
	public function getWarningColor(): string
	{
		return $this->warningColor;
	}

	/**
	 * @param string $warningColor
	 */
	public function setWarningColor(string $warningColor)
	{
		$this->warningColor = $warningColor;
	}

	/**
	 * @return string
	 */
	public function getDangerColor(): string
	{
		return $this->dangerColor;
	}

	/**
	 * @param string $dangerColor
	 */
	public function setDangerColor(string $dangerColor)
	{
		$this->dangerColor = $dangerColor;
	}

	/**
	 * @return string
	 */
	public function getFontSizeBase(): string
	{
		return $this->fontSizeBase;
	}

	/**
	 * @param string $fontSizeBase
	 */
	public function setFontSizeBase(string $fontSizeBase)
	{
		$this->fontSizeBase = $fontSizeBase;
	}

	/**
	 * @return string
	 */
	public function getBtnDefaultBgColor(): string
	{
		return $this->btnDefaultBgColor;
	}

	/**
	 * @param string $btnDefaultBgColor
	 */
	public function setBtnDefaultBgColor(string $btnDefaultBgColor)
	{
		$this->btnDefaultBgColor = $btnDefaultBgColor;
	}

	/**
	 * @return string
	 */
	public function getBtnDefaultBorder(): string
	{
		return $this->btnDefaultBorder;
	}

	/**
	 * @param string $btnDefaultBorder
	 */
	public function setBtnDefaultBorder(string $btnDefaultBorder)
	{
		$this->btnDefaultBorder = $btnDefaultBorder;
	}

	/**
	 * @return string
	 */
	public function getBtnDefaultColor(): string
	{
		return $this->btnDefaultColor;
	}

	/**
	 * @param string $btnDefaultColor
	 */
	public function setBtnDefaultColor(string $btnDefaultColor)
	{
		$this->btnDefaultColor = $btnDefaultColor;
	}

	/**
	 * @return string
	 */
	public function getBtnPrimaryColor(): string
	{
		return $this->btnPrimaryColor;
	}

	/**
	 * @param string $btnPrimaryColor
	 */
	public function setBtnPrimaryColor(string $btnPrimaryColor)
	{
		$this->btnPrimaryColor = $btnPrimaryColor;
	}

	/**
	 * @return string
	 */
	public function getBtnSuccessColor(): string
	{
		return $this->btnSuccessColor;
	}

	/**
	 * @param string $btnSuccessColor
	 */
	public function setBtnSuccessColor(string $btnSuccessColor)
	{
		$this->btnSuccessColor = $btnSuccessColor;
	}

	/**
	 * @return string
	 */
	public function getBtnInfoColor(): string
	{
		return $this->btnInfoColor;
	}

	/**
	 * @param string $btnInfoColor
	 */
	public function setBtnInfoColor(string $btnInfoColor)
	{
		$this->btnInfoColor = $btnInfoColor;
	}

	/**
	 * @return string
	 */
	public function getBtnWarningColor(): string
	{
		return $this->btnWarningColor;
	}

	/**
	 * @param string $btnWarningColor
	 */
	public function setBtnWarningColor(string $btnWarningColor)
	{
		$this->btnWarningColor = $btnWarningColor;
	}

	/**
	 * @return string
	 */
	public function getBtnDangerColor(): string
	{
		return $this->btnDangerColor;
	}

	/**
	 * @param string $btnDangerColor
	 */
	public function setBtnDangerColor(string $btnDangerColor)
	{
		$this->btnDangerColor = $btnDangerColor;
	}

}