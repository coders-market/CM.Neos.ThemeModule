<?php
namespace CM\Neos\ThemeModule;

/*
 * This file is part of the CM.Neos.ThemeModule package.
 *
 * (c) 2017, Alexander Kappler
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Utility\Files;

abstract class FileUtility extends Files
{
    /**
     * Write content to static file
     *
     * @param $path string
     * @param $filename string
     * @param $content string
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function writeStaticFile($path, $filename, $content)
    {
        if ((strpos($path, 'resource://') === false) && (strpos($path, '/Public/') === false)) {
            throw new \InvalidArgumentException('Path does not contain "resource://" and/or "/Public/"', 1508353698);
        }

        Files::createDirectoryRecursively($path);

        $pathAndFile = Files::concatenatePaths([$path, $filename]);
        file_put_contents($pathAndFile, $content);
    }
}
