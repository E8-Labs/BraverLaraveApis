<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {

		$table->integer('menuid',11);
		$table->string('menutitle',40);
        $table->string('menuDisplayName',100);
		$table->string('menuimage',50);
		$table->timestamp('dateadded')->useCurrent();
		$table->string('menuheader',40);

        });
    }

    public function down()
    {
        Schema::dropIfExists('menu');
    }
}