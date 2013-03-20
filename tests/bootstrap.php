<?php
require 'PHPUnit/TestMore.php';
require 'vendor/pear/Universal/ClassLoader/BasePathClassLoader.php';
require 'tests/DriverFactory.php';
require 'tests/PHPUnit/PDO/TestCase.php';
$loader = new \Universal\ClassLoader\BasePathClassLoader(array('src','vendor/pear'));
$loader->useIncludePath(true);
// prepend to the spl class loader list
$loader->register(true); // use prepend
