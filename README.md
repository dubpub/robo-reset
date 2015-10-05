#Dubpub: RoboReset

[![Build Status](https://travis-ci.org/dubpub/robo-reset.svg?branch=master)](https://travis-ci.org/dubpub/robo-reset)
[![Code Climate](https://codeclimate.com/github/dubpub/robo-reset/badges/gpa.svg)](https://codeclimate.com/github/dubpub/robo-reset)
[![Coverage Status](https://coveralls.io/repos/dubpub/robo-reset/badge.svg?branch=master)](https://coveralls.io/r/dubpub/robo-reset?branch=master)

>**_dubpub/robo-reset_** is an extension for _codegyro/robo_ package. It allows you to restart your *robo* process. 

####Contents

+ <a href="#installing">Installing</a>
+ <a href="#usage">Usage</a>
+ <a href="#examples">Examples</a>

##<a name="installing">Installing</a>

You can install ___dubpub/robo-reset___ using composer:

```json
    "require": {
        "dubpub/robo-reset": "dev-master"
    }
```

or from shell

```
composer require dubpub/robo-reset
```

##<a name="usage">Usage</a>

You can use **_dubpub/robo-reset_** either from trait, provided by package `Dubpub\RoboReset\RoboResetTrait`:

```php
<?php // file - ./RoboFile.php

include_once 'vendor/autoload.php'

class RoboFile extends \Robo\Tasks
{
    use Dubpub\RoboReset\RoboResetTrait;
}

```

Or you can use **_dubpub/robo-reset_** by extending `\Dubpub\RoboReset\RoboRestartable`, that extends `\Robo\Tasks`:

```php
<?php // file - ./RoboFile.php

include_once 'vendor/autoload.php'

class RoboFile extends \Dubpub\RoboReset\RoboRestartable
{

}

```

##<a name="examples">Examples</a>

The most simple example of usage is monitoring your composer.json changes - if your composer.json file was changed, 
you need to dump autoloader and restart your RoboFile with new autoloader:

```php
<?php // file - ./RoboFile.php

include_once 'vendor/autoload.php'

class RoboFile extends \Robo\Tasks
{
    use Dubpub\RoboReset\RoboResetTrait;
    
    public function watchComposer() 
    {
        $this->taskWatch('composer.json', function () {
            if ($this->taskComposerDumpAutoload()->run()->wasSuccessful()) {
                /**
                * Reset robo and output reason-message(optional)
                **/
                $this->resetRobo('Dumped autoloader');
            }
        })->run();
    }
}

```

Or you could restart your RoboFile each time it gets modified as well:

```php
<?php // file - ./RoboFile.php

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

```