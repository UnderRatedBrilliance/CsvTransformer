#!/usr/bin/env php
<?php

//Set to run indefinitely
set_time_limit(0);

// include the composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application('CSV Transformer', '0.1.0');

//Add Commands to application
$app->addCommands([
    new CsvTransformer\Commands\TransformCsvCommand(),
    new CsvTransformer\Commands\TransformXmltoCsvCommand(),
]);

$app->run();