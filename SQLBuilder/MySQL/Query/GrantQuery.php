<?php
namespace SQLBuilder\MySQL\Query;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\MySQL\Syntax\UserSpecification;
use SQLBuilder\MySQL\Traits\UserSpecTrait;
use InvalidArgumentException;
use Exception;
/**

    SYNTAX
    ============
    GRANT
        priv_type [(column_list)]
        [, priv_type [(column_list)]] ...
        ON [object_type] priv_level
        TO user_specification [, user_specification] ...
        [REQUIRE {NONE | ssl_option [[AND] ssl_option] ...}]
        [WITH with_option ...]

    GRANT PROXY ON user_specification
        TO user_specification [, user_specification] ...
        [WITH GRANT OPTION]

    object_type:
        TABLE
    | FUNCTION
    | PROCEDURE

    priv_level:
        *
    | *.*
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
        IDENTIFIED BY 'goodsecret'
        REQUIRE ISSUER '/C=FI/ST=Some-State/L=Helsinki/
            O=MySQL Finland AB/CN=Tonu Samuel/emailAddress=tonu@example.com';



    MySQL and Standard SQL Versions of GRANT
    ========================================

    The biggest differences between the MySQL and standard SQL versions of GRANT 
        are:

    MySQL associates privileges with the combination of a host name and user name 
    and not with only a user name.

    Standard SQL does not have global or database-level privileges, nor does it 
    support all the privilege types that MySQL supports.

    MySQL does not support the standard SQL UNDER privilege.

    Standard SQL privileges are structured in a hierarchical manner. If you remove 
    a user, all privileges the user has been granted are revoked. This is also true 
    in MySQL if you use DROP USER. See Section 13.7.1.3, “DROP USER Syntax”.

    In standard SQL, when you drop a table, all privileges for the table are 
    revoked. In standard SQL, when you revoke a privilege, all privileges that were 
    granted based on that privilege are also revoked. In MySQL, privileges can be 
    dropped only with explicit DROP USER or REVOKE statements or by manipulating 
    the MySQL grant tables directly.

    In MySQL, it is possible to have the INSERT privilege for only some of the 
    columns in a table. In this case, you can still execute INSERT statements 
    on the table, provided that you insert values only for those columns for 
    which you have the INSERT privilege. The omitted columns are set to their 
    implicit default values if strict SQL mode is not enabled. In strict mode, 
    the statement is rejected if any of the omitted columns have no default 
    value. (Standard SQL requires you to have the INSERT privilege on all 
    columns.) Section 5.1.7, “Server SQL Modes”, discusses strict mode. 
    Section 11.6, “Data Type Default Values”, discusses implicit default 
    values.

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

    public function of($objectType) {
        $this->objectType = $objectType;
        return $this;
    }

    /**
     * $target can be a string "*.*" or a user spec string
     *
     * user specification is only supported in GRANT PROXY statement.
     */
    public function on($target, $objectType = NULL) {
        if ($objectType) {
            $this->objectType = $objectType;
        }
        // check if it's a user spec
        if (is_string($target) && strpos($target,'@') !== false) {
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

    public function to($spec) {
        if ($spec instanceof UserSpecification) {
            $this->to[] = $spec;
        } elseif (strpos($spec,'@') !== false) {
            $user = UserSpecification::createWithFormat($this, $spec);
            $this->to[] = $user;
        } else {
            throw new InvalidArgumentException("Unsupported user specification: $spec");
        }
        return $this;
    }

    public function with($option, $value = NULL)
    {
        $this->options[] = array($option, $value);
        return $this;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) {
        $sql = 'GRANT';

        foreach($this->privTypes as $privType) {
            list($privType, $columns) = $privType;
            $sql .= ' ' . $privType;
            if (!empty($columns)) {
                $sql .= ' (' . join(',', $columns) . ')';
            }
            $sql .= ',';
        }
        $sql = rtrim($sql, ',');

        if ($this->on) {
            $sql .= ' ON';

            if ($this->objectType) {
                $sql .= ' ' . strtoupper($this->objectType);
            }

            if ($this->on instanceof UserSpecification) {
                $sql .= ' ' . $this->on->toSql($driver, $args);
            } elseif( is_string($this->on) ) {
                $sql .= ' ' . $this->on;
            }
        }
       
        if (!empty($this->to)) {
            $sql .= ' TO ';
            $subclause = array();
            foreach($this->to as $t) {
                $subclause[] = $t->getIdentitySql($driver, $args);
            }
            $sql .= join(',', $subclause);
        }

        // WITH MAX_QUERIES_PER_HOUR 500 MAX_UPDATES_PER_HOUR 100;
        if ($this->options) {
            $sql .= ' WITH';
            foreach($this->options as $option) {
                list($n, $val) = $option;
                $sql .= ' ' . $n;
                if ($val) {
                    $sql .= ' ' . $driver->deflate($val);
                }
            }
        }
        return $sql;

    }


}



