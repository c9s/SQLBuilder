<?php
use SQLBuilder\Testing\QueryTestCase;
use SQLBuilder\Universal\Query\CreateIndexQuery;
use SQLBuilder\Driver\MySQLDriver;

class CreateIndexQueryTest extends QueryTestCase
{
    public function createDriver() {
        return new MySQLDriver;
    }

    public function testCreateIndex()
    {
        // CREATE INDEX CONCURRENTLY idx_salary ON employees(last_name, salary);
        $q = new CreateIndexQuery;
        $q->create('idx_salary')
            ->on('employees', [ 'last_name', 'salary' ])
            ;
        $this->assertSql('CREATE INDEX `idx_salary` ON `employees` (last_name,salary)', $q);
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

