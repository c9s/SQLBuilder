<?php

namespace SQLBuilder\Universal\Traits;

use SQLBuilder\Universal\Syntax\Constraint;

trait ConstraintTrait
{
    /**
     * @var Constraint[]
     */
    protected $constraints = [];

    /**
     * @param string $name
     *
     * @return \SQLBuilder\Universal\Syntax\Constraint
     */
    public function constraint($name)
    {
        $this->constraints[] = $constraint = new Constraint($name, $this);

        return $constraint;
    }

    /**
     * @param string|array $cols
     *
     * @return \SQLBuilder\Universal\Syntax\Constraint
     */
    public function foreignKey($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->foreignKey($cols);

        return $constraint;
    }

    /**
     * @param string|array $cols
     *
     * @return \SQLBuilder\Universal\Syntax\Constraint
     */
    public function primaryKey($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->primaryKey($cols);

        return $constraint;
    }

    /**
     * @param string|array $cols
     *
     * @return \SQLBuilder\Universal\Syntax\Constraint
     */
    public function uniqueKey($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->uniqueKey($cols);

        return $constraint;
    }

    /**
     * @param string|array $cols
     *
     * @return \SQLBuilder\Universal\Syntax\Constraint
     */
    public function index($cols)
    {
        $this->constraints[] = $constraint = new Constraint(null, $this);
        $constraint->index($cols);

        return $constraint;
    }

    /**
     * @return \SQLBuilder\Universal\Syntax\Constraint[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }
}
