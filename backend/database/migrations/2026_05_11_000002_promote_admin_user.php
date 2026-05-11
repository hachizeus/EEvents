<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminEmail = 'elitjohnsdigital@gmail.com';

        $user = DB::table('users')->where('email', $adminEmail)->first();

        if (!$user) {
            return;
        }

        // Update role in account_users pivot table
        DB::table('account_users')
            ->where('user_id', $user->id)
            ->update(['role' => 'SUPERADMIN']);
    }

    public function down(): void
    {
        // Intentionally left empty - don't demote on rollback
    }
};
