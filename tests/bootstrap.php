<?php
$loader = require 'vendor/autoload.php';
require 'tests/DriverFactory.php';

if (extension_loaded('xhprof') ) {
    ini_set('xhprof.output_dir','/tmp');
}
