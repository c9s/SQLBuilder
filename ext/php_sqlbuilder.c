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

typedef struct {
    char *str;
    int   len;
    int   cap;
} xstring;



PHPAPI xstring * xstring_alloc();
PHPAPI xstring * xstring_new(int cap);
PHPAPI xstring * xstring_new_from_string(char * str, int len);
PHPAPI void      xstring_free(xstring *xstr);
PHPAPI void      xstring_scale(xstring *xstr, int size);
PHPAPI void      xstring_scale_large(xstring *xstr, int size);


/* allocate an empty xstring structure */
PHPAPI xstring * xstring_alloc()
{
    return emalloc( sizeof(xstring) );
}


PHPAPI xstring * xstring_new(int cap)
{
    xstring * x = xstring_alloc();
    x->cap = cap;
    x->str = emalloc(sizeof(char) * cap);
    x->len = 0;
    return x;
}


/* create an xstring from exsiting string */
PHPAPI xstring * xstring_new_from_string(char * str, int len)
{
    xstring * xstr;
    xstr = xstring_alloc();
    xstr->str = str;
    xstr->len = len;
    xstr->cap = len;
    return xstr;
}


PHPAPI void xstring_concat_string(xstring * xstr, char * str, int len)
{
    if ( xstr->len + len > xstr->cap ) {
        // do realloc
        xstring_scale(xstr, len);
    }
    memcpy(xstr->str + xstr->len, str, len);
    xstr->len += len;
    memcpy(xstr->str + xstr->len, "\0", 1);
}


PHPAPI void xstring_realloc(xstring *xstr, int size)
{
    xstr->cap += size;
    xstr->str = realloc( xstr->str , sizeof(char) * xstr->cap );
}


PHPAPI void xstring_scale(xstring *xstr, int size)
{
    xstr->cap += size + 256;
    xstr->str = realloc( xstr->str , sizeof(char) * xstr->cap );
}

PHPAPI void xstring_scale_large(xstring *xstr, int size)
{
    xstr->cap += size + 512;
    xstr->str = realloc( xstr->str , sizeof(char) * xstr->cap );
}



PHPAPI xstring * xstring_quote_string(xstring * xstr, char * quote , int quote_len)
{
    if ( xstr->len + quote_len > xstr->cap ) {
        xstring_realloc(xstr, quote_len);
    }

    xstring *xnewstr;
    
    if ( xstr->len + quote_len > xstr->cap ) {
        // create a new empty string with larger capacity
        xnewstr = xstring_new( xstr->cap + quote_len );
    } else {
        xnewstr = xstring_new( xstr->cap );
    }

    xnewstr->len = xstr->len + quote_len;
    memcpy(xnewstr, quote, quote_len);
    memcpy(xnewstr + quote_len, xstr->str, xstr->len);
    memcpy(xnewstr + quote_len + xstr->len, quote, quote_len);
    return xnewstr;
}

PHPAPI void xstring_free(xstring *xstr)
{
    // free up the string
    efree(xstr->str);
    // free up the structure itself
    efree(xstr);
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





