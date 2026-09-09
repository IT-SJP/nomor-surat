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
        Schema::table('letters', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('branch_id')->constrained('letters')->cascadeOnDelete();
            $table->unsignedInteger('sub_number')->nullable()->after('sequence_number');

            $table->index(['parent_id', 'sub_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'sub_number']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('sub_number');
        });
    }
};
