<?php

namespace Project\Tool;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Application;
use Project\Tool\Checker\CodingStandardsChecker;
use Project\Tool\Checker\MessDetector;
use Project\Tool\Checker\SyntaxErrorChecker;
use Project\Tool\Checker\TestsChecker;
use Project\Util\ProjectUtil;

/**
 * Runs various code quality checks
 *   - syntax error checks
 *   - coding standards checks
 *   - mess checker
 *   - checks if tests are passing.
 */
class CodeQualityTool extends Application
{
    private $output;
    private $conf;

    /**
     * Constructor.
     *
     * @param array $files
     * @param array $options
     */
    public function __construct(array $files = null, array $options = array())
    {
        parent::__construct('Code Quality Tool', '0.1');

        $this->conf = array_merge($this->getConfigurationDefaults(), $options);

        // set the project root dir
        if (!isset($this->conf['projectDir']) || empty($this->conf['projectDir'])) {
            $this->conf['projectDir'] = ProjectUtil::getProjectDirectory();
        }
        // set files
        if (!is_null($files) && !empty($files)) {
            $this->files = $files;
        } else {
            $this->files = ProjectUtil::getProjectFiles($this->conf['projectDir'], 'php');
        }
    }

    /**
     * Flag to exclude tests.
     *
     * @param type $exclude
     */
    public function excludeTests($exclude = true)
    {
        $this->conf['excludeTests'] = $exclude;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->configureIO();

        $this->checkSyntax($this->files);
        $this->checkCodingStandards($this->files);
        $this->checkTests();
        $this->checkComposer($this->files);
    }

    /**
     * If composer file has been modified and is being commited, ensure lock file is also included.
     *
     * @param array $files
     *
     * @throws \Exception
     */
    private function checkComposer(array $files)
    {
        if (in_array('composer.json', $files) && !in_array('composer.lock', $files)) {
            $this->output->writeln('<error>WARNING: Composer.json has changed and lock file is not included</error>');
            //throw new \Exception('composer.lock must be commited if composer.json is modified!');
        }
    }

    /**
     * Ensures there are no php syntax errors in files.
     *
     * @param array $files
     *
     * @throws \Exception
     */
    private function checkSyntax(array $files)
    {
        $this->output->writeln('<info>Checking for syntax errors...</info>');
        $checker = new SyntaxErrorChecker($this->conf['projectDir'], $this->output);
        if (!$checker->check($files)) {
            throw new \Exception('There are syntax errors!');
        }
    }

    /**
     * Check files comply with Coding Standards.
     *
     * @param array $files
     *
     * @throws \Exception
     */
    private function checkCodingStandards(array $files)
    {
        $this->output->writeln('<info>Checking coding standards...</info>');
        if (is_array($this->conf['codingStandard'])) {
            foreach ($this->conf['codingStandard'] as $standard) {
                $this->runPhpCs($files, $standard);
            }
        } else {
            $this->runPhpCs($files, $this->conf['codingStandard']);
        }

        $this->output->writeln('<info>Checking code for php mess rules...</info>');
        $phpmdChecker = new MessDetector($this->conf['projectDir'], $this->output);
        if (!$phpmdChecker->check($files, $this->conf['messRules'])) {
            throw new \Exception(sprintf('There are php mess code violations!'));
        }
    }
    
    private function runPhpCs($files, $standard)
    {
        $phpcsChecker = new CodingStandardsChecker($this->conf['projectDir'], $this->output);
        if (!$phpcsChecker->check($files, $standard)) {
            throw new \Exception(sprintf('There are %s coding standard violations!', $standard));
        }
    }

    /**
     * Ensures tests are passed.
     *
     * @throws \Exception
     */
    private function checkTests()
    {
        if (isset($this->conf['excludeTests']) && $this->conf['excludeTests'] === true) {
            $this->output->writeln('<info>Skipping tests...</info>');
        } else {
            $this->output->writeln('<info>Checking tests...</info>');
            $checker = new TestsChecker($this->conf['projectDir'], $this->output);
            if (!$checker->check()) {
                throw new \Exception(sprintf('Tests are failing!'));
            }
        }
    }

    /**
     * Returns conf defaults.
     *
     * @return array
     */
    private function getConfigurationDefaults()
    {
        return array(
            'excludeTests' => false,
            'codingStandard' => 'PSR2',
            'messRules' => 'controversial',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureIO(InputInterface $input = null, OutputInterface $output = null)
    {
        if (is_null($input)) {
            $this->input = new ArgvInput();
        }

        if (is_null($output)) {
            $this->output = new ConsoleOutput();
        }

        parent::configureIO($this->input, $this->output);
    }
}
