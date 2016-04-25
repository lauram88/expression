<?php

$loader = require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Laura\Expressions\Command\GreetCommand;
use Laura\Expressions\Command\CalculateExpressions;

$application = new Application();
$application->add(new CalculateExpressions());
$application->run(); 