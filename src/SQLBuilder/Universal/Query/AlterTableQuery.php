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

class AlterTableQuery implements ToSqlInterface
{
    protected $table;

    protected $specs = array();

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

    public function dropColumn(Column $column)
    {
        $this->specs[] = $spec = new AlterTableDropColumn($column);
        return $spec;
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
