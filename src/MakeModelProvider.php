<?php

namespace BenSherred\MakeModel;

use BenSherred\MakeModel\Commands\MakeModelCommand;
use Illuminate\Foundation\Providers\ArtisanServiceProvider;

class MakeModelProvider extends ArtisanServiceProvider
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

        $this->app->singleton('command.model.make', function ($app) {
            return new MakeModelCommand($app['files']);
        });
    }
}
