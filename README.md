# JDZ Output

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A lightweight PHP library for handling process output with different verbosity levels and formatting options. Perfect for CLI applications, logging, and process monitoring.

## Features

- **Multiple output types**: step, info, warn, error, and dump messages
- **Dual storage system**: Filtered output based on verbosity + complete message dump
- **Verbosity control**: Fine-grained filtering with hierarchical levels
- **CLI detection**: Automatically detects CLI environment and outputs to console
- **File export**: Save filtered or complete output to files for logging
- **Tagged messages**: All messages are automatically tagged and aligned
- **String conversion**: Access both filtered and complete output programmatically

## Installation

Install via Composer:

```bash
composer require jdz/output
```

## Requirements

- PHP 8.1 or higher

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use JDZ\Output\Output;

// Create an output instance
$output = new Output();

// Set verbosity level (optional)
$output->setVerbosity(Output::VERBOSITY_ALL);

// Add different types of messages
$output->step('Starting process...');
$output->info('Process initialized successfully');
$output->warn('Configuration file not found, using defaults');
$output->error('Failed to connect to database');
$output->dump('Debug information: variable state');

// Get output as string
echo $output->toString();

// Or use the object directly (calls __toString())
echo $output;
```

## Verbosity Levels

The library supports different verbosity levels to control which messages are displayed in filtered output. All messages are always stored internally regardless of verbosity level.

| Constant | Value | Description |
|----------|-------|-------------|
| `VERBOSITY_NONE` | 0 | No messages in filtered output |
| `VERBOSITY_STEP` | 1 | Only step messages |
| `VERBOSITY_ERROR` | 4 | Step and error messages |
| `VERBOSITY_WARN` | 8 | Step, error, and warning messages |
| `VERBOSITY_INFO` | 16 | Step, error, warning, and info messages |
| `VERBOSITY_ALL` | 32 | All messages including debug/dump |

```php
$output = new Output();

// Show only steps and errors
$output->setVerbosity(Output::VERBOSITY_ERROR);

// Show all messages (default)
$output->setVerbosity(Output::VERBOSITY_ALL);

// Get filtered output based on verbosity
echo $output->toString(false); // or just $output->toString()

// Get ALL messages regardless of verbosity
echo $output->toString(true);
```

## Message Types

### Step Messages
Use for major process milestones:

```php
$output->step('Initializing application...');
$output->step('Processing data...');
$output->step('Finalizing output...');
```

### Info Messages
Use for general information:

```php
$output->info('Found 150 records to process');
$output->info('Configuration loaded successfully');
```

### Warning Messages
Use for non-critical issues:

```php
$output->warn('Using default configuration');
$output->warn('Cache directory not writable');
```

### Error Messages
Use for errors and failures:

```php
$output->error('Database connection failed');
$output->error('Invalid input parameters');
```

### Debug/Dump Messages
Use for debugging and detailed information:

```php
$output->dump('Current memory usage: 45MB');
$output->dump('Processing file: /path/to/file.txt');
```

## CLI Mode

When running in CLI mode (command line interface), the Output class automatically detects the environment and outputs messages directly to the console in real-time, **but only for messages that pass the verbosity filter**. All messages are still stored internally regardless of verbosity.

```php
// In CLI, this will echo messages immediately if they pass verbosity filter
$output = new Output(); // Automatically detects CLI mode
$output->setVerbosity(Output::VERBOSITY_WARN);

$output->step('Starting process');    // Echoed immediately + stored
$output->info('Processing data');     // Only stored (below WARN level)  
$output->warn('Memory low');          // Echoed immediately + stored
$output->error('Connection failed');  // Echoed immediately + stored

// You can still access all messages programmatically:
echo $output->toString(true);  // Gets all messages including info
```

## File Output

Save output to files for logging purposes. You can save either filtered output or complete message dump:

```php
$output = new Output();
$output->setVerbosity(Output::VERBOSITY_WARN);

$output->step('Process started');
$output->info('Data processed successfully');
$output->warn('Minor issue detected');
$output->dump('Debug: processing 1000 records');

// Save filtered output (based on verbosity level)
$output->toFile('/path/to/filtered-log.txt', false);

// Save ALL messages regardless of verbosity
$output->toFile('/path/to/complete-log.txt', true);
```

**Filtered log** (`VERBOSITY_WARN`) will contain:
```
[STEP]  Process started
[WARN]  Minor issue detected
```

**Complete log** will contain:
```
[STEP]  Process started
[INFO]  Data processed successfully
[WARN]  Minor issue detected
[DUMP]  Debug: processing 1000 records
```

## Advanced Usage

### Dual Output Access

The library maintains two separate message stores:

```php
$output = new Output();
$output->setVerbosity(Output::VERBOSITY_WARN);

$output->step('Starting process');
$output->info('Processing 100 items');  // Won't appear in filtered output
$output->warn('Low memory warning');
$output->dump('Debug info');            // Won't appear in filtered output

// Get filtered output (respects verbosity)
$filtered = $output->toString(false);
echo $filtered;
// Output:
// [STEP]  Starting process
// [WARN]  Low memory warning

// Get complete output (all messages)
$complete = $output->toString(true);
echo $complete;
// Output:
// [STEP]  Starting process  
// [INFO]  Processing 100 items
// [WARN]  Low memory warning
// [DUMP]  Debug info
```

### Custom Message Addition

You can add messages with custom tags:

```php
$output = new Output();
$output->add('Custom message', 'custom');
$output->add('System status', 'status');

// Custom tags get proper formatting:
// [CUSTOM]Custom message
// [STATUS]System status
```

### Fluent Interface

The `setVerbosity()` method returns the instance for method chaining:

```php
$output = new Output();
$result = $output->setVerbosity(Output::VERBOSITY_ALL);
// $result is the same instance as $output

// You can chain if you prefer:
$output->setVerbosity(Output::VERBOSITY_INFO);
$output->step('Processing...');
```

### Empty Output Handling

```php
$output = new Output();

// Check if any messages were added
echo $output->toString();        // Returns empty string if no messages
echo $output->toString(true);    // Returns empty string if no messages

if (empty($output->toString())) {
    echo "No output generated";
}
```

### Understanding Message Formatting

All messages are formatted with consistent padding for better readability:

```php
$output = new Output();
$output->step('Step message');      // [STEP]  Step message
$output->error('Error occurred');   // [ERROR] Error occurred  
$output->info('Information');       // [INFO]  Information
$output->warn('Warning message');   // [WARN]  Warning message
$output->dump('Debug data');        // [DUMP]  Debug data
```

Tags are padded to 8 characters for consistent alignment.

## Error Handling

The library throws exceptions for file operation errors:

```php
try {
    $output->toFile('/invalid/path/file.txt');
} catch (\RuntimeException $e) {
    echo "Error: " . $e->getMessage();
}
```

## Testing

Run the test suite:

```bash
composer test
```

Or using PHPUnit directly:

```bash
vendor/bin/phpunit
```

## Examples

See the `examples/` directory for more usage examples:

```bash
composer run example
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This library is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Author

**Joffrey Demetz**
- Email: joffrey.demetz@gmail.com
- Website: [https://joffreydemetz.com](https://joffreydemetz.com)
- Package Homepage: [https://jdz.joffreydemetz.com/output/](https://jdz.joffreydemetz.com/output/)

## Support

If you encounter any issues or have questions, please [open an issue](https://github.com/joffreydemetz/output/issues) on GitHub.