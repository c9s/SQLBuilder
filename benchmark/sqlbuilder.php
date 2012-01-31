<?php
require( 'tests/bootstrap.php');

#  $pdo = new PDO('mysql:host=localhost;dbname=benchmarks', 'root', '123123' );
#  $mysqli = new mysqli("localhost", "root", "123123", "benchmarks");


$driver = new SQLBuilder\Driver;
$driver->configure('driver','postgresql');
$driver->configure('quote_table',true);
$driver->configure('quote_column',true);
$driver->configure('trim',true);
$driver->configure('placeholder','named');

$sb = new SQLBuilder\CRUDBuilder;
$sb->driver = $driver;


$bench = new SimpleBench( array( 'gc' => 1 ));
$bench->n = 10000;
$bench->title = 'sql builder';

$bench->iterate( 'select' , 'select' , function() use($sb) {
    $sb->select( '*' )->table('users')->alias('u')->build();
});

$result = $bench->compare();
echo $result->output('console');

