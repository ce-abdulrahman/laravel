<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tajweed_rules', function (Blueprint $table) {
            $table->string('name_ku')->nullable()->after('name');
            $table->string('name_ar')->nullable()->after('name_ku');
            $table->text('description_ku')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tajweed_rules', function (Blueprint $table) {
            $table->dropColumn(['name_ku', 'name_ar', 'description_ku']);
        });
    }
};
