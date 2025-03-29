<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameFileToPathInDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('file', 'path');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->renameColumn('path', 'file');
        });
    }
}
