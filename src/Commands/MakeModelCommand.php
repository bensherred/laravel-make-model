<?php

namespace BenSherred\MakeModel\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Facades\File;
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

        if ($this->option('policy')) {
            $this->createPolicy();
        }

        if ($this->option('requests')) {
            $this->createRequests();
        }

        if ($this->option('views')) {
            $this->createViews();
        }
    }

    /**
     * Create a policy for the model.
     *
     * @return void
     */
    protected function createPolicy()
    {
        $policy = Str::studly(class_basename($this->argument('name')));

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
        $request = Str::studly(class_basename($this->argument('name')));

        $this->call('make:request', [
            'name' => "{$request}/StoreRequest",
        ]);

        $this->call('make:request', [
            'name' => "{$request}/UpdateRequest",
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
        $model = strtolower(Str::studly(class_basename($this->argument('name'))));

        foreach ($views as $view) {
            $path = $this->viewPath($view);

            $this->createDir($path);

            if (File::exists($path)) {
                $this->error("View {$model}/{$view}.blade.php already exists.");
                return;
            }

            File::put($path, $path);

            $this->info(ucfirst($view) . ' view created successfully.');
        }
    }

    /**
     * Get the full path of the view.
     *
     * @param  string  $view
     * @return string
     */
    protected function viewPath($view)
    {
        $model = strtolower(Str::studly(class_basename($this->argument('name'))));
        $file = str_replace('.', '/', $view) . '.blade.php';

        $path = 'resources/views/' . $model . '/' . $file;

        return $path;
    }

    /**
     * Create the directory.
     *
     * @param $path
     */
    protected function createDir($path)
    {
        $dir = dirname($path);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            ['policy', 'P', InputOption::VALUE_NONE, 'Create a new policy for the model'],

            ['requests', 'R', InputOption::VALUE_NONE, 'Create new request files for the model'],

            ['views', null, InputOption::VALUE_NONE, 'Create new view files for the model'],
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
