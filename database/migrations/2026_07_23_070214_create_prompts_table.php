<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcategory_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('body');
            $table->text('tip_note')->nullable();
            $table->boolean('is_best')->default(false);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_free')->default(true);
            $table->string('status')->default('published');
            $table->unsignedInteger('copy_count')->default(0);
            $table->timestamps();

            $table->unique(['subcategory_id', 'slug']);
            $table->index(['status', 'is_best']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};
