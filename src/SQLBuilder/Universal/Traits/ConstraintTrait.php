<?php
namespace SQLBuilder\Universal\Traits;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Syntax\Constraint;

trait ConstraintTrait
{
    protected $constraints = array();

    public function constraint($name)
    {
        $this->constraints[] = $constraint = new Constraint($name, $this);
        return $constraint;
    }

    public function foreignKey($name)
    {
        $this->constraints[] = $constraint = new Constraint(NULL, $this);
        $constraint->foreignKey($name);
        return $constraint;
    }

    public function primaryKey($name)
    {
        $this->constraints[] = $constraint = new Constraint(NULL, $this);
        $constraint->primaryKey($name);
        return $constraint;
    }

    public function uniqueKey($name)
    {
        $this->constraints[] = $constraint = new Constraint(NULL, $this);
        $constraint->uniqueKey($name);
        return $constraint;
    }

    public function getConstraints() {
        return $this->constraints;
    }
}


