<?php namespace Terbium\DbConfig;

use DB;
use Cache;
use Illuminate\Support\NamespacedItemResolver;
use Terbium\DbConfig\Exceptions\SaveException;

class DbProvider extends NamespacedItemResolver implements Interfaces\DbProviderInterface
{

    /**
     * The database table.
     */
    protected $table;

    /**
     * Cache configuration
     */
    protected $cache;

    /**
     * 分库字段
     */
    protected $database;

    /**
     * 服务器id
     */
    protected $server_id;

    /**
     * 是否开启分站配置功能
     */
    protected $multi_site;


    /**
     * 默认库id
     */
    const database = 0;


    /**
     * 默认服务器id
     */
    const server = 0;


    /**
     * Create a new database configuration loader.
     * DbProvider constructor.
     *
     * @param $config
     */
    public function __construct($config) {
        $this->table = $config['table'];

        $this->cache = $config['cache'];

        $this->database = $config['database'];

        $this->server_id = $config['server_id'];

        $this->multi_site = $config['multi_site'];

    }

    /**
     * Load the given configuration collection.
     *
     * @param  string $collection
     *
     * @return array
     */
    public function load($collection = null) {

        if ($this->cache['enable']) {
            $items = Cache::remember($this->cache['key'], $this->cache['minutes'], function () use ($collection) {
                return $this->initItems($collection);
            });
            return $items;
        }

        return $this->initItems($collection);

    }

    /**
     * get config data from DB
     *
     * @param null $collection
     *
     * @return array
     */
    private function initItems($collection = null) {
        $items = array();

        $list = DB::table($this->table);

        /**
         * 若开启多站点设置，则限制搜索内容
         */
        $database = $this->multi_site ? $this->database : self::database;

        $list = $list->where('database', '=', $database);

        if ($collection !== null) {
            $list = $list->where('key', 'LIKE', $collection . '%');
        }

        $list = $list->pluck('value', 'key');

        // convert dotted list back to multidimensional array
        foreach ($list as $key => $value) {
            $value = json_decode($value);
            array_set($items, $key, $value);
        }

        return $items;
    }

    /**
     * Save item to the database or update the existing one
     *
     * @param string  $key
     * @param mixed   $value
     * @param bool    $server
     * @param integer $database
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function store($key, $value, $server = null, $database = null) {

        if (!is_array($value)) {
            $value = array($key => $value);
        } else {
            $value = array_dot($value);

            foreach ($value as $k => $v) {
                $value[$key . '.' . $k] = $v;
                unset($value[$k]);
            }
        }

        foreach ($value as $k => $v) {
            $this->_store($k, $v, $server, $database);
        }

    }


    /**
     * @param string  $key
     * @param string  $value
     * @param integer $server
     * @param integer $database
     *
     * @throws Exceptions\SaveException
     */
    private function _store($key, $value, $server = null, $database = null) {

        $provider = $this;
        $table = $this->table;

        DB::transaction(
            function () use (&$provider, $table, $key, $value, $server, $database) {

                // remove old keys
                // set 1.2.3.4
                // set 1.2.3.4.5
                // set 1 - will keep previous 2 records in database, and that's bad =)
                $provider->forget($key, $server, $database);


                // Try to insert a pair of key => value to DB.
                // In case of exception - update them.
                // This code should be replaced with insert_with_update method after its being implemented
                // See http://laravel.uservoice.com/forums/175973-laravel-4/suggestions/3535821-provide-support-for-bulk-insert-with-update-such-


                $value = json_encode($value);

                $list = DB::table($table);

                $database = is_null($database) ? self::database : $database;

                $server = is_null($server) ? self::server : $server;

                $insert = array(
                    'key'      => $key,
                    'value'    => $value,
                    'database' => $database,
                    'server'   => $server,
                );

                $result = $list->where('key', $key)->where('database', '=', $database)->where('server', '=', $server)->get();

                if (empty($result)) {
                    try {
                        $list->insert($insert);
                    } catch (\Exception $e) {
                        throw new SaveException("Cannot insert to database: " . $e->getMessage());
                    }
                } else {
                    try {
                        $list->where('key', $key)->where('database', '=', $database)->where('server', '=', $server)->update(array('value' => $value));
                    } catch (\Exception $e) {
                        throw new SaveException("Cannot save to database: " . $e->getMessage());
                    }
                }
            }
        );
    }


    /**
     * Remove item from the database
     *
     * @param string $key
     * @param int    $server
     * @param int    $database
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function forget($key, $server = null, $database = null) {

        try {
            $list = DB::table($this->table);

            $database = is_null($database) ? self::database : $database;

            $list = $list->where('database', '=', $database);

            if (!is_null($server)) {
                $list = $list->where('server', '=', $server);
            }

            $list->where('key', 'LIKE', $key . '.%')->delete();

            $list->where('key', 'LIKE', $key)->delete();

        } catch (\Exception $e) {

            throw new SaveException("Cannot remove item from database: " . $e->getMessage());

        }
    }

    /**
     * Clear the table with settings
     *
     * @param int $database
     *
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function clear($database = null) {

        try {
            $database = is_null($database) ? self::database : $database;

            DB::table($this->table)->where('database', '=', $database);

        } catch (\Exception $e) {

            throw new SaveException("Cannot clear database: " . $e->getMessage());

        }

    }

    /**
     * Clear the table with settings
     * @return void
     *
     * @throws Exceptions\SaveException
     */
    public function clearAll() {
        try {

            DB::table($this->table)->truncate();

        } catch (\Exception $e) {

            throw new SaveException("Cannot clear database: " . $e->getMessage());

        }
    }


    /**
     * Return query builder with list of settings from database
     *
     * @param null $wildcard
     *
     * @return $this|\Illuminate\Database\Query\Builder
     */
    public function listDb($wildcard = null) {

        $query = DB::table($this->table);
        if (!empty($wildcard)) {
            $query = $query->where('key', 'LIKE', $wildcard . '%');
        }

        return $query;

    }


}
