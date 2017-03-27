<?php
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Universal\Query\CreateIndexQuery;

class CreateIndexQueryTest extends QueryTestCase
{
    public function createDriver() {
        return new MySQLDriver;
    }

    public function testCreateIndexConstructor()
    {
        $q = new CreateIndexQuery('idx_salary');
        $q->on('employees', [ 'last_name', 'salary' ])
            ->concurrently()
            ;
        $this->assertSqlStrings($q, [
            [ new MySQLDriver, 'CREATE INDEX `idx_salary` ON `employees` (last_name,salary)' ],
            //[ new PgSQLDriver, 'CREATE INDEX CONCURRENTLY "idx_salary" ON "employees" (last_name,salary)' ],
        ]);

    }


    public function testCreateIndexConcurrently()
    {
        // CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
        $q = new CreateIndexQuery;
        $q->create('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ->concurrently()
            ;
        $this->assertSqlStrings($q, [
            [ new MySQLDriver, 'CREATE INDEX `idx_salary` ON `employees` (last_name,salary)' ],
            //[ new PgSQLDriver, 'CREATE INDEX CONCURRENTLY "idx_salary" ON "employees" (last_name,salary)' ],
        ]);
    }

    /**
     * @expectedException SQLBuilder\Exception\IncompleteSettingsException
     */
    public function testCreateIndexWithoutTableName()
    {
        // CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
        $q = new CreateIndexQuery;
        $q->unique('idx_salary');
        $this->assertSql('', $q);
    }

    public function testCreateIndexFulltext()
    {
        $q = new CreateIndexQuery;
        $q->fulltext('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ;
        $this->assertSqlStrings($q, [ 
            [ new MySQLDriver, 'CREATE FULLTEXT INDEX `idx_salary` ON `employees` (last_name,salary)' ],
        ]);
    }

    public function testCreateSpatialIndex()
    {
        $q = new CreateIndexQuery;
        $q->spatial('salary_idx')
            ->on('employees', [ 'last_name', 'salary' ])
            ;
        $this->assertSql('CREATE SPATIAL INDEX `salary_idx` ON `employees` (last_name,salary)', $q);
    }



    public function testCreateUniqueIndexWithStorageParameters()
    {
        // CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
        $q = new CreateIndexQuery;
        $q->unique('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ->using('BTREE')
            ->with('fastupdate', 'off')
            ;
        $this->assertSqlStrings($q,[
            //[ new PgSQLDriver, 'CREATE UNIQUE INDEX "idx_salary" ON "employees" USING BTREE (last_name,salary) WITH fastupdate = off'],
            [ new MySQLDriver, 'CREATE UNIQUE INDEX `idx_salary` ON `employees` (last_name,salary) USING BTREE'],
        ]);
    }

    public function testCreateUniqueIndexUsing()
    {
        // CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
        $q = new CreateIndexQuery;
        $q->unique('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ->using('BTREE')
            ;
        $this->assertSql('CREATE UNIQUE INDEX `idx_salary` ON `employees` (last_name,salary) USING BTREE', $q);
    }



    public function testCreateIndexFunctional() 
    {
        // CREATE INDEX on tokens (substr(token), 0, 8)
        $q = new CreateIndexQuery;
        $q->create('idx_salary')
            ->on('employees', [ 'substr(token)', 0, 8 ])
            ;
        $this->assertSql('CREATE INDEX `idx_salary` ON `employees` (substr(token),0,8)', $q);
    }


}

