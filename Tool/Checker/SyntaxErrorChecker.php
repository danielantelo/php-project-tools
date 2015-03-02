<?php

namespace Project\Tool\Checker;

use Project\Util\ProjectUtil;

/**
 * Checks for php syntax errors.
 */
class SyntaxErrorChecker extends AbstractChecker implements CheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function check(array $files = null, $conf = null)
    {
        if (is_null($files) || empty($files)) {
            $this->files = ProjectUtil::getProjectFiles($this->projectDir, 'php');
        }

        return $this->checkFiles($files);
    }

    /**
     * Checks an array of file string paths for syntax errors.
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
                    $this->log(sprintf('<error>syntax error for %s</error>', $file));
                    $succeed = false;
                } else {
                    $this->log(sprintf('<info>syntax ok for %s</info>', $file));
                }
            }
        }

        return $succeed;
    }

    /**
     * Returns the process command for mess.
     *
     * @return array $cmd
     */
    private function getBinCommand($src)
    {
        return array(
            'php',
            '-l',
            $src,
        );
    }
}
