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
 * @Flow\ValueObject
 */
final class Font
{
    const FONT_SOURCE_SYSTEM = 'system';
    const FONT_SOURCE_LOCAL = 'local';
    const FONT_SOURCE_CDN = 'cdn';
    const FONT_SOURCE_GOOGLE = 'google';

    /**
     * @ORM\Id
     * @var string
     */
    public $family;

    /**
     * @var string
     */
    public $category = '';

    /**
     * @var string
     */
    public $fontSource = self::FONT_SOURCE_GOOGLE;

    /**
     * @var array
     */
    public $variants = [];

    /**
     * @var array
     */
    public $subsets = [];

    /**
     * @var array
     */
    public $files = [];

    public function __construct(
        $family,
        $category = '',
        $variants = [],
        $subsets = [],
        $files = [],
        $fontSource = 'FONT_SOURCE_GOOGLE'
    ) {
        $this->family = $family;
        $this->category = $category;
        $this->variants = $variants;
        $this->subsets = $subsets;
        $this->files = $files;
        $this->fontSource = constant('self::' . strtoupper($fontSource));
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
