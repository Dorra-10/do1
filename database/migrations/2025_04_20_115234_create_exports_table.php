<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('type_id')->unsigned()->nullable();
            $table->string('file_type');
            $table->bigInteger('project_id')->unsigned();
            $table->string('path');
            $table->timestamps();
            $table->string('owner')->nullable();
            $table->string('company')->nullable();
            $table->text('description')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('exports');
    }
    
};
