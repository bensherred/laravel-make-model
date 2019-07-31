<?php

namespace BenSherred\MakeModel\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeControllerCommand extends ControllerMakeCommand
{
    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        if (! $this->option('invokable')) {
            $usesModel = $this->option('parent') || $this->option('model');

            if ($usesModel && $this->option('policy')) {
                $this->createPolicy();
                $replace = $this->buildPolicyReplacements($replace);
            }

            if ($usesModel || $this->option('resource')) {
                if ($this->option('requests')) {
                    $this->createRequests();
                    $replace = $this->buildRequestsReplacements($replace);
                }

                if ($this->option('views')) {
                    $this->createViews();
                    $replace = $this->buildViewsReplacements($replace);
                }
            }
        }

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace),
            array_values($replace),
            call_user_func([$this->getGrandparentClass(), 'buildClass'], $name)
        );
    }

    /**
     * Build the policy replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildPolicyReplacements(array $replace)
    {
        return $replace;
    }

    /**
     * Build the requests replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildRequestsReplacements(array $replace)
    {
        return $replace;
    }

    /**
     * Build the views replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildViewsReplacements(array $replace)
    {
        return $replace;
    }

    /**
     * Create a policy for the model.
     *
     * @return void
     */
    protected function createPolicy()
    {
        $policy = Str::studly(class_basename(last(explode('\\', $this->option('model')))));

        $this->call('make:policy', [
            'name' => "{$policy}Policy",
        ]);
    }

    /**
     * Create request files for the model.
     *
     * @return void
     */
    protected function createRequests()
    {
        $model = $this->getBaseClassName(
            Str::studly(class_basename($this->option('model')))
        );

        $this->call('make:request', [
            'name' => "{$model}/StoreRequest",
        ]);

        $this->call('make:request', [
            'name' => "{$model}/UpdateRequest",
        ]);
    }

    /**
     * Create the views for the model.
     *
     * @return void
     */
    protected function createViews()
    {
        $views = ['index', 'create', 'show', 'edit'];
        $model = $this->getBaseClassName(
            strtolower(Str::studly(class_basename($this->option('model'))))
        );

        foreach ($views as $view) {
            $this->call('make:view', [
                'name' => "{$model}/{$view}"
            ]);
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = null;

        if ($this->option('parent')) {
            $stub = '/stubs/controller.nested.stub';
        } elseif ($this->option('model')) {
            $stub = '/stubs/controller.model.stub';
        } elseif ($this->option('invokable')) {
            $stub = '/stubs/controller.invokable.stub';
        } elseif ($this->option('resource')) {
            $stub = '/stubs/controller.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = '/stubs/controller.api.stub';
        } elseif ($this->option('api') && ! is_null($stub) && ! $this->option('invokable')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        if (! is_null($stub) && ! $this->option('invokable')) {
            $usesModel = $this->option('parent') || $this->option('model');

            if ($usesModel && $this->option('policy')) {
                $stub = str_replace('.stub', '.policy.stub', $stub);
            }
            if (($usesModel || $this->option('resource')) && $this->option('views')) {
                $stub = str_replace('.stub', '.views.stub', $stub);
            }
        }

        $stub = $stub ?? '/stubs/controller.plain.stub';

        return __DIR__.$stub;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            ['policy', 'P', InputOption::VALUE_NONE, 'Create a new policy for the model controller'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Create new request files for the model controller'],
            ['views', null, InputOption::VALUE_NONE, 'Create new view files for the model controller'],
        ];

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get the class name without "Controller".
     *
     * @param  string  $name
     * @return string
     */
    protected function getBaseClassName($name)
    {
        if (strpos('Controller', $name) === false) {
            return $name;
        }

        return substr($name, 0, strlen($name) - strlen('Controller'));
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
