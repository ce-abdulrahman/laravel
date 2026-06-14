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
        Schema::create('backup_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->string('status', 50)->default('active'); // active, deprecated
            $table->timestamps();
        });

        Schema::create('backup_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translatable_id'); // references backup_versions.id
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('field', 50);
            $table->text('value');
            $table->timestamps();

            $table->unique(['translatable_id', 'language_id', 'field'], 'backup_trans_unique');
        });

        Schema::create('user_backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('backup_type', 50); // manual, auto
            $table->string('storage_type', 50); // local, s3, etc.
            $table->string('backup_version', 50);
            $table->string('file_name', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('checksum_sha256', 64);
            $table->boolean('is_encrypted')->default(false);
            $table->string('status', 50)->default('pending'); // pending, success, failed
            $table->string('device_type', 100)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('app_version', 50)->nullable();
            $table->boolean('is_processing')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('backup_restore_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('backup_id')->nullable()->constrained('user_backups')->nullOnDelete();
            $table->string('restore_type', 50); // local_file, cloud
            $table->string('status', 50)->default('pending'); // pending, success, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_restore_logs');
        Schema::dropIfExists('user_backups');
        Schema::dropIfExists('backup_translations');
        Schema::dropIfExists('backup_versions');
    }
};
