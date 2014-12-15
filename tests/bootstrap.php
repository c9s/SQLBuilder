<?php
$loader = require 'vendor/autoload.php';
if (extension_loaded('xhprof') ) {
    ini_set('xhprof.output_dir','/tmp');
}
