
PHP_ARG_ENABLE(sqlbuilder,
    [Whether to enable the "sqlbuilder" extension],
    [  --enable-sqlbuilder      Enable "sqlbuilder" extension support])

if test $PHP_SQLBUILDER != "no"; then
    PHP_REQUIRE_CXX()
    PHP_SUBST(SQLBUILDER_SHARED_LIBADD)
    PHP_ADD_LIBRARY(stdc++, 1, SQLBUILDER_SHARED_LIBADD)
    PHP_NEW_EXTENSION(sqlbuilder, php_sqlbuilder.c, $ext_shared)
fi
