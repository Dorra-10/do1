<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessesTable extends Migration
 {
    public function up()
    {
        Schema::create('accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // Clé étrangère vers Project
            $table->foreignId('document_id')->constrained()->onDelete('cascade'); // Clé étrangère vers Document
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Clé étrangère vers User
            $table->enum('permission', ['read', 'write'])->default('read'); // Type d'accès
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accesses');
    }
};