<?php
namespace SQLBuilder\Driver;
use DateTime;
use Exception;
use RuntimeException;

class PgSQLDriver extends BaseDriver
{
    public function quoteIdentifier($id) {
        return '"' . $id . '"';
    }
}

