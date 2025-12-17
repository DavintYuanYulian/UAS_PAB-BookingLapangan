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
        Schema::table('field_schedules', function (Blueprint $table) {
            // $table->foreignId('field_id')
            //     ->after('id')
            //     ->nullable();
            $table->foreign('field_id')
                ->references('id')
                ->on('fields')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('field_schedules', function (Blueprint $table) {
            // $table->dropColumn('field_id');
            $table->dropForeign(['field_id']);
        });
    }
};
