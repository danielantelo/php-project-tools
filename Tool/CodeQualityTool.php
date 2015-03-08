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
use Project\Tool\Checker\CustomChecker;
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
    const CONF_PROJECT_DIR = 'projectDir';
    const CONF_EXCLUDE_TESTS = 'excludeTests';
    const CONF_CODING_STANDARD = 'codingStandard';
    const CONF_MESS_RULES = 'messRules';
    const CONF_CUSTOM = 'customChecks';

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

        // set files
        if (!is_null($files) && !empty($files)) {
            $this->files = $files;
        } else {
            $this->files = ProjectUtil::getProjectFiles($this->conf[self::CONF_PROJECT_DIR], 'php');
        }
    }

    /**
     * Flag to exclude tests.
     *
     * @param type $exclude
     */
    public function excludeTests($exclude = true)
    {
        $this->conf[self::CONF_EXCLUDE_TESTS] = $exclude;
    }

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->configureIO();

        $this->checkSyntax($this->files);
        $this->checkCodingStandards($this->files);
        $this->checkMess($this->files);
        $this->checkCustom($this->files);
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
        $checker = new SyntaxErrorChecker($this->conf[self::CONF_PROJECT_DIR], $this->output);
        if (!$checker->check($files)) {
            throw new \Exception('There are syntax errors!');
        }
    }

    /**
     * Check files comply with Coding Standards (runs phpcs).
     *
     * @param array $files
     *
     * @throws \Exception
     */
    private function checkCodingStandards(array $files)
    {
        if (is_null($this->conf[self::CONF_CODING_STANDARD])) {
            $this->output->writeln('<info>Skipping coding standards...</info>');

            return;
        }

        $this->output->writeln('<info>Checking coding standards...</info>');
        $phpcsChecker = new CodingStandardsChecker($this->conf[self::CONF_PROJECT_DIR], $this->output);

        // check if its an array of standards
        if (is_array($this->conf[self::CONF_CODING_STANDARD])) {
            foreach ($this->conf[self::CONF_CODING_STANDARD] as $standard) {
                if (!$phpcsChecker->check($files, $standard)) {
                    throw new \Exception(sprintf('There are %s coding standard violations!', $standard));
                }
            }
        } else {
            if (!$phpcsChecker->check($files, $this->conf[self::CONF_CODING_STANDARD])) {
                throw new \Exception(sprintf('There are %s coding standard violations!', $this->conf[self::CONF_CODING_STANDARD]));
            }
        }
    }

    /**
     * Checks files for mess and bad practices (runs phpmd).
     *
     * @param array $files
     *
     * @throws \Exception
     */
    private function checkMess(array $files)
    {
        if (is_null($this->conf[self::CONF_MESS_RULES])) {
            $this->output->writeln('<info>Skipping mess rules...</info>');

            return;
        }

        $rules = $this->conf[self::CONF_MESS_RULES];
        if (is_array($this->conf[self::CONF_MESS_RULES])) {
            $rules = implode(',', $this->conf[self::CONF_MESS_RULES]);
        }

        $this->output->writeln('<info>Checking code for php mess rules...</info>');
        $phpmdChecker = new MessDetector($this->conf[self::CONF_PROJECT_DIR], $this->output);
        if (!$phpmdChecker->check($files, $rules)) {
            throw new \Exception(sprintf('There are php mess code violations!'));
        }
    }

    /**
     * Ensures tests are passed.
     *
     * @throws \Exception
     */
    private function checkTests()
    {
        if (isset($this->conf[self::CONF_EXCLUDE_TESTS]) && $this->conf[self::CONF_EXCLUDE_TESTS] === true) {
            $this->output->writeln('<info>Skipping tests...</info>');

            return;
        }

        $this->output->writeln('<info>Checking tests...</info>');
        $checker = new TestsChecker($this->conf[self::CONF_PROJECT_DIR], $this->output);
        if (!$checker->check()) {
            throw new \Exception(sprintf('Tests are failing!'));
        }
    }

    /**
     * Runs any given custom check commands.
     *
     * @param array $files
     *
     * @throws \Exception
     */
    private function checkCustom(array $files)
    {
        if (is_null($this->conf[self::CONF_CUSTOM])) {
            $this->output->writeln('<info>No custom checks...</info>');

            return;
        }

        $checker = new CustomChecker($this->conf[self::CONF_PROJECT_DIR], $this->output);

        foreach ($this->conf[self::CONF_CUSTOM] as $check) {
            $this->output->writeln(sprintf('<info>Checking %s...</info>', print_r($check, true)));
            if (!$checker->check($files, $check)) {
                throw new \Exception(sprintf('There are %s violations!', $check[CustomChecker::PARAM_CMD]));
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
            self::CONF_PROJECT_DIR => ProjectUtil::getProjectDirectory(),
            self::CONF_EXCLUDE_TESTS => false,
            self::CONF_CODING_STANDARD => 'PSR2',
            self::CONF_MESS_RULES => 'controversial',
            self::CONF_CUSTOM => array(),
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
