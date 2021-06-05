#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use App\Command\BoundaryCommand;
use App\Command\GeoJSONCommand;
use App\Command\OverpassCommand;
use App\Command\StatisticsCommand;
use App\Command\Tool\NormalizeCSVCommand;
use App\Command\WikidataCommand;
use Symfony\Component\Console\Application;

$application = new Application('EqualStreetNames Data process');

$application->add(new OverpassCommand());
$application->add(new WikidataCommand());
$application->add(new BoundaryCommand());
$application->add(new GeoJSONCommand());
$application->add(new StatisticsCommand());

$application->add(new NormalizeCSVCommand());

$application->run();
