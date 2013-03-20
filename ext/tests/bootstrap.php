<?php
require 'PHPUnit/TestMore.php';
require 'PHPUnit/Framework/ExtensionTestCase.php';
// require '../PHPUnit_Framework_ExtensionTestCase/src/PHPUnit/Framework/ExtensionTestCase.php';
require 'Universal/ClassLoader/BasePathClassLoader.php';


if ( ! extension_loaded('sqlbuilder') ) {
    // require "src/FileUtil.php";
}

if ( !defined('BASEDIR') ) {
    define('BASEDIR',dirname(dirname(__FILE__)));
}
$classLoader = new \Universal\ClassLoader\BasePathClassLoader(array( 
    BASEDIR . '/src',
    BASEDIR . '/vendor/pear',
));
$classLoader->useIncludePath(false);
$classLoader->register();
