<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('semantic_index_status', 32)->nullable()->after('thumbnail');
            $table->timestamp('semantic_indexed_at')->nullable()->after('semantic_index_status');
            $table->text('semantic_index_last_error')->nullable()->after('semantic_indexed_at');
        });

        if (Schema::hasTable('post_semantic_chunks')) {
            DB::statement('
                UPDATE posts p
                INNER JOIN (
                    SELECT post_id, MAX(indexed_at) AS last_indexed
                    FROM post_semantic_chunks
                    GROUP BY post_id
                ) c ON c.post_id = p.id
                SET p.semantic_index_status = \'indexed\',
                    p.semantic_indexed_at = c.last_indexed,
                    p.semantic_index_last_error = NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'semantic_index_status',
                'semantic_indexed_at',
                'semantic_index_last_error',
            ]);
        });
    }
};
