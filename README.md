# SQLBuilder for PHP

[![Build Status](https://secure.travis-ci.org/c9s/SQLBuilder.png)](http://travis-ci.org/c9s/SQLBuilder)

If you're looking for something that is not an ORM but can generate SQL for
you, you just found the right one.


SQLBuilder is not an ORM (Object relational mapping) system, but a toolset that helps you generate 
cross-platform SQL queries in PHP.

SQLBuilder is a stand-alone library, you can simply install it through composer
or just require them (the class files) with your autoloader, and it has no
dependencies.

Unlike other SQL utility, SQLBuilder let you define the quote style and the
parameter marker type. there are 2 parameter marker type:

1. Question mark parameter marker.
2. Named parameter.

The above two are supported by PDO directly, and the first one is also
supported by MySQLi extension.

The API is simple, easy to remember, you can just define one query, then pass
different query driver to the query object to get a different SQL string.

It also supports cross-platform query generation, there are three types of
query (currently): *Universal*, *MySQL*, *PgSQL*.  The *Universal* queries are
cross-platform queries, you can use them to create a cross-platform PHP API to
your database system, and the supported platforms are: MySQL, PgSQL, SQLite.


## Features

* Simple API, easy to remember.
* Fast & Powerful.
* Custom parameter marker support:
  * Question-mark parameter marker.
  * Named parameter marker.
* Configurable quote handler.
* Zero dependency.


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

[[https://github.com/c9s/SQLBuilder/wiki|Documentation]]

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

