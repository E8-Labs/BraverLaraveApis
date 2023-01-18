<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductmediaTable extends Migration
{
    public function up()
    {
        Schema::create('productmedia', function (Blueprint $table) {

		$table->integer('mediaid');
		$table->string('mediatitle',50);
		$table->string('mediatype',100);
		$table->string('mediaurl',100);
		$table->string('productid',100);
		$table->double('height')->default(0);
		$table->double('width')->default(0);
		$table->timestamp('dateadded')->useCurrent();
		$table->string('baseUrl',10)->default('Old');
		
		$table->integer('sorter')->default(0);

        });
    }

    public function down()
    {
        Schema::dropIfExists('productmedia');
    }
}