<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InitialDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema already imported directly via phpMyAdmin
        // SQL files removed to prevent PostgreSQL syntax errors on MySQL
        // Migration is marked complete and does nothing
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
