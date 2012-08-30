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

