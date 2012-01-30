SQL Builder for generating CRUD SQL

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

## Configure database driver for `postgresql`:

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

    insert into table (foo ,bar ) values (:foo, :bar);

Or question-mark style for mysqli:

    insert into table (foo ,bar ) values (?,?);

## Select

CRUD SQL Builder for table 'Member':

    $sqlbuilder = new SQLBuilder('Member');

Do Select

```php
<?php
    $sqlbuilder->select('*')
        ->where()
            ->equal( 'a' , 'bar' )   // a = 'bar'
            ->notEqual( 'a' , 'bar' )   // a != 'bar'
            ->is( 'a' , 'null' )       // a is null
            ->isNot( 'a' , 'null' )    // a is not equal
            ->greater( 'a' , '2011-01-01' );
            ->greater( 'a' , array('date(2011-01-01)') );  // do not escape
            ->or()->less( 'a' , 123 )
                ->and()->like( 'content' , '%content%' );
            ->group()             and ( a = 123 or b != 123 )
                ->is( 'a' , 123 )
                ->isNot( 'b', 123 )             
            ->ungroup()
            ->back()                  // back to sql builder
            ->build();
```

`where()` returns Condition builder object.

`Condition->back()` returns SQL builder object

## Insert

Do insertion:

    $sqlbuilder->insert(array(
        // placeholder => 'value'
        'foo' => 'foo',
        'bar' => 'bar',
    ));

For question-mark style SQL, you might need this:

    $sqlbuilder->insert(array(
        'foo',
        'bar',
    ));

The last, build SQL:

    $sql = $sqlbuilder->build();

## Do Join

    $sb = new CRUDBuilder('Member');
    $sb->alias('m')
        ->join('table_name')
            ->alias('t')
            ->on()->equal( 't.zzz', array('m.ccc') );

## Delete

    $sb = new CRUDBuilder('member');
    $sb->driver = new Driver;
    $sb->driver->configure('driver','mysql');
    $sb->driver->configure('trim',true);
    $sb->delete();
    $sb->whereFromArgs(array( 'foo' => '123' ));

    $sb->where()->equal('foo',123);

    $sql = $sb->build();  // DELETE FROM member  WHERE foo = '123'
