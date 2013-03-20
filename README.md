# SQLBuilder for PHP5.3

[![Build Status](https://secure.travis-ci.org/c9s/php-SQLBuilder.png)](http://travis-ci.org/c9s/php-SQLBuilder)

SQLBuilder focuses on providing a simple syntax for building SQL statements.

When switching database backend, you can simplely change the driver type of query
builder, and it will generate the proper SQL for your backend, you don't have
to modify the code to support different backend.

For example, pgsql support `returning` statement, this kind of syntax will only
be built when this feature is supported.

## Features

* Simple.
* Fast & Powerful.
* Ability to change SQL style, question-mark style, named-placeholder style.
* Ability to change quote style, table name quoting, column name quoting..etc.
* Configurable escaper and quoter.
* No package dependency.

## Installation

### Install through PEAR

```sh
$ pear channe-discover pear.corneltek.com
$ pear install corneltek/SQLBuilder
```

### Install through Composer

```json
{
    "require": {
        "c9s/sqlbuilder": "*"
    }
}
```

## Synopsis

```php
$driver = new Driver('mysql');

$b = new SQLBuilder\QueryBuilder($driver,'Member');
$b->select('*');
$b->where()
    ->equal( 'a' , 'bar' );
$sql = $b->build();

// SELECT * FROM Member where a = 'bar'
```

## Driver

Get your SQL driver

```php
$driver = new SQLBuilder\Driver('pgsql');
$driver = SQLBuilder\Driver::getInstance();
$driver = SQLBuilder\Driver::create('pgsql');
```

### Configuring Driver Quoter

string quote/escape handler:

```php
$driver->configure('escape',array($pg,'escape'));
$driver->configure('quoter',array($pdo,'quote'));

$driver->escaper = 'addslashes';

$driver->quoter = function($string) {
    return '\'' . $string . '\'';
};
```

### Configuring Database Driver For pgsql

```php
$driver->configure('driver','pgsql');
```

Trim spaces for SQL ? 

```php
$driver->configure('trim',true);
```

### Changing Placeholder Style

SQLBuilder supports two placeholder styles:

- named parameter by PDO
- question-mark paramter by mysql, PDO.

#### Named Placeholder:

```php
$driver->configure('placeholder','named');
```

This generates SQL with named-parameter for PDO:

```
INSERT INTO table (foo ,bar ) values (:foo, :bar);
```

#### Question-mark Placeholder

If you pass variables to build SQL with named parameters, query
builder converts named parameters for you, to get variables, you
can use `getVars` method:

```php
$vars = $sb->getVars();
```

Which returns:

```php
array(
    ':name' => 'Foo',
    ':phone' => 'Bar',
);
```

Or to use question-mark style:

```php
$driver->configure('placeholder',true);
```

This generates:

```sql
INSERT INTO table (foo ,bar ) values (?,?);
```

## Query SQL Generation

### Select

Build SQL query for table 'Member':

```php
$builder = new SQLBuilder\QueryBuilder($driver);
$builder->table('Member');
$builder->select('*','column1','column2');
$builder->select(array( 
    'column1' => 'as1',
    'column2' => 'as2',
));
```

Build Select SQL

```php
$sql = $builder->table('Member')->select('*')
    ->where()
        ->equal( 'a' , 'bar' )   // a = 'bar'
        ->notEqual( 'a' , 'bar' )   // a != 'bar'
        ->is( 'a' , 'null' )       // a is null
        ->isNot( 'a' , 'null' )    // a is not equal
        ->greater( 'a' , '2011-01-01' );
        ->in( 'a', array(1,2,3,4,5) )
        ->greater( 'a' , array('date(2011-01-01)') );  // do not escape
            ->or()->less( 'a' , 123 )
            ->and()->like( 'content' , '%content%' );
        ->group()                  // AND ( a = 123 AND b != 123 )
            ->is( 'a' , 123 )
            ->isNot( 'b', 123 )             
        ->ungroup()
        ->back()                  // back to sql builder
        ->build();
```

The `where()` returns `SQLBuilder\Expression` object.

`Condition->back()` returns QueryBuilder object

### Limit And Offset

```php
$builder->select('*')->table('items')
    ->groupBy('name')
    ->limit(10)->offset(100);
?>
```

For PostgreSQL, which generates:

```sql
SELECT * FROM items OFFSET 100 LIMIT 10;
```

For MySql, which generates:

```sql
SELECT * FROM items LIMIT 100,10;
```

### Between

```php
$query->select('*')->table('items')
    ->where()
    ->between('created_on', '2011-01-01' , '2011-02-01' );
```

```sql
SELECT * FROM items WHERE created_on BETWEEN '2011-01-01' AND '2011-02-01'
```

### In

```php
$query->select('*')->table('items')
    ->where()
    ->in('a', array(1,2,3,4));
```

```sql
SELECT * FROM items WHERE a IN (1,2,3,4);
```

```php
$query->select('*')->table('City')
    ->where()
    ->in('name', array('Taipei','France','Japan'));
```

```sql
SELECT * FROM City WHERE name IN ('Taipei','France','Japan');
```

### Insert

Insertion:

```php
$builder->insert(array(
    // placeholder => 'value'
    'foo' => 'foo',
    'bar' => 'bar',
));
```

For question-mark style SQL, you might need this:

```php
$builder->insert(array(
    'foo',
    'bar',
));
```

The last thing, build the SQL statement:

```php
$sql = $builder->build();
```

### Update

```php
$driver = new Driver;
$driver->configure('driver','mysql');
$driver->configure('placeholder','named');

$sb = new QueryBuilder('member',$driver);
$sb->update( array( 'set1' => 'value1') );
$sb->whereFromArgs(array( 
    'cond1' => ':blah',       // is equal to    where()->equal('cond1',':blah')
));
$sql = $sb->build();   // UPDATE member SET set1 = 'value1' WHERE cond1 = :cond1
```


### Join

```php
$sb = new QueryBuilder($driver,'Member');
$sb->alias('m')
    ->join('table_name')
        ->alias('t')
        ->on()->equal( 't.zzz', array('m.ccc') )        // not to escape string (with array())
        ->back()                                        // return to join expression object
        ->on()->equal( 'a.foo', 'string' )              // treat as string, escape string
        ->back()          // go back to SqlBuilder object.
        ->toSql();
```

### Delete

```php
$driver = new Driver;
$driver->configure('driver','mysql');
$driver->configure('trim',true);
$sb = new QueryBuilder($driver,'member');
$sb->delete();
$sb->whereFromArgs(array( 'foo' => '123' ));

$sb->where()->equal('foo',123);

$sql = $sb->build();  // DELETE FROM member  WHERE foo = 123
```


## Migration Builder

```php
$builder = new SQLBuilder\MigrationBuilder( $driver );
$sql = $builder->addColumn( 'members' , 
    SQLBuilder\Column::create('price')
        ->integer()
        ->notNull()
        ->default(100)
);
// ALTER TABLE members ADD COLUMN price integer DEFAULT 100 NOT NULL

$sql = $builder->addColumn( 'members' , 
    SQLBuilder\Column::create('email')
        ->varchar(64)
);
// ALTER TABLE members ADD COLUMN email varchar(64)

$sql = $builder->createIndex( 'members', 'email_index', 'email' ); // create index email_index on members (email);

$sql = $builder->dropIndex( 'members', 'email_index' );
```

## Development

`PHPUnit_TestMore` is needed.

```sh
$ pear channel-discover pear.corneltek.com
$ pear install corneltek/PHPUnit_TestMore
```

Install Universal package for the classloader:

```sh
curl -s http://install.onionphp.org/ | sh
onion -d install
```

Copy the `phpunit.xml` file for your local configuration:

```sh
phpunit -c your-phpunit.xml tests
```

## Reference

- http://dev.mysql.com/doc/refman/5.0/en/sql-syntax.html
- http://www.postgresql.org/docs/8.2/static/sql-syntax.html
- http://www.sqlite.org/optoverview.html

## Author

Yo-An Lin (c9s) <cornelius.howl@gmail.com>

