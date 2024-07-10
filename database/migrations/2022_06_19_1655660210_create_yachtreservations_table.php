<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYachtreservationsTable extends Migration
{
    public function up()
    {
        Schema::create('yachtreservations', function (Blueprint $table) {
       		$table->id();
			$table->string('reservationid',50);
			$table->timestamp('dateadded')->useCurrent();
			$table->string('reservedfor',50);
			$table->string('agenthandler',50);
			$table->string('reservationstatus',50);
			$table->string('reservationdate',30);
			$table->string('reservationtime',30);
			$table->string('yachtid',40);
			$table->string('transactionid',50);
			$table->string('amountpaid',30);
			$table->string('chatid',50);
			$table->string('paymentmethod',100);
			$table->string('customaddress',400);
			$table->string('refundid',100);
			$table->timestamp('refunddate')->nullable()->default('NULL');
			$table->string('cancelledby',100);
			$table->string('invoiceid',50);
			$table->text('invoicedescription');
			$table->text('reservationdescription');
			$table->string('reservationenddate',100);
			$table->string('reservationendtime',100);
			$table->integer('guests',5)->nullable();
			$table->integer('days',5)->nullable();

        });
    }

    public function down()
    {
        Schema::dropIfExists('yachtreservations');
    }
}