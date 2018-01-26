<?php
namespace CM\Neos\ThemeModule\Domain\Model;

/*
 * This file is part of the CM.Neos.ThemeModule package.
 *
 * (c) 2017, Alexander Kappler
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Theme settings
 *
 * @Flow\Entity
 */
class Settings
{
    /**
     * @ORM\Id
     * @Flow\Identity
     * @ORM\Column(type="string", options={"default": ""})
     * @var string
     */
    protected $packageKey;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $customScss = '';

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $customCss = '';

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $customSettings = '[]';

    /**
     * Settings constructor.
     *
     * @param string $packageKey
     */
    public function __construct($packageKey)
    {
        $this->packageKey = $packageKey;
    }

    /**
     * @return string
     */
    public function getPackageKey()
    {
        return $this->packageKey;
    }

    /**
     * @return string
     */
    public function getCustomScss(): string
    {
        return $this->customScss;
    }

    /**
     * @param string $customScss
     * @return void
     */
    public function setCustomScss($customScss)
    {
        $this->customScss = $customScss;
    }

    /**
     * @return string
     */
    public function getCustomCss(): string
    {
        return $this->customCss;
    }

    /**
     * @param string $customCss
     * @return void
     */
    public function setCustomCss($customCss)
    {
        $this->customCss = $customCss;
    }

    /**
     * @return array
     */
    public function getCustomSettings(): array
    {
        return json_decode($this->customSettings, true);
    }

    /**
     * @param array $customSettings
     * @return void
     */
    public function setCustomSettings(array $customSettings)
    {
        $this->customSettings = json_encode($customSettings);
    }
}
