<?php

namespace SQLBuilder\ANSI;

use SQLBuilder\Universal\Expr\FuncCallExpr;
use SQLBuilder\Utils;

class AggregateFunction
{
    public static function AVG($expr)
    {
        return new FuncCallExpr('AVG', Utils::buildFunctionArguments(array($expr)));
    }

    public static function COUNT($expr)
    {
        return new FuncCallExpr('COUNT', Utils::buildFunctionArguments(array($expr)));
    }

    public static function MAX($expr)
    {
        return new FuncCallExpr('MAX', Utils::buildFunctionArguments(array($expr)));
    }

    public static function MIN($expr)
    {
        return new FuncCallExpr('MIN', Utils::buildFunctionArguments(array($expr)));
    }

    public static function SUM($expr)
    {
        return new FuncCallExpr('SUM', Utils::buildFunctionArguments(array($expr)));
    }
}
