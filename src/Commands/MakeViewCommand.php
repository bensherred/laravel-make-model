<?php

namespace BenSherred\MakeModel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:view {name : The name of the file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view file';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $view = $this->getNameInput();

        $path = $this->getPath($view);

        $this->createDir($path);

        if (File::exists($path)) {
            $this->error('View already exists!');

            return;
        }

        File::put($path, File::get($this->getStub()));

        $this->info('View created successfully.');
    }

    /**
     * Get the full path of the view.
     *
     * @param  string  $view
     * @return string
     */
    protected function getPath($view)
    {
        $file = str_replace('.', '/', $view).'.blade.php';

        $path = 'resources/views/'.$file;

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

        if (! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Get the desired file name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/view.stub';
    }
}
