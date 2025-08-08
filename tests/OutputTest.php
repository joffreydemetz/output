<?php

namespace JDZ\Output\Tests;

use JDZ\Output\Output;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\Output\Output
 */
class OutputTest extends TestCase
{
    private Output $output;

    protected function setUp(): void
    {
        $this->output = new Output();
    }

    public function testConstructor(): void
    {
        $output = new Output();
        $this->assertInstanceOf(Output::class, $output);
    }

    public function testAddMessage(): void
    {
        $this->output->add('Test message');
        $string = (string) $this->output;
        $this->assertStringContainsString('[INFO]  Test message', $string);
    }

    public function testAddMessageWithTag(): void
    {
        $this->output->add('Error message', 'error');
        $string = (string) $this->output;
        $this->assertStringContainsString('[ERROR] Error message', $string);
    }

    public function testAddMultipleMessages(): void
    {
        $this->output->add('First message', 'info');
        $this->output->add('Second message', 'warn');
        $this->output->add('Third message', 'error');

        $string = (string) $this->output;
        $this->assertStringContainsString('[INFO]  First message', $string);
        $this->assertStringContainsString('[WARN]  Second message', $string);
        $this->assertStringContainsString('[ERROR] Third message', $string);
    }

    public function testToString(): void
    {
        $this->output->add('Test message 1');
        $this->output->add('Test message 2');

        $string = (string) $this->output;
        $lines = explode("\n", $string);

        $this->assertCount(2, $lines);
        $this->assertEquals('[INFO]  Test message 1', $lines[0]);
        $this->assertEquals('[INFO]  Test message 2', $lines[1]);
    }

    public function testVerbosityConstants(): void
    {
        $this->assertEquals(0, Output::VERBOSITY_NONE);
        $this->assertEquals(1, Output::VERBOSITY_STEP);
        $this->assertEquals(4, Output::VERBOSITY_ERROR);
        $this->assertEquals(8, Output::VERBOSITY_WARN);
        $this->assertEquals(16, Output::VERBOSITY_INFO);
        $this->assertEquals(32, Output::VERBOSITY_ALL);
    }

    public function testFluentInterface(): void
    {
        $result = $this->output->setVerbosity(Output::VERBOSITY_ALL);

        $this->assertInstanceOf(Output::class, $result);
        $this->assertSame($this->output, $result);
    }

    public function testEmptyOutput(): void
    {
        $string = (string) $this->output;
        $this->assertEquals('', $string);
    }

    public function testToFile(): void
    {
        $filePath = __DIR__ . '/output.txt';
        $this->output->add('File output test');
        $this->output->toFile($filePath);

        $this->assertFileExists($filePath);
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('[INFO]  File output test', $content);

        unlink($filePath); // Clean up
    }

    public function testVerbosityNoneLevel(): void
    {
        $output = new Output(''); // Non-CLI to avoid console output
        $output->setVerbosity(Output::VERBOSITY_NONE);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // Filtered output should be empty when verbosity is NONE
        $filteredString = $output->toString(false);
        $this->assertEquals('', $filteredString);

        // But all messages should still be available in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[STEP]  Step message', $allString);
        $this->assertStringContainsString('[ERROR] Error message', $allString);
        $this->assertStringContainsString('[WARN]  Warning message', $allString);
        $this->assertStringContainsString('[INFO]  Info message', $allString);
        $this->assertStringContainsString('[DUMP]  Debug message', $allString);
    }

    public function testVerbosityStepLevel(): void
    {
        $output = new Output(''); // Non-CLI to avoid console output
        $output->setVerbosity(Output::VERBOSITY_STEP);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // Only step messages should pass through at STEP level
        $filteredString = $output->toString(false);
        $this->assertStringContainsString('[STEP]  Step message', $filteredString);
        $this->assertStringNotContainsString('[ERROR] Error message', $filteredString);
        $this->assertStringNotContainsString('[WARN]  Warning message', $filteredString);
        $this->assertStringNotContainsString('[INFO]  Info message', $filteredString);
        $this->assertStringNotContainsString('[DUMP]  Debug message', $filteredString);

        // But all messages should still be available in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[STEP]  Step message', $allString);
        $this->assertStringContainsString('[ERROR] Error message', $allString);
        $this->assertStringContainsString('[WARN]  Warning message', $allString);
        $this->assertStringContainsString('[INFO]  Info message', $allString);
        $this->assertStringContainsString('[DUMP]  Debug message', $allString);
    }

    public function testVerbosityErrorLevel(): void
    {
        $output = new Output(''); // Non-CLI to avoid console output
        $output->setVerbosity(Output::VERBOSITY_ERROR);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // Step and error messages should pass through at ERROR level
        $filteredString = $output->toString(false);
        $this->assertStringContainsString('[STEP]  Step message', $filteredString);
        $this->assertStringContainsString('[ERROR] Error message', $filteredString);
        $this->assertStringNotContainsString('[WARN]  Warning message', $filteredString);
        $this->assertStringNotContainsString('[INFO]  Info message', $filteredString);
        $this->assertStringNotContainsString('[DUMP]  Debug message', $filteredString);

        // All messages should be available in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[STEP]  Step message', $allString);
        $this->assertStringContainsString('[ERROR] Error message', $allString);
        $this->assertStringContainsString('[WARN]  Warning message', $allString);
        $this->assertStringContainsString('[INFO]  Info message', $allString);
        $this->assertStringContainsString('[DUMP]  Debug message', $allString);
    }

    public function testVerbosityWarnLevel(): void
    {
        $output = new Output(''); // Non-CLI to avoid console output
        $output->setVerbosity(Output::VERBOSITY_WARN);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // Step, error, and warn messages should pass through at WARN level
        $filteredString = $output->toString(false);
        $this->assertStringContainsString('[STEP]  Step message', $filteredString);
        $this->assertStringContainsString('[ERROR] Error message', $filteredString);
        $this->assertStringContainsString('[WARN]  Warning message', $filteredString);
        $this->assertStringNotContainsString('[INFO]  Info message', $filteredString);
        $this->assertStringNotContainsString('[DUMP]  Debug message', $filteredString);

        // All messages should be available in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[STEP]  Step message', $allString);
        $this->assertStringContainsString('[ERROR] Error message', $allString);
        $this->assertStringContainsString('[WARN]  Warning message', $allString);
        $this->assertStringContainsString('[INFO]  Info message', $allString);
        $this->assertStringContainsString('[DUMP]  Debug message', $allString);
    }

    public function testVerbosityInfoLevel(): void
    {
        $output = new Output(''); // Non-CLI to avoid console output
        $output->setVerbosity(Output::VERBOSITY_INFO);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // Step, error, warn, and info messages should pass through at INFO level
        $filteredString = $output->toString(false);
        $this->assertStringContainsString('[STEP]  Step message', $filteredString);
        $this->assertStringContainsString('[ERROR] Error message', $filteredString);
        $this->assertStringContainsString('[WARN]  Warning message', $filteredString);
        $this->assertStringContainsString('[INFO]  Info message', $filteredString);
        $this->assertStringNotContainsString('[DUMP]  Debug message', $filteredString);

        // All messages should be available in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[STEP]  Step message', $allString);
        $this->assertStringContainsString('[ERROR] Error message', $allString);
        $this->assertStringContainsString('[WARN]  Warning message', $allString);
        $this->assertStringContainsString('[INFO]  Info message', $allString);
        $this->assertStringContainsString('[DUMP]  Debug message', $allString);
    }

    public function testVerbosityAllLevel(): void
    {
        $output = new Output(''); // Non-CLI to avoid console output
        $output->setVerbosity(Output::VERBOSITY_ALL);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // All messages should pass through at ALL level
        $filteredString = $output->toString(false);
        $this->assertStringContainsString('[STEP]  Step message', $filteredString);
        $this->assertStringContainsString('[ERROR] Error message', $filteredString);
        $this->assertStringContainsString('[WARN]  Warning message', $filteredString);
        $this->assertStringContainsString('[INFO]  Info message', $filteredString);
        $this->assertStringContainsString('[DUMP]  Debug message', $filteredString);

        // All messages should also be available in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[STEP]  Step message', $allString);
        $this->assertStringContainsString('[ERROR] Error message', $allString);
        $this->assertStringContainsString('[WARN]  Warning message', $allString);
        $this->assertStringContainsString('[INFO]  Info message', $allString);
        $this->assertStringContainsString('[DUMP]  Debug message', $allString);
    }

    public function testToFileWithVerbosity(): void
    {
        $output = new Output('');
        $output->setVerbosity(Output::VERBOSITY_WARN);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        // Test filtered output to file
        $filteredPath = __DIR__ . '/filtered_output.txt';
        $output->toFile($filteredPath, false);

        $this->assertFileExists($filteredPath);
        $filteredContent = file_get_contents($filteredPath);
        $this->assertStringContainsString('[STEP]  Step message', $filteredContent);
        $this->assertStringContainsString('[ERROR] Error message', $filteredContent);
        $this->assertStringContainsString('[WARN]  Warning message', $filteredContent);
        $this->assertStringNotContainsString('[INFO]  Info message', $filteredContent);
        $this->assertStringNotContainsString('[DUMP]  Debug message', $filteredContent);

        // Test all output to file
        $allPath = __DIR__ . '/all_output.txt';
        $output->toFile($allPath, true);

        $this->assertFileExists($allPath);
        $allContent = file_get_contents($allPath);
        $this->assertStringContainsString('[STEP]  Step message', $allContent);
        $this->assertStringContainsString('[ERROR] Error message', $allContent);
        $this->assertStringContainsString('[WARN]  Warning message', $allContent);
        $this->assertStringContainsString('[INFO]  Info message', $allContent);
        $this->assertStringContainsString('[DUMP]  Debug message', $allContent);

        // Clean up
        unlink($filteredPath);
        unlink($allPath);
    }

    public function testHelperMethods(): void
    {
        $output = new Output('');
        $output->setVerbosity(Output::VERBOSITY_ALL);

        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');

        $string = $output->toString(false);

        // Test that helper methods work correctly
        $this->assertStringContainsString('[STEP]  Step message', $string);
        $this->assertStringContainsString('[ERROR] Error message', $string);
        $this->assertStringContainsString('[WARN]  Warning message', $string);
        $this->assertStringContainsString('[INFO]  Info message', $string);
        $this->assertStringContainsString('[DUMP]  Debug message', $string);
    }

    public function testCustomTags(): void
    {
        $output = new Output('');
        $output->setVerbosity(Output::VERBOSITY_ALL);

        $output->add('Custom message', 'custom');
        $output->add('Debug message', 'debug');

        // Custom tags should pass through when verbosity > 0
        $filteredString = $output->toString(false);
        $this->assertStringContainsString('[CUSTOM]Custom message', $filteredString);
        $this->assertStringContainsString('[DEBUG] Debug message', $filteredString);

        // And should always be in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[CUSTOM]Custom message', $allString);
        $this->assertStringContainsString('[DEBUG] Debug message', $allString);
    }

    public function testCustomTagsWithZeroVerbosity(): void
    {
        $output = new Output('');
        $output->setVerbosity(Output::VERBOSITY_NONE);

        $output->add('Custom message', 'custom');
        $output->add('Debug message', 'debug');

        // Custom tags should not pass through when verbosity is 0
        $filteredString = $output->toString(false);
        $this->assertEquals('', $filteredString);

        // But should still be in dump
        $allString = $output->toString(true);
        $this->assertStringContainsString('[CUSTOM]Custom message', $allString);
        $this->assertStringContainsString('[DEBUG] Debug message', $allString);
    }
}
