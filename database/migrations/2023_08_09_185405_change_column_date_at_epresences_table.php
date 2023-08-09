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
        Schema::table('epresences', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->timestamp('time')->nullable()->after('is_approve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epresences', function (Blueprint $table) {
            $table->date('date')->nullable()->after('is_approve');
            $table->dropColumn('time');
        });
    }
};
