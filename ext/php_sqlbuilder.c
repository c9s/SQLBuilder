#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_sqlbuilder.h"

static const zend_function_entry sqlbuilder_funcs_driverObject[] = {


};

PHPAPI zend_class_entry  *spl_ce_ArrayObject;



static const zend_function_entry sqlbuilder_functions[] = {
    PHP_FE(sqlbuilder_test, NULL)
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
	// PHP_MINIT(spl_iterators)(INIT_FUNC_ARGS_PASSTHRU);
	// PHP_MINIT(spl_array)(INIT_FUNC_ARGS_PASSTHRU);
	// PHP_MINIT(spl_directory)(INIT_FUNC_ARGS_PASSTHRU);
	// PHP_MINIT(spl_dllist)(INIT_FUNC_ARGS_PASSTHRU);
	// PHP_MINIT(spl_heap)(INIT_FUNC_ARGS_PASSTHRU);
	// PHP_MINIT(spl_fixedarray)(INIT_FUNC_ARGS_PASSTHRU);
	return SUCCESS;
}

PHP_MINIT_FUNCTION(sqlbuilder_driver)
{
    return SUCCESS;
}


PHP_FUNCTION(sqlbuilder_test)
{
    RETURN_STRING("Hello World", 1);
}





