<?php

namespace SQLBuilder\Universal\Traits;

/**
 * Class OptionTrait
 *
 * @package SQLBuilder\Universal\Traits
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
trait OptionTrait
{
    protected $options = [];

    /**
     * MySQL Select Options:.
     *
     *   [ALL | DISTINCT | DISTINCTROW ]
     *   [HIGH_PRIORITY]
     *   [MAX_STATEMENT_TIME = N]
     *   [STRAIGHT_JOIN]
     *   [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
     *   [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
     *
     * $this->option([ 'SQL_SMALL_RESULT', 'SQL_CALC_FOUND_ROWS', 'MAX_STATEMENT_TIME = N']);
     *
     * @param $selectOption
     *
     * @return $this
     */
    public function option($selectOption)
    {
        if (is_array($selectOption)) {
            $this->options = array_merge_recursive($this->options, $selectOption);
        } else {
            $this->options = array_merge_recursive($this->options, func_get_args());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function options()
    {
        $this->options = func_get_args();

        return $this;
    }

    /**
     * @return string
     */
    public function buildOptionClause()
    {
        if (empty($this->options)) {
            return '';
        }

        return ' ' . implode(' ', $this->options);
    }
}
