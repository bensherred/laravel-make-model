<?php

namespace BenSherred\MakeModel;

use BenSherred\MakeModel\Commands\MakeControllerCommand;
use BenSherred\MakeModel\Commands\MakeModelCommand;
use BenSherred\MakeModel\Commands\MakeViewCommand;
use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class MakeModelServiceProvider extends ArtisanServiceProvider
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
        parent::register();

        $this->app->singleton('command.controller.make', function ($app) {
            return new MakeControllerCommand($app['files']);
        });

        $this->app->singleton('command.model.make', function ($app) {
            return new MakeModelCommand($app['files']);
        });

        $this->commands([
            MakeViewCommand::class,
        ]);
    }
}
