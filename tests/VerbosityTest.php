<?php

namespace JDZ\Output\Tests;

use JDZ\Output\Verbosity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Verbosity::class)]
class VerbosityTest extends TestCase
{
    public function testIncludesMethod(): void
    {
        // NONE includes nothing
        $this->assertFalse(Verbosity::NONE->includes(Verbosity::STEP));
        $this->assertFalse(Verbosity::NONE->includes(Verbosity::ERROR));
        $this->assertFalse(Verbosity::NONE->includes(Verbosity::WARN));
        $this->assertFalse(Verbosity::NONE->includes(Verbosity::INFO));
        $this->assertFalse(Verbosity::NONE->includes(Verbosity::ALL));

        // STEP includes only itself
        $this->assertTrue(Verbosity::STEP->includes(Verbosity::STEP));
        $this->assertFalse(Verbosity::STEP->includes(Verbosity::ERROR));
        $this->assertFalse(Verbosity::STEP->includes(Verbosity::WARN));
        $this->assertFalse(Verbosity::STEP->includes(Verbosity::INFO));
        $this->assertFalse(Verbosity::STEP->includes(Verbosity::ALL));

        // ERROR includes STEP and ERROR
        $this->assertTrue(Verbosity::ERROR->includes(Verbosity::STEP));
        $this->assertTrue(Verbosity::ERROR->includes(Verbosity::ERROR));
        $this->assertFalse(Verbosity::ERROR->includes(Verbosity::WARN));
        $this->assertFalse(Verbosity::ERROR->includes(Verbosity::INFO));
        $this->assertFalse(Verbosity::ERROR->includes(Verbosity::ALL));

        // WARN includes STEP, ERROR, and WARN
        $this->assertTrue(Verbosity::WARN->includes(Verbosity::STEP));
        $this->assertTrue(Verbosity::WARN->includes(Verbosity::ERROR));
        $this->assertTrue(Verbosity::WARN->includes(Verbosity::WARN));
        $this->assertFalse(Verbosity::WARN->includes(Verbosity::INFO));
        $this->assertFalse(Verbosity::WARN->includes(Verbosity::ALL));

        // INFO includes STEP, ERROR, WARN, and INFO
        $this->assertTrue(Verbosity::INFO->includes(Verbosity::STEP));
        $this->assertTrue(Verbosity::INFO->includes(Verbosity::ERROR));
        $this->assertTrue(Verbosity::INFO->includes(Verbosity::WARN));
        $this->assertTrue(Verbosity::INFO->includes(Verbosity::INFO));
        $this->assertFalse(Verbosity::INFO->includes(Verbosity::ALL));

        // ALL includes everything
        $this->assertTrue(Verbosity::ALL->includes(Verbosity::STEP));
        $this->assertTrue(Verbosity::ALL->includes(Verbosity::ERROR));
        $this->assertTrue(Verbosity::ALL->includes(Verbosity::WARN));
        $this->assertTrue(Verbosity::ALL->includes(Verbosity::INFO));
        $this->assertTrue(Verbosity::ALL->includes(Verbosity::ALL));
    }

    public function testIncludesItself(): void
    {
        $this->assertTrue(Verbosity::NONE->includes(Verbosity::NONE));
        $this->assertTrue(Verbosity::STEP->includes(Verbosity::STEP));
        $this->assertTrue(Verbosity::ERROR->includes(Verbosity::ERROR));
        $this->assertTrue(Verbosity::WARN->includes(Verbosity::WARN));
        $this->assertTrue(Verbosity::INFO->includes(Verbosity::INFO));
        $this->assertTrue(Verbosity::ALL->includes(Verbosity::ALL));
    }

    public function testDescription(): void
    {
        $this->assertEquals('No messages in filtered output', Verbosity::NONE->description());
        $this->assertEquals('Only step messages', Verbosity::STEP->description());
        $this->assertEquals('Step and error messages', Verbosity::ERROR->description());
        $this->assertEquals('Step, error, and warning messages', Verbosity::WARN->description());
        $this->assertEquals('Step, error, warning, and info messages', Verbosity::INFO->description());
        $this->assertEquals('All messages including debug/dump', Verbosity::ALL->description());
    }
}
