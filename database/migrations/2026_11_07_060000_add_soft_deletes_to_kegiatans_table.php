<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            if (!Schema::hasColumn('kegiatans', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('kegiatans', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kegiatans', function (Blueprint $table) {
            if (Schema::hasColumn('kegiatans', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('kegiatans', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
