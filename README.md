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

