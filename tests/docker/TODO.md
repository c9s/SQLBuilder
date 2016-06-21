
wait for [phpunit docker](https://github.com/JulienBreux/phpunit-docker/issues/38) response (pre install pdo drivers), then we can add following two files for testing.

###### run.test.sh

```sh
docker run \
-d \
--name='test_SQLBuilder_mysql' \
-v $(pwd):/app \
--env MYSQL_ALLOW_EMPTY_PASSWORD=yes \
mysql:5.5

# pgsql
docker run \
-d \
--name='test_SQLBuilder_pgsql' \
-v $(pwd):/app \
postgres:9

docker run \
--rm \
-v $(pwd):/app \
--link test_SQLBuilder_mysql \
--link test_SQLBuilder_pgsql \
phpunit/phpunit -v

# clean
docker rm -f test_SQLBuilder_mysql
docker rm -f test_SQLBuilder_pgsql
```

###### phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/docker/bootstrap.php"
         backupGlobals="false"
         colors="true"
         verbose="true">
  <php>
    <env name="MYSQL_DSN"   value="mysql:host=localhost;dbname=sqlbuilder"/>
    <env name="MYSQL_USER"  value="root"/>

    <env name="PGSQL_DSN"   value="pgsql:host=localhost;dbname=sqlbuilder"/>
    <env name="PGSQL_USER"  value="postgres"/>
  </php>

  <testsuites>
    <testsuite name="PHPUnit">
      <directory suffix="Test.php">tests</directory>
    </testsuite>
  </testsuites>
</phpunit>
```