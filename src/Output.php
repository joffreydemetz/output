<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Output;

use JDZ\Output\Verbosity;

class Output
{
  private string $mode;
  private Verbosity $verbosity = Verbosity::ALL;
  private array $output = []; // output messages based on verbosity
  private array $dump = [];   // all messages regardless of verbosity

  public function __construct(?string $mode = null)
  {
    if (null === $mode) {
      $mode = 'cli' === \PHP_SAPI ? 'cli' : '';
    }
    $this->mode = $mode;
  }

  public function __toString(): string
  {
    return $this->toString();
  }

  public function setVerbosity(Verbosity|int $verbosity): self
  {
    $this->verbosity = $verbosity instanceof Verbosity
      ? $verbosity
      : Verbosity::from($verbosity);

    return $this;
  }

  public function getVerbosity(): Verbosity
  {
    return $this->verbosity;
  }

  public function toString(bool $all = false): string
  {
    $str = '';
    if ($all) {
      if (!empty($this->dump)) {
        $str = implode("\n", $this->dump);
      }
    } else {
      if (!empty($this->output)) {
        $str = implode("\n", $this->output);
      }
    }

    return $str;
  }

  public function toFile(string $path, bool $all = false): void
  {
    if (!$path || !is_writable(dirname($path))) {
      throw new \RuntimeException('Dump output path is not valid.');
    }

    $dump = $this->toString($all);

    try {
      file_put_contents($path, $dump);
      chmod($path, 0777);
    } catch (\Throwable $e) {
      throw new \RuntimeException('Error dumping output: ' . $e->getMessage(), 0, $e);
    }
  }

  public function add(string $message, string $tag = 'info'): void
  {
    $output = str_pad('[' . strtoupper($tag) . ']', 8, ' ', STR_PAD_RIGHT) . $message;

    $this->dump[] = $output;

    if ($this->verbosity === Verbosity::NONE) {
      return;
    }

    $shouldOutput = match ($tag) {
      'step' => $this->verbosity->includes(Verbosity::STEP),
      'error' => $this->verbosity->includes(Verbosity::ERROR),
      'warn' => $this->verbosity->includes(Verbosity::WARN),
      'info' => $this->verbosity->includes(Verbosity::INFO),
      'dump' => $this->verbosity->includes(Verbosity::ALL),
      default => $this->verbosity !== Verbosity::NONE,
    };

    if (!$shouldOutput) {
      return;
    }

    $this->output[] = $output;

    if ('cli' === $this->mode) {
      echo $output . "\n";
    }
  }

  public function step(string $message): void
  {
    $this->add($message, 'step');
  }

  public function error(string $message): void
  {
    $this->add($message, 'error');
  }

  public function warn(string $message): void
  {
    $this->add($message, 'warn');
  }

  public function info(string $message): void
  {
    $this->add($message, 'info');
  }

  public function dump(string $message): void
  {
    $this->add($message, 'dump');
  }
}
