<?php

namespace Mokamoto12\Evaluate;

use PHPUnit\Framework\TestCase;
use Psy\Shell;
use Symfony\Component\Console\Output\StreamOutput;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    public $app;

    public function setUp()
    {
        $output = new StreamOutput(fopen('php://output', 'w'));
        $this->app = new Application(new Shell(), $output);
    }

    public function testValidCode()
    {
        $this->expectOutputString("=> 2\n");
        $this->app->eval('1 + 1');
    }

    public function testValidCode2()
    {
        $this->expectOutputString(<<<_EXPECT
=> [
     2,
     3,
     4,
   ]

_EXPECT
);
        $this->app->eval('array_map(function (int $n) {return $n + 1;}, [1, 2, 3])');
    }

    public function testOutput()
    {
        $this->expectOutputString("100⏎\n");
        $this->app->eval('echo 100');
    }

    public function testOutput2()
    {
        $this->expectOutputString(<<<_EXPECT
100
200⏎

_EXPECT
);
        $this->app->eval('echo 100;echo "\n";echo 200');
    }

    public function testOutputWithReturnValue()
    {
        $this->expectOutputString(<<<_EXPECT
100⏎
=> [
     1,
     2,
   ]

_EXPECT
);
        $this->app->eval('echo 100; [1, 2]');
    }

    public function testTypeError()
    {
        $output = "TypeError: Argument 1 passed to {closure}() must be of the type integer, string given on line 3\n";
        $this->expectOutputString($output);
        $this->app->eval('declare(strict_types=1);(function (int $n){})("1")');
    }

    public function testError()
    {
        $this->expectOutputString("TypeError: test message\n");
        $this->app->eval('throw new TypeError("test message")');
    }
}
