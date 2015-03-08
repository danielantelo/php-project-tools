<?php

namespace Project\Tool\Checker;

use Project\Util\ProjectUtil;

/**
 * Runs a custom checker i.e. scss-lint, jscs.
 */
class CustomChecker extends AbstractChecker implements CheckerInterface
{
    const PARAM_CMD = 'cmd';
    const PARAM_EXTENSION = 'ext';

    private $check;

    /**
     * {@inheritdoc}
     */
    public function check(array $files = null, $check = 'scss-lint')
    {
        if (!is_array($check) || !isset($check[self::PARAM_CMD]) || !isset($check[self::PARAM_EXTENSION])) {
            throw new \Exception(sprintf('Invalid custom check configuration %s', print_r($check, true)));
        }

        $this->check = $check;

        if (is_null($files) || empty($files)) {
            $this->files = ProjectUtil::getProjectFiles($this->projectDir);
        }

        return $this->checkFiles($files);
    }

    /**
     * Checks an array of file string paths for sass issues.
     *
     * @param array $files
     *
     * @return boolean $succeed
     */
    private function checkFiles(array $files)
    {
        $succeed = true;

        foreach ($files as $file) {
            if (file_exists($file) && $this->isFileOfExtension($file, $this->check[self::PARAM_EXTENSION])) {
                $cmd = $this->getBinCommand($file);
                $process = $this->buildProcess($cmd);
                $process->run();
                if (!$process->isSuccessful()) {
                    $this->log(sprintf('<error>%s</error>', $process->getOutput()));
                    $succeed = false;
                } else {
                    $this->log(sprintf('<info>%s ok for %s</info>', $this->check[self::PARAM_CMD], $file));
                }
            }
        }

        return $succeed;
    }

    /**
     * Returns the process command.
     *
     * @return array $cmd
     */
    private function getBinCommand($src)
    {
        $cmd = explode(' ', $this->check[self::PARAM_CMD]);
        $cmd[] = $src;

        return $cmd;
    }
}
