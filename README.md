# Laravel 5 Config with DB-storage support
This package extends default laravel Config, so fallback capability is built in

### For Laravel 4, please use the [1.* branch](https://github.com/TerbiumLibs/dbConfig/tree/1.0)!

## Installation
Require this package in your composer.json:

~~~json
"apt-getyou/db-config": "2.*"
~~~

And add the ServiceProvider to the providers array in app/config/app.php

~~~php
'Terbium\DbConfig\DbConfigServiceProvider',
~~~

Publish config and migrations using artisan CLI.

~~~bash
php artisan vendor:publish
~~~

Run migration to create settings table

~~~bash
php artisan migrate
~~~



You can register the facade in the `aliases` key of your `app/config/app.php` file.

~~~php
'aliases' => array(
    'DbConfig' => 'Terbium\DbConfig\Facade'
)
~~~

Or replace default one
~~~php
'aliases' => array(
    'Config' => 'Terbium\DbConfig\Facade'
)
~~~

##Config

~~~php
return [
    /**
     * 数据库表名
     */
    'table'      => 'settings',

    /**
     * 缓存配置
     * enable -- 是否缓存数据库内的配置
     * key -- 缓存键名
     * minutes -- 缓存时间
     */
    'cache'      => [
        'enable'  => true,
        'key'     => 'DbConfigCache',
        'minutes' => 1,
    ],

    /**
     * 是否开启多站点模式
     */
    'multi_site' => false,

    /**
     * 分库字段,供独立服务器配置
     * 自定义时需定义为大于1的整数
     */
    'database'   => 0,

    /**
     * 服务器id
     * 自定义时需定义为大于1的整数
     * 可自定义为env
     */
    'server_id'  => 0,

    /**
     * 配置白名单
     */
    'white_list' => [
        'app.env',
    ],



];
~~~

##Specific commands

###Store item into database table

~~~php
Config::store($key, $value,$server = null, $database = null) 
// this sets the key immediately
~~~

###Remove item from the database

~~~php
Config::forget($key,$server = null, $database = null)
~~~

###Clear all current items from memory (they will be reloaded on next call)

~~~php
Config::clear()
~~~

###Truncate the table with settings

~~~php
Config::clearDb($database = null)
~~~

###Return query builder with list of settings from database

~~~php
Config::listDb($wildcard = null)
~~~
