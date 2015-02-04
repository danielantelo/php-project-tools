<?php

namespace Project\Util;

/**
 * Adds misc global git functionality
 *
 */
class GitUtil
{
    const EMPTY_TREE_SHA1 = '4b825dc642cb6eb9a060e54bf8d69288fbee4904';

    /**
     * Grab all added, copied or modified files into an array
     *
     * @return array
     */
    public static function getCommitedFiles()
    {
        $files = array();
        exec('git diff --cached --name-only --diff-filter=ACM', $files);

        return $files;
    }
}
