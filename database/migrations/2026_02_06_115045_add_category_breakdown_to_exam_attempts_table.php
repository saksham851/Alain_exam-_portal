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
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->json('category_breakdown')->nullable()->after('status');
            // We can drop legacy columns if we are sure. User said "IG/DM jesa kuch hai he nhi hata do".
            // To be safe against rollback issues, we drop them. 
            // But let's check if they exist first to avoid errors.
             if (Schema::hasColumn('exam_attempts', 'ig_score')) {
                 $table->dropColumn(['ig_score', 'dm_score']);
             }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn('category_breakdown');
            $table->float('ig_score')->default(0);
            $table->float('dm_score')->default(0);
        });
    }
};
