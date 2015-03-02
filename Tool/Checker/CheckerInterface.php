<?php

namespace Project\Tool\Checker;

/**
 * Abstract class to be extended by checkers.
 */
interface CheckerInterface
{
    /**
     * Executes the checker.
     *
     * @param null|array $files
     * @param null|mixed $conf
     *
     * @return boolean $succeed
     */
    public function check(array $files = null, $conf = null);
}
