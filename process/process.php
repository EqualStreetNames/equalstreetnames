#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use App\Command\OverpassCommand;
use App\Command\WikidataCommand;
use Symfony\Component\Console\Application;

$application = new Application('EqualStreetNames Data process');

$application->add(new OverpassCommand());
$application->add(new WikidataCommand());

$application->run();