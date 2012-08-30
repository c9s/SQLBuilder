Migration
=========

SQLBuilder provides MigrationBuilder for generating 
SQL for migration tasks.

To use MigrationBuilder is pretty simple, just like QueryBuilder 
simply pass the driver object to the constructor of MigrationBuilder object.

    $driver = new SQLBuilder\QueryDriver;
    $builder = new SQLBuilder\MigrationBuilder( $driver );

To Add column

    use SQLBuilder\Column;
    $sql = $builder->addColumn( 'members', 
            Column::create('price')
                ->integer()
                ->notNull()
                ->default(100) );

    $sql = $builder->addColumn( 'members', 
            array(
                'name' => 'price',
                'type' => 'integer',
                'notNull' => true,
                'default' => 200
            ));

To Add foreign key:

    $sql = $builder->addForeignKey('books','author_id','authors','id');


To create index:

    $sql = $builder->createIndex( 'members', 'email_index', 'email' );
    $sql = $builder->createIndex( 'members', 'name_index', array('name','identity') );

To drop index:

    $sql = $builder->dropIndex( 'members', 'email_index' );


