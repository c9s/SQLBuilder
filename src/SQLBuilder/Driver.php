<?php


namespace SQLBuilder;


/*
 *  $driver->configure('driver','postgresql');
 *
 *  trim spaces
 *
 *  $driver->configure('trim',true);
 *
 *  $driver->configure('placeholder','named');
 *
 *  $driver->configure('escaper',array($pg,'escape'));
 *
 *  $driver->configure('escaper',array($pdo,'quote'));
 */

class Driver
{

	public $type = 'PDO';

    /**
     * should we quote table name in SQL ?
     */
	public $quoteTable = false;


    /**
     * should we quote column name in SQL ?
     */
	public $quoteColumn = false;


    /**
     * should we trim space ?
     */
	public $trim = false;


    /**
     * get place holder
     */
	public $placeholder = false;


    /**
     * string escaper handler
     *  
     *  Array:
     *
     *    array($obj,'method')
     */
	public $escaper;


    static function create()
    {
        return new static;
    }

    static function getInstance()
    {
        static $self;
        return $self ? $self : $self = new static;
    }

    public function __construct()
    {
		$this->escaper = 'addslashes';
    }


	public function configure($key,$value)
	{
		switch( $key ) {
			case 'trim':
				$this->trim = $value;
				break;


			/* named or true */
			case 'placeholder':
				$this->placeholder = $value;
				break;

			case 'quote_table':
				$this->quoteTable = $value;
				break;

			case 'quote_column':
				$this->quoteColumn = $value;
				break;
			
			case 'driver':
				$this->type = $value;
                if( $this->type == 'mysql' ) {
                    $this->quoteColumn = false;
                    $this->quoteTable = false;
                }
				break;

			case 'style':
				$this->style = $value;
				break;
		}
	}


	public function getPlaceHolder($key)
	{
		if( $this->placeholder && $this->placeholder === 'named' ) {
			return ':' . $key;
		}
		else {
			return '?';
		}
	}

	public function getQuoteColumn($name)
	{
		if( $c = $this->quoteColumn ) {
            if( is_string($c) )
                return $c . $name . $c;
            return '"' . $name . '"';
		}
		return $name;
	}


    /**
     * escape single quote 
     */
    public function escape($string)
    {
        return call_user_func( $this->escaper , $string );
    }

}




