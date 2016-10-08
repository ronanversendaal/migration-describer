<?php

namespace Ronanversendaal\MigrationDescriber;

use Illuminate\Support\ServiceProvider;
use Ronanversendaal\MigrationDescriber\Console\Commands\Describe;

class MigrationDescriberServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //

        $this->registerCommands();
    }

    public function registerCommands(){

        $this->registerDescribeCommand();
    }

    private function registerDescribeCommand()
    {
        $this->commands('command.migrate.describe');
        $this->app->singleton('command.migrate.describe', function($app) {


            return new Describe($app->migrator);
        });

    }

    public function provides()
    {
        return [

            'command.migrate.describe'
        ];
    }
}
