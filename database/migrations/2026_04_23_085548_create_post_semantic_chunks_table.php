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
        Schema::create('post_semantic_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->longText('chunk_text');
            $table->string('content_hash', 64);
            $table->string('embedding_model')->nullable();
            $table->unsignedSmallInteger('embedding_dimensions')->nullable();
            $table->string('vector_point_id')->nullable()->unique();
            $table->boolean('is_indexed')->default(false)->index();
            $table->timestamp('indexed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'chunk_index']);
            $table->index(['post_id', 'is_indexed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_semantic_chunks');
    }
};
