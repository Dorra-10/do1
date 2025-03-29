<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessToDocumentsTable extends Migration
{
    public function up()
    {
        // Ajoute la colonne 'access' à la table 'documents'
        Schema::table('documents', function (Blueprint $table) {
            $table->string('access')->nullable(); // Type de colonne selon tes besoins (string, text, etc.)
        });
    }

    public function down()
    {
        // Si la migration est annulée, on supprime la colonne 'access'
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('access');
        });
    }
}
