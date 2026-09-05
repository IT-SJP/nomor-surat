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
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
        });

        // Backfill existing letters with matching branch
        $branches = DB::table('branches')->get();
        foreach ($branches as $branch) {
            DB::table('letters')
                ->whereNull('branch_id')
                ->where(function ($q) use ($branch) {
                    $hasCondition = false;
                    if (! empty($branch->branch_code)) {
                        $q->where('branch_code', $branch->branch_code);
                        $hasCondition = true;
                    }
                    if (! empty($branch->hr_code)) {
                        $method = $hasCondition ? 'orWhere' : 'where';
                        $q->{$method}('branch_code', $branch->hr_code);
                        $hasCondition = true;
                    }
                    if (! empty($branch->name)) {
                        $method = $hasCondition ? 'orWhere' : 'where';
                        $q->{$method}('branch_name', $branch->name);
                    }
                })
                ->update(['branch_id' => $branch->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
