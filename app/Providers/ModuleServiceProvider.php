<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $modulesPath = base_path('Modules');

        $directories = File::directories($modulesPath);

        foreach ($directories as $modulePath) {
            $configPath = $modulePath . '/module.json';

            if (!File::exists($configPath)) {
                continue;
            }

            $config = json_decode(File::get($configPath), true);

            if (!($config['enabled'] ?? false)) {
                continue;
            }

            // Load routes
            $routePath = $modulePath . '/Routes/web.php';
            if (File::exists($routePath)) {
                Route::middleware(['web'])
                    ->group($routePath);
            }
        }
    }
}
