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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('document_name');
            $table->dateTime('last_viewed_at')->nullable();
            $table->dateTime('last_modified_at')->nullable();
            $table->unsignedBigInteger('last_modified_by')->nullable(); // ID de l’utilisateur
            $table->timestamps();
    
            // Clés étrangères
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('last_modified_by')->references('id')->on('users')->onDelete('set null');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
