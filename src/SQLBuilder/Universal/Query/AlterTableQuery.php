<?php
namespace SQLBuilder\Universal\Query;
use Exception;
use SQLBuilder\Raw;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\ArgumentArray;
use SQLBuilder\Bind;
use SQLBuilder\ParamMarker;
use SQLBuilder\Universal\Syntax\Column;
use SQLBuilder\Universal\Syntax\AlterTableAddConstraint;
use SQLBuilder\Universal\Syntax\AlterTableRenameColumn;
use SQLBuilder\Universal\Syntax\AlterTableAddColumn;
use SQLBuilder\Universal\Syntax\AlterTableDropColumn;
use SQLBuilder\Universal\Syntax\AlterTableRenameTable;
use SQLBuilder\Universal\Syntax\AlterTableModifyColumn;
use SQLBuilder\Universal\Syntax\AlterTableDropPrimaryKey;
use SQLBuilder\Universal\Syntax\AlterTableDropForeignKey;
use SQLBuilder\Universal\Syntax\AlterTableDropIndex;

use SQLBuilder\Exception\CriticalIncompatibleUsageException;

use SQLBuilder\MySQL\Syntax\AlterTableOrderBy;

use BadMethodCallException;
use ReflectionObject;
use ReflectionClass;

trait SyntaxExtender {

    protected $extraSyntax = array();

    protected $syntaxClass = array();

    protected $reflectionCache = array();

    public function registerClass($methodName, $class) 
    {
        $this->syntaxClass[ $methodName ] = $class;
    }

    public function registerCallback($methodName, callable $callback) 
    {
        $this->extraSyntax[$methodName] = $callback;
    }


    public function handleSyntax($methodName, array $arguments = array())
    {
        if (isset($this->syntaxClass[$methodName])) {
            $refClass = null;
            if (isset($this->reflectionCache[$methodName])) {
                $refClass = $this->reflectionCache[$methodName];
            } else {
                $this->reflectionCache[$methodName] = $refClass = new ReflectionClass($this->syntaxClass[$methodName]);
            }
            return $refClass->newInstanceArgs($arguments);
        } else if (isset($this->extraSyntax[$methodName])) {
            return call_user_func_array($this->extraSyntax[$methodName], $arguments);
        } else {
            throw new BadMethodCallException;
        }
    }
}

class AlterTableQuery implements ToSqlInterface
{
    protected $table;

    protected $specs = array();

    use SyntaxExtender;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function add()
    {
        $this->specs[] = $spec = new AlterTableAddConstraint;
        return $spec;
    }

    public function modifyColumn(Column $column) {
        $this->specs[] = $spec = new AlterTableModifyColumn($column);
        return $spec;
    }


    /**
     * Rename table column
     *
     * @param string $fromColumn
     * @param Column $toColumn
     */
    public function renameColumn($fromColumn, Column $toColumn)
    {
        $this->specs[] = $spec = new AlterTableRenameColumn($fromColumn, $toColumn);
        return $spec;
    }

    public function addColumn(Column $toColumn)
    {
        $this->specs[] = $spec = new AlterTableAddColumn($toColumn);
        return $spec;
    }

    public function dropColumn($column)
    {
        if (is_string($column)) {
            $column = new Column($column);
        } else if (!$column instanceof Column) {
            throw new CriticalIncompatibleUsageException('Argument must be `Column` or string');
        }
        $this->specs[] = $spec = new AlterTableDropColumn($column);
        return $spec;
    }


    public function dropIndex($indexName)
    {
        $this->specs[] = $spec = new AlterTableDropIndex($indexName);
        return $spec;
    }

    public function dropForeignKey($fkSymbol)
    {
        $this->specs[] = $spec = new AlterTableDropForeignKey($fkSymbol);
        return $spec;
    }

    public function dropPrimaryKey()
    {
        $this->specs[] = $spec = new AlterTableDropPrimaryKey;
        return $spec;
    }

    public function orderBy(array $columnNames) 
    {
        $this->specs[] = $spec = new AlterTableOrderBy($columnNames);
        return $spec;
    }

    public function __call($method, $arguments) {
        return $this->specs[] = $this->handleSyntax($method, $arguments);
    }

    /**
     * Rename Table
     *
     * @param string $toTable table name
     * @api
     */
    public function rename($toTable)
    {
        $this->specs[] = $spec = new AlterTableRenameTable($toTable);
        return $spec;
    }

    public function toSql(BaseDriver $driver, ArgumentArray $args) 
    {
        $sql = 'ALTER TABLE ' . $driver->quoteIdentifier($this->table) . ' ';
        $alterSpecSqls = array();

        foreach($this->specs as $spec) {
            $alterSpecSqls[] = $spec->toSql($driver, $args);
        }
        $sql .= join(', ', $alterSpecSqls);
        return $sql;
    }
}
