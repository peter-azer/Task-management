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
        Schema::table('boards', function (Blueprint $table) {
            if (!Schema::hasColumn('boards', 'archive')) {
                $table->boolean('archive')->default(false)->after('image_path');
            }
            if (!Schema::hasColumn('boards', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('archive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            if (Schema::hasColumn('boards', 'archived_at')) {
                $table->dropColumn('archived_at');
            }
            if (Schema::hasColumn('boards', 'archive')) {
                $table->dropColumn('archive');
            }
        });
    }
};
