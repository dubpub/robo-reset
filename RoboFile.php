<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */

include_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use \Dubpub\RoboReset\RoboResetTrait;

    public function __construct()
    {

    }

    public function task()
    {
        $this->restartOnRoboChange()->run();
    }
}
