<?php
namespace SQLBuilder\ANSI;
use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Raw;
use SQLBuilder\Utils;

class AggregateFunction
{
    static public function AVG($expr) {
        return new FuncCallExpr('AVG', Utils::buildFunctionArguments(array($expr)));
    }

    static public function COUNT($expr) {
        return new FuncCallExpr('COUNT', Utils::buildFunctionArguments(array($expr)));
    }

    static public function MAX($expr) {
        return new FuncCallExpr('MAX', Utils::buildFunctionArguments(array($expr)));
    }

    static public function MIN($expr) {
        return new FuncCallExpr('MIN', Utils::buildFunctionArguments(array($expr)));
    }

    static public function SUM($expr) {
        return new FuncCallExpr('SUM', Utils::buildFunctionArguments(array($expr)));
    }
}



