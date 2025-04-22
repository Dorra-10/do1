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
    Schema::table('exports', function (Blueprint $table) {
        $table->softDeletes(); // Ajoute la colonne 'deleted_at' pour le soft delete
    });
}

public function down()
{
    Schema::table('exports', function (Blueprint $table) {
        $table->dropSoftDeletes(); // Permet de revenir en arrière
    });
}

};
