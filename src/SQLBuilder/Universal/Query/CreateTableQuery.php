<?php
namespace SQLBuilder\Universal\Query;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Syntax\Constraint;
use SQLBuilder\Universal\Traits\ConstraintTrait;


/**
 * MySQL Create Table Syntax
 *
 * @see http://dev.mysql.com/doc/refman/5.0/en/create-table.html
 */
class CreateTableQuery implements ToSqlInterface
{
    use ConstraintTrait;

    protected $tableName;

    protected $engine;

    protected $columns = array();

    public function __construct($tableName) {
        $this->tableName = $tableName;
    }

    public function table($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function engine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    public function column($name) {
        $col = new Column($name);
        $this->columns[] = $col;
        return $col;
    }


    /**
    Build reference

    track(
        FOREIGN KEY(trackartist) REFERENCES artist(artistid)
        artist_id INTEGER REFERENCES artist
    )

    MySQL Syntax:
    
        reference_definition:

        REFERENCES tbl_name (index_col_name,...)
            [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE]
            [ON DELETE reference_option]
            [ON UPDATE reference_option]

        reference_option:
            RESTRICT | CASCADE | SET NULL | NO ACTION

    A reference example:

    PRIMARY KEY (`idEmployee`) ,
    CONSTRAINT `fkEmployee_Addresses`
    FOREIGN KEY `fkEmployee_Addresses` (`idAddresses`)
    REFERENCES `schema`.`Addresses` (`idAddresses`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION

    */
    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = "CREATE TABLE " . $driver->quoteIdentifier($this->tableName);
        $sql .= "(";
        $columnClauses = array();
        foreach($this->columns as $col) {
            $sql .= "\n" . $col->toSql($driver, $args) . ",";
        }

        if ($constraints = $this->getConstraints()) {
            foreach($constraints as $constraint) {
                $sql .= "\n" . $constraint->toSql($driver, $args) . ",";
            }
        }

        $sql = rtrim($sql,',') . "\n)";

        if ($this->engine && $driver instanceof MySQLDriver) {
            $sql .= ' ENGINE=' . $this->engine;
        }
        return $sql;
    }
}




