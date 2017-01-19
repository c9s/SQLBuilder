<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;

class LeftJoin extends Join implements ToSqlInterface
{
    protected $joinType = 'LEFT';
}
