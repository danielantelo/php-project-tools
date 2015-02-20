<?php

namespace Project\Tool\Checker;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use Project\Util\ProjectUtil;

/**
 * Abstract class to be extended by checkers
 *
 */
abstract class Checker
{
    protected $projectDir;
    protected $binDir;
    
    private $output;

    public function __construct($projectDir = null, OutputInterface $output = null)
    {
        $this->output = $output;
        $this->projectDir = $projectDir;
        $this->binDir = ProjectUtil::getProjectBinDirectory($this->projectDir);
    }

    /**
     * Executes the checker
     */
    abstract public function check(array $params = null);

    /**
     * Sets the project directory
     *
     * @return string
     */
    public function setProjectDir($projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Returns cmd process
     *
     * @param array $cmd
     */
    protected function buildProcess(array $cmd)
    {
        $processBuilder = new ProcessBuilder($cmd);
        $processBuilder->setWorkingDirectory($this->projectDir);
        //$processBuilder->setTimeout(3600);
        $process = $processBuilder->getProcess();

        return $process;
    }

    /**
     * Logs information to output
     *
     * @param type $string
     */
    protected function log($string)
    {
        if (!is_null($this->output)) {
            $this->output->writeln($string);
        } else {
            echo "$string\n";
        }
    }

    /**
     * Checks if a file is a php file
     *
     * @param boolean
     */
    protected function isPhpFile($file)
    {
        $fileInfo = pathinfo($file);
        if (isset($fileInfo["extension"]) && $fileInfo["extension"] == "php") {
            return true;
        }

        return false;
    }
}
