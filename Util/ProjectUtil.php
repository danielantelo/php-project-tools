<?php

namespace Project\Util;

use Symfony\Component\Finder\Finder;
use Exception;

/**
 * Adds misc global project functionality
 *
 */
class ProjectUtil
{
    /**
     * Gets the base directory of current project
     * Uses current files expected location inside vendor folder
     *
     * @throws Exception
     * @return string     $path
     */
    public static function getProjectDirectory()
    {
        $path = sprintf('%s/../../../../', __DIR__);
        if (!file_exists(sprintf('%s/composer.json', $path))) {
            throw new Exception('Unable to determine project base directory');
        }

        return $path;
    }

    /**
     * Locates the bin dir: can be in in root or inside vendor dir
     *
     * @param  string     $projectDir
     * @throws Exception
     * @return string     $path
     */
    public static function getProjectBinDirectory($projectDir)
    {
        $possibleDirs = array(
            sprintf('%s/bin', $projectDir),
            sprintf('%s/vendor/bin', $projectDir),
        );
        foreach ($possibleDirs as $dir) {
            if (file_exists($dir)) {
                return $dir;
            }
        }

        throw new Exception('Bin directory not found. Should be bin/ or vendor/bin!');
    }

    /**
     * Gets project files
     *
     * @param  string      $projectDir
     * @param  string|null $type
     * @return array       $files
     */
    public static function getProjectFiles($projectDir, $type = null)
    {
        if (!file_exists($projectDir)) {
            throw new Exception("Specified directory $projectDir does not exist");
        }

        $finder = new Finder();
        $finder->files()
            ->in($projectDir)
            ->exclude('vendor')
            ->exclude('cache')
            ->exclude('logs')
        ;

        if (!is_null($type)) {
            $finder->name("*.$type");
        }

        $files = array();
        foreach ($finder as $file) {
            $files[] = $file->getRealpath();
        }

        return $files;
    }
}
