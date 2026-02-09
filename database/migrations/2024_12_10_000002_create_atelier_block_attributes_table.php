<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('atelier.table_prefix', 'atelier_') . 'block_attributes';
        $blocksTable = config('atelier.table_prefix', 'atelier_') . 'blocks';
        
        Schema::create($tableName, function (Blueprint $table) use ($blocksTable) {
            $table->id();
            $table->foreignId('block_id')
                ->constrained($blocksTable)
                ->cascadeOnDelete();
            $table->string('key'); // Field name
            $table->text('value')->nullable(); // Actual value
            $table->string('type')->default('string'); // For casting
            $table->string('locale', 5)->nullable(); // en, fr, etc
            $table->boolean('translatable')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['block_id', 'key', 'locale'], 'atelier_attrs_block_key_locale_index');
            $table->index(['block_id', 'translatable'], 'atelier_attrs_block_translatable_index');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists(config('atelier.table_prefix', 'atelier_') . 'block_attributes');
    }
};
