<?php
namespace SQLBuilder\Driver;

class MySQLDriver extends BaseDriver
{

    public $quoteColumn = false;
    public $quoteTable = false;

    public function quoteIdentifier($id) {
        return '`' . $id . '`';
    }
}


