<?php

namespace Project\Tests\Unit;

use Project\Util\ProjectUtil;

/**
 * Tests expected ProjectUtil functionality.
 */
class ProjectUtilTest extends \PHPUnit_Framework_TestCase
{
    private $projectDir;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->projectDir = sprintf('%s/../Resources/project', __DIR__);
    }

    /**
     * Tests we are correctly extracting files in project.
     *
     * @test
     */
    public function testGetProjectFiles()
    {
        // test for all files
        $allFiles = ProjectUtil::getProjectFiles($this->projectDir);
        $this->assertEquals(9, sizeof($allFiles));

        // test for php files
        $phpFiles = ProjectUtil::getProjectFiles($this->projectDir, 'php');
        $this->assertEquals(4, sizeof($phpFiles));

        // test for js files
        $jsFiles = ProjectUtil::getProjectFiles($this->projectDir, 'js');
        $this->assertEquals(2, sizeof($jsFiles));
    }

    /**
     * Test we are correctly locating the bin dir.
     *
     * @test
     */
    public function testGetProjectBinDirectory()
    {
        $expected = sprintf('%s/vendor/bin', $this->projectDir);
        $actual = ProjectUtil::getProjectBinDirectory($this->projectDir);
        $this->assertEquals($expected, $actual);
    }
}
