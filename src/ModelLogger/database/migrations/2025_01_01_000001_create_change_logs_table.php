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
        Schema::create('model_logger', function (Blueprint $table) {
            $table->id();
            $table->uuid('hash');
            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();
            $table->string('parent_type')->nullable();
            $table->string('parent_id')->nullable();
            $table->string('logger');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_logger');
    }
};
