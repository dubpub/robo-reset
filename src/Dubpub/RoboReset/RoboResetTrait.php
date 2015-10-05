<?php namespace Dubpub\RoboReset;

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

    protected function resetRobo($message = null)
    {
        $_ = $_SERVER['_'];

        $this->_exec('clear');

        if (null !== $message) {
            $this->say('Restarting. Reason: '.$message);
        }

        register_shutdown_function(function () use ($_) {
            global $argv;

            $argvLocal = $argv;

            array_shift($argvLocal);

            pcntl_exec($_, $argvLocal);
        });

        die;
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
            $fileContents = str_replace(
                'class RoboFile',
                'class RoboFile_'.uniqid(),
                file_get_contents($this->getRoboFilePath())
            );

            if (eval('?>'.$fileContents) !== false) {
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
