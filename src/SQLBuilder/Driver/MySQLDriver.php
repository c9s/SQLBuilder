<?php
namespace SQLBuilder\Driver;
use SQLBuilder\Raw;
use SQLBuilder\DataType\Unknown;
use SQLBuilder\ArgumentArray;
use SQLBuilder\ParamMarker;
use SQLBuilder\Bind;
use SQLBuilder\ToSqlInterface;
use Closure;
use DateTime;
use Exception;
use RuntimeException;
use LogicException;

class MySQLDriver extends BaseDriver
{
    public $quoteColumn = false;
    public $quoteTable = false;

    public function quoteIdentifier($id) {
        return '`' . addcslashes($id,'`') . '`';
    }

    public function deflate($value, ArgumentArray $args = NULL)
    {
        if ($value instanceof DateTime) {
            // MySQL does not support date time string with timezone
            return $value->format('Y-m-d H:i:s');
        }
        return parent::deflate($value, $args);
    }

}


