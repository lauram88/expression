<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader = require __DIR__ . '/../src/calculate_expression.php';

use Symfony\Component\Console\Application;
use Laura\Expressions\Command\GreetCommand;
use Laura\Expressions\Command\CalculateExpressions;

$application = new Application();
$application->add(new CalculateExpressions());
$application->run();

//$calc_expr = new Calculate_expression();
//$espr = "F&((T&F)|T&(F|T))&(F|T)";
//$calc_expr->calc_expr($espr);

//echo 'Front controller!' . PHP_EOL;
