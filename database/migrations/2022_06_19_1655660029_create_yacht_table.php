<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYachtTable extends Migration
{
    public function up()
    {
        Schema::create('yacht', function (Blueprint $table) {

		$table->string('yachtid',20);
		$table->string('yachtname',50);
		$table->text('yachtdescription');
		$table->string('yachtprice',50)->nullable()->default('NULL');
		$table->string('price_full_day',20)->nullable()->default('NULL');
		$table->string('yachtaddress',80);
		$table->string('yachtweburl',100);
		$table->string('yachtphone',20);
		$table->string('addedby',50);
		$table->timestamp('dateadded')->useCurrent();
		$table->boolean('featured')->default(false);
		$table->boolean('deleted')->default(false);
		$table->string('type',50)->default('Yacht');
		$table->string('seatingimage',100);
		$table->string('instaurl',100);
		$table->string('eventdate',20);
		$table->string('eventtime',20);
		$table->string('eventenddate',50)->nullable()->default('NULL');
		$table->string('eventendtime',50)->nullable()->default('NULL');
		;
		;

        });
    }

    public function down()
    {
        Schema::dropIfExists('yacht');
    }
}