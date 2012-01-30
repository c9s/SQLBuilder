SQL Builder for generating CRUD SQL



CRUD SQL Builder for table 'Member':

    $sqlbuilder = new SQLBuilder('Member');

Configure database driver for `postgresql`:

    $sqlbuilder->configure('driver','postgresql');

Trim spaces for SQL ? 

    $sqlbuilder->configure('trim',true);

Place holder style ? named-parameter is supported by POD:

    $sqlbuilder->configure('placeholder','named');

This generates SQL with named-parameter for PDO:

    insert into table (foo ,bar ) values (:foo, :bar);

Or question-mark style for mysqli:

    insert into table (foo ,bar ) values (?,?);

Configure escaper:

    $sqlbuilder->configure('escaper',array($pg,'escape'));

    $sqlbuilder->configure('escaper',array($pdo,'quote'));

## Select

    $sqlbuilder->select('*')
        ->where()
            ->isEqual( 'a' , 'bar' )   // a = 'bar'
            ->isNotEqual( 'a' , 'bar' )   // a != 'bar'
            ->is( 'a' , 'null' )       // a is null
            ->isNot( 'a' , 'null' )    // a is not equal
            ->greaterThan( 'a' , '2011-01-01' );
            ->greaterThan( 'a' , array('date(2011-01-01)') );  // do not escape
            ->or()->lessThan( 'a' , 123 )
            ->and()->like( 'content' , '%content%' );
            ->group()             and ( a = 123 or b != 123 )
                ->is( ... )
                ->not( ... )             
            ->ungroup()
            ->back()->build();

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


