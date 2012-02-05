<?php
require( 'tests/bootstrap.php');

#  $pdo = new PDO('mysql:host=localhost;dbname=benchmarks', 'root', '123123' );
#  $mysqli = new mysqli("localhost", "root", "123123", "benchmarks");


$driver = new SQLBuilder\Driver;
$driver->configure('driver','pgsql');
$driver->configure('quote_table',true);
$driver->configure('quote_column',true);
$driver->configure('trim',true);
$driver->configure('placeholder','named');

$sb = new SQLBuilder\QueryBuilder;
$sb->driver = $driver;


$bench = new SimpleBench( array( 'gc' => 1 ));
$bench->n = 1000;
$bench->title = 'sql builder';

$bench->iterate( 'select' , 'select' , function() use($sb) {
    $sb->select( '*' )->table('users')->alias('u')->build();
});

$bench->iterate( 'join' , 'join' , function() use($sb) {
    $sb->select( '*' )
        ->table('users')->alias('u')
        ->join('tweets')
            ->alias('t')
            ->on()
                ->equal('u.id',array('t.user_id'))->back()->build();
});

$result = $bench->compare();
echo $result->output('console');
