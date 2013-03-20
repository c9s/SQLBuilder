#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_sqlbuilder.h"

static const zend_function_entry sqlbuilder_funcs_driverObject[] = {


};

PHPAPI zend_class_entry  *spl_ce_ArrayObject;



static const zend_function_entry sqlbuilder_functions[] = {
    PHP_FE(sqlbuilder_single_quote, NULL)
    PHP_FE(sqlbuilder_double_quote, NULL)
    // PHP_FE(sqlbuilder_test, NULL)
    {NULL, NULL, NULL}
};

zend_module_entry sqlbuilder_module_entry = {
#if ZEND_MODULE_API_NO >= 20010901
    STANDARD_MODULE_HEADER,
#endif
    PHP_SQLBUILDER_EXTNAME,
    sqlbuilder_functions,
    PHP_MINIT(sqlbuilder),
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


PHP_MINIT_FUNCTION(sqlbuilder)
{
    PHP_MINIT(sqlbuilder_driver)(INIT_FUNC_ARGS_PASSTHRU);
    return SUCCESS;
}

PHP_MINIT_FUNCTION(sqlbuilder_driver)
{
    return SUCCESS;
}


PHPAPI void str_column_double_quote(char * str, int str_len, zval * return_value)
{
    char *newstr;
    int   newstr_len;

    newstr_len = str_len + 2;
    newstr = emalloc( sizeof(char) * (newstr_len) );
    memcpy(newstr, "\"", 1);
    memcpy(newstr + 1, str, str_len);
    memcpy(newstr + 1 + str_len, "\"", 1);
    RETURN_STRINGL(newstr, newstr_len, 0);
}

PHPAPI void str_column_single_quote(char * str, int str_len, zval * return_value)
{
    char *newstr;
    int   newstr_len;

    newstr_len = str_len + 2;
    newstr = emalloc( sizeof(char) * (newstr_len) );
    memcpy(newstr, "'", 1);
    memcpy(newstr + 1, str, str_len);
    memcpy(newstr + 1 + str_len, "'", 1);
    RETURN_STRINGL(newstr, newstr_len, 0);
}


/* proto:   sqlbuilder_single_quote('string') */
PHP_FUNCTION(sqlbuilder_single_quote)
{
    char *str;
    int   str_len = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &str, &str_len ) == FAILURE) {
        RETURN_FALSE;
    }
    str_column_single_quote(str, str_len, return_value);
}

PHP_FUNCTION(sqlbuilder_double_quote)
{
    char *str;
    int   str_len = 0;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &str, &str_len ) == FAILURE) {
        RETURN_FALSE;
    }
    str_column_double_quote(str, str_len, return_value);
}





