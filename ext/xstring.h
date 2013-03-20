#ifndef XSTRING_H
#define XSTRING_H
#include "php.h"

typedef struct {
    char *str;
    int   len;
    int   cap;
} xstring;

PHPAPI xstring * xstring_alloc();
PHPAPI xstring * xstring_new(int cap);
PHPAPI xstring * xstring_new_from_stringl(char * str, int len);
PHPAPI xstring * xstring_new_from_string(char * str);

PHPAPI void xstring_init_from_stringl(xstring * xstr, char * str, int len);

PHPAPI void      xstring_free(xstring *xstr);
PHPAPI void      xstring_free_outer(xstring *xstr);

PHPAPI void      xstring_realloc(xstring *xstr, int size);
PHPAPI void      xstring_scale(xstring *xstr, int size);
PHPAPI void      xstring_scale_large(xstring *xstr, int size);

PHPAPI xstring * xstring_quote_stringl(xstring * xstr, char * quote , int quote_len);
#define xstring_quote_string(xstr,quote)     xstring_quote_stringl(xstr,quote,strlen(quote))

PHPAPI zval *    xstring_to_zval(xstring *xstr);
PHPAPI void      xstring_set_zval(xstring *xstr, zval *zval, short copy);

#endif
