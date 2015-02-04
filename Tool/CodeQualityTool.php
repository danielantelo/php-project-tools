<?php

namespace Project\Tool;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Project\Tool\Checker\CodingStandardsChecker;
use Project\Tool\Checker\MessDetector;
use Project\Tool\Checker\SyntaxErrorChecker;
use Project\Tool\Checker\TestsChecker;
use Project\Util\ProjectUtil;
use Project\Tool\CodeQualityException;

/**
 * Runs various code quality checks
 *   - syntax error checks
 *   - coding standards checks
 *   - mess checker
 *   - checks if tests are passing
 */
class CodeQualityTool extends Application
{
    private $output;
    private $input;
    private $projectDir;
    private $excludeTests = false;

    /**
     * Constructor
     *
     * @param array   $files
     * @param boolean $excludeTests
     * @param string  $projectDir
     */
    public function __construct(array $files = null, $excludeTests = false, $projectDir = null)
    {
        parent::__construct('Code Quality Tool', '0.1');

        // set exclude tests flag
        $this->excludeTests = $excludeTests;
        // set the project root dir
        if (!is_null($projectDir)) {
            $this->projectDir = $projectDir;
        } else {
            $this->projectDir = ProjectUtil::getProjectDirectory();
        }
        // set files
        if (!is_null($files) && !empty($files)) {
            $this->files = $files;
        } else {
            $this->files = ProjectUtil::getProjectFiles($this->projectDir, 'php');
        }
    }

    /**
     * Flag to exclude tests
     *
     * @param type $exclude
     */
    public function excludeTests($exclude = true)
    {
        $this->excludeTests = $exclude;
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->checkSyntax($this->files);
        $this->checkCodingStandards($this->files);
        $this->checkTests();
        $this->checkComposer($this->files);
    }

    /**
     * If composer file has been modified and is being commited, ensure lock file is also included
     *
     * @param  array                $files
     * @throws CodeQualityException
     */
    protected function checkComposer(array $files)
    {
        if (in_array('composer.json', $files) && !in_array('composer.lock', $files)) {
            $this->output->writeln('<error>WARNING: Composer.json has changed and lock file is not included</error>');
            //throw new CodeQualityException('composer.lock must be commited if composer.json is modified!');
        }
    }

    /**
     * Ensures there are no php syntax errors in files
     *
     * @param  array                $files
     * @throws CodeQualityException
     */
    protected function checkSyntax(array $files)
    {
        $this->output->writeln('<info>Checking for syntax errors...</info>');
        $checker = new SyntaxErrorChecker($this->projectDir, $this->output);
        if (!$checker->check($files)) {
            throw new CodeQualityException('There are syntax errors!');
        }
    }

    /**
     * Check files comply with Coding Standards
     *
     * @param  array                $files
     * @throws CodeQualityException
     */
    protected function checkCodingStandards(array $files)
    {
        $this->output->writeln('<info>Checking coding standards...</info>');
        $checker = new CodingStandardsChecker($this->projectDir, $this->output);
        if (!$checker->check($files, 'PSR2')) {
            throw new CodeQualityException(sprintf('There are conding standard violations!'));
        }

        $this->output->writeln('<info>Checking code for controversial rules...</info>');
        $checker = new MessDetector($this->projectDir, $this->output);
        if (!$checker->check($files, 'controversial')) {
            throw new CodeQualityException(sprintf('There are controversial code violations!'));
        }
    }

    /**
     * Ensures tests are passed
     *
     * @throws CodeQualityException
     */
    protected function checkTests()
    {
        if (!$this->excludeTests) {
            $this->output->writeln('<info>Checking tests...</info>');
            $checker = new TestsChecker($this->projectDir, $this->output);
            if (!$checker->check()) {
                throw new CodeQualityException(sprintf('Tests are failing!'));
            }
        }
    }
}
