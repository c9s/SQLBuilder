<?php
/**
 * Description SelectQuery.php
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */

namespace SQLBuilder\BigQuery\Query;


use SQLBuilder\BigQuery\Traits\WhereTrait;

class SelectQuery extends \SQLBuilder\Universal\Query\SelectQuery
{
    use WhereTrait;
}