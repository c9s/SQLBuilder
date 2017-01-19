<?php

namespace SQLBuilder\MySQL\Query;

use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\MySQL\Syntax\UserSpecification;
use SQLBuilder\MySQL\Traits\UserSpecTrait;
use InvalidArgumentException;

/**
 | *.*.
 | db_name.*
 | db_name.tbl_name
 | tbl_name
 | db_name.routine_name
 
 user_specification:
 user
 [
 | IDENTIFIED WITH auth_plugin [AS 'auth_string']
 IDENTIFIED BY [PASSWORD] 'password'
 ]
 
 ssl_option:
 SSL
 | X509
 | CIPHER 'cipher'
 | ISSUER 'issuer'
 | SUBJECT 'subject'
 
 with_option:
 GRANT OPTION
 | MAX_QUERIES_PER_HOUR count
 | MAX_UPDATES_PER_HOUR count
 | MAX_CONNECTIONS_PER_HOUR count
 | MAX_USER_CONNECTIONS count
 
 
 GRANT ALL ON db1.* TO 'jeffrey'@'localhost';
 GRANT ALL ON *.* TO 'someuser'@'somehost';
 GRANT SELECT, INSERT ON *.* TO 'someuser'@'somehost';
 
 GRANT EXECUTE ON PROCEDURE mydb.myproc TO 'someuser'@'somehost';
 
 
 Column Privileges
 ===================
 
 GRANT SELECT (col1), INSERT (col1,col2) ON mydb.mytbl TO 'someuser'@'somehost';
 
 
 GRANT PROXY
 ====================
 
 GRANT PROXY ON 'localuser'@'localhost' TO 'externaluser'@'somehost';
 
 GRANT USAGE ON *.* TO ...
 WITH MAX_QUERIES_PER_HOUR 500 MAX_UPDATES_PER_HOUR 100;
 
 Require SSL
 
 GRANT ALL PRIVILEGES ON test.* TO 'root'@'localhost'
 IDENTIFIED BY 'goodsecret' REQUIRE SSL;
 
 Grant with issuer
 
 GRANT ALL PRIVILEGES ON test.* TO 'root'@'localhost'
 */
class GrantQuery implements ToSqlInterface
{
    use UserSpecTrait;

    protected $privTypes = array();

    protected $on;

    protected $to = array();

    protected $objectType;

    protected $options = array();

    public function grant($privType, array $columns = array())
    {
        $this->privTypes[] = array($privType, $columns);

        return $this;
    }

    public function of($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * $target can be a string "*.*" or a user spec string.
     *
     * user specification is only supported in GRANT PROXY statement.
     */
    public function on($target, $objectType = null)
    {
        if ($objectType) {
            $this->objectType = $objectType;
        }
        // check if it's a user spec
        if (is_string($target) && strpos($target, '@') !== false) {
            $user = UserSpecification::createWithFormat($this, $target);
            $this->on = $user;
        } elseif ($target instanceof UserSpecification) {
            $this->on = $target;
        } elseif (is_string($target)) {
            $this->on = $target;
        } else {
            throw new InvalidArgumentException('The "ON" clause only supports UserSpecification class or string type');
        }

        return $this;
    }

    public function to($spec)
    {
        if ($spec instanceof UserSpecification) {
            $this->to[] = $spec;
        } elseif (strpos($spec, '@') !== false) {
            $user = UserSpecification::createWithFormat($this, $spec);
            $this->to[] = $user;
        } else {
            throw new InvalidArgumentException("Unsupported user specification: $spec");
        }

        return $this;
    }

    public function with($option, $value = null)
    {
        $this->options[] = array($option, $value);

        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql = 'GRANT';

        foreach ($this->privTypes as $privType) {
            list($privType, $columns) = $privType;
            $sql .= ' '.$privType;
            if (!empty($columns)) {
                $sql .= ' ('.implode(',', $columns).')';
            }
            $sql .= ',';
        }
        $sql = rtrim($sql, ',');

        if ($this->on) {
            $sql .= ' ON';

            if ($this->objectType) {
                $sql .= ' '.strtoupper($this->objectType);
            }

            if ($this->on instanceof UserSpecification) {
                $sql .= ' '.$this->on->toSql($driver, $args);
            } elseif (is_string($this->on)) {
                $sql .= ' '.$this->on;
            }
        }

        if (!empty($this->to)) {
            $sql .= ' TO ';
            $subclause = array();
            foreach ($this->to as $t) {
                $subclause[] = $t->getIdentitySql($driver, $args);
            }
            $sql .= implode(',', $subclause);
        }

        // WITH MAX_QUERIES_PER_HOUR 500 MAX_UPDATES_PER_HOUR 100;
        if ($this->options) {
            $sql .= ' WITH';
            foreach ($this->options as $option) {
                list($n, $val) = $option;
                $sql .= ' '.$n;
                if ($val) {
                    $sql .= ' '.$driver->deflate($val);
                }
            }
        }

        return $sql;
    }
}
