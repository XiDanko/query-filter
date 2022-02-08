<?php

namespace XiDanko\QueryFilter;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateFilterCommand extends Command
{
    protected $signature = 'make:filter {name : The name of the class}';
    protected $description = 'Create a new query filter class';

    public function __construct()
    {
        parent::__construct();

    }

    public function handle()
    {
        File::ensureDirectoryExists(app_path('Filters'));
        $filePath = app_path('Filters\\' . $this->argument('name') . '.php');
        if (File::exists($filePath)) return $this->error("Filter already exists!");
        $fileContent = str_replace('$className', $this->argument('name'), file_get_contents(__DIR__ . '\FilterClass.stub'));
        $handler = fopen($filePath, 'w') or die("Unable to open file!");
        fwrite($handler, $fileContent);
        fclose($handler);
        $this->info('Filter created successfully.');
    }
}
