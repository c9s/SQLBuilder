<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\Universal\Syntax\Constraint;

trait ConstraintTrait
{
    protected $constraints = array();

    public function constraint($name)
    {
        $this->constraints[] = $constraint = new Constraint($name, $this);

        return $constraint;
    }

    public function foreignKey($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->foreignKey($cols);

        return $constraint;
    }

    public function primaryKey($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->primaryKey($cols);

        return $constraint;
    }

    public function uniqueKey($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->uniqueKey($cols);

        return $constraint;
    }

    public function index($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->index($cols);

        return $constraint;
    }

    public function getConstraints()
    {
        return $this->constraints;
    }
}
