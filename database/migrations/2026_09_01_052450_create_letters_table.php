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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->unsignedInteger('sequence_number');
            $table->string('branch_code', 30);
            $table->string('branch_name')->nullable();
            $table->string('target_code', 100);
            $table->string('month_roman', 10);
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->string('subject');
            $table->text('purpose')->nullable();
            $table->string('archive_location')->nullable();
            $table->string('requestor_department')->nullable();
            $table->string('requestor_position')->nullable();
            $table->string('requestor_name');
            $table->string('requestor_email')->nullable();
            $table->string('requestor_phone', 50)->nullable();
            $table->timestamps();

            $table->index(['branch_code', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
