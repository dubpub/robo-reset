<?php namespace Dubpub\RoboReset;

use Robo\Task\Base\Exec;
use Robo\Task\Base\Watch;

trait RoboResetTrait
{
    private $roboFilePath;

    protected function getRoboFilePath()
    {
        if ($this->roboFilePath === null) {
            $this->roboFilePath = getcwd() . '/RoboFile.php';
        }

        return $this->roboFilePath;
    }

    protected function shutDownCallback()
    {
        $_ = $_SERVER['_'];

        return function () use ($_) {
            global $argv;

            $argvLocal = $argv;

            array_shift($argvLocal);

            // @codeCoverageIgnoreStart
            if (!defined('UNIT_TESTING')) {
                pcntl_exec($_, $argvLocal);
            }
            // @codeCoverageIgnoreEnd

            return;
        };
    }

    protected function lintRoboFile()
    {
        /**
         * @var Exec $task
         */
        $task = $this->taskExec('php -l ' . $this->getRoboFilePath());
        $task->printed(false);
        $result = $task->run();
        return $result->getExitCode() !== 255;
    }

    protected function resetRobo($message = null)
    {

        $this->taskExec('clear')->run();

        if (null !== $message) {
            $this->say('Restarting. Reason: '.$message);
        }

        register_shutdown_function($this->shutDownCallback());

        // @codeCoverageIgnoreStart
        if (!defined('UNIT_TESTING')) {
            die;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param bool|false $automaticRun
     * @return Watch|static
     */
    protected function restartOnRoboChange($automaticRun = false)
    {
        /**
         * @var $watch Watch
         */
        $watch = $this->taskWatch();

        $watch->monitor($this->getRoboFilePath(), function () {
            if ($this->lintRoboFile()) {
                $this->resetRobo('RoboFile modified');
            } else {
                $this->say('RoboFile was modified, but it seems that php code is not valid, ignoring');
            }
        });

        if ($automaticRun === true) {
            $watch->run();
            return $this;
        }

        return $watch;
    }
}
