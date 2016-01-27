<?php namespace Terbium\DbConfig;

use Illuminate\Support\ServiceProvider;
use Illuminate\Config;

class DbConfigServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../../config/config.php' => config_path('db-config.php'),
        ], 'config');

        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/../../../resources/migrations/create_settings_table.php.temp' => database_path('migrations/'.$timestamp.'_create_settings_table.php')
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        // merge & publihs config
        $configPath = __DIR__ . '/../../../config/config.php';
        $this->mergeConfigFrom($configPath, 'db-config');
        $this->publishes([$configPath => config_path('db-config.php')]);

        $this->app['db-config'] = $this->app->share(function($app) {

            $config = $app['config']->get('db-config');

            $dbProvider = new DbProvider($config);

            return new DbConfig($app['config'], $dbProvider);
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {

        return array();
    }

}