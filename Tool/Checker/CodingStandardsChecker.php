<?php

namespace Project\Tool\Checker;

use Project\Util\ProjectUtil;

/**
 * Checks Coding Standards.
 */
class CodingStandardsChecker extends AbstractChecker implements CheckerInterface
{
    private $standards;

    /**
     * {@inheritdoc}
     */
    public function check(array $files = null, $standards = 'PSR2')
    {
        $this->standards = $standards;

        if (is_null($files) || empty($files)) {
            $this->files = ProjectUtil::getProjectFiles($this->projectDir, 'php');
        }

        return $this->checkFiles($files);
    }

    /**
     * Sets the coding stadanrds rules to use.
     *
     * @param string $standards (comma separated with no spaces)
     */
    public function setStandards($standards)
    {
        $this->standards = $standards;
    }

    /**
     * Checks an array of file string paths for coding standards.
     *
     * @param array $files
     *
     * @return boolean $succeed
     */
    private function checkFiles(array $files)
    {
        $succeed = true;

        foreach ($files as $file) {
            if ($this->isPhpFile($file) && file_exists($file)) {
                $cmd = $this->getBinCommand($file);
                $process = $this->buildProcess($cmd);
                $process->run();
                if (!$process->isSuccessful()) {
                    $this->log(sprintf('<error>%s</error>', $process->getOutput()));
                    $succeed = false;
                } else {
                    $this->log(sprintf('<info>coding standards ok for %s</info>', $file));
                }
            }
        }

        return $succeed;
    }

    /**
     * Returns the process command for the builder.
     *
     * @return array $cmd
     */
    private function getBinCommand($src)
    {
        return array(
            'php',
            sprintf('%s/phpcs', $this->binDir),
            sprintf('--standard=%s', $this->standards),
            '--ignore=*/vendor/*',
            $src,
        );
    }
}
