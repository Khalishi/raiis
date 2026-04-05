<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->string('recording_object_key', 2048)->nullable()->after('summary_script');
            $table->text('recording_url')->nullable()->after('recording_object_key');
        });
    }

    public function down(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropColumn(['recording_object_key', 'recording_url']);
        });
    }
};
