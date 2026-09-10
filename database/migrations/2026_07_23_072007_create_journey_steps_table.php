<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journey_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prompt_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('step_number');
            $table->string('title');
            $table->string('goal')->nullable();
            $table->text('instructions')->nullable();
            $table->text('prompt_body');
            $table->text('tip_note')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->unique(['journey_id', 'step_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journey_steps');
    }
};
