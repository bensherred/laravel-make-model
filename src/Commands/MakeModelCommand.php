<?php

namespace BenSherred\MakeModel\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeModelCommand extends ModelMakeCommand
{
    /**
     * Execute the console command.
     *
     * @return void|bool
     */
    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }

        if ($this->option('policy')) {
            $this->createPolicy();
        }
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $options = [
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') ? $modelName : null,
            '--views' => true,
            '--requests' => true,
        ];

        // If the developer does not want to create explicitly a policy with the model,
        // the command for making a controller will deal with it.
        if (! $this->option('policy')) {
            $options['--policy'] = true;
        }

        $this->call('make:controller', $options);
    }

    /**
     * Create a policy for the model.
     *
     * @return void
     */
    protected function createPolicy()
    {
        $model = Str::studly(class_basename($this->argument('name')));

        $this->call('make:policy', [
            'name' => "{$model}Policy",
            '--model' => $model,
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration, factory and resource controller with request classes, views and policy for the model'],

            ['policy', 'P', InputOption::VALUE_NONE, 'Create a new policy for the model'],

            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model with request classes, views and a policy'],

            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated controller should be a resource controller with request classes, views and a policy'],
        ];

        return array_merge(parent::getOptions(), $options);
    }
}
