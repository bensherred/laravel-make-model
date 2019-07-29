<?php

namespace BenSherred\MakeModel\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeModelCommand extends ModelMakeCommand
{
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        // logic for all the default commands and our commands
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

            ['views', 'VV', InputOption::VALUE_NONE, 'Create new view files for the model'],
        ];

        return array_merge(parent::getOptions(), $options);
    }
}