<?php

namespace CM\Neos\ThemeModule\Domain\Repository;

use CM\Neos\ThemeModule\Domain\Model\Settings;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;

/**
 * Theme Settings Repository
 *
 * @Flow\Scope("singleton")
 */
class SettingsRepository extends Repository {

	/**
	 * Finds the active theme settings
	 *
	 * @return Settings The active theme settings or FALSE if none exists
	 */
	public function findActive() {
		$query = $this->createQuery();
		return $query->execute()->getFirst();
	}
}