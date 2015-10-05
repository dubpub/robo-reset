<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */

include_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use Dubpub\RoboReset\RoboResetTrait;

    public function watch()
    {
        /**
         * This method binds a listener on RoboFile.
         * If RoboFile was modified and it's code passes
         * standart php lint checks the robo process will be
         * restarted.
         *
         * Method returns an instance of \Robo\Task\Base\Watch
         *
         * @var \Robo\Task\Base\Watch $taskWatch
         */
        $taskWatch = $this->restartOnRoboChange();

        $taskWatch->monitor(['composer.json'], function () {
            if ($this->taskComposerDumpAutoload()->run()->wasSuccessful()) {
                /**
                 * Reset robo and output reason-message(optional)
                 **/
                $this->resetRobo('Dumped autoloader');
            }
        });

        $taskWatch->run();
    }
}
