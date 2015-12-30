<?php
namespace SQLBuilder;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Raw;

class Utils
{
    static public function buildExprArg($a) {
        if ($a instanceof ToSqlInterface) {
            return $a;
        } else {
            return new Raw($a);
        }
    }

    static public function buildFunctionArguments($args) {
        return array_map(array('SQLBuilder\\Utils','buildExprArg'), $args);
    }
}



