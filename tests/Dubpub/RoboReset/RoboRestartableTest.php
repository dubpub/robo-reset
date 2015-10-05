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
        $_SERVER['_'] = realpath($_SERVER['_']);

        $this->testInstance = $this->getMock(RoboRestartable::class, [
            'taskWatch'
        ]);

        $this->watchMock = $this->getMock(
            Watch::class,
            ['monitor', 'run'],
            [],
            '',
            false
        );
    }

    public function testWatch()
    {
        $path = realpath(__DIR__.'/../../../RoboFile.php');

        $reflectionRestartWatchMethod = new \ReflectionMethod($this->testInstance, 'restartOnRoboChange');
        $reflectionRestartWatchMethod->setAccessible(true);

        $this->testInstance
            ->expects($this->any())
            ->method('taskWatch')
            ->will($this->returnValue($this->watchMock));

        $this->watchMock
            ->expects($this->exactly(2))
            ->method('monitor')
            ->will(
                $this->returnCallback(function ($passedPath, $closure) use ($path, &$i) {
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


        $this->assertSame($this->watchMock, $reflectionRestartWatchMethod->invoke($this->testInstance));
        $this->assertSame($this->testInstance, $reflectionRestartWatchMethod->invoke($this->testInstance, true));

        $reflectionRestartWatchMethod->setAccessible(false);

    }

    public function testShutDownCallback()
    {
        $reflectionRestartWatchMethod = new \ReflectionMethod($this->testInstance, 'shutDownCallback');

        $reflectionRestartWatchMethod->setAccessible(true);

        /**
         * @var callable $shutDownCallback
         */
        $shutDownCallback = $reflectionRestartWatchMethod->invoke($this->testInstance);

        $shutDownCallback();

        $reflectionRestartWatchMethod->setAccessible(false);

    }
}
