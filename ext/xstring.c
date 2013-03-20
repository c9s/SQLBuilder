#include "xstring.h"
#include <string.h>

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
PHPAPI xstring * xstring_new_from_stringl(char * str, int len)
{
    xstring * xstr;
    xstr = xstring_alloc();
    xstr->str = str;
    xstr->len = len;
    xstr->cap = len;
    return xstr;
}

PHPAPI xstring * xstring_new_from_string(char * str)
{
    return xstring_new_from_stringl(str, strlen(str));
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



// Zend related methods
PHPAPI zval *    xstring_to_zval(xstring *xstr)
{
    zval *zstr;
    MAKE_STD_ZVAL(zstr);

    zstr->type = IS_STRING;
    Z_STRVAL_P(zstr) = xstr->str;
    Z_STRLEN_P(zstr) = xstr->len;
    return zstr;
}



