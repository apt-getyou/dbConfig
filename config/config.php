<?php

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