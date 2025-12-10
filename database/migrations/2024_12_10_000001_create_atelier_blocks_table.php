<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('atelier.table_prefix', 'atelier_') . 'blocks', function (Blueprint $table) {
            $table->id();
            $table->morphs('blockable'); // blockable_type, blockable_id
            $table->string('block_type'); // Fully qualified class name
            $table->integer('position')->default(0);
            $table->uuid('uuid')->unique();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            
            $table->index(['blockable_type', 'blockable_id', 'position'], 'atelier_blocks_blockable_position_index');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists(config('atelier.table_prefix', 'atelier_') . 'blocks');
    }
};
