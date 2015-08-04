PHP console tools
====================
[![Code Climate](https://codeclimate.com/github/chornij/console/badges/gpa.svg)](https://codeclimate.com/github/chornij/console)
[![Test Coverage](https://codeclimate.com/github/chornij/console/badges/coverage.svg)](https://codeclimate.com/github/chornij/console/coverage)
[![Build Status](https://secure.travis-ci.org/chornij/console.png)](http://travis-ci.org/chornij/console)

- Report component - allow colorize output in console (useful for testing) 

Installation
------------

The preferred way to install this component is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require chornij/console "0.1.*"
```

or add

```
"chornij/console": "0.1.8"
```

to the require section of your `composer.json` file.

USAGE
-------

Using Report component in PHPUnit tests:
-------------------

```php
<?php

class ReportTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Report Report object
     */
    private $report;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->report = new Report();
        
        echo $this->report->title('Testing some component');
    }
    
    public function testComponent()
    {
        $this->report->write('Start testing', 'blue');
        
        $obj = new Component();
        $obj->attribute = 1234;
        
        $this->assertTrue($obj->isValidated());
        
        $this->report->write($obj->result, 'green');
        
        $this->report->write('XML result:', ['bold', 'magenta']);
        $this->report->writeXml($obj->xmlResult);
    }

}
```
