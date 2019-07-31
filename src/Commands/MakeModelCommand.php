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
        if (call_user_func([$this->getGrandparentClass(), 'handle']) === false && ! $this->option('force')) {
            return false;
        }

        if ($this->option('all')) {
            $this->input->setOption('factory', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('resource', true);
            $this->input->setOption('policy', true);
            $this->input->setOption('requests', true);
            $this->input->setOption('views', true);
        }

        if ($this->option('factory')) {
            $this->createFactory();
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        if ($this->option('controller') || $this->option('resource')) {
            $this->createController();
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
        ];

        if ($this->option('resource')) {
            if ($this->option('views')) {
                $options['--views'] = true;
            }

            if ($this->option('requests')) {
                $options['--requests'] = true;
            }

            if ($this->option('policy')) {
                $options['--policy'] = true;
            }
        }

        $this->call('make:controller', $options);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration, factory, resource controller, policy, request classes and views for the model'],

            ['policy', 'P', InputOption::VALUE_NONE, 'Create a new policy for the model if a resource controller is created'],

            ['requests', 'R', InputOption::VALUE_NONE, 'Create new request files for the model if a resource controller is created'],

            ['views', null, InputOption::VALUE_NONE, 'Create new view files for the model if a resource controller is created'],
        ];

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get the class name of the grandparent class.
     *
     * @return string
     */
    protected function getGrandparentClass()
    {
        return get_parent_class(
            get_parent_class($this)
        );
    }
}
