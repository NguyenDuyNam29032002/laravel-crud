<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeEnumCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:enum {enum}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name        = $this->argument('enum');
        $getStubPath = base_path('stubs/enums.stub');
        if (!File::exists($getStubPath)) {
            $this->error('Stub file not found');

            return;
        }

        $stub        = File::get($getStubPath);
        $enumContent = str_replace('{{class}}', $name, $stub);
        $enumPath    = app_path('Enums/' . $name . '.php');
        File::put($enumPath, $enumContent);
        $this->info('Enum created successfully');
    }
}
