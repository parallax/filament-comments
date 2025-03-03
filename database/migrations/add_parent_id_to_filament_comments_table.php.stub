<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('filament-comments.table_name', 'filament_comments'), function (Blueprint $table) {
            $table->foreignId('parent_id')->after('id')->nullable()->constrained(config('filament-comments.table_name', 'filament_comments'))->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(config('filament-comments.table_name', 'filament_comments'), function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
}; 