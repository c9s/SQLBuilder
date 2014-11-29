<?php
namespace SQLBuilder;

class Column
{
    public $name;

    public $attributes = array();

    static function create($name)
    {
        return new self($name);
    }

    public function __construct($name)
    {
        $this->name = $name;
        return $this;
    }

    public function __set($n,$v)
    {
        $this->attributes[ $n ] = $v;
    }

    public function __isset($n)
    {
        return isset( $this->attributes[ $n ] );
    }

    public function __get($n)
    {
        if( isset( $this->attributes[ $n ] ) )
            return $this->attributes[ $n ];
    }

    public function __call($m,$a)
    {
        if (empty($a)) {
            $this->attributes[ $m ] = true;
        } elseif( count($a) > 1 ) {
            $this->attributes[ $m ] = $a;
        } else {
            $this->attributes[ $m ] = $a[0];
        }
        return $this;
    }

    public function timestamp()
    {
        $this->type = 'timestamp';
        return $this;
    }

    public function varchar($length)
    {
        $this->type = "varchar($length)";
        return $this;
    }

    public function blob()
    {
        $this->type = 'blob';
        return $this;
    }

    public function integer()
    {
        $this->type = 'integer';
        return $this;
    }

    public function null()
    {
        $this->null = true;
        return $this;
    }

    public function notNull()
    {
        $this->notNull = true;
        return $this;
    }

    public function unique()
    {
        $this->unique = true;
        return $this;
    }

    public function primary()
    {
        $this->primary = true;
        return $this;
    }


}
