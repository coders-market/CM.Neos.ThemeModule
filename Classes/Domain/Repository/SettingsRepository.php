<?php
namespace CM\Neos\ThemeModule\Domain\Repository;

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
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;

/**
 * Theme Settings Repository
 * @method Settings findByIdentifier($identifier)
 *
 * @Flow\Scope("singleton")
 */
class SettingsRepository extends Repository
{
    /**
     * Finds the active theme settings
     *
     * @return Settings The active theme settings or FALSE if none exists
     */
    public function findActive()
    {
        $query = $this->createQuery();

        return $query->execute()->getFirst();
    }
}
