Version 2.7.3 - Tue Sep 22 15:50:39 2015

- Added compare method to `Conditions`.
- Added compare method to `Bind`.

Version 2.7.2 - Tue Sep 22 15:51:53 2015

- Added mysql create database query: add ifNotExists support

Version 2.7.1 - Tue Sep 22 15:52:22 2015

- Added cast support for PgSQLDriver

Version 2.6.0 - Fri Apr 17 13:21:42 2015

- Separated alter table query method argument constraint
    - addColumnByCallable(... )
    - addColumn(..)
    - dropColumn(..)
    - dropColumnBy(name)

Version 2.3.3 - Sat Apr 11 17:31:49 2015

AlterTableQuery improvements:
- Added "ALTER TABLE ... ADD COLUMN ... AFTER column name | FIRST"  support
- Added "ALTER TABLE ... ORDER BY" syntax support

Version 1.2.0 - 日  3/ 4 22:09:43 2012

- Save place holder vars.
- getVars() accessor to QueryBuilder


