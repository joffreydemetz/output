# JDZ Output

A lightweight PHP library for handling process output with different verbosity levels and formatting options. Perfect for CLI applications, logging, and process monitoring.

## Features

- **Multiple output types**: step, info, warn, error, and dump messages
- **Dual storage system**: Filtered output based on verbosity + complete message dump
- **Verbosity control**: Fine-grained filtering with hierarchical levels using PHP 8.1+ enums
- **CLI detection**: Automatically detects CLI environment and outputs to console
- **File export**: Save filtered or complete output to files for logging
- **Tagged messages**: All messages are automatically tagged and aligned
- **String conversion**: Access both filtered and complete output programmatically
- **Type-safe**: Uses backed enums for type safety and better IDE support

## Installation

Install via Composer:

```bash
composer require jdz/output
```

## Requirements

- PHP 8.1 or higher

## Quick Start

```php
use JDZ\Output\Output;
use JDZ\Output\Verbosity;

// Create output instance (auto-detects CLI mode)
$output = new Output();

// Add messages
$output->step('Starting process...');
$output->info('Processing 100 records');
$output->warn('Skipping invalid record');
$output->error('Database connection failed');
$output->dump('Debug info: memory usage');

// Control verbosity
$output->setVerbosity(Verbosity::WARN);

// Get output
echo $output->toString(false); // Filtered based on verbosity
echo $output->toString(true);  // All messages regardless of verbosity

// Save to file
$output->toFile('output.log');        // Filtered output
$output->toFile('complete.log', true); // Complete output
```

## Verbosity Levels

The library uses a PHP 8.1+ backed enum for type-safe verbosity levels. All messages are always stored internally regardless of verbosity level.

### Enum Cases

| Enum Case | Value | Description |
|-----------|-------|-------------|
| `Verbosity::NONE` | 0 | No messages in filtered output |
| `Verbosity::STEP` | 1 | Only step messages |
| `Verbosity::ERROR` | 4 | Step and error messages |
| `Verbosity::WARN` | 8 | Step, error, and warning messages |
| `Verbosity::INFO` | 16 | Step, error, warning, and info messages |
| `Verbosity::ALL` | 32 | All messages including debug/dump |

### Usage

```php
use JDZ\Output\Output;
use JDZ\Output\Verbosity;

$output = new Output();

// Using enum (recommended - type-safe)
$output->setVerbosity(Verbosity::WARN);
$output->setVerbosity(Verbosity::INFO);
$output->setVerbosity(Verbosity::ALL); // default

// Using integer value (backward compatible)
$output->setVerbosity(8);  // Same as Verbosity::WARN

// Using constants (backward compatible)
$output->setVerbosity(Verbosity::WARN); // Same as 8

// Get current verbosity level
$level = $output->getVerbosity(); // Returns Verbosity enum
echo $level->name; // "WARN"
echo $level->value; // 8
echo $level->description(); // "Step, error, and warning messages"

// Check verbosity hierarchy
if (Verbosity::INFO->includes(Verbosity::WARN)) {
    // INFO level includes WARN level
}
```

### Verbosity Hierarchy

Higher verbosity levels include all lower level messages:

```
NONE (0)
  ↓
STEP (1)
  ↓
ERROR (4)  ← includes STEP
  ↓
WARN (8)   ← includes STEP + ERROR
  ↓
INFO (16)  ← includes STEP + ERROR + WARN
  ↓
ALL (32)   ← includes everything
```

### Enum Methods

```php
// Check if one level includes another
Verbosity::INFO->includes(Verbosity::WARN);  // true
Verbosity::WARN->includes(Verbosity::INFO);  // false

// Get human-readable description
Verbosity::WARN->description(); 
// Returns: "Step, error, and warning messages"

// Get all cases
foreach (Verbosity::cases() as $level) {
    echo $level->name . ": " . $level->description() . "\n";
}

// Create from integer
$level = Verbosity::from(8);      // Returns Verbosity::WARN
$level = Verbosity::tryFrom(999); // Returns null for invalid value
```

## Message Types

The Output class provides methods for different types of messages, each tagged appropriately.

```php
// Step Messages (verbosity >= Verbosity::STEP)
$output->step('Initializing application...');
// Output: [STEP]  Initializing application...

// Info Messages (verbosity >= Verbosity::INFO)
$output->info('Found 150 records to process');
// Output: [INFO]  Found 150 records to process

// Warning Messages (verbosity >= Verbosity::WARN)
$output->warn('Using default configuration');
// Output: [WARN]  Using default configuration

// Error Messages (verbosity >= Verbosity::ERROR)
$output->error('Database connection failed');
// Output: [ERROR] Database connection failed

// Debug/Dump Messages (verbosity >= Verbosity::ALL)
$output->dump('Current memory usage: 45MB');
// Output: [DUMP]  Current memory usage: 45MB

// Custom Tagged Messages
$output->add('Custom log entry', 'custom');
// Output: [CUSTOM]Custom log entry
```

## CLI Mode

The library automatically detects if it's running in CLI mode and outputs messages to the console in real-time.

```php
// Auto-detect mode
$output = new Output(); // Will detect CLI automatically

// Force CLI mode
$output = new Output('cli');

// Force non-CLI mode (no console output)
$output = new Output('');

// Only messages that pass the verbosity filter are echoed to CLI
// But all messages are stored internally regardless
```

## Dual Storage System

One of the key features is the dual storage system:

1. **Filtered Output** (`$output` array): Contains only messages that pass the verbosity filter
2. **Complete Dump** (`$dump` array): Contains ALL messages regardless of verbosity

```php
$output = new Output('');
$output->setVerbosity(Verbosity::WARN);

$output->step('Step 1');   // Will be included
$output->error('Error 1'); // Will be included
$output->warn('Warning 1'); // Will be included
$output->info('Info 1');    // Will NOT be included in filtered output
$output->dump('Debug 1');   // Will NOT be included in filtered output

// Get filtered output (respects verbosity)
$filtered = $output->toString(false);
// Contains: STEP, ERROR, WARN

// Get complete output (ignores verbosity)
$complete = $output->toString(true);
// Contains: STEP, ERROR, WARN, INFO, DUMP
```

## File Output

Save output to files with optional verbosity filtering:

```php
$output = new Output('');
$output->setVerbosity(Verbosity::WARN);

$output->step('Processing started');
$output->info('Record 1 processed');
$output->warn('Skipped invalid record');
$output->error('Failed to process record 5');
$output->dump('Memory usage: 45MB');

// Save filtered output (respects verbosity level)
$output->toFile('filtered.log', false);
// File contains: step, error, warn messages only

// Save complete output (all messages)
$output->toFile('complete.log', true);
// File contains: all messages including info and dump
```

**File contents example:**

`filtered.log`:
```
[STEP]  Processing started
[WARN]  Skipped invalid record
[ERROR] Failed to process record 5
```

`complete.log`:
```
[STEP]  Processing started
[INFO]  Record 1 processed
[WARN]  Skipped invalid record
[ERROR] Failed to process record 5
[DUMP]  Memory usage: 45MB
```

## Advanced Usage

### Dual Output Access

```php
$output = new Output('');
$output->setVerbosity(Verbosity::ERROR);

$output->step('Step 1');
$output->error('Error occurred');
$output->warn('Warning message');
$output->info('Info message');
$output->dump('Debug data');

// Method 1: toString() with parameter
$filtered = $output->toString(false); // Only STEP and ERROR
$complete = $output->toString(true);  // All messages

// Method 2: __toString() always returns filtered
$filtered = (string) $output; // Only STEP and ERROR
```

### Custom Tags

Add custom tagged messages that don't fit standard categories:

```php
$output->add('Database connected successfully', 'db');
// Output: [DB]    Database connected successfully

$output->add('Cache cleared', 'cache');
// Output: [CACHE] Cache cleared

$output->add('Custom event triggered', 'event');
// Output: [EVENT] Custom event triggered
```

### Understanding Message Formatting

All tags are padded to 8 characters for consistent alignment:

```php
[STEP]  Message  // 4-char tag + 2 spaces + message
[INFO]  Message  // 4-char tag + 2 spaces + message
[WARN]  Message  // 4-char tag + 2 spaces + message
[ERROR] Message  // 5-char tag + 1 space + message
[DUMP]  Message  // 4-char tag + 2 spaces + message
[CUSTOM]Message  // 6-char tag + 0 spaces + message
```

## Examples

See the [examples](examples/) directory for detailed examples:

- `example.php` - Complete usage demonstration with all verbosity levels

## Testing

Run the test suite:

```bash
# Run all tests
composer test

# Run with coverage
composer test -- --coverage-html coverage

# Run specific test file
vendor/bin/phpunit tests/OutputTest.php
vendor/bin/phpunit tests/VerbosityTest.php

# Run with detailed output
vendor/bin/phpunit --testdox
```

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Changelog

### Version 2.0.0
- Added PHP 8.1+ backed enum for type-safe verbosity levels
- Added `Verbosity::includes()` method for hierarchy checking
- Added `Verbosity::description()` method for human-readable descriptions
- Improved dual storage system documentation
- Enhanced test coverage

### Version 1.0.0
- Initial release
- Basic output handling with verbosity levels
- CLI detection and console output
- File export functionality
- Tagged messages with alignment