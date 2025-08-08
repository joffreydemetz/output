<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\Output;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Output
{
  public const VERBOSITY_NONE = 0;
  public const VERBOSITY_STEP = 1;
  public const VERBOSITY_ERROR = 4;
  public const VERBOSITY_WARN = 8;
  public const VERBOSITY_INFO = 16;
  public const VERBOSITY_ALL = 32;

  private string $mode;
  private int $verbosity = self::VERBOSITY_ALL;
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

  public function setVerbosity(int $verbosity = 0): self
  {
    $this->verbosity = $verbosity;
    return $this;
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

    if (!$this->verbosity) {
      return;
    }

    if ($tag === 'step') {
      if ($this->verbosity < self::VERBOSITY_STEP) {
        return;
      }
    } elseif ($tag === 'error') {
      if ($this->verbosity < self::VERBOSITY_ERROR) {
        return;
      }
    } elseif ($tag === 'warn') {
      if ($this->verbosity < self::VERBOSITY_WARN) {
        return;
      }
    } elseif ($tag === 'info') {
      if ($this->verbosity < self::VERBOSITY_INFO) {
        return;
      }
    } elseif ($tag === 'dump') {
      if ($this->verbosity < self::VERBOSITY_ALL) {
        return;
      }
    } else {
      // Allow any other tag to pass through
      if ($this->verbosity === self::VERBOSITY_NONE) {
        return;
      }
    }

    $this->output[] = $output;

    if ('cli' === $this->mode) {
      echo $output . "\n";
    }
  }

  public function step(string $message)
  {
    $this->add($message, 'step');
  }

  public function error(string $message)
  {
    $this->add($message, 'error');
  }

  public function warn(string $message)
  {
    $this->add($message, 'warn');
  }

  public function info(string $message)
  {
    $this->add($message, 'info');
  }

  public function dump(string $message)
  {
    $this->add($message, 'dump');
  }
}
