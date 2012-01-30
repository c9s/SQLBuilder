SQL Builder for generating CRUD SQL

## Driver

get your SQL driver

    $driver = new SQLBuilder\Driver('postgresql');
    $driver = SQLBuilder\Driver::getInstance();
    $driver = SQLBuilder\Driver::create('postgresql');

### Configure Driver escaper

    $driver->configure('escaper',array($pg,'escape'));

    $driver->configure('escaper',array($pdo,'quote'));

## Configure database driver for `postgresql`:

    $driver->configure('driver','postgresql');

Trim spaces for SQL ? 

    $driver->configure('trim',true);

Place holder style ? named-parameter is supported by POD:

    $driver->configure('placeholder','named');

This generates SQL with named-parameter for PDO:

    insert into table (foo ,bar ) values (:foo, :bar);

Or question-mark style for mysqli:

    insert into table (foo ,bar ) values (?,?);

## Select

CRUD SQL Builder for table 'Member':

    $sqlbuilder = new SQLBuilder('Member');

Do Select

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


