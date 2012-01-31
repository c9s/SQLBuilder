# SQLBuilder for PHP5.3 

SQLBuilder Simply focuses on providing a simple syntax for building SQL statement.

## Driver

get your SQL driver

```php
<?php
$driver = new SQLBuilder\Driver('postgresql');
$driver = SQLBuilder\Driver::getInstance();
$driver = SQLBuilder\Driver::create('postgresql');
```

### Configure Driver escaper

```php
<?php
$driver->configure('escaper',array($pg,'escape'));
$driver->configure('escaper',array($pdo,'quote'));
```

### Configure database driver for postgresql

```php
<?php
$driver->configure('driver','postgresql');
```

Trim spaces for SQL ? 

```php
<?php
$driver->configure('trim',true);
```

Place holder style ? named-parameter is supported by POD:

```php
<?php
$driver->configure('placeholder','named');
```

This generates SQL with named-parameter for PDO:

    INSERT INTO table (foo ,bar ) values (:foo, :bar);

Or question-mark style:

```php
<?php
$driver->configure('placeholder',true);
```

This generates:

    INSERT INTO table (foo ,bar ) values (?,?);


## CRUD SQL Generation

### Select

CRUD SQL Builder for table 'Member':

```php
<?php
$sqlbuilder = new SQLBuilder\CRUDBuilder;
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
<?php
    $sql = $sqlbuilder->table('Member')->select('*')
        ->where()
            ->equal( 'a' , 'bar' )   // a = 'bar'
            ->notEqual( 'a' , 'bar' )   // a != 'bar'
            ->is( 'a' , 'null' )       // a is null
            ->isNot( 'a' , 'null' )    // a is not equal
            ->greater( 'a' , '2011-01-01' );
            ->greater( 'a' , array('date(2011-01-01)') );  // do not escape
                ->or()->less( 'a' , 123 )
                ->and()->like( 'content' , '%content%' );
            ->group()                  // and ( a = 123 or b != 123 )
                ->is( 'a' , 123 )
                ->isNot( 'b', 123 )             
            ->ungroup()
            ->back()                  // back to sql builder
            ->build();
```

`where()` returns Expression object.

`Condition->back()` returns CRUD SQL builder object

Limit, Offset:

```php
<?php
$sqlbuilder->where().....
    ->back()
    ->limit(30)->offset(100);
?>
```


### Between

    $query->select('*')->table('items')->where()
        ->between('created_on')
            ->greater('2011-01-01');
            ->less('2012-01-01')
            ->back()
        ->limit(10);

    // select * from items where created_on 
    //    between '2011-01-01' and '2012-01-01'

### Insert

Do insertion:

```php
<?php
    $sqlbuilder->insert(array(
        // placeholder => 'value'
        'foo' => 'foo',
        'bar' => 'bar',
    ));
```

For question-mark style SQL, you might need this:

```php
<?php
    $sqlbuilder->insert(array(
        'foo',
        'bar',
    ));
```

The last, build SQL:

    $sql = $sqlbuilder->build();

### Update


```php
<?php
$sb = new CRUDBuilder('member');
$sb->driver = new Driver;
$sb->driver->configure('driver','mysql');
$sb->driver->configure('placeholder','named');

$sb->update( array( 'set1' => 'value1') );
$sb->whereFromArgs(array( 
    'cond1' => ':blah',       // is equal to    where()->equal('cond1',':blah')
));

$sql = $sb->build();
```


### Join

```php
<?php
$sb = new CRUDBuilder('Member');
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
<?php
    $sb = new CRUDBuilder('member');
    $sb->driver = new Driver;
    $sb->driver->configure('driver','mysql');
    $sb->driver->configure('trim',true);
    $sb->delete();
    $sb->whereFromArgs(array( 'foo' => '123' ));

    $sb->where()->equal('foo',123);

    $sql = $sb->build();  // DELETE FROM member  WHERE foo = 123
```

## Development

`PHPUnit_TestMore` is needed.

    curl -s http://install.onionphp.org/ | sh
    onion -d bundle
    phpunit tests

