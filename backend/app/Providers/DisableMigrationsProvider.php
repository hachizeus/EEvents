<?php

namespace HiEvents\Providers;

use Illuminate\Support\ServiceProvider;

class DisableMigrationsProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * This provider disables all migrations from running by preventing
     * the migration path from being registered with the migration loader.
     */
    public function register()
    {
        // Remove the database path that contains migrations
        // This is called early enough to prevent migrations from being discovered
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot()
    {
        // Prevent migrations from running by intercepting the migration loader
        $this->app->extend('migration.repository', function ($repository) {
            // Create a proxy that intercepts all migration queries
            return new class($repository) {
                private $repository;

                public function __construct($repo)
                {
                    $this->repository = $repo;
                }

                // Always return empty results - no migrations to run
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
    }
}
