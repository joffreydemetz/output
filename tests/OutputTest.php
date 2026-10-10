<?php

namespace JDZ\Output\Tests;

use JDZ\Output\Output;
use JDZ\Output\Verbosity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Output::class)]
class OutputTest extends TestCase
{
    private const ALL_LINES = [
        '[STEP]  Step message',
        '[ERROR] Error message',
        '[WARN]  Warning message',
        '[INFO]  Info message',
        '[DUMP]  Debug message',
        '[NOTE]  Custom message',
    ];

    private ?string $dir = null;

    protected function tearDown(): void
    {
        if (null !== $this->dir) {
            foreach (glob($this->dir . '/*') ?: [] as $file) {
                is_dir($file) ? rmdir($file) : unlink($file);
            }
            rmdir($this->dir);
        }
    }

    private function tempDir(): string
    {
        $this->dir ??= sys_get_temp_dir() . '/jdz-output-' . bin2hex(random_bytes(6));
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }

        return $this->dir;
    }

    /** one message through every helper, plus a custom tag; mode '' buffers without echoing */
    private static function filled(Verbosity|int $verbosity): Output
    {
        $output = (new Output(''))->setVerbosity($verbosity);
        $output->step('Step message');
        $output->error('Error message');
        $output->warn('Warning message');
        $output->info('Info message');
        $output->dump('Debug message');
        $output->add('Custom message', 'note');

        return $output;
    }

    public static function verbosities(): array
    {
        [$step, $error, $warn, $info, $dump, $custom] = self::ALL_LINES;

        return [
            'NONE' => [Verbosity::NONE, []],
            'STEP' => [Verbosity::STEP, [$step, $custom]],
            'ERROR' => [Verbosity::ERROR, [$step, $error, $custom]],
            'WARN' => [Verbosity::WARN, [$step, $error, $warn, $custom]],
            'INFO' => [Verbosity::INFO, [$step, $error, $warn, $info, $custom]],
            'ALL' => [Verbosity::ALL, self::ALL_LINES],
            'set as an int' => [8, [$step, $error, $warn, $custom]],
        ];
    }

    /**
     * The filtered output keeps the tags the verbosity includes (a custom tag
     * passes unless the verbosity is NONE); the full dump keeps everything.
     */
    #[DataProvider('verbosities')]
    public function testTheVerbosityFiltersTheOutputNotTheDump(Verbosity|int $verbosity, array $lines): void
    {
        $output = self::filled($verbosity);

        $this->assertSame(implode("\n", $lines), $output->toString());
        $this->assertSame(implode("\n", $lines), (string) $output);
        $this->assertSame(implode("\n", self::ALL_LINES), $output->toString(true));
    }

    public function testTheVerbosityIsAllByDefault(): void
    {
        $this->assertSame(Verbosity::ALL, (new Output(''))->getVerbosity());
        $this->assertSame(Verbosity::INFO, (new Output(''))->setVerbosity(16)->getVerbosity());
    }

    public function testAnUnknownVerbosityIsRefused(): void
    {
        $this->expectException(\ValueError::class);

        (new Output(''))->setVerbosity(2);
    }

    public function testTheCliModeEchoesEachShownMessage(): void
    {
        $output = (new Output('cli'))->setVerbosity(Verbosity::ERROR);

        $this->expectOutputString("[STEP]  Started\n[ERROR] Failed\n");

        $output->step('Started');
        $output->error('Failed');
        $output->info('Not shown');
    }

    public function testTheModeIsCliWhenRunFromTheCommandLine(): void
    {
        $this->expectOutputString("[INFO]  Hello\n");

        (new Output())->info('Hello');
    }

    public function testToFileWritesTheFilteredOrTheFullOutput(): void
    {
        $dir = $this->tempDir();
        $output = self::filled(Verbosity::WARN);

        $output->toFile($dir . '/filtered.log');
        $output->toFile($dir . '/all.log', true);

        $this->assertSame($output->toString(), file_get_contents($dir . '/filtered.log'));
        $this->assertSame($output->toString(true), file_get_contents($dir . '/all.log'));
    }

    public static function invalidPaths(): array
    {
        return [
            'an empty path' => [''],
            'a missing folder' => ['/jdz-output-no-such-folder/sub/out.log'],
        ];
    }

    #[DataProvider('invalidPaths')]
    public function testToFileRefusesAPathItCannotWrite(string $path): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Dump output path is not valid.');

        (new Output(''))->toFile($path);
    }
}
