<?php

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use JDZ\Output\Output;

$output = new Output();
$output->setVerbosity(Output::VERBOSITY_ALL);

// Example usage of the output methods
$output->step('Starting process...');
$output->info('Task completed successfully.');
$output->warn('This is a warning message.');
$output->dump('This is a debug message.');
$output->error('An error occurred.');
$output->step('Finalizing output...');

// $strOutput = $output->toString(); // Get output as string
// echo $strOutput;

// Save output to a file
$output->toFile(__DIR__ . '/output.txt');
