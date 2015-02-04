<?php

namespace Project\Script;

use Composer\Script\Event;

class GitHooks
{
    /**
     * Setups the git hooks on your project and checks they are ok:
     * For manual/custom configuration create/touch a .git/hooks/manual file to avoid
     * modified hooks being replaced with default ones
     *
     * @param  Event      $event
     * @throws \Exception
     */
    public static function setup(Event $event)
    {
        $io = $event->getIO();

        // determine roots
        $projectDir = sprintf('%s/../../../..', __DIR__);
        $gitHooksDir = sprintf('%s/.git/hooks', $projectDir);
        $srcHooksDir = sprintf('%s/vendor/daa/project-tools/Hooks/git', $projectDir);

        // if not manually being set
        if (!file_exists("$gitHooksDir/manual")) {
            $io->write('<info>Copying git hooks...</info>');
            GitHooks::copyHooks($srcHooksDir, $gitHooksDir);
        }

        // ensure hooks exists
        if (!file_exists("$gitHooksDir/pre-commit")) {
            throw new \Exception('<error>Git pre-commit hook not installed!</error>');
        }

        $io->write('<info>Git hooks ok!</info>');
    }

    /**
     * Copies the default packaged hooks
     *
     * @throws \Exception
     */
    public static function copyHooks($srcHooksDir, $gitHooksDir)
    {
        // prepare hook folder with permissions
        exec("rm -rf $gitHooksDir");
        mkdir("$gitHooksDir", 0755);

        // copy hooks
        if (!copy("$srcHooksDir/pre-commit", "$gitHooksDir/pre-commit")) {
            throw new \Exception("<error>Failed to copy hook file</error>");
        }

        // ensure right permissions
        if (!chmod("$gitHooksDir/pre-commit", 0755)) {
            throw new \Exception("<error>Failed to set hook permissions</error>");
        }

        // ensure hooks are as expected
        if (file_get_contents("$gitHooksDir/pre-commit") !== file_get_contents("$srcHooksDir/pre-commit")) {
            throw new \Exception('<error>Error setting up git hooks</error>');
        }
    }
}
