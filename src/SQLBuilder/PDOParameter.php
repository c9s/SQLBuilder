<?php
namespace SQLBuilder;

class PDOParameter
{

    /**
     * PDO has boolean value cast problem
     */
    static function cast($value, $type = null)
    {
        if ( $value === true )
            return 1;
        if ( $value === false )
            return 0;
        return $value;
    }
}



