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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_type');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('file');
            $table->timestamp('date_added')->useCurrent(); // Utilise la date et l'heure actuelle par défaut
            $table->string('access')->nullable(); // Le champ "access" est nullable
            $table->unsignedBigInteger('type_id')->nullable()->after('file_type');
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
