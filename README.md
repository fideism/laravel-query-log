# laravel-query-log

[![Packagist](https://img.shields.io/packagist/l/doctrine/orm.svg)](https://github.com/fideism/laravel-query-log/blob/master/LICENSE)
[![Build Status](https://travis-ci.org/fideism/laravel-query-log.svg?branch=master)](https://travis-ci.org/fideism/laravel-query-log)

Log Datagase Query Messages

## Install
```shell
composer require fideism/laravel-query-log:^1.1
```

## Configuration
- Before Used, You Should Change The Configuration In `.env`
```shell
DB_DEBUG=true
```
- Other Configurations
```shell
DB_DEBUG=single         //single Or daily default single
DB_LOG_FILE=xxx          //default storage_path('logs/sql.log')
DB_LOG_DAYS=7           //default 7
DB_LOG_LEVEL=debug      //default debug
DB_EXPLAIN=false      //default false;show select query explain
```
## Example
- Default Without Explain
```
[2019-01-16 11:23:53] database.sql.DEBUG: 
select `id` from `users` where `gender` = '1' order by `created_at` desc [mysql][1.93ms]
select `id`,`url` from `user_profiles` where `user_id` = '1' order by `created_at` desc [mysql][0.18ms]
```

- With Explain
```
[2019-01-16 11:23:53] database.sql.DEBUG: 
select `id` from `users` where `gender` = '1' order by `created_at` desc [mysql][1.93ms]
EXPLAIN：{"id":1,"select_type":"SIMPLE","table":"articles","partitions":null,"type":"ALL","possible_keys":null,"key":null,"key_len":null,"ref":null,"rows":11,"filtered":9.090909004211426,"Extra":"Using where; Using filesort"}
select `id`,`url` from `user_profiles` where `user_id` = '1' order by `created_at` desc [mysql][0.18ms]
EXPLAIN：{"id":1,"select_type":"SIMPLE","table":"articles","partitions":null,"type":"ALL","possible_keys":null,"key":null,"key_len":null,"ref":null,"rows":11,"filtered":9.090909004211426,"Extra":"Using where; Using filesort"}
```
