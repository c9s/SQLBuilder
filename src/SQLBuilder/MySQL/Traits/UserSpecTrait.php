<?php
namespace SQLBuilder\MySQL\Traits;
use SQLBuilder\MySQL\Syntax\UserSpecification;

trait UserSpecTrait 
{

    public $userSpecifications = array();

    public function user($spec = NULL) {
        $user = new UserSpecification($this);
        $this->userSpecifications[] = $user;
        if (is_string($spec) && strpos($spec,'@') !== false) {
            list($account, $host) = explode('@', $spec);
            $user->account(trim($account, "`'"));
            $user->host(trim($host, "`'"));
        }
        return $user;
    }

}

