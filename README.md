# laravel-query-log

[![Packagist](https://img.shields.io/packagist/l/doctrine/orm.svg)](https://github.com/fideism/laravel-query-log/blob/master/README.md)
[![Build Status](https://travis-ci.org/fideism/laravel-query-log.svg?branch=master)](https://travis-ci.org/fideism/laravel-query-log)

Log Datagase Query Messages

## Install
```shell
composer require fideism/laravel-query-log:^1.0
```

## Configuration
- Before Used, You Should Change The Configuration In `.env`
```shell
DB_DEBUG=true
```
- Other Configurations
```shell
DB_DEBUG=single         //single Or daily default single
DB_LOG_FILE=xx          //default storage_path('logs/sql.log')
DB_LOG_DAYS=7           //default 7
DB_LOG_LEVEL=debug      //default debug
```
