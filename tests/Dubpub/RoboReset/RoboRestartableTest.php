<?php namespace Dubpub\RoboReset;

use Robo\Result;
use Robo\Task\Base\Exec;
use Robo\Task\Base\Watch;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class RoboRestartableTest extends \PHPUnit_Framework_TestCase
{
    use RoboResetTrait;

    /**
     * @var RoboRestartable|Mock
     */
    private $testInstance;

    /**
     * @var Watch|Mock
     */
    private $watchMock;

    public function setUp()
    {
        $this->testInstance = $this->getMock('\Dubpub\RoboReset\RoboRestartable', [
            'taskWatch'
        ]);

        $this->watchMock = $this->getMock(
            '\Robo\Task\Base\Watch',
            ['monitor', 'run'],
            [],
            '',
            false
        );
    }

    public function testWatch()
    {
        $path = realpath(__DIR__.'/../../../RoboFile.php');

        $restartWatchMethod = new \ReflectionMethod($this->testInstance, 'restartOnRoboChange');
        $restartWatchMethod->setAccessible(true);

        $this->testInstance
            ->expects($this->any())
            ->method('taskWatch')
            ->will($this->returnValue($this->watchMock));

        $this->watchMock
            ->expects($this->exactly(2))
            ->method('monitor')
            ->will(
                $this->returnCallback(function ($passedPath, $closure) use ($path) {
                    $this->assertSame($path, $passedPath);

                    $fileContentsOriginal = file_get_contents($path);

                    $fileContents = str_replace('class RoboFile', 'clas RoboFile', $fileContentsOriginal);

                    $closure();

                    file_put_contents($path, $fileContents);

                    $closure();

                    file_put_contents($path, $fileContentsOriginal);

                    return true;
                })
            );


        $this->assertSame($this->watchMock, $restartWatchMethod->invoke($this->testInstance));
        $this->assertSame($this->testInstance, $restartWatchMethod->invoke($this->testInstance, true));

        $restartWatchMethod->setAccessible(false);

    }

    public function testShutDownCallback()
    {
        $shutDownMethod = new \ReflectionMethod($this->testInstance, 'shutDownCallback');

        $shutDownMethod->setAccessible(true);

        /**
         * @var callable $shutDownCallback
         */
        $shutDownCallback = $shutDownMethod->invoke($this->testInstance);

        $shutDownCallback();

        $shutDownMethod->setAccessible(false);

    }
}
