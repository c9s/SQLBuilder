<?php
namespace SQLBuilder\MySQL\Syntax;

class UserSpecification { 

    public $account;

    public $host = 'localhost';

    public $password;

    public $passwordByHash;

    public $parent;

    public $authPlugin;

    public function __construct($parent) {
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

    public function getPassword() {
        return $this->password;
    }

    public function getHost() {
        return $this->host;
    }

    public function getAuthPlugin() {
        return $this->authPlugin;
    }

    public function __call($m , $args) {
        return call_user_func_array(array($this->parent, $m), $args);
    }
}
