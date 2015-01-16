<?php
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Universal\Query\CreateIndexQuery;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;

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
        $this->assertSqlStatements($q, [ 
            [ new MySQLDriver, 'CREATE INDEX `idx_salary` ON `employees` (last_name,salary)' ],
            [ new PgSQLDriver, 'CREATE INDEX CONCURRENTLY "idx_salary" ON "employees" (last_name,salary)' ],
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
        $this->assertSqlStatements($q, [ 
            [ new MySQLDriver, 'CREATE INDEX `idx_salary` ON `employees` (last_name,salary)' ],
            [ new PgSQLDriver, 'CREATE INDEX CONCURRENTLY "idx_salary" ON "employees" (last_name,salary)' ],
        ]);
    }

    public function testCreateIndexFulltext()
    {
        $q = new CreateIndexQuery;
        $q->fulltext('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ;
        $this->assertSqlStatements($q, [ 
            [ new MySQLDriver, 'CREATE FULLTEXT INDEX `idx_salary` ON `employees` (last_name,salary)' ],
        ]);
    }

    public function testCreateUniqueIndex()
    {
        // CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
        $q = new CreateIndexQuery;
        $q->unique('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ;
        $this->assertSql('CREATE UNIQUE INDEX `idx_salary` ON `employees` (last_name,salary)', $q);
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

