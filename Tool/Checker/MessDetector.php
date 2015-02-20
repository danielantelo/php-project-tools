<?php

namespace Project\Tool\Checker;

use Project\Util\ProjectUtil;

/**
 * Checks code with the phpmd command for issues
 *
 */
class MessDetector extends Checker
{
    private $rules;

    /**
     * {@inheritdoc}
     */
    public function check(array $files = null, $rules = 'controversial')
    {
        $this->rules = $rules;

        if (is_null($files) || empty($files)) {
            $this->files = ProjectUtil::getProjectFiles($this->projectDir, 'php');
        }

        return $this->checkFiles($files);
    }

    /**
     * Sets the php rules to use
     *
     * @param string $rules (comma separated with no spaces)
     */
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    /**
     * Checks an array of file string paths for mess
     *
     * @param  array   $files
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
                    $this->log(sprintf('<info>PHPMD %s ok for %s</info>', $this->rules, $file));
                }
            }
        }

        return $succeed;
    }

    /**
     * Returns the process command for mess
     *
     * @return array $cmd
     */
    private function getBinCommand($src)
    {
        return array(
            'php',
            sprintf('%s/phpmd', $this->binDir),
            $src,
            'text',
            $this->rules
        );
    }
}
