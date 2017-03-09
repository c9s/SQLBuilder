<?php

namespace SQLBuilder\PgSQL;

/**
 * @author Yo-An Lin
 * @copyright Yo-An Lin, 14 January, 2015
 */
class Types
{
    public static $typemap = array(
        'bool' => true,
        'boolean' => true,
        'tinyint' => true,
        'smallint' => true,
        'mediumint' => true,
        'int2' => true,
        'int' => true,
        'int4' => true,
        'serial4' => true,
        'integer' => true,
        'int8' => true,
        'bigint' => true,
        'bigserial' => true,
        'serial8' => true,
        'int24' => true,
        'real' => true,
        'float' => true,
        'float4' => true,
        'decimal' => true,
        'numeric' => true,
        'double' => true,
        'float8' => true,
        'char' => true,
        'character' => true,
        'varchar' => true,
        'date' => true,
        'time' => true,
        'timetz' => true,
        // 'year' => true,
        'datetime' => true,
        'timestamp' => true,
        'timestamptz' => true,
        'bytea' => true,
        'text' => true,
        'time without time zone' => true,
        'timestamp without time zone' => true,
        'double precision' => true,
    );

    public static $defaultTypeSizes = array(
        'char' => 1,
        'character' => 1,
        'integer' => 32,
        'bigint' => 64,
        'smallint' => 16,
        'double precision' => 54,
    );
}
