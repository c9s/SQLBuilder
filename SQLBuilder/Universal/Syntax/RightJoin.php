<?php

namespace SQLBuilder\Universal\Syntax;

use SQLBuilder\ToSqlInterface;

class RightJoin extends Join implements ToSqlInterface
{
    protected $joinType = 'RIGHT';
}
