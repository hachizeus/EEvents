<?php

namespace HiEvents\Providers;

use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class NoOpMigrationRepository implements MigrationRepositoryInterface
{
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
    public function getMigrationsByBatch($batch) { return []; }
    public function getLast() { return []; }
    public function createRepository() { return true; }
    public function repositoryExists() { return true; }
    public function deleteRepository() { return true; }
}

class DisableMigrationsProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     * This provider disables all migrations from running by providing
     * a no-op migration repository.
     */
    public function register()
    {
        // Override the migration repository with a no-op implementation
        $this->app->bind(
            MigrationRepositoryInterface::class,
            NoOpMigrationRepository::class
        );
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot()
    {
        // No additional bootstrap needed
    }
}
