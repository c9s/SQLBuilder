<?php

namespace SQLBuilder\SQLite;

/**
 * @author Yo-An Lin
 * @copyright Yo-An Lin, 14 January, 2015
 */
class Types
{
    public static $typemap = array(
        'tinyint' => true,
        'smallint' => true,
        'mediumint' => true,
        'int' => true,
        'integer' => true,
        'bigint' => true,
        'int24' => true,
        'real' => true,
        'float' => true,
        'decimal' => true,
        'numeric' => true,
        'double' => true,
        'char' => true,
        'varchar' => true,
        'date' => true,
        'time' => true,
        'year' => true,
        'datetime' => true,
        'timestamp' => true,
        'tinyblob' => true,
        'blob' => true,
        'mediumblob' => true,
        'longblob' => true,
        'longtext' => true,
        'tinytext' => true,
        'mediumtext' => true,
        'text' => true,
        'enum' => true,
        'set' => true,
    );
}
