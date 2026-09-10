<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->string('recommended_platform', 80)->nullable()->after('tip_note');
            $table->string('recommended_model', 80)->nullable()->after('recommended_platform');
        });

        Schema::table('journey_steps', function (Blueprint $table) {
            $table->string('recommended_platform', 80)->nullable()->after('tip_note');
            $table->string('recommended_model', 80)->nullable()->after('recommended_platform');
        });
    }

    public function down(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            $table->dropColumn(['recommended_platform', 'recommended_model']);
        });

        Schema::table('journey_steps', function (Blueprint $table) {
            $table->dropColumn(['recommended_platform', 'recommended_model']);
        });
    }
};
