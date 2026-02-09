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
            $table->integer('collection_index')->nullable()->after('collection_name');
            $table->index(['block_id', 'collection_name', 'collection_index'], 'atelier_attrs_collection_index');
        });
    }

    public function down(): void
    {
        $tablePrefix = config('atelier.table_prefix', 'atelier_');

        Schema::table($tablePrefix . 'block_attributes', function (Blueprint $table) {
            $table->dropIndex('atelier_attrs_collection_index');
            $table->dropColumn('collection_index');
        });
    }
};
