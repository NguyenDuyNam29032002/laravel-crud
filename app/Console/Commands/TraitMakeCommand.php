<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TraitMakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:trait {trait}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected string $type = 'Trait';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name     = $this->argument('trait');
        $stubPath = base_path('stubs/traits.stub');
        if (!File::exists($stubPath)) {
            $this->error('Stub file not found');

            return;
        }

        $stub         = File::get($stubPath);
        $traitContent = str_replace('{{class}}', $name, $stub);
        $traitPath    = app_path('Traits/' . $name . '.php');
        File::put($traitPath, $traitContent);
        $this->info('Traits created successfully');
    }
}
