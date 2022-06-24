<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsercardTable extends Migration
{
    public function up()
    {
        Schema::create('usercard', function (Blueprint $table) {

		$table->integer('cardid',11);
		$table->string('cardnumber',30);
		$table->string('cvc',5);
		$table->string('cardholdername',30);
		$table->string('expirydate',10);
		$table->string('stripecardid',50);
		$table->string('cardbrand',30);
		$table->string('userid',50);
		$table->timestamp('dateadded')->useCurrent();

        });
    }

    public function down()
    {
        Schema::dropIfExists('usercard');
    }
}