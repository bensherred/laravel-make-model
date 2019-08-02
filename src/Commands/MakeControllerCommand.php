<?php

namespace BenSherred\MakeModel\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeControllerCommand extends ControllerMakeCommand
{
    /**
     * Execute the console command.
     *
     * @return void|bool
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        if ($this->option('requests')) {
            $this->createRequests();
        }

        if ($this->option('policy')) {
            $this->createPolicy();
        }

        if ($this->option('views') && ! $this->option('api')) {
            $this->createViews();
        }
    }

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

        $replace = $this->buildRequestsReplacements($replace);

        if (! $this->option('invokable')) {
            $hasResource = $this->option('parent') || $this->option('model') || $this->option('resource');

            if ($hasResource && $this->option('views') && ! $this->option('api')) {
                $replace = $this->buildViewsReplacements($replace);
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
     * Build the requests replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildRequestsReplacements(array $replace)
    {
        $controller = Str::studly($this->getBaseClassName());
        $requestPath = str_replace('/', '\\', $controller);

        if ($this->option('requests')) {
            $replace['use Illuminate\\Http\\Request;'] = '';

            $replace['DummyStoreRequestClass'] = 'StoreRequest';
            $replace['DummyFullStoreRequestClass;'] = "App\\Http\\Requests\\{$requestPath}\\StoreRequest;";
            $replace['DummyFullStoreRequestMethodClass'] = 'StoreRequest';

            $replace['DummyUpdateRequestClass'] = 'UpdateRequest';
            $replace['DummyFullUpdateRequestClass;'] = "App\\Http\\Requests\\{$requestPath}\\UpdateRequest;";
            $replace['DummyFullUpdateRequestMethodClass'] = 'UpdateRequest';

        } else {
            $replace['DummyStoreRequestClass'] = 'Request';
            $replace['DummyFullStoreRequestClass;'] = 'Illuminate\Http\Request;';
            $replace['DummyFullStoreRequestMethodClass'] = 'Request';

            $replace['DummyUpdateRequestClass'] = 'Request';
            $replace["use DummyFullUpdateRequestClass;\n"] = '';
            $replace['DummyFullUpdateRequestMethodClass'] = 'Request';
        }

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
        $controller = strtolower(Str::studly($this->getBaseClassName()));
        $viewPath = str_replace('/', '.', $controller);

        return array_merge($replace, [
            'DummyViewPath' => $viewPath,
        ]);
    }

    /**
     * Create a policy for the model.
     *
     * @return void
     */
    protected function createPolicy()
    {
        $policy = $this->option('policy');

        if ($policy != '' && class_exists("\\App\\Policies\\{$policy}")) {
            return;
        }

        $model = Str::studly($this->getModelName());

        $this->call('make:policy', [
            'name' => "{$model}Policy",
            '--model' => $model,
        ]);
    }

    /**
     * Create request files for the model.
     *
     * @return void
     */
    protected function createRequests()
    {
        $controller = Str::studly($this->getBaseClassName());

        $this->call('make:request', [
            'name' => "{$controller}/StoreRequest",
        ]);

        $this->call('make:request', [
            'name' => "{$controller}/UpdateRequest",
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
        $controller = strtolower(Str::studly($this->getBaseClassName()));

        foreach ($views as $view) {
            $this->call('make:view', [
                'name' => "{$controller}/{$view}"
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
            $hasResource = $this->option('parent') || $this->option('model') || $this->option('resource');

            if ($this->option('model') && $this->option('policy')) {
                $stub = str_replace('.stub', '.policy.stub', $stub);
            }

            if ($hasResource && $this->option('views') && ! $this->option('api')) {
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
            ['policy', 'P', InputOption::VALUE_OPTIONAL, 'Create a new policy'],
            ['requests', 'R', InputOption::VALUE_NONE, 'Create new request classes'],
            ['views', null, InputOption::VALUE_NONE, 'Create new view files if the controller is not for the API'],
        ];

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get the path with the name of the class without the controller suffix.
     *
     * @return string
     */
    protected function getBaseClassName()
    {
        return preg_replace('/Controller$/', '', $this->argument('name'));
    }

    /**
     * Get the model class name with the path.
     *
     * @return string
     */
    protected function getModelName()
    {
        if ($this->option('model')) {
            return str_replace(['App\\', 'Model\\'], ['', ''], $this->option('model'));
        }

        return $this->getBaseClassName();
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
