<?php

namespace Project\Tool\Checker;

use Project\Util\ProjectUtil;

/**
 * Checks Coding Standards.
 */
class CodingStandardsChecker extends AbstractChecker implements CheckerInterface
{
    private $standard;

    /**
     * {@inheritdoc}
     */
    public function check(array $files = null, $standard = 'PSR2')
    {
        $this->standard = $standard;

        if (is_null($files) || empty($files)) {
            $this->files = ProjectUtil::getProjectFiles($this->projectDir, 'php');
        }

        return $this->checkFiles($files);
    }

    /**
     * Checks an array of file string paths for coding standard.
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
                    $this->log(sprintf('<info>%s coding standard ok for %s</info>', $this->standard, $file));
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
            sprintf('--standard=%s', $this->standard),
            '--ignore=*/vendor/*',
            $src,
        );
    }
}
