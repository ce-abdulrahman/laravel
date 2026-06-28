<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reciters', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('country')->nullable()->after('riwayah');
            $table->string('audio_base_url')->nullable()->after('image');
            $table->boolean('supports_ayah_audio')->default(false)->after('audio_base_url');
        });

        // Populate existing reciters if any
        $reciters = DB::table('reciters')->get();
        foreach ($reciters as $reciter) {
            $slug = Str::slug($reciter->name);
            $count = 1;
            $originalSlug = $slug;
            while (DB::table('reciters')->where('slug', $slug)->where('id', '!=', $reciter->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            DB::table('reciters')->where('id', $reciter->id)->update([
                'slug' => $slug,
                'audio_base_url' => 'https://audio.example.com/' . $slug . '/',
            ]);
        }

        // Add indexes for slug and is_active
        Schema::table('reciters', function (Blueprint $table) {
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reciters', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['slug']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['slug', 'country', 'audio_base_url', 'supports_ayah_audio']);
        });
    }
};
