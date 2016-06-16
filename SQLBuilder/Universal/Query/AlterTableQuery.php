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
use SQLBuilder\Universal\Syntax\AlterTableChangeColumn;
use SQLBuilder\Universal\Syntax\AlterTableAddColumn;
use SQLBuilder\Universal\Syntax\AlterTableDropColumn;
use SQLBuilder\Universal\Syntax\AlterTableRenameTable;
use SQLBuilder\Universal\Syntax\AlterTableModifyColumn;
use SQLBuilder\Universal\Syntax\AlterTableDropPrimaryKey;
use SQLBuilder\Universal\Syntax\AlterTableDropForeignKey;
use SQLBuilder\Universal\Syntax\AlterTableDropIndex;
use SQLBuilder\Universal\Syntax\AlterTableAdd;

use SQLBuilder\Exception\CriticalIncompatibleUsageException;

use SQLBuilder\MySQL\Syntax\AlterTableOrderBy;
use SQLBuilder\SyntaxExtender;

class AlterTableQuery implements ToSqlInterface
{
    protected $table;

    protected $specs = array();

    use SyntaxExtender;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function add($subquery = null)
    {
        if ($subquery) {
            return $this->specs[] = new AlterTableAdd($subquery);
        } else {
            return $this->specs[] = new AlterTableAddConstraint;
        }
    }

    public function modifyColumn(Column $column) {
        $this->specs[] = $spec = new AlterTableModifyColumn($column);
        return $spec;
    }

    /**
     * @param string|Column $oldColumn
     * @param Column $newColumn
     */
    public function changeColumn($oldColumn, Column $newColumn)
    {
        $this->specs[] = $spec = new AlterTableChangeColumn($oldColumn,$newColumn);
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


    public function dropColumnByName($columnName)
    {
        $column = new Column($columnName);
        return $this->dropColumn($column);
    }

    public function dropColumn(Column $column)
    {
        // throw new CriticalIncompatibleUsageException('Argument must be `Column` or string');
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
        $sql .= join(",\n  ", $alterSpecSqls);
        return $sql;
    }
}
