<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            if (! Schema::hasColumn('admins', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            if (Schema::hasColumn('admins', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
