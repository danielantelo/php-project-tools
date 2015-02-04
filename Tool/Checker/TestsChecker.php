<?php

namespace Project\Tool\Checker;

/**
 * Ensures that all project tests are being passed
 *
 */
class TestsChecker extends Checker
{
    /**
     * {@inheritdoc}
     */
    public function check(array $params = null)
    {
        // check for php unit tests
        $phpunitSuccess = $this->checkPhpUnitTests();
        // check for behat tests
        $behatSuccess = $this->checkBehatTests();

        return ($phpunitSuccess && $behatSuccess);
    }

    /**
     * Ensures php unit tests are all ok
     *
     * @return boolean $succeed
     */
    private function checkPhpUnitTests()
    {
        $succeed = true;

        // if conf file found execute
        $confFile = $this->getTestsConfigurationFile('phpunit');
        if (!is_null($confFile)) {
            $this->log(sprintf('<info>Found phpunit config file: %s</info>', $confFile));
            $cmd =  array(sprintf('%s/phpunit', $this->getBinDir()), '-c', $confFile);
            $process = $this->buildProcess($cmd);
            $process->run(function ($type, $buffer) {
                $this->log($buffer);
            });
            if (!$process->isSuccessful()) {
                $succeed = false;
            }
        } else {
            $this->log(sprintf('<error>No phpunit conf found</error>'));
        }

        return $succeed;
    }

    /**
     * Ensures behat tests are all ok
     *
     * @return boolean $succeed
     */
    private function checkBehatTests()
    {
        $succeed = true;

        // if conf file found execute
        $confFile = $this->getTestsConfigurationFile('behat');
        if (!is_null($confFile)) {
            $this->log(sprintf('<info>Found behat config file: %s</info>', $confFile));
            $cmd =  array(sprintf('%s/behat', $this->getBinDir()), '--config', $confFile);
            $process = $this->buildProcess($cmd);
            $process->run(function ($type, $buffer) {
                $this->log($buffer);
            });
            if (!$process->isSuccessful()) {
                $succeed = false;
            }
        } else {
            $this->log(sprintf('<error>No behat conf found</error>'));
        }

        return $succeed;
    }

    /**
     * Returns conf file path for tests:
     *
     * @param  string      $type
     * @return null|string $filePath
     */
    private function getTestsConfigurationFile($type)
    {
        $confFile = null;

        // locate conf file
        foreach ($this->getPossibleConfPaths($type) as $conf) {
            if (file_exists($conf)) {
                $confFile = $conf;
                break;
            }
        }

        return $confFile;
    }

    /**
     *  Returns list of possible test conf file locations in a particular order
     *  - Prioritises faster conf files without code coverage which must
     * be named in the format type.nocoverage.extendions (e.g. phpunit.nocoverage.xml)
     *  - Prioritises local conf files before distribution files (.xml over .xml.dist)
     *
     * @return array
     */
    private function getPossibleConfPaths($type)
    {
        $possibleFiles = array();
        $possibleExtensions = array('xml', 'yml');

        foreach ($possibleExtensions as $ext) {
            $possibleFiles[] = sprintf('%s/%s.nocoverage.%s', $this->getProjectDir(), $type, $ext);
            $possibleFiles[] = sprintf('%s/%s.nocoverage.%s.dist', $this->getProjectDir(), $type, $ext);

            $possibleFiles[] = sprintf('%s/%s.%s', $this->getProjectDir(), $type, $ext);
            $possibleFiles[] = sprintf('%s/%s.%s.dist', $this->getProjectDir(), $type, $ext);

            $possibleFiles[] = sprintf('%s/app/%s.nocoverage.%s', $this->getProjectDir(), $type, $ext);
            $possibleFiles[] = sprintf('%s/app/%s.nocoverage.%s.dist', $this->getProjectDir(), $type, $ext);

            $possibleFiles[] = sprintf('%s/app/%s.%s', $this->getProjectDir(), $type, $ext);
            $possibleFiles[] = sprintf('%s/app/%s.%s.dist', $this->getProjectDir(), $type, $ext);

            $possibleFiles[] = sprintf('%s/tests/%s.nocoverage.%s', $this->getProjectDir(), $type, $ext);
            $possibleFiles[] = sprintf('%s/tests/%s.nocoverage.%s.dist', $this->getProjectDir(), $type, $ext);

            $possibleFiles[] = sprintf('%s/tests/%s.%s', $this->getProjectDir(), $type, $ext);
            $possibleFiles[] = sprintf('%s/tests/%s.%s.dist', $this->getProjectDir(), $type, $ext);
        }

        return $possibleFiles;
    }
}
