<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class DisableMigrationsProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * This provider disables all migrations from running.
     */
    public function register()
    {
        // Prevent Laravel from discovering and running migrations
        $this->app->singleton('migration.repository', function () {
            return new class {
                // Return empty array - no migrations to run
                public function getRan() { return []; }
                public function getMigrationBatches() { return []; }
                public function getMigrations($steps = 0) { return []; }
                public function getLastBatchNumber() { return 0; }
                public function getNextBatchNumber() { return 1; }
                public function log($file, $batch) { return true; }
                public function delete($migration) { return true; }
                public function up($file, $batch) { return true; }
                public function down($file) { return true; }
                public function rollback($migrations, $pretend = false) { return true; }
                public function has($file) { return false; }
            };
        });

        // Prevent migration publishing
        $this->loadMigrationsFrom([]);
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot()
    {
        // Disable migration commands visibility
        if ($this->app->runningInConsole()) {
            // Artisan commands won't show migration commands
            $this->commands([]);
        }
    }
}
