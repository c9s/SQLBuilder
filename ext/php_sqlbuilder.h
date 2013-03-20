

#ifndef PHP_SQLBUILDER_H
#define PHP_SQLBUILDER_H 1
#define PHP_SQLBUILDER_VERSION "1.0"
#define PHP_SQLBUILDER_EXTNAME "sqlbuilder"

PHP_FUNCTION(sqlbuilder_test);

extern zend_module_entry sqlbuilder_module_entry;
#define phpext_sqlbuilder_ptr &sqlbuilder_module_entry

#endif
