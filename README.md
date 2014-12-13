# SQLBuilder for PHP

[![Build Status](https://secure.travis-ci.org/c9s/SQLBuilder.png)](http://travis-ci.org/c9s/SQLBuilder)

If you're looking for something that is not an ORM but can generate SQL for
you, you just found the right one.

SQLBuilder is not an ORM (Object relational mapping) system, but a toolset that helps you generate 
cross-platform SQL queries in PHP.

SQLBuilder is a stand-alone library, you can simply install it through composer
or just require them (the class files) with your autoloader, and it has no
dependencies.

## Features

* Simple API, easy to remember.
* Fast & Powerful.
* Custom parameter marker support:
  * Question-mark parameter marker.
  * Named parameter marker.
* Configurable quote handler.
* Zero dependency.

## Synopsis

Here is a short example of using Universal SelectQuery

```php
use SQLBuilder\Universal\Query\SelectQuery;
use SQLBuilder\Driver\MySQLDriver;
use SQLBuilder\Driver\PgSQLDriver;
use SQLBuilder\Driver\SQLiteDriver;

$mysql = new MySQLDriver;
$args = new ArgumentArray;

$query = new SelectQuery;
$query->select(array('id', 'name', 'phone', 'address','confirmed'))
    ->from('users', 'u')
    ->partitions('u1', 'u2', 'u3')
    ->where()
        ->is('confirmed', true)
        ->in('id', [1,2,3])
    ;
$query
    ->join('posts')
        ->as('p')
        ->on('p.user_id = u.id')
    ;
$query
    ->orderBy('rand()')
    ->orderBy('id', 'DESC')
    ;

$sql = $query->toSql($mysql, $args);

var_dump($sql);
var_dump($args);
```


## A More Detailed Description

Unlike other SQL utilities, SQLBuilder let you define the quote style and the
parameter marker type. there are 2 parameter marker type you can choose:

1. Question mark parameter marker.
2. Named parameter.

The above two are supported by PDO directly, and the first one is also
supported by `mysqli`, `pgsql` extension.

The API is *dead simple, easy to remember*, you can just define one query, then pass
different query driver to the query object to get a different SQL string for
your targettting platform.

It also supports cross-platform query generation, there are three types of
query (currently): **Universal**, **MySQL**, **PgSQL**.  The **Universal** queries are
cross-platform, you can use them to create a cross-platform PHP API to
your database system, and the supported platforms are: MySQL, PgSQL, SQLite.

Universql Queries:

- CreateDatabaseQuery
- DropDatabaseQuery
- SelectQuery
- InsertQuery
- UpdateQuery
- DeleteQuery
- UnionQuery
- CreateIndexQuery
- DropIndexQuery

To see the implementation details, you can check the source code inside **Universal** namespace:
<https://github.com/c9s/SQLBuilder/tree/master/src/SQLBuilder/Universal/Query>

MySQL Queries:

- CreateUserQuery
- DropUserQuery
- GrantQuery
- SetPasswordQuery

For MySQL platform, the implementation is according to the specification of MySQL 5.6.

For PostgreSQL platform, the implementation is according to the specification of PostgreSQL 9.2.


## Installation

### Install through Composer

```json
{
    "require": {
        "c9s/sqlbuilder": "2~"
    }
}
```

## Getting Started

[Documentation](https://github.com/c9s/SQLBuilder/wiki)

## Development

```
composer install --dev --prefer-source
```

Copy the `phpunit.xml` file for your local configuration:

```sh
phpunit -c your-phpunit.xml tests
```

## Contribution

To test with mysql database:

    $ mysql -uroot -p
    create database sqlbuilder charset utf8;
    grant all privileges on sqlbuilder.* to 'testing'@'localhost' identified by '';

    --- or use this to remove password for testing account
    SET PASSWORD FOR testing@localhost=PASSWORD('');

To test with pgsql database:

    $ sudo -u postgres createdb sqlbuilder

## Reference

- http://dev.mysql.com/doc/refman/5.0/en/sql-syntax.html
- http://www.postgresql.org/docs/8.2/static/sql-syntax.html
- http://www.sqlite.org/optoverview.html

## Author

Yo-An Lin (c9s) <cornelius.howl@gmail.com>

