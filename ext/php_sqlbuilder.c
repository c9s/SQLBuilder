#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_sqlbuilder.h"

static function_entry sqlbuilder_functions[] = {
    PHP_FE(sqlbuilder_test, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry sqlbuilder_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_SQLBUILDER_EXTNAME,
    sqlbuilder_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
#if ZEND_MODULE_API_NO >= 20010901
    PHP_SQLBUILDER_VERSION,
#endif
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_SQLBUILDER
ZEND_GET_MODULE(sqlbuilder)
#endif

PHP_FUNCTION(sqlbuilder_test)
{
    RETURN_STRING("Hello World", 1);
}





