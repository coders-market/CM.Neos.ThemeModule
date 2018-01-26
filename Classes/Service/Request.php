<?php
namespace CM\Neos\ThemeModule\Service;

/*
 * This file is part of the CM.Neos.ThemeModule package.
 *
 * (c) 2017, Karsten Dambekalns, Flownative GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Repository\DomainRepository;
use Neos\Neos\Domain\Repository\SiteRepository;

class Request
{
    /**
     * @Flow\Inject
     * @var DomainRepository
     */
    protected $domainRepository;

    /**
     * @Flow\Inject
     * @var SiteRepository
     */
    protected $siteRepository;

    /**
     * Returns the current site package key as per active request.
     *
     * @return string
     */
    public function getCurrentSitePackageKey(): string
    {
        $domain = $this->domainRepository->findOneByActiveRequest();
        if ($domain === null) {
            return $this->siteRepository->findDefault()->getSiteResourcesPackageKey();
        }

        return $domain->getSite()->getSiteResourcesPackageKey();
    }
}
