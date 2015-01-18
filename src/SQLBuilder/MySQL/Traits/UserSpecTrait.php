<?php
namespace SQLBuilder\MySQL\Traits;
use SQLBuilder\MySQL\Syntax\UserSpecification;

trait UserSpecTrait 
{

    public $userSpecifications = array();

    public function user($spec = NULL) {
        $user = NULL;
        if (is_string($spec) && $user = UserSpecification::createWithFormat($this, $spec)) {
            $this->userSpecifications[] = $user;
        } else {
            $this->userSpecifications[] = $user = new UserSpecification($this);
        }
        return $user;
    }

}

