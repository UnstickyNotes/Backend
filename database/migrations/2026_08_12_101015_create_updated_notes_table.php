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
        Schema::create('updated_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('originalNote_id')->constrained('notes','id')->cascadeOnDelete();
            $table->foreignId('updatedNote_id')->constrained('notes', 'id')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updated_notes');
    }
};
