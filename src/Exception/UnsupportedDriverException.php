<?php

namespace SQLBuilder\Exception;

use RuntimeException;
use SQLBuilder\Driver\BaseDriver;

/**
 * Class UnsupportedDriverException
 *
 * @package SQLBuilder\Exception
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class UnsupportedDriverException extends RuntimeException
{
    public $driver;

    public $caller;

    /**
     * UnsupportedDriverException constructor.
     *
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param                               $caller
     */
    public function __construct(BaseDriver $driver, $caller)
    {
        $this->driver = $driver;
        $this->caller = $caller;
        parent::__construct(get_class($driver) . ' is not supported for ' . get_class($this->caller));
    }
}
