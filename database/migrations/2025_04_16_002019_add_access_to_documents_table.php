<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccessToDocumentsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('documents', 'access')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->string('access')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('access');
        });
    }
}
