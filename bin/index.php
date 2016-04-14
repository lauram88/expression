<?php
require  '../../../slugify/vendor/autoload.php';
require  '../controllers/calculate_expression.php';
error_reporting(E_ALL);
$slugify = new Slugify();
$calculate_expression = new Calculate_expression();

echo $slugify->slugify('Hello World, this is a long sentence and I need to make a slug from it!');

?>
