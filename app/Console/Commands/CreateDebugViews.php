<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateDebugViews extends Command
{
    protected $signature = 'app:create-debug-views';

    protected $description = 'Create all MySQL debug views';

    public function handle(): int
    {
        $namespace = 'App\\DebugViews\\';
        $path = app_path('DebugViews');

        foreach (File::files($path) as $file) {
            $className = $namespace.$file->getFilenameWithoutExtension();

            if (is_subclass_of($className, $namespace.'BaseDebugView')) {
                $className::create();
            }
        }

        return Command::SUCCESS;
    }
}
