<?php

namespace CM\Neos\ThemeModule;

use Neos\Utility\Files;

class FileUtility extends Files
{

    /**
     * Write content to static file
     *
     * @param $path string
     * @param $filename string
     * @param $content string
     * @param $createFolders boolean Create folders recursively
     * @throws \Exception
     */
    public static function writeStaticFile($path, $filename, $content, $createFolders = true)
    {

        if ((strpos($path, 'resource://') === false) && (strpos($path, '/Public/') === false)) {
            throw new \Exception('Path does not contain "resource://" and/or "/Public/"');
        }

        if ($createFolders) {
            Files::createDirectoryRecursively($path);
        }

        $pathAndFile = Files::concatenatePaths(array($path, $filename));

        $fopen = fopen($pathAndFile, 'w');
        fwrite($fopen, $content);
        fclose($fopen);

    }
}
