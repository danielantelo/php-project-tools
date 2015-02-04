<?php

namespace Project\Tests\Unit;

use Project\Util\GitUtil;

/**
 * Tests expected GitUtil functionality
 *
 */
class GitUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests we are correctly extracting files being commited
     *
     * @test
     */
    public function testGetCommitedFiles()
    {
        GitUtil::getCommitedFiles();
    }
}
