<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {service-name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service with the specified';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name        = $this->argument('service-name');
        $getStubPath = base_path('stubs/service.stub');
        if (!File::exists($getStubPath)) {
            $this->error('File does not exist');

            return;
        }
        $stub           = File::get($getStubPath);
        $serviceContent = str_replace('{{class}}', $name, $stub);
        $servicePath    = app_path('Services/' . $name . '.php');
        File::put($servicePath, $serviceContent);
        $this->info('Service created successfully');
    }
}
