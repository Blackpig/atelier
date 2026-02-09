<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablePrefix = config('atelier.table_prefix', 'atelier_');

        Schema::table($tablePrefix . 'block_attributes', function (Blueprint $table) {
            $table->string('collection_name')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('atelier.table_prefix', 'atelier_');

        Schema::table($tablePrefix . 'block_attributes', function (Blueprint $table) {
            $table->dropColumn('collection_name');
        });
    }
};
