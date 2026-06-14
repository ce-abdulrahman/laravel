<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('avatar')->nullable()->after('email');
            $table->string('gender')->nullable()->after('avatar');
            $table->unsignedInteger('birth_year')->nullable()->after('gender');
            $table->foreignId('country_id')->nullable()->after('birth_year')->constrained('countries')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->after('country_id')->constrained('provinces')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->softDeletes()->after('updated_at');
        });

        // Safe conversion of legacy users
        $users = DB::table('users')->whereNull('username')->get();
        foreach ($users as $user) {
            $emailParts = explode('@', $user->email);
            $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $emailParts[0]));
            if (empty($baseUsername)) {
                $baseUsername = 'user_' . $user->id;
            }
            $username = $baseUsername;
            $count = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = $baseUsername . '_' . $count;
                $count++;
            }
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }

        // Enforce required username constraint for all futures
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'username',
                'avatar',
                'gender',
                'birth_year',
                'country_id',
                'province_id',
                'last_login_at',
                'deleted_at'
            ]);
        });
    }
};
