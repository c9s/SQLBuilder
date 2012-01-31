<?php
require( 'tests/bootstrap.php');

#  $pdo = new PDO('mysql:host=localhost;dbname=benchmarks', 'root', '123123' );
#  $mysqli = new mysqli("localhost", "root", "123123", "benchmarks");

$bench = new SimpleBench( array( 'gc' => 1 ));
$bench->n = 10000;
$bench->title = 'sql builder';

$bench->iterate( 'pdo_query' , 'pdo' , function() use($pdo) {
});

$bench->iterate( 'pdo_prepare' , 'pdo' , function() use($pdo) {
});

$result = $bench->compare();
echo $result->output('console');

