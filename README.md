# SQLBuilder for PHP5.3

[![Build Status](https://secure.travis-ci.org/c9s/SQLBuilder.png)](http://travis-ci.org/c9s/SQLBuilder)

SQLBuilder Simply focuses on providing a simple syntax for building SQL statement.

When switching database backend, you can simple change the driver type of query
builder, and it will generate the proper SQL for your backend, you don't have
to modify code to support different platform.

for example, pgsql support `returning` statement, this kind of syntax will only 
be built when this feature is supported.


## Install through PEAR

    $ sudo pear channe-discover pear.corneltek.com
    $ sudo pear install corneltek/SQLBuilder

## Driver

get your SQL driver

```php
$driver = new SQLBuilder\Driver('pgsql');
$driver = SQLBuilder\Driver::getInstance();
$driver = SQLBuilder\Driver::create('pgsql');
```

### Configure Driver Quoter

string quote/escape handler:

```php
$driver->configure('escape',array($pg,'escape'));
$driver->configure('quoter',array($pdo,'quote'));

$driver->escaper = 'addslashes';

$driver->quoter = function($string) {
    return '\'' . $string . '\'';
};
```

### Configure database driver for pgsql

```php
$driver->configure('driver','pgsql');
```

Trim spaces for SQL ? 

```php
$driver->configure('trim',true);
```

### Place Holder style

SQLBuilder supports two styles: 
- named parameter by PDO
- question-mark paramter by mysql, PDO.

configure for named-parameter:

```php
$driver->configure('placeholder','named');
```

This generates SQL with named-parameter for PDO:

    INSERT INTO table (foo ,bar ) values (:foo, :bar);

Configure for question-mark style:

If you pass variables to build SQL with named parameters, query builder
converts named parameters for you, to get variables, you can use `getVars` method:

```php
$vars = $sb->getVars();

/*
array(
    ':name' => 'Foo',
    ':phone' => 'Bar',
);
*/
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
$sqlbuilder = new SQLBuilder\QueryBuilder;
$sqlbuilder->driver = $driver;
$sqlbuilder->table('Member');
$sqlbuilder->select('*','column1','column2');
$sqlbuilder->select(array( 
    'column1' => 'as1',
    'column2' => 'as2',
));
```

Build Select SQL

```php
$sql = $sqlbuilder->table('Member')->select('*')
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

`where()` returns Expression object.

`Condition->back()` returns QueryBuilder object

### Limit, Offset

```php
$sqlbuilder->select('*')->table('items')
    ->groupBy('name')
    ->limit(10)->offset(100);
?>
```

For pgsql, generates:

```sql
SELECT * FROM items OFFSET 100 LIMIT 10;
```

For mysql, generates:

```sql
SELECT * FROM items LIMIT 100,10;
```

### Between

```php
$query->select('*')->table('items')
    ->where()
    ->between('created_on', '2011-01-01' , '2011-02-01' )
    ->limit(10)->offset(100);
```

```sql
SELECT * FROM items WHERE created_on BETWEEN '2011-01-01' AND '2011-02-01'
```

### Insert

Insertion:

```php
$sqlbuilder->insert(array(
    // placeholder => 'value'
    'foo' => 'foo',
    'bar' => 'bar',
));
```

For question-mark style SQL, you might need this:

```php
$sqlbuilder->insert(array(
    'foo',
    'bar',
));
```

The last thing, build the SQL statement:

```php
$sql = $sqlbuilder->build();
```

### Update

```php
$sb = new QueryBuilder('member');
$sb->driver = new Driver;
$sb->driver->configure('driver','mysql');
$sb->driver->configure('placeholder','named');
$sb->update( array( 'set1' => 'value1') );
$sb->whereFromArgs(array( 
    'cond1' => ':blah',       // is equal to    where()->equal('cond1',':blah')
));
$sql = $sb->build();   // UPDATE member SET set1 = 'value1' WHERE cond1 = :cond1
```


### Join

```php
$sb = new QueryBuilder('Member');
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
$sb = new QueryBuilder('member');
$sb->driver = new Driver;
$sb->driver->configure('driver','mysql');
$sb->driver->configure('trim',true);
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

    curl -s http://install.onionphp.org/ | sh
    onion -d bundle
    phpunit tests

## Reference

- http://dev.mysql.com/doc/refman/5.0/en/sql-syntax.html
- http://www.postgresql.org/docs/8.2/static/sql-syntax.html
- http://www.sqlite.org/optoverview.html

## Author

Yo-An Lin (c9s) <cornelius.howl@gmail.com>

