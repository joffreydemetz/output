<?php

namespace JDZ\Output\Tests;

use JDZ\Output\Verbosity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\Output\Verbosity
 */
class VerbosityTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertInstanceOf(Verbosity::class, Verbosity::NONE);
        $this->assertInstanceOf(Verbosity::class, Verbosity::STEP);
        $this->assertInstanceOf(Verbosity::class, Verbosity::ERROR);
        $this->assertInstanceOf(Verbosity::class, Verbosity::WARN);
        $this->assertInstanceOf(Verbosity::class, Verbosity::INFO);
        $this->assertInstanceOf(Verbosity::class, Verbosity::ALL);
    }

    public function testEnumValues(): void
    {
        $this->assertEquals(0, Verbosity::NONE->value);
        $this->assertEquals(1, Verbosity::STEP->value);
        $this->assertEquals(4, Verbosity::ERROR->value);
        $this->assertEquals(8, Verbosity::WARN->value);
        $this->assertEquals(16, Verbosity::INFO->value);
        $this->assertEquals(32, Verbosity::ALL->value);
    }

    public function testFromMethod(): void
    {
        $this->assertSame(Verbosity::NONE, Verbosity::from(0));
        $this->assertSame(Verbosity::STEP, Verbosity::from(1));
        $this->assertSame(Verbosity::ERROR, Verbosity::from(4));
        $this->assertSame(Verbosity::WARN, Verbosity::from(8));
        $this->assertSame(Verbosity::INFO, Verbosity::from(16));
        $this->assertSame(Verbosity::ALL, Verbosity::from(32));
    }

    public function testFromMethodThrowsExceptionForInvalidValue(): void
    {
        $this->expectException(\ValueError::class);
        Verbosity::from(999);
    }

    public function testTryFromMethod(): void
    {
        $this->assertSame(Verbosity::NONE, Verbosity::tryFrom(0));
        $this->assertSame(Verbosity::STEP, Verbosity::tryFrom(1));
        $this->assertSame(Verbosity::ERROR, Verbosity::tryFrom(4));
        $this->assertSame(Verbosity::WARN, Verbosity::tryFrom(8));
        $this->assertSame(Verbosity::INFO, Verbosity::tryFrom(16));
        $this->assertSame(Verbosity::ALL, Verbosity::tryFrom(32));
        $this->assertNull(Verbosity::tryFrom(999));
    }

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

    public function testDescriptionReturnsString(): void
    {
        foreach (Verbosity::cases() as $verbosity) {
            $this->assertIsString($verbosity->description());
            $this->assertNotEmpty($verbosity->description());
        }
    }

    public function testEnumComparison(): void
    {
        // Test strict equality
        $this->assertTrue(Verbosity::NONE === Verbosity::NONE);
        $this->assertFalse(Verbosity::NONE === Verbosity::STEP);

        // Test identity
        $this->assertSame(Verbosity::NONE, Verbosity::from(0));
        $this->assertNotSame(Verbosity::NONE, Verbosity::STEP);
    }

    public function testEnumInSwitch(): void
    {
        $result = match (Verbosity::WARN) {
            Verbosity::NONE => 'none',
            Verbosity::STEP => 'step',
            Verbosity::ERROR => 'error',
            Verbosity::WARN => 'warn',
            Verbosity::INFO => 'info',
            Verbosity::ALL => 'all',
        };

        $this->assertEquals('warn', $result);
    }

    public function testAllCases(): void
    {
        $cases = Verbosity::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(Verbosity::NONE, $cases);
        $this->assertContains(Verbosity::STEP, $cases);
        $this->assertContains(Verbosity::ERROR, $cases);
        $this->assertContains(Verbosity::WARN, $cases);
        $this->assertContains(Verbosity::INFO, $cases);
        $this->assertContains(Verbosity::ALL, $cases);
    }

    public function testEnumName(): void
    {
        $this->assertEquals('NONE', Verbosity::NONE->name);
        $this->assertEquals('STEP', Verbosity::STEP->name);
        $this->assertEquals('ERROR', Verbosity::ERROR->name);
        $this->assertEquals('WARN', Verbosity::WARN->name);
        $this->assertEquals('INFO', Verbosity::INFO->name);
        $this->assertEquals('ALL', Verbosity::ALL->name);
    }

    public function testVerbosityHierarchy(): void
    {
        // Test that higher verbosity levels include lower ones
        $levels = [
            Verbosity::NONE,
            Verbosity::STEP,
            Verbosity::ERROR,
            Verbosity::WARN,
            Verbosity::INFO,
            Verbosity::ALL,
        ];

        for ($i = 0; $i < count($levels); $i++) {
            for ($j = 0; $j <= $i; $j++) {
                $this->assertTrue(
                    $levels[$i]->includes($levels[$j]),
                    sprintf('%s should include %s', $levels[$i]->name, $levels[$j]->name)
                );
            }
        }
    }

    public function testVerbosityDoesNotIncludeHigherLevels(): void
    {
        // Test that lower verbosity levels don't include higher ones
        $this->assertFalse(Verbosity::STEP->includes(Verbosity::ALL));
        $this->assertFalse(Verbosity::ERROR->includes(Verbosity::INFO));
        $this->assertFalse(Verbosity::WARN->includes(Verbosity::ALL));
        $this->assertFalse(Verbosity::INFO->includes(Verbosity::ALL));
    }

    public function testEnumSerialization(): void
    {
        // Test that enums can be serialized and unserialized
        foreach (Verbosity::cases() as $verbosity) {
            $serialized = serialize($verbosity);
            $unserialized = unserialize($serialized);

            $this->assertSame($verbosity, $unserialized);
            $this->assertEquals($verbosity->value, $unserialized->value);
        }
    }

    public function testEnumInArray(): void
    {
        $array = [
            'verbosity' => Verbosity::WARN,
            'message' => 'Test message'
        ];

        $this->assertArrayHasKey('verbosity', $array);
        $this->assertInstanceOf(Verbosity::class, $array['verbosity']);
        $this->assertSame(Verbosity::WARN, $array['verbosity']);
    }
}
