<?php

/**
 * @author    Joffrey Demetz <joffrey.demetz@gmail.com>
 * @license   MIT License; <https://opensource.org/licenses/MIT>
 */

namespace JDZ\Output;

enum Verbosity: int
{
    case NONE = 0;
    case STEP = 1;
    case ERROR = 4;
    case WARN = 8;
    case INFO = 16;
    case ALL = 32;

    /**
     * Check if this verbosity level includes another level
     */
    public function includes(self $level): bool
    {
        return $this->value >= $level->value;
    }

    /**
     * Get a human-readable description
     */
    public function description(): string
    {
        return match ($this) {
            self::NONE => 'No messages in filtered output',
            self::STEP => 'Only step messages',
            self::ERROR => 'Step and error messages',
            self::WARN => 'Step, error, and warning messages',
            self::INFO => 'Step, error, warning, and info messages',
            self::ALL => 'All messages including debug/dump',
        };
    }
}
