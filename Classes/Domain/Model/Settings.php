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
	 * @ORM\Column(type="text", nullable=true, options={"default":""})
	 * @var string
	 */
	protected $customSettings;


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
	public function getCustomSettings()
	{
		return $this->customSettings;
	}

	/**
	 * @param string $customSettings
	 */
	public function setCustomSettings(string $customSettings)
	{
		$this->customSettings = $customSettings;
	}
}
