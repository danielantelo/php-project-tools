<?php

namespace Project\Tests\Unit;

use Project\Tool\CodeQualityTool;

/**
 * Tests expected CodeQualityTool functionality.
 */
class CodeQualityToolTest extends \PHPUnit_Framework_TestCase
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
     * Tests exception is being thrown when files have syntax errors.
     *
     * @test
     * @expectedException        \Exception
     * @expectedExceptionMessage There are syntax errors!
     */
    public function testCheckSyntaxErrorsException()
    {
        $files = array(
            sprintf('%s/src/checker-files/file-with-syntax-error.php', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
        ));
        $tool->run();
    }

    /**
     * Tests exception is being thrown when files have coding standards issues.
     *
     * @test
     * @expectedException        \Exception
     * @expectedExceptionMessage There are PSR2 coding standard violations!
     */
    public function testCheckCodingStandardsException()
    {
        $files = array(
            sprintf('%s/src/checker-files/file-with-cs-issues.php', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
        ));
        $tool->run();
    }

    /**
     * Tests exception is being thrown when files have "controversial mess".
     *
     * @test
     * @expectedException        \Exception
     * @expectedExceptionMessage There are php mess code violations!
     */
    public function testCheckMessException()
    {
        $files = array(
            sprintf('%s/src/checker-files/file-with-mess.php', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
        ));
        $tool->run();
    }

    /**
     * Tests running multiple coding standards.
     *
     * @test
     */
    public function testMultipleCodingStandards()
    {
        $files = array(
            sprintf('%s/src/file1.php', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
            'codingStandard' => array('PSR2', 'PSR1'),
        ));
        $tool->run();
    }

    /**
     * Tests error is thrown for invalid custom checker conf.
     *
     * @test
     * @expectedException        \Exception
     * @expectedExceptionMessage Invalid custom check configuration
     */
    public function testInvalidCustomCheckConfiguration()
    {
        $files = array(
            sprintf('%s/src/assets/style.css', $this->projectDir),
            sprintf('%s/src/assets/style-with-warnings.css', $this->projectDir),
            sprintf('%s/src/assets/script.css', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
            'codingStandard' => null,
            'messRules' => null,
            'customChecks' => array('scss-lint'),
        ));
        $tool->run();
    }

    /**
     * Tests custom checker e.g. scss-lint.
     *
     * @test
     * @expectedException        \Exception
     * @expectedExceptionMessage There are scss-lint violations!
     */
    public function testCustomChecks()
    {
        $files = array(
            sprintf('%s/src/assets/style.css', $this->projectDir),
            sprintf('%s/src/assets/style-with-warnings.css', $this->projectDir),
            sprintf('%s/src/assets/script.js', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
            'codingStandard' => null,
            'messRules' => null,
            'customChecks' => array(
                array('cmd' => 'scss-lint', 'ext' => 'css'),
            ),
        ));
        $tool->run();
    }

    /**
     * Tests multiple custom checker e.g. scss-lint.
     *
     * @test
     * @expectedException        \Exception
     * @expectedExceptionMessage There are jscs --preset=jquery violations!
     */
    public function testMultipleCustomChecks()
    {
        $files = array(
            sprintf('%s/src/assets/style.css', $this->projectDir),
            sprintf('%s/src/assets/script.js', $this->projectDir),
            sprintf('%s/src/assets/script-with-warnings.js', $this->projectDir),
        );

        $tool = new CodeQualityTool($files, array(
            'excludeTests' => true,
            'projectDir' => $this->projectDir,
            'codingStandard' => null,
            'messRules' => null,
            'customChecks' => array(
                array('cmd' => 'scss-lint', 'ext' => 'css'),
                array('cmd' => 'jscs --preset=jquery', 'ext' => 'js'),
            ),
        ));
        $tool->run();
    }
}
