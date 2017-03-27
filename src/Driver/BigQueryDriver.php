<?php

namespace SQLBuilder\Driver;


/**
 * Class BigQueryDriver
 *
 * @package SQLBuilder\Driver
 *
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class BigQueryDriver extends BaseDriver
{
    public $quoteTable = true;

    /**
     * @param $id
     *
     * @return string
     */
    public function quoteIdentifier($id)
    {
        return '`' . addcslashes($id, '`') . '`';
    }

    /**
     * @inheritDoc
     */
    public function quote($string)
    {
        return $string;
    }


}