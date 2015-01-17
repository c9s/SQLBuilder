<?php
namespace SQLBuilder\MySQL\Syntax;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ArgumentArray;

class UserSpecification { 

    public $account;

    public $host = 'localhost';

    public $password;

    public $passwordByHash;

    public $parent;

    public $authPlugin;

    public function __construct($parent = null) {
        $this->parent = $parent;
    }

    public function account($account)
    {
        $this->account = $account;
        return $this;
    }

    public function host($host) {
        $this->host = $host;
        return $this;
    }

    public function identifiedBy($pass, $byHash = false) {
        $this->password = $pass;
        $this->passwordByHash = $byHash;
        return $this;
    }

    public function identifiedWith($authPlugin) {
        $this->authPlugin = $authPlugin;
        return $this;
    }

    public function getAccount() {
        return $this->account;
    }

    public function getCurrentPassword() {
        return $this->password;
    }

    public function getHost() {
        return $this->host;
    }

    public function getAuthPlugin() {
        return $this->authPlugin;
    }

    public function __call($m , $args) {
        if ($this->parent) {
            return call_user_func_array(array($this->parent, $m), $args);
        }
    }


    static public function createWithSpec($parent, $spec) 
    {
        if (is_string($spec) && strpos($spec,'@') !== false) {
            list($account, $host) = explode('@', $spec);
            $user = new self($parent);
            $user->account(trim($account, "`'"));
            $user->host(trim($host, "`'"));
            return $user;
        }
        return NULL;
    }


    public function getIdentitySql(BaseDriver $driver, ArgumentArray $args) 
    {
        return $driver->quoteIdentifier($this->getAccount()) . '@' . $driver->quoteIdentifier($this->getHost());
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = $this->getIdentitySql($driver, $args);
        if ($pass = $this->getCurrentPassword()) {
            $sql .= ' IDENTIFIED BY';
            if ($this->passwordByHash) {
                $sql .= ' PASSWORD';
            }
            $sql .= ' ' . $driver->quote($pass);
        } elseif ($authPlugin = $this->getAuthPlugin()) {
            $sql .= ' IDENTIFIED WITH ' . $driver->quoteIdentifier($authPlugin);
        }
        return $sql;
    }


}
