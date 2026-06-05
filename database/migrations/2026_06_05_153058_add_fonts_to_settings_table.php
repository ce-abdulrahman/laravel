<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('font_ar')->default('Wafeq-Regular.otf');
            $table->string('font_ku')->default('3_NRT-Bd.ttf');
            $table->string('font_en')->default('PatuaOne-Regular.ttf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['font_ar', 'font_ku', 'font_en']);
        });
    }
};
