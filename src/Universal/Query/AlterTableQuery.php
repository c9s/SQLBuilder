<?php

namespace SQLBuilder\Universal\Query;

use SQLBuilder\ArgumentArray;
use SQLBuilder\Driver\BaseDriver;
use SQLBuilder\MySQL\Syntax\AlterTableOrderBy;
use SQLBuilder\SyntaxExtender;
use SQLBuilder\ToSqlInterface;
use SQLBuilder\Universal\Syntax\AlterTableAdd;
use SQLBuilder\Universal\Syntax\AlterTableAddColumn;
use SQLBuilder\Universal\Syntax\AlterTableAddConstraint;
use SQLBuilder\Universal\Syntax\AlterTableChangeColumn;
use SQLBuilder\Universal\Syntax\AlterTableDropColumn;
use SQLBuilder\Universal\Syntax\AlterTableDropForeignKey;
use SQLBuilder\Universal\Syntax\AlterTableDropIndex;
use SQLBuilder\Universal\Syntax\AlterTableDropPrimaryKey;
use SQLBuilder\Universal\Syntax\AlterTableModifyColumn;
use SQLBuilder\Universal\Syntax\AlterTableRenameColumn;
use SQLBuilder\Universal\Syntax\AlterTableRenameTable;
use SQLBuilder\Universal\Syntax\Column;

/**
 * Class AlterTableQuery
 *
 * @package SQLBuilder\Universal\Query
 *
 * @author  Yo-An Lin (c9s) <cornelius.howl@gmail.com>
 * @author  Aleksey Ilyenko <assada.ua@gmail.com>
 */
class AlterTableQuery implements ToSqlInterface
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $specs = [];

    use SyntaxExtender;

    /**
     * AlterTableQuery constructor.
     *
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * @param string|ToSqlInterface $subQuery
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableAdd|\SQLBuilder\Universal\Syntax\AlterTableAddConstraint
     */
    public function add($subQuery = null)
    {
        if ($subQuery) {
            return $this->specs[] = new AlterTableAdd($subQuery);
        }

        return $this->specs[] = new AlterTableAddConstraint();
    }

    public function modifyColumn(Column $column)
    {
        $this->specs[] = $spec = new AlterTableModifyColumn($column);

        return $spec;
    }

    /**
     * @param string|Column $oldColumn
     * @param Column        $newColumn
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableChangeColumn
     */
    public function changeColumn($oldColumn, Column $newColumn)
    {
        $this->specs[] = $spec = new AlterTableChangeColumn($oldColumn, $newColumn);

        return $spec;
    }

    /**
     * Rename table column.
     *
     * @param string $fromColumn
     * @param Column $toColumn
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableRenameColumn
     */
    public function renameColumn($fromColumn, Column $toColumn)
    {
        $this->specs[] = $spec = new AlterTableRenameColumn($fromColumn, $toColumn);

        return $spec;
    }

    /**
     * @param \SQLBuilder\Universal\Syntax\Column $toColumn
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableAddColumn
     */
    public function addColumn(Column $toColumn)
    {
        $this->specs[] = $spec = new AlterTableAddColumn($toColumn);

        return $spec;
    }

    /**
     * @param $columnName
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableDropColumn
     */
    public function dropColumnByName($columnName)
    {
        $column = new Column($columnName);

        return $this->dropColumn($column);
    }

    /**
     * @param \SQLBuilder\Universal\Syntax\Column $column
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableDropColumn
     */
    public function dropColumn(Column $column)
    {
        // throw new CriticalIncompatibleUsageException('Argument must be `Column` or string');
        $this->specs[] = $spec = new AlterTableDropColumn($column);

        return $spec;
    }

    /**
     * @param $indexName
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableDropIndex
     */
    public function dropIndex($indexName)
    {
        $this->specs[] = $spec = new AlterTableDropIndex($indexName);

        return $spec;
    }

    /**
     * @param $fkSymbol
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableDropForeignKey
     */
    public function dropForeignKey($fkSymbol)
    {
        $this->specs[] = $spec = new AlterTableDropForeignKey($fkSymbol);

        return $spec;
    }

    /**
     * @return \SQLBuilder\Universal\Syntax\AlterTableDropPrimaryKey
     */
    public function dropPrimaryKey()
    {
        $this->specs[] = $spec = new AlterTableDropPrimaryKey();

        return $spec;
    }

    /**
     * @param array $columnNames
     *
     * @return \SQLBuilder\MySQL\Syntax\AlterTableOrderBy
     */
    public function orderBy(array $columnNames)
    {
        $this->specs[] = $spec = new AlterTableOrderBy($columnNames);

        return $spec;
    }

    /**
     * @param $method
     * @param $arguments
     *
     * @return mixed|object
     */
    public function __call($method, $arguments)
    {
        return $this->specs[] = $this->handleSyntax($method, $arguments);
    }

    /**
     * Rename Table.
     *
     * @param string $toTable table name
     *
     * @return \SQLBuilder\Universal\Syntax\AlterTableRenameTable
     */
    public function rename($toTable)
    {
        $this->specs[] = $spec = new AlterTableRenameTable($toTable);

        return $spec;
    }

    /**
     * @param \SQLBuilder\Driver\BaseDriver $driver
     * @param \SQLBuilder\ArgumentArray     $args
     *
     * @return string
     */
    public function toSql(BaseDriver $driver, ArgumentArray $args)
    {
        $sql           = 'ALTER TABLE ' . $driver->quoteIdentifier($this->table) . ' ';
        $alterSpecSqls = [];

        foreach ($this->specs as $spec) {
            $alterSpecSqls[] = $spec->toSql($driver, $args);
        }
        $sql .= implode(",\n  ", $alterSpecSqls);

        return $sql;
    }
}
