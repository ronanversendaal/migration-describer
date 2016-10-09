# MigrationDescriber
A small Laravel 5.1 package for describing one or more migration files as SQL.


#Installation

Use composer to install the package

`composer require ronanversendaal/migration-describer:dev-release/1.0.0`

Add the service provider to config/app.php

`Ronanversendaal\MigrationDescriber\MigrationDescriberServiceProvider::class`

#Usage

## Basic

`php artisan migrate:describe`

This will display a list of your migrations to choose from.

## Single files and wildcards

The `--file` option allows one migration file to describe, with support of wildcards.

`php artisan migrate:describe --file=database/migrations/2016_10_10_create_users_table.php`

Or

`php artisan migrate:describe --file=database/migrations/2016_10*`


## Options

 ```
  --file      The migration file to read. Supports wildcards
  --database  The database connection to use.
```
